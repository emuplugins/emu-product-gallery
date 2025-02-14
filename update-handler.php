<?php

$plugin_slug = basename(__DIR__);
$plugin_dir = basename(__DIR__); // Mantemos o diretório original para referência

// Remove sufixo '-main' se presente
if (substr($plugin_slug, -5) === '-main') {
    $plugin_slug = substr($plugin_slug, 0, -5);
}

if (!class_exists('Emu_Updater')) {
    class Emu_Updater {
        private $api_url;
        private $plugin_slug;
        private $plugin_dir;
        
        public function __construct($plugin_slug, $plugin_dir) {
            $this->plugin_slug = $plugin_slug;
            $this->plugin_dir = $plugin_dir;
            $this->api_url = 'https://raw.githubusercontent.com/emuplugins/' . $this->plugin_slug . '/main/info.json';

            add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
            add_filter('site_transient_update_plugins', [$this, 'check_for_update']);
            add_action('upgrader_process_complete', [$this, 'auto_reactivate_plugin_after_update'], 10, 2);
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

            // Caminho correto considerando o diretório real
            $plugin_file_path = $this->plugin_dir . '/' . $this->plugin_slug . '.php';
            $current_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file_path)['Version'];

            if (version_compare($current_version, $plugin_info->version, '<')) {
                // Chave corrigida usando diretório real
                $transient->response[$plugin_file_path] = (object) [
                    'slug'        => $this->plugin_slug,
                    'plugin'      => $plugin_file_path,
                    'new_version' => $plugin_info->version,
                    'package'     => $plugin_info->download_url,
                    'tested'      => $plugin_info->tested,
                    'requires'    => $plugin_info->requires
                ];
            }
            return $transient;
        }

        public function auto_reactivate_plugin_after_update($upgrader_object, $options) {
            $plugin_file = $this->plugin_dir . '/' . $this->plugin_slug . '.php';

            if ($options['action'] === 'update' && 
                $options['type'] === 'plugin' && 
                in_array($plugin_file, $options['plugins'])) 
            {
                // Renomeia diretório se necessário
                if ($this->plugin_dir !== $this->plugin_slug) {
                    $old_path = WP_PLUGIN_DIR . '/' . $this->plugin_dir;
                    $new_path = WP_PLUGIN_DIR . '/' . $this->plugin_slug;

                    if (rename($old_path, $new_path)) {
                        // Atualiza caminho do plugin após renomeação
                        $plugin_file = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
                    }
                }

                // Reativa o plugin
                if (!is_plugin_active($plugin_file)) {
                    activate_plugin($plugin_file);
                }
            }
        }
    }
}




new Emu_Updater($plugin_slug, $plugin_dir);











// Captura variáveis no contexto dos closures
add_filter('plugin_action_links_' . $plugin_dir . '/' . $plugin_slug . '.php', function($actions) use ($plugin_dir) {
    $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$plugin_dir"), "force_check_update_$plugin_dir");
    $actions['check_update'] = '<a href="' . esc_url($url) . '">Verificar Atualizações</a>';
    return $actions;
});

add_action('admin_init', function() use ($plugin_dir) {
    if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $plugin_dir) {
        check_admin_referer("force_check_update_$plugin_dir");
        delete_site_transient('update_plugins');
        wp_safe_redirect(admin_url("plugins.php?checked-update=$plugin_dir"));
        exit;
    }
});

add_action('admin_notices', function() use ($plugin_dir) {
    if (isset($_GET['checked-update']) && $_GET['checked-update'] === $plugin_dir) {
        echo '<div class="notice notice-success"><p>Verificação de atualizações concluída!</p></div>';
    }
});

add_filter('upgrader_post_install', function($response, $hook_extra, $result) use ($plugin_dir) {
    global $wp_filesystem;
    
    $proper_destination = WP_PLUGIN_DIR . '/' . $plugin_dir;
    $current_destination = $result['destination'];
    
    if ($current_destination !== $proper_destination) {
        $wp_filesystem->move($current_destination, $proper_destination);
        $result['destination'] = $proper_destination;
    }
    
    return $response;
}, 10, 3);

// Reativa o plugin após a atualização

if (!function_exists('auto_reactivate_plugin_after_update')) {

function auto_reactivate_plugin_after_update($upgrader_object, $options) {
    $plugin_basedir = $plugin_dir; // Diretório real do plugin instalado
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
