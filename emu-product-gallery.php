<?php
/*
Plugin Name: Emu Product Gallery
Plugin URI: https://example.com/emu-product-gallery
Description: Um plugin para exibir sliders de galeria de imagens e vídeos do YouTube.
Version: 1.0.8
Author: Angard Agency
Author URI: https://aganrdagency.com
*/
require_once plugin_dir_path(__FILE__) . 'update-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/option_page.php';
// Enfileirando o CSS e o JS do plugin
function emu_product_gallery_enqueue_assets() {
    // Enfileira o CSS do Swiper
    wp_enqueue_style('swiper-style', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), null);

    // Enfileira o CSS do seu plugin
    wp_enqueue_style('emu-product-gallery-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');

    // Enfileira o JS do Swiper
    wp_enqueue_script('swiper-script', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);

    // Enfileira o JS do seu plugin
    wp_enqueue_script('emu-product-gallery-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('swiper-script', 'jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'emu_product_gallery_enqueue_assets');

// Incluindo o shortcode do slider
function emu_product_gallery_include_slider_shortcode() {
    // Inclui o arquivo que contém o shortcode
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/slider_template.php')) {
        require_once plugin_dir_path(__FILE__) . 'includes/slider_template.php';
    }
}

add_action('init', 'emu_product_gallery_include_slider_shortcode');
