<?php
/*
Plugin Name: Emu Product Gallery
Description: A plugin to display image and YouTube video gallery sliders.
Version: 1.1.9
Author: Emu Plugins
*/

if (!defined('ABSPATH')) exit;

define('EPG_DIR', plugin_dir_path(__FILE__));

require_once EPG_DIR . 'includes/classes/oembed.php';

// Load backend files
if (is_admin()) {
    require_once EPG_DIR . 'update-handler.php';
}

if (!is_admin()) {
    // Enqueueing plugin CSS and JS
    function emu_product_gallery_enqueue_assets() {
    
    // Enqueue Swiper CSS
    wp_enqueue_style('swiper-style', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), null);

    // Enqueue your plugin's CSS
    wp_enqueue_style('emu-product-gallery-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');

    // Enqueue Swiper JS
    wp_enqueue_script('swiper-script', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);

    // Enqueue your plugin's JS
    wp_enqueue_script('emu-product-gallery-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('swiper-script', 'jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'emu_product_gallery_enqueue_assets');
}

// Including the slider shortcode
function emu_product_gallery_include_slider_shortcode() {
    // Includes the file containing the shortcode
    if (file_exists(EPG_DIR . 'includes/slider_template.php')) {
        require_once EPG_DIR . 'includes/slider_template.php';
    }
}

add_action('init', 'emu_product_gallery_include_slider_shortcode');