<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Emu_Updater')) {
    class Emu_Updater {
        private $api_url;
        private $plugin_slug;
        private $plugin_dir; // Adicione esta linhaa
        private $self_plugin_dir;
        
        public function __construct($plugin_slug, $self_plugin_dir) {
            $this->plugin_slug = $plugin_slug;
            $this->plugin_dir = $self_plugin_dir;
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
            // Verifica se a ação é de atualização de plugin e se o nosso plugin está na lista
            if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
                return;
            }
            
            $updated = false;
            foreach ($options['plugins'] as $plugin) {
                if (strpos($plugin, $this->plugin_slug) !== false) {
                    $updated = true;
                    break;
                }
            }
            
            if (!$updated) {
                return;
            }
            
            // Se o diretório instalado possui um sufixo (por exemplo, "meuplugin-main") e precisamos renomeá-lo
            if ($this->plugin_dir !== $this->plugin_slug) {
                $old_path = WP_PLUGIN_DIR . '/' . $this->plugin_dir;
                $new_path = WP_PLUGIN_DIR . '/' . $this->plugin_slug;
                
                if (rename($old_path, $new_path)) {
                    // Atualiza a propriedade para o novo caminho
                    $this->plugin_dir = $this->plugin_slug;
                } else {
                    error_log('Erro ao renomear a pasta do plugin.');
                }
            }
            
            // Define os dois possíveis caminhos para o arquivo principal do plugin
            $possivel_arquivo_padrao = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
            $possivel_arquivo_main   = $this->plugin_slug . '/' . $this->plugin_slug . '-main.php';
            
            // Verifica qual dos arquivos existe
            if (file_exists(WP_PLUGIN_DIR . '/' . $possivel_arquivo_padrao)) {
                $plugin_file = $possivel_arquivo_padrao;
            } elseif (file_exists(WP_PLUGIN_DIR . '/' . $possivel_arquivo_main)) {
                $plugin_file = $possivel_arquivo_main;
            } else {
                error_log('Arquivo principal do plugin não encontrado.');
                return;
            }
            
            // Reativa o plugin, se ainda não estiver ativo
            if (!is_plugin_active($plugin_file)) {
                activate_plugin($plugin_file);
            }
        }
    }
}

new Emu_Updater($plugin_slug, $self_plugin_dir);

// Obtém o nome da pasta atual e define o slug desejado
function emu_get_plugin_slug() {
    $plugin_dir = basename(__DIR__);
    return (substr($plugin_dir, -5) === '-main') ? substr($plugin_dir, 0, -5) : $plugin_dir;
}

$plugin_slug = emu_get_plugin_slug();
$self_plugin_dir = basename(__DIR__);
$desired_plugin_dir = $plugin_slug;

// Adiciona o link "Verificar Atualizações"
function emu_add_check_update_link($actions) {
    global $self_plugin_dir;
    $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$self_plugin_dir"), "force_check_update_$self_plugin_dir");
    $actions['check_update'] = '<a href="' . esc_url($url) . '">Verificar Atualizações</a>';
    return $actions;
}
add_filter("plugin_action_links_{$self_plugin_dir}/{$plugin_slug}.php", 'emu_add_check_update_link');

// Força a verificação de atualizações
function emu_force_check_update() {
    global $self_plugin_dir;
    if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $self_plugin_dir) {
        check_admin_referer("force_check_update_$self_plugin_dir");
        delete_site_transient('update_plugins');
        wp_safe_redirect(admin_url("plugins.php?checked-update=$self_plugin_dir"));
        exit;
    }
}
add_action('admin_init', 'emu_force_check_update');

// Exibe notificação após a verificação
function emu_admin_notices() {
    global $self_plugin_dir;
    if (isset($_GET['checked-update']) && $_GET['checked-update'] === $self_plugin_dir) {
        echo '<div class="notice notice-success"><p>Verificação de atualizações concluída!</p></div>';
    }
}
add_action('admin_notices', 'emu_admin_notices');

// Move o plugin para o diretório correto após a instalação
function emu_fix_plugin_directory($response, $hook_extra, $result) {
    global $wp_filesystem, $desired_plugin_dir;
    $proper_destination = WP_PLUGIN_DIR . '/' . $desired_plugin_dir;
    $current_destination = $result['destination'];
    
    if ($current_destination !== $proper_destination) {
        $wp_filesystem->move($current_destination, $proper_destination);
        $result['destination'] = $proper_destination;
    }
    
    return $response;
}
add_filter('upgrader_post_install', 'emu_fix_plugin_directory', 10, 3);

// Renomeia o diretório do plugin e reativa após a atualização
function emu_handle_plugin_update($upgrader_object, $options) {
    global $self_plugin_dir, $desired_plugin_dir, $plugin_slug;
    $current_plugin_file = $self_plugin_dir . '/' . $plugin_slug . '.php';
    
    if (isset($options['action'], $options['type']) && 
        $options['action'] === 'update' && 
        $options['type'] === 'plugin' && 
        in_array($current_plugin_file, $options['plugins'])) {
        
        $plugin_file = $current_plugin_file;
        
        if ($self_plugin_dir !== $desired_plugin_dir) {
            $old_path = WP_PLUGIN_DIR . '/' . $self_plugin_dir;
            $new_path = WP_PLUGIN_DIR . '/' . $desired_plugin_dir;
            
            if (rename($old_path, $new_path)) {
                $plugin_file = $desired_plugin_dir . '/' . $plugin_slug . '.php';
            } else {
                error_log('Erro ao renomear a pasta do plugin.');
            }
        }
        
        if (!is_plugin_active($plugin_file)) {
            $result = activate_plugin($plugin_file);
            if (is_wp_error($result)) {
                error_log('Erro ao reativar o plugin: ' . $result->get_error_message());
            }
        }
    }
}