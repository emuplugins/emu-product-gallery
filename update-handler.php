<?php

class Emu_Product_Gallery_Updater {
    private $api_url = 'https://raw.githubusercontent.com/tonnynho2004/emu-product-gallery/refs/heads/main/info.json';

    public function __construct() {
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
    }

    public function plugin_info($res, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== 'emu-product-gallery') {
            return $res;
        }

        $remote = wp_remote_get($this->api_url);
        if (is_wp_error($remote)) {
            return $res;
        }

        $plugin_info = json_decode(wp_remote_retrieve_body($remote));
        if (!$plugin_info) {
            return $res;
        }

        $res = new stdClass();
        $res->name = $plugin_info->name;
        $res->slug = $plugin_info->slug;
        $res->version = $plugin_info->version;
        $res->author = '<a href="' . $plugin_info->author_homepage . '">' . $plugin_info->author . '</a>';
        $res->download_link = $plugin_info->download_url;
        $res->tested = $plugin_info->tested;
        $res->requires = $plugin_info->requires;
        $res->sections = (array) $plugin_info->sections;

        return $res;
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = wp_remote_get($this->api_url);
        if (is_wp_error($remote)) {
            return $transient;
        }

        $plugin_info = json_decode(wp_remote_retrieve_body($remote));
        if (!$plugin_info) {
            return $transient;
        }

        $current_version = get_plugin_data(__FILE__)['Version'];
        if (version_compare($current_version, $plugin_info->version, '<')) {
            $transient->response['emu-product-gallery/emu-product-gallery.php'] = (object) [
                'slug'        => $plugin_info->slug,
                'plugin'      => 'emu-product-gallery/emu-product-gallery.php',
                'new_version' => $plugin_info->version,
                'package'     => $plugin_info->download_url,
                'tested'      => $plugin_info->tested,
                'requires'    => $plugin_info->requires
            ];
        }

        return $transient;
    }
}

new Emu_Product_Gallery_Updater();









add_action('admin_notices', function() {
    // Exibe o diretório do arquivo do plugin
    var_dump(basename(__DIR__)); // Verifique se o caminho completo está correto
});

add_filter('plugin_action_links_emu-product-gallery/emu-product-gallery.php', function($actions) {
    error_log('plugin_action_links hook is being triggered');
    
    // Usa basename(__DIR__) para pegar apenas o nome da pasta
    $slug = basename(__DIR__); // Ex: emu-product-gallery
    
    // Cria a URL para forçar a verificação de atualização
    $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$slug"), "force_check_update_$slug");
    // Adiciona o link de verificação de atualização
    $actions['check_update'] = '<a href="' . esc_url($url) . '">Verificar Atualização</a>';
    return $actions;
});

add_action('admin_init', function() {
    // Obtém o slug corretamente
    $slug = basename(__DIR__);
    if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $slug) {
        check_admin_referer("force_check_update_$slug");

        // Força o WordPress a verificar atualizações
        delete_site_transient('update_plugins');
        wp_safe_redirect(admin_url("plugins.php?checked-update=$slug"));
        exit;
    }
});

add_action('admin_notices', function() {
    // Obtém o slug corretamente
    $slug = basename(__DIR__);
    if (isset($_GET['checked-update']) && $_GET['checked-update'] === $slug) {
        echo '<div class="updated"><p>Verificação de atualização concluída! Se houver uma nova versão, ela aparecerá em breve.</p></div>';
    }
});

add_filter('upgrader_post_install', function($response, $hook_extra, $result) {
    global $wp_filesystem;

    // Obtém o slug correto da pasta
    $current_plugin_slug = basename(__DIR__);
    $proper_destination = WP_PLUGIN_DIR . '/' . $current_plugin_slug;
    $new_plugin_dir = WP_PLUGIN_DIR . '/' . basename($result['destination']);

    // Se o nome da pasta estiver errado, renomeia corretamente
    if ($new_plugin_dir !== $proper_destination) {
        $wp_filesystem->move($new_plugin_dir, $proper_destination);
    }

    return $response;
}, 10, 3);
