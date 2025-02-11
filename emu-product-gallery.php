<?php
/*
Plugin Name: Emu Product Gallery
Plugin URI: https://example.com/emu-product-gallery
Description: Um plugin para exibir sliders de galeria de imagens e vídeos do YouTube.
Version: 1.0.3
Author: Angard Agency
Author URI: https://aganrdagency.com
*/
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes.php';
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





// Inclui o arquivo do Plugin Update Checker
require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://raw.githubusercontent.com/tonnynho2004/emu-product-gallery/main/details.json', // URL direta do details.json
    __FILE__,
    'emu-product-gallery'
);




// Adiciona o link "Verificar Atualizações" na página de plugins
function emu_product_gallery_plugin_action_links($links) {
    // Adiciona o link ao final
    $links[] = '<a href="' . esc_url( admin_url('admin.php?page=emu-product-gallery-update') ) . '">Verificar Atualizações</a>';
    return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'emu_product_gallery_plugin_action_links');

// Adiciona a página no menu de administração
function emu_product_gallery_update_menu() {
    add_submenu_page(
        'plugins.php',                   // Menu principal onde a página será adicionada
        'Verificar Atualizações',         // Título da página
        'Verificar Atualizações',         // Título do menu
        'manage_options',                 // Permissões necessárias
        'emu-product-gallery-update',     // Slug da página
        'emu_product_gallery_check_update_page' // Função de callback
    );
}
add_action('admin_menu', 'emu_product_gallery_update_menu');

// Página para verificar atualizações
function emu_product_gallery_check_update_page() {
    ?>
    <div class="wrap">
        <h1>Verificar Atualizações do Emu Product Gallery</h1>
        <p>Clique no botão abaixo para verificar se há uma nova versão do plugin.</p>
        <form method="post">
            <input type="hidden" name="emu_check_update" value="1">
            <?php submit_button('Verificar Agora'); ?>
        </form>
    </div>
    <?php

    // Se o botão for pressionado, força a verificação de atualização
    if (isset($_POST['emu_check_update'])) {
        require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

        $myUpdateChecker = PucFactory::buildUpdateChecker(
            'https://raw.githubusercontent.com/tonnynho2004/emu-product-gallery/main/details.json',
            __FILE__,
            'emu-product-gallery'
        );

        // Força a verificação de atualizações
        $myUpdateChecker->checkForUpdates();

        echo '<div class="updated"><p>Verificação concluída! Se houver uma nova versão, ela aparecerá na página de plugins.</p></div>';
    }
}
