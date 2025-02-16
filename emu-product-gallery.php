<?php
/*
Plugin Name: Emu Product Gallery
Plugin URI: https://example.com/emu-product-gallery
Description: A plugin to display image and YouTube video gallery sliders.
Version: 1.1.6
Author: Emu Plugins
Author URI: https://aganrdagency.com
*/

if (!defined('ABSPATH')) exit;


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
add_action('admin_init', 'emu_load_on_update_pages');


// Sistema de atualização do plugin

// Verifica se o plugin principal está ativo
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if (!is_plugin_active('emu-update-core/emu-update-core.php')) {

    if (!defined('EMU_UPDATE_HANDLER')) {
        define('EMU_UPDATE_HANDLER', __FILE__); // Se o principal não estiver ativo, este assume
    }

    function emu_load_on_update_pages() {
        global $pagenow;

        $update_pages = ['update-core.php', 'update.php', 'plugins.php', 'themes.php'];

        if (in_array($pagenow, $update_pages)) {

            $plugin_slug = basename(__DIR__);
            if (substr($plugin_slug, -5) === '-main') {
                $plugin_slug = substr($plugin_slug, 0, -5);
            }
            $self_plugin_dir = basename(__DIR__);

            require_once plugin_dir_path(__FILE__) . 'update-handler.php';

            new Emu_Updater($plugin_slug, $self_plugin_dir);
            add_action('upgrader_process_complete', 'emu_handle_plugin_update', 10, 2);
        }
    }

    add_action('admin_init', 'emu_load_on_update_pages');
}