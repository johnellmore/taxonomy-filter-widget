<?php

namespace Ellmore\TaxonomyFilterWidget;

class FilterWidget extends \WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'ellmore_taxonomy_filter', // Base ID
            'Taxonomy Filter', // Name
            array(
                'description' => 'Taxonomy filters for an archive page'
            )
        );
    }

    public function widget($args, $instance)
    {

        extract($args, EXTR_SKIP);

        if (!@$instance['taxonomy']) {
            return;
        }

        echo $before_widget;

        $title = apply_filters('widget_title', @$instance['title']);
        if ($title) {
            echo $before_title;
            echo $title;
            echo '<i class="fa" aria-hidden="true"></i>';
            echo $after_title;
        }

        // get currently selected terms
        $terms = array();
        if (isset($_GET[$instance['taxonomy']])) {
            $terms = $_GET[$instance['taxonomy']];
            $terms = explode(',', $terms);
        } else {
            $object = get_queried_object();
            if (is_a($object, 'WP_Term')) {
                $terms[] = $object->slug;
            }
        }

        echo '<ul class="filter-menu">';
        wp_list_categories(array(
            'show_count'            => false,
            'hide_empty'            => true,
            'use_desc_for_title'    => @$instance['showcount'] == 'show',
            'hierarchical'          => true,
            'title_li'              => false,
            'pad_counts'            => true,
            'orderby'               => 'slug',
            'order'                 => 'ASC',
            'taxonomy'              => @$instance['taxonomy'],
            'walker'                => new TaxonomyWalker,
            'current_category'      => $terms,
            'post_type'             => @$instance['post_type'],
            'checkbox_checked'      => '<i class="fa fa-fw fa-check-square-o"></i> ',
            'checkbox_unchecked'    => '<i class="fa fa-fw fa-square-o"></i> ',
        ));
        echo '</ul>';

        echo $after_widget;
    }

    public function form($instance)
    {
        $taxonomies = get_taxonomies(array(
            'public' => true,
        ), 'objects');
        $postTypes = get_post_types(array(
            'public' => true,
        ), 'objects');
        ?>
        <div class="ellmore_gallery_filter">
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:</label><br />
                <input
                    type="text"
                    id="<?php echo $this->get_field_id('title'); ?>"
                    name="<?php echo $this->get_field_name('title'); ?>"
                    value="<?php echo esc_attr(@$instance['title']); ?>"
                />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('post_type'); ?>">Post type to filter:</label><br />
                <select
                    id="<?php echo $this->get_field_id('post_type'); ?>"
                    name="<?php echo $this->get_field_name('post_type'); ?>"
                    >
                    <option value=""></option>
                    <?php foreach ($postTypes as $slug => $type) { ?>
                        <?php $selected = (@$instance['post_type'] == $slug) ? 'selected="selected"' : ''; ?>
                        <option
                            <?php echo $selected; ?>
                            value="<?php echo $slug; ?>"
                        >
                            <?php echo $type->labels->name; ?>
                        </option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('taxonomy'); ?>">Category to list:</label><br />
                <select
                    id="<?php echo $this->get_field_id('taxonomy'); ?>"
                    name="<?php echo $this->get_field_name('taxonomy'); ?>"
                >
                    <option value=""></option>
                    <?php foreach ($taxonomies as $slug => $tax) : ?>
                        <?php $selected = (@$instance['taxonomy'] == $slug) ? 'selected="selected"' : ''; ?>
                        <option
                            <?php echo $selected; ?>
                            value="<?php echo $slug; ?>">
                            <?php echo $tax->labels->singular_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <input
                    type="checkbox"
                    <?php echo (@$instance['showcount'] == 'show') ? 'checked="checked"': ''; ?>
                    id="<?php echo $this->get_field_id('showcount'); ?>"
                    name="<?php echo $this->get_field_name('showcount'); ?>"
                    value="show"
                />
                <label for="<?php echo $this->get_field_id('showcount'); ?>">Show number of items in parentheses</label>
            </p>
        </div>
        <?php
    }

    public function update($newInstance, $oldInstance)
    {
        $taxonomies = get_taxonomies(array(
            'public' => true,
        ), 'names');
        $postTypes = get_post_types(array(
            'public' => true,
        ));
        $instance = array(
            'title' => sanitize_text_field($newInstance['title']),
            'taxonomy' => (in_array($newInstance['taxonomy'], $taxonomies)) ? $newInstance['taxonomy'] : '',
            'post_type' => (in_array($newInstance['post_type'], $postTypes)) ? $newInstance['post_type'] : '',
            'showcount' => (in_array($newInstance['showcount'], array('', 'show'))) ? $newInstance['showcount'] : ''
        );
        return $instance;
    }
}
