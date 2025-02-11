<?php
// Adiciona o link para verificar atualizações manualmente na página de plugins
function emu_add_update_check_button($links, $file) {
    // Verifica se é o plugin correto
    if ($file == 'emu-product-gallery/emu-product-gallery.php') {
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
        // Obter os dados do plugin: como este arquivo está em /includes, precisamos subir um nível
        $plugin_file = plugin_dir_path(__FILE__) . '../emu-product-gallery.php';
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

// Função para registrar a atualização automaticamente no sistema do WordPress
function emu_check_for_plugin_update($transient) {
    // Se não houver atualizações já verificadas, retorna o transient sem alterações
    if (empty($transient->checked)) {
        return $transient;
    }

    // URL da API do GitHub para a última release
    $repo_url = 'https://api.github.com/repos/tonnynho2004/emu-product-gallery/releases/latest';

    // Realiza a requisição à API do GitHub
    $response = wp_remote_get($repo_url, array(
        'headers' => array('User-Agent' => 'WordPress')
    ));

    // Se houver erro, retorna o transient sem alterações
    if (is_wp_error($response)) {
        return $transient;
    }

    // Processa a resposta da API
    $data = json_decode(wp_remote_retrieve_body($response));

    // Se a resposta contiver a tag da versão
    if (isset($data->tag_name)) {
        $github_version = ltrim($data->tag_name, 'v');

        // Obter os dados do plugin (subindo um nível, pois este arquivo está em /includes)
        $plugin_file = plugin_dir_path(__FILE__) . '../emu-product-gallery.php';
        $plugin_data = get_plugin_data($plugin_file);
        $current_version = $plugin_data['Version'];

        // Se a versão do GitHub for maior, registra a atualização
        if (version_compare($github_version, $current_version, '>')) {
            $transient->response['emu-product-gallery/emu-product-gallery.php'] = (object)array(
                'slug'        => 'emu-product-gallery',
                'new_version' => $github_version,
                'url'         => 'https://github.com/tonnynho2004/emu-product-gallery',
                'package'     => 'https://github.com/tonnynho2004/emu-product-gallery/archive/refs/tags/' . $data->tag_name . '.zip'
            );
        }
    }

    return $transient;
}
add_filter('site_transient_update_plugins', 'emu_check_for_plugin_update');
