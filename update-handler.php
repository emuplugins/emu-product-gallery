<?php

if (!defined('ABSPATH')) {
    exit;
}
if ( ! class_exists( 'Emu_Updater' ) ) {
    
class Emu_Updater {
    private $plugin_slug;
    private $api_url;

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

new Emu_Updater($plugin_slug);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($actions) {
    $slug = basename(__DIR__);
    $url = wp_nonce_url(admin_url("plugins.php?force-check-update=$slug"), "force_check_update_$slug");
    $actions['check_update'] = '<a href="' . esc_url($url) . '">Check for Update</a>';
    return $actions;
});

add_action('admin_init', function() {
    $slug = basename(__DIR__);
    if (isset($_GET['force-check-update']) && $_GET['force-check-update'] === $slug) {
        check_admin_referer("force_check_update_$slug");
        delete_site_transient('update_plugins');
        wp_safe_redirect(admin_url("plugins.php?checked-update=$slug"));
        exit;
    }
});

add_action('admin_notices', function() {
    $slug = basename(__DIR__);
    if (isset($_GET['checked-update']) && $_GET['checked-update'] === $slug) {
        echo '<div class="updated"><p>Update check completed! If there is a new version, it will appear soon.</p></div>';
    }
});

add_filter('upgrader_post_install', function($response, $hook_extra, $result) {
    global $wp_filesystem;

    if (!function_exists('get_filesystem_method')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $current_plugin_slug = basename(__DIR__);
    $proper_destination = WP_PLUGIN_DIR . '/' . $current_plugin_slug;
    $new_plugin_dir = WP_PLUGIN_DIR . '/' . basename($result['destination']);

    if ($new_plugin_dir !== $proper_destination) {
        $wp_filesystem->move($new_plugin_dir, $proper_destination);
    }

    return $response;
}, 10, 3);

if (!function_exists('auto_reactivate_plugin_after_update')) {
    function auto_reactivate_plugin_after_update($upgrader_object, $options) {
        if (isset($options['action'], $options['type']) && 
            $options['action'] === 'update' && 
            $options['type'] === 'plugin') {

            foreach ($options['plugins'] as $plugin) {
                if (!is_plugin_active($plugin)) {
                    $result = activate_plugin($plugin);
                    if (is_wp_error($result)) {
                        error_log('Erro ao reativar o plugin: ' . $result->get_error_message());
                    }
                }
            }
        }
    }
    add_action('upgrader_process_complete', 'auto_reactivate_plugin_after_update', 10, 2);
}
