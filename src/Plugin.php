<?php

namespace Ellmore\TaxonomyFilterWidget;

class Plugin
{

    public static function bootstrap()
    {
        // register the widget with WordPress
        add_action('widgets_init', array(self::class, 'registerWidget'));

        // enqueue scripts/style for widget editing
        //add_action('admin_enqueue_scripts', array($this, 'enqueueWidgetScripts'));
        //add_action('customize_controls_enqueue_scripts', array($this, 'enqueueWidgetScripts'));
    }

    public static function registerWidget()
    {
        register_widget(FilterWidget::class);
    }

    public static function enqueueWidgetScripts()
    {
        wp_enqueue_style('emiw-editing', plugins_url('image-widget-editing.css', __FILE__));
        wp_enqueue_script('emiw-editing', plugins_url('image-widget-editing.js', __FILE__), array('jquery'));
    }
}
