<?php

// Retrieves the selected post types and uses them in the metabox code
function emu_product_gallery_add_metabox() {
    // Retrieves the selected post types from the Options Page
    $post_types = get_option('post_types_selected', array());

    if (!empty($post_types)) {
        add_meta_box(
            'gallery_video_metabox',            // Metabox ID
            'Image and Video Gallery',          // Title
            'display_metabox_gallery_video',    // Function to display the content
            $post_types,                        // Selected post types
            'normal',                           // Context
            'high'                              // Priority
        );
    }
}
add_action('add_meta_boxes', 'emu_product_gallery_add_metabox');

// Exibe o conteúdo do metabox
function exibir_metabox_galeria_video($post) {
    // Obtém os dados salvos
    $galeria = get_post_meta($post->ID, '_galeria', true);

    // Garante que $galeria seja um array
    if (!is_array($galeria)) {
        $galeria = [];
    }

    // Campo nonce para segurança
    wp_nonce_field('salvar_galeria_video', 'galeria_video_nonce');
    
    ?>
    
    <div id="galeria-container">
        <!-- Botões para adicionar imagem e vídeo -->
        <button type="button" id="add-galeria-imagem" class="button">Adicionar Imagem</button>
        <button type="button" id="add-galeria-video" class="button">Adicionar Vídeo</button>

        <!-- Lista de pré-visualização -->
        <ul class="galeria-list">
                <?php foreach ($galeria as $item): 
             // Verifica se é um vídeo do YouTube
             $is_youtube = preg_match('/(?:youtube\.com\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $item, $matches);
             $video_id = $is_youtube ? $matches[1] : '';
             $thumbnail = $video_id ? 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg' : $item;
         ?>
            <li class="<?php echo $video_id ? 'video-item' : 'image-item'; ?>">
                <img 
                    src="<?php echo esc_url($thumbnail); ?>" 
                    style="width: 100px; height: 100px; object-fit: cover;">
                <a href="#" class="remove-item" data-url="<?php echo esc_url($item); ?>">Excluir</a>
                
                    <?php echo $video_id ? 'Vídeo' : 'Imagem'; ?>
                
            </li>
        <?php endforeach; ?>

        </ul>

        <!-- Campo hidden para armazenar as URLs -->
        <input type="hidden" name="galeria" id="galeria" value="<?php echo esc_attr(json_encode($galeria)); ?>" />
    </div>
    
    <style>

#galeria-container{
    display:flex;
    flex-direction:row;
    flex-wrap:wrap;
    justify-content:space-between;
    gap:2.5%
}
.galeria-list {
    display: grid;
    grid-template-columns: repeat(3, calc(34% - 3.5%));
    gap: 3.5%;
    width:100%!important;
}

.galeria-list li {
    list-style: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap:5px
}

.galeria-list img {
    max-width: 100%!important;
    height: auto!important;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius:5px
}

.galeria-list .remove-item {
    color: white;
    padding:0.1em 0.7em;
    text-decoration: none;
    font-size: 0.8em;
    font-weight:600;
    background-color: #dc3232;
    border-radius:3px!important
}
#add-galeria-imagem{
    background-color:#2271b1!important;
    border-radius:0.3em;
    color:white!important;
    border-color:#2271b1;
    flex-grow:1;
}
#add-galeria-video{
    background-color:#dc3232!important;
    border-radius:0.3em;
    color:white!important;
    border-color:#dc3232;
    flex-grow:1
}

    </style>


    <?php
}

// Salva os dados do metabox (mantido igual)
function salvar_metabox_galeria_video($post_id) {
    if (!isset($_POST['galeria_video_nonce']) || !wp_verify_nonce($_POST['galeria_video_nonce'], 'salvar_galeria_video')) {
        return $post_id;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['galeria'])) {
        $galeria_urls = json_decode(stripslashes($_POST['galeria']), true);
        update_post_meta($post_id, '_galeria', $galeria_urls);
    } else {
        delete_post_meta($post_id, '_galeria');
    }

    return $post_id;
}
add_action('save_post', 'salvar_metabox_galeria_video');

function emu_metabox_gallery_scripts($hook) {
    if ('post.php' != $hook && 'post-new.php' != $hook ) {
        return;
    }

    wp_enqueue_media();
    
    $script_url = plugin_dir_url(__DIR__) . 'assets/js/emu-metabox.js';
    wp_enqueue_script('emu-metabox-gallery-script', $script_url, array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'emu_metabox_gallery_scripts');
