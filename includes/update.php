<?php

function emu_check_for_updates($transient) {
    // URL da API do GitHub para pegar a última release
    $repo_url = 'https://api.github.com/repos/tonnynho2004/emuproductgallery/releases/latest';
    
    // Realiza a requisição à API do GitHub
    $response = wp_remote_get($repo_url, array(
        'headers' => array('User-Agent' => 'WordPress')
    ));

    // Verifica se houve erro na requisição
    if (is_wp_error($response)) {
        return $transient;
    }

    // Processa a resposta da API
    $data = json_decode(wp_remote_retrieve_body($response));

    // Verifica se a versão mais recente do GitHub é superior à versão do plugin
    if (version_compare($data->tag_name, get_plugin_data(__FILE__)['Version'], '>')) {
        // Adiciona informações de atualização no WordPress
        $transient->response['emu-product-gallery/emu-product-gallery.php'] = (object) [
            'slug'        => 'emu-product-gallery',
            'new_version' => $data->tag_name,
            'url'         => $data->html_url, // URL da release no GitHub
            'package'     => $data->zipball_url, // URL para o arquivo ZIP da release
        ];
    }

    return $transient;
}
add_filter('site_transient_update_plugins', 'emu_check_for_updates');
