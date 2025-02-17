<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('Emu_Updater')) {
    class Emu_Updater {
        private $api_url;
        private $plugin_slug;
        private $plugin_dir; // Adicione esta linha
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

// Obtém o nome da pasta atual e define o slug desejado (sem o "-main")
$plugin_dir_unsanitized = basename(__DIR__);
$plugin_slug = $plugin_dir_unsanitized;
if (substr($plugin_slug, -5) === '-main') {
    $plugin_slug = substr($plugin_slug, 0, -5);
}
$desired_plugin_dir = $plugin_slug; // Nome que desejamos para a pasta
$self_plugin_dir = $plugin_dir_unsanitized; // Nome atual (pode conter "-main")

// Executa apenas se estiver em uma página administrativa relacionada a atualizações
add_action('admin_init', function() use ($self_plugin_dir, $plugin_slug) {
    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    $allowed_screens = [
        'update-core',     // Página principal de atualizações
        'plugins',         // Lista de plugins (onde há botões de atualização)
        'themes',          // Lista de temas
        'plugin-install',  // Instalação de plugins (onde também há atualizações)
        'theme-install'    // Instalação de temas
    ];

    if (in_array($screen->id, $allowed_screens)) {
        // Adicionar link "Verificar Atualizações"
        add_filter("plugin_action_links_{$self_plugin_dir}/{$plugin_slug}.php", function($actions) use ($self_plugin_dir) {
            $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$self_plugin_dir"), "force_check_update_$self_plugin_dir");
            $actions['check_update'] = '<a href="' . esc_url($url) . '">Verificar Atualizações</a>';
            return $actions;
        });

        // Ação para forçar a verificação de atualizações
        add_action('admin_init', function() use ($self_plugin_dir) {
            if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $self_plugin_dir) {
                check_admin_referer("force_check_update_$self_plugin_dir");
                delete_site_transient('update_plugins');
                wp_safe_redirect(admin_url("plugins.php?checked-update=$self_plugin_dir"));
                exit;
            }
        });

        // Notificação após a verificação
        add_action('admin_notices', function() use ($self_plugin_dir) {
            if (isset($_GET['checked-update']) && $_GET['checked-update'] === $self_plugin_dir) {
                echo '<div class="notice notice-success"><p>Verificação de atualizações concluída!</p></div>';
            }
        });
    }
});

// Após a instalação/atualização, move o plugin para o diretório desejado
add_filter('upgrader_post_install', function($response, $hook_extra, $result) use ($desired_plugin_dir) {
    global $wp_filesystem;
    
    $proper_destination = WP_PLUGIN_DIR . '/' . $desired_plugin_dir;
    $current_destination = $result['destination'];
    
    if ($current_destination !== $proper_destination) {
        if ($wp_filesystem->move($current_destination, $proper_destination)) {
            $result['destination'] = $proper_destination;
        } else {
            error_log("Erro ao mover o diretório do plugin: $current_destination -> $proper_destination");
        }
    }
    
    return $result;
}, 10, 3);

// Após a atualização, renomeia o diretório (se necessário) e reativa o plugin
add_action('upgrader_process_complete', function($upgrader_object, $options) use ($self_plugin_dir, $desired_plugin_dir, $plugin_slug) {
    if (!isset($options['action'], $options['type']) || 
        $options['action'] !== 'update' || 
        $options['type'] !== 'plugin' || 
        !isset($options['plugins'])) {
        return;
    }

    $current_plugin_file = "$self_plugin_dir/$plugin_slug.php";

    if (in_array($current_plugin_file, $options['plugins'])) {
        $old_path = WP_PLUGIN_DIR . '/' . $self_plugin_dir;
        $new_path = WP_PLUGIN_DIR . '/' . $desired_plugin_dir;

        // Se o diretório instalado não for o desejado, renomeia-o
        if ($self_plugin_dir !== $desired_plugin_dir) {
            if (rename($old_path, $new_path)) {
                $current_plugin_file = "$desired_plugin_dir/$plugin_slug.php";
            } else {
                error_log('Erro ao renomear a pasta do plugin.');
            }
        }

        // Reativa o plugin se não estiver ativo
        if (!is_plugin_active(WP_PLUGIN_DIR . '/' . $current_plugin_file)) {
            $result = activate_plugin($current_plugin_file);
            if (is_wp_error($result)) {
                error_log('Erro ao reativar o plugin: ' . $result->get_error_message());
            }
        }
    }
}, 10, 2);