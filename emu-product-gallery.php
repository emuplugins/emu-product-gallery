<?php
/*
Plugin Name: Emu Product Gallery
Plugin URI: https://example.com/emu-product-gallery
Description: A plugin to display image and YouTube video gallery sliders.
Version: 1.1.6
Author: Emu Plugins
Author URI: https://aganrdagency.com
*/

if (!defined('ABSPATH')) {
    exit;
}



$plugin_slug = basename(__DIR__);  // Diretório do plugin
if (substr($plugin_slug, -5) === '-main') {
    $plugin_slug = substr($plugin_slug, 0, -5); // Remove o sufixo '-main'
}
$plugin_dir = basename(__DIR__); // Mantemos o diretório original para referência

require_once plugin_dir_path(__FILE__) . 'update-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/option_page.php';

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

// Including the slider shortcode
function emu_product_gallery_include_slider_shortcode() {
    // Includes the file containing the shortcode
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/slider_template.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/slider_template.php';
    }
}

add_action('init', 'emu_product_gallery_include_slider_shortcode');
