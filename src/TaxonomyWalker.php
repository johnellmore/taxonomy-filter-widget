<?php

namespace Ellmore\TaxonomyFilterWidget;

class TaxonomyWalker extends \Walker_Category
{

    /**
    * Start the element output.
    *
    * @see Walker::start_el()
    *
    * @since 2.1.0
    *
    * @param string $output   Passed by reference. Used to append additional content.
    * @param object $category Category data object.
    * @param int    $depth    Depth of category in reference to parents. Default 0.
    * @param array  $args     An array of arguments. @see wp_list_categories()
    * @param int    $id       ID of the current category.
    */
    public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0)
    {
        /** This filter is documented in wp-includes/category-template.php */
        $cat_name = apply_filters(
            'list_cats',
            esc_attr($category->name),
            $category
        );

        // Don't generate an element if the category name is empty.
        if (!$cat_name) {
            return;
        }

        $isCurrent = false;
        $isCurrentParent = false;

        if (!empty($args['current_category'])) {
            // 'current_category' can be an array, so we use `get_terms()`.
            $_current_terms = get_terms($category->taxonomy, array(
                'slug' => $args['current_category'],
                'hide_empty' => false,
            ));

            foreach ($_current_terms as $_current_term) {
                if ($category->term_id == $_current_term->term_id) {
                    $isCurrent = true;
                } elseif ($category->term_id == $_current_term->parent) {
                    $isCurrentParent = true;
                }
            }
        }

        $isParent = (bool)$args['has_children'] && $depth == 0;
        $title = '';

        // BUILD LINK URL

        // build initial list of filters
        $filters = array();
        if (count($_GET)) {
            // there are multiple terms in the URL that we're already filtering by
            $filters = $_GET;
        } else {
            $object = get_queried_object();
            if (is_a($object, 'WP_Term')) {
                // we're filtering by exactly one term--pull it out
                $filters[$object->taxonomy] = $object->slug;
            } else {
                // we have no terms to filter by
            }
        }

        // turn this taxonomy into an array
        if (!isset($filters[$category->taxonomy])) {
            $filters[$category->taxonomy] = array();
        } else {
            $filters[$category->taxonomy] = explode(',', $filters[$category->taxonomy]);
        }

        // add or remove this link from the list of filters
        if ($isCurrent) {
            // currently selected--REMOVE from the list of filters
            if (($key = array_search($category->slug, $filters[$category->taxonomy])) !== false) {
                // should evalute true, assuming WordPress is consistent
                unset($filters[$category->taxonomy][$key]); // remove the offending index
            }
        } else {
            // not currently selected--ADD to the list of filters
            $filters[$category->taxonomy][] = $category->slug;

            // if this term has a parent
            if ($category->parent) {
                // get the parent slug
                $parent = get_term($category->parent, $category->taxonomy);

                // if this term's parent is in the list, remove said parent from the list
                if (($key = array_search($parent->slug, $filters[$category->taxonomy])) !== false) {
                    unset($filters[$category->taxonomy][$key]);
                }
            }
        }

        // stringify this taxonomy filter back
        if (empty($filters[$category->taxonomy])) {
            unset($filters[$category->taxonomy]);
        } else {
            $filters[$category->taxonomy] = implode(',', $filters[$category->taxonomy]);
        }

        // put together the full link URL
        $queryString = http_build_query($filters);
        if (strlen($queryString)) {
            $link = '/?'.$queryString;
        } else {
            $link = get_post_type_archive_link($args['post_type']);
        }

        // ASSEMBLE TITLE HTML

        $title .= '<a href="';
        $title .= esc_url($link);
        $title .= '">';
        $title .= $isCurrent ? $args['checkbox_checked']: $args['checkbox_unchecked'];
        $title .= '<span class="title">';
        $title .= $cat_name;
        $title .= '</span>';
        $title .= '</a>';

        if (!empty($args['show_count'])) {
            $title .= ' (' . number_format_i18n($category->count) . ')';
        }

        $output .= "\t<li";
        $css_classes = array(
            'cat-item',
            'cat-item-' . $category->term_id,
            ($isParent ? 'parent' : 'child')
        );
        if ($isCurrent) {
            $css_classes[] = 'current';
        }




        /**
        * Filter the list of CSS classes to include with each category in the list.
        *
        * @since 4.2.0
        *
        * @see wp_list_categories()
        *
        * @param array  $css_classes An array of CSS classes to be applied to each list item.
        * @param object $category    Category data object.
        * @param int    $depth       Depth of page, used for padding.
        * @param array  $args        An array of wp_list_categories() arguments.
        */
        $css_classes = implode(' ', apply_filters('category_css_class', $css_classes, $category, $depth, $args));

        $output .=  ' class="' . $css_classes . '"';
        $output .= ">$title\n";
    }
}
