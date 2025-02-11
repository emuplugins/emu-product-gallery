<?php
/*
Plugin Name: Emu Product Gallery
Plugin URI: https://example.com/emu-product-gallery
Description: Um plugin para exibir sliders de galeria de imagens e vídeos do YouTube.
Version: 1.0.2
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

// Inclua o arquivo do Update Checker, se ainda não tiver feito isso
require 'plugin-update-checker/plugin-update-checker.php'; // ajuste o caminho para onde está o arquivo

// Crie uma instância do plugin update checker
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/tonnynho2004/emu-product-gallery', // URL do repositório no GitHub
    __FILE__, // Caminho do arquivo principal do seu plugin
    'emu-product-gallery' // O slug do seu plugin
);

// Define o branch (caso você esteja usando um branch diferente de "master")
$myUpdateChecker->setBranch('main'); // Substitua "main" pelo seu branch se necessário

// Adiciona o link para verificar atualizações manualmente na página de plugins
function emu_add_update_check_button($links, $file) {
    // Verifica se é o plugin correto
    if ($file == 'emu-product-gallery/emu-product-gallery.php') {
        // URL para acionar a verificação de atualização
        $url = wp_nonce_url(admin_url('admin-ajax.php?action=emu_check_for_updates_manual'), 'emu_check_for_updates_manual');
        $links[] = '<a href="' . $url . '">Verificar Atualização</a>';
    }
    return $links;
}
add_filter('plugin_action_links_emu-product-gallery/emu-product-gallery.php', 'emu_add_update_check_button', 10, 2);

// Função AJAX para verificar atualizações manualmente
function emu_check_for_updates_manual() {
    // Verifica a segurança com nonce
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'emu_check_for_updates_manual')) {
        die('Você não tem permissão para realizar esta ação.');
    }

    // URL da API do GitHub para pegar a última release
    $repo_url = 'https://api.github.com/repos/tonnynho2004/emu-product-gallery/releases/latest';

    // Realiza a requisição à API do GitHub
    $response = wp_remote_get($repo_url, array(
        'headers' => array('User-Agent' => 'WordPress')
    ));

    // Se ocorrer erro na requisição, redireciona com mensagem
    if (is_wp_error($response)) {
        wp_redirect(admin_url('plugins.php?plugin_status=all&message=Erro na verificação de atualização'));
        exit;
    }

    // Processa a resposta da API
    $data = json_decode(wp_remote_retrieve_body($response));

    // Verifica se a resposta contém a tag da versão
    if (isset($data->tag_name)) {
        // Obter os dados do plugin
        $plugin_file = plugin_dir_path(__FILE__) . 'emu-product-gallery.php';
        $plugin_data = get_plugin_data($plugin_file);
        $current_version = $plugin_data['Version'];

        // Remove o prefixo "v" da versão do GitHub (se houver)
        $github_version = ltrim($data->tag_name, 'v');

        if (version_compare($github_version, $current_version, '>')) {
            wp_redirect(admin_url('plugins.php?plugin_status=all&message=Nova versão disponível! Verifique a atualização.'));
        } else {
            wp_redirect(admin_url('plugins.php?plugin_status=all&message=O plugin está atualizado.'));
        }
    } else {
        wp_redirect(admin_url('plugins.php?plugin_status=all&message=Erro ao obter informações de atualização.'));
    }

    exit;
}
add_action('wp_ajax_emu_check_for_updates_manual', 'emu_check_for_updates_manual');
