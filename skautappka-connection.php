<?php
/*
* Plugin Name: Skautappka connection
* Plugin URI: https://github.com/skaut/levitio-connection
* Description: Propojení Levitia s WordPressem.
* Version: 1.0
* Author: Junák - český skaut
* Author URI: https://www.levitio.cz/
* Text Domain: skautappka-connection
*/

if (!defined('ABSPATH')) {exit;}

include_once('skautappka-shortcodes.php'); // Shortcode functions
include_once('SkautAppkaWidget.php'); // Shortcode functions



// register widget
function skautappka_register_widget()
{
	register_widget("skautAppkaWidget");
}

add_action( 'widgets_init', 'skautappka_register_widget' );

// Settings link
function skautappka_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=skautappka-widget">Jak na to?</a>';
    array_push( $links, $settings_link );
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin", 'skautappka_add_settings_link' );
