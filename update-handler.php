<?php
if (!class_exists('Emu_Updater')) {
    class Emu_Updater {
        private $api_url;
        private $plugin_slug;

        public function __construct($plugin_slug) {
            $this->plugin_slug = $plugin_slug;
            $this->api_url = 'https://raw.githubusercontent.com/emuplugins/' . $this->plugin_slug . '/refs/heads/main/info.json';

            add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
            add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
        }

        public function plugin_info($res, $action, $args) {
            if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
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

            $current_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugin_slug . '/' . $this->plugin_slug . '.php')['Version'];
            if (version_compare($current_version, $plugin_info->version, '<')) {
                $transient->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = (object) [
                    'slug'        => $plugin_info->slug,
                    'plugin'      => $this->plugin_slug . '/' . $this->plugin_slug . '.php',
                    'new_version' => $plugin_info->version,
                    'package'     => $plugin_info->download_url,
                    'tested'      => $plugin_info->tested,
                    'requires'    => $plugin_info->requires
                ];
            }
            return $transient;
        }
    }
}
$plugin_slug = basename(__DIR__);
new Emu_Updater($plugin_slug);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($actions) use ($plugin_slug) {
    $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$plugin_slug"), "force_check_update_$plugin_slug");
    $actions['check_update'] = '<a href="' . esc_url($url) . '">Check for Update</a>';
    return $actions;
});

add_action('admin_init', function() use ($plugin_slug) {
    if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $plugin_slug) {
        check_admin_referer("force_check_update_$plugin_slug");
        delete_site_transient('update_plugins');
        wp_safe_redirect(admin_url("plugins.php?checked-update=$plugin_slug"));
        exit;
    }
});

add_action('admin_notices', function() use ($plugin_slug) {
    if (isset($_GET['checked-update']) && $_GET['checked-update'] === $plugin_slug) {
        echo '<div class="updated"><p>Update check completed! If there is a new version, it will appear soon.</p></div>';
    }
});

add_filter('upgrader_post_install', function($response, $hook_extra, $result) use ($plugin_slug) {
    global $wp_filesystem;

    $proper_destination = WP_PLUGIN_DIR . '/' . $plugin_slug;
    $new_plugin_dir = WP_PLUGIN_DIR . '/' . basename($result['destination']);

    if ($new_plugin_dir !== $proper_destination) {
        $wp_filesystem->move($new_plugin_dir, $proper_destination);
    }

    return $response;
}, 10, 3);


if (!function_exists('auto_reactivate_plugin_after_update')) {

function auto_reactivate_plugin_after_update($upgrader_object, $options) {
    $plugin_basedir = basename(dirname(__FILE__)); // Diretório real do plugin instalado
    $plugin_slug = basename(__DIR__); // Nome esperado do diretório do plugin
    $plugin_file = $plugin_basedir . '/' . $plugin_slug . '.php'; // Caminho do arquivo do plugin

    // Verifica se foi uma atualização de plugin
    if (isset($options['action'], $options['type']) && 
        $options['action'] === 'update' && 
        $options['type'] === 'plugin' && 
        in_array($plugin_file, $options['plugins'])) {
        
        // Se o diretório do plugin for diferente do esperado, renomeia a pasta
        if ($plugin_basedir !== $plugin_slug) {
            $old_path = WP_PLUGIN_DIR . '/' . $plugin_basedir;
            $new_path = WP_PLUGIN_DIR . '/' . $plugin_slug;

            if (rename($old_path, $new_path)) {
                $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php'; // Atualiza o caminho do arquivo
            } else {
                error_log('Erro ao renomear a pasta do plugin.');
            }
        }

        // Reativa o plugin se não estiver ativo
        if (!is_plugin_active($plugin_file)) {
            $result = activate_plugin($plugin_file);
            if (is_wp_error($result)) {
                error_log('Erro ao reativar o plugin: ' . $result->get_error_message());
            }
        }
    }
}
}

add_action('upgrader_process_complete', 'auto_reactivate_plugin_after_update', 10, 2);
