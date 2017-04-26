<?php
/*
Plugin Name: Taxonomy Filter Widget
Plugin URI: https://github.com/johnellmore/taxonomy-filter-widget
Description: Provides a widget that will filter the active archive view by category.
Author: John Ellmore
Author URI: http://johnellmore.com/
Version: 0.1
*/

// load dependencies and project files
require_once('src/Plugin.php');
require_once('src/FilterWidget.php');
require_once('src/TaxonomyWalker.php');

// kick off the plugin
\Ellmore\TaxonomyFilterWidget\Plugin::bootstrap();
