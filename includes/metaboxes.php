<?php

if (!defined('ABSPATH')) {
    exit;
}
// Retrieves the selected post types and uses them in the metabox code
function emu_product_gallery_add_metabox() {
    // Retrieves the selected post types from the Options Page, ensuring it's an array
    $emu_post_types = (array) get_option('emu_product_gallery_posttypes', array());

    // Verifies if there are selected post types before adding the metabox
    // Ensures that the array is not empty and contains valid post types
    if (!empty($emu_post_types) && count(array_filter($emu_post_types)) > 0) {
        add_meta_box(
            'gallery_video_metabox',            // Metabox ID
            'Image and Video Gallery',          // Title
            'display_metabox_gallery_video',    // Function to display the content
            $emu_post_types,                   // Selected post types
            'normal',                           // Context
            'high'                              // Priority
        );
    }
}
add_action('add_meta_boxes', 'emu_product_gallery_add_metabox');

// Displays the content of the metabox
function display_metabox_gallery_video($post) {
    // Recupera os dados salvos
    $gallery = get_post_meta($post->ID, '_product_image_gallery', true);
	
    // Se for uma string (separada por vírgulas), converta para um array
    if (!empty($gallery) && is_string($gallery)) {
        $gallery = explode(',', $gallery);  // Converte a string separada por vírgulas em um array
    }

    // Garante que $gallery seja um array
    if (!is_array($gallery)) {
        $gallery = [];
    }

    // Nonce para segurança
    wp_nonce_field('save_gallery_video', 'gallery_video_nonce');
    
    ?>
    
    <div id="gallery-container">
        
        <!-- Lista de pré-visualização -->
        <ul class="gallery-list">
            <?php foreach ($gallery as $item): 
                // Verifica se é um vídeo do YouTube
                $is_youtube = preg_match('/(?:youtube\.com\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $item, $matches);
                $video_id = $is_youtube ? $matches[1] : '';
                // Se for vídeo do YouTube, define a miniatura, caso contrário, obtém a imagem ou usa a URL
                if ($video_id) {
                    $thumbnail = 'https://i.ytimg.com/vi/' . $video_id . '/maxres2.jpg';
                } elseif (is_numeric($item)) {
                    // Se o item for um ID, obtém a URL da imagem
                    $thumbnail = wp_get_attachment_image_url($item, 'thumbnail');
                } else {
                    // Se já for uma URL, use diretamente
                    $thumbnail = $item;
                }
            ?>
                <li class="<?php echo $video_id ? 'video-item' : 'image-item'; ?>">
                    <img 
                        src="<?php echo esc_url($thumbnail); ?>" 
                        style="width: 100px; height: 100px; object-fit: cover;">
                    <a href="#" class="remove-item" data-url="<?php echo esc_url($item); ?>">Remove</a>
                    <?php echo $video_id ? 'Video' : 'Image'; ?>
                </li>
            <?php endforeach; ?>

        </ul>
        <!-- Botões para adicionar imagem e vídeo -->
        <button type="button" id="add-gallery-image" class="button">Add Image</button>
        <button type="button" id="add-gallery-video" class="button">Add Video</button>

        <!-- Campo oculto para armazenar os IDs -->
        <input type="hidden" name="gallery" id="gallery" value="<?php echo esc_attr(json_encode($gallery)); ?>" />
    </div>
    
    <style>
        #gallery-container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0px 10px;
        }
        .gallery-list {
            display: flex;
            gap: 10px;
            width: 100% !important;
            flex-wrap: wrap;
        }
        .gallery-list li {
            list-style: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            width: 30%;
            max-width: 100px;
        }
        .gallery-list img {
            max-width: 100% !important;
            height: auto !important;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 5px;
        }
        .gallery-list .remove-item {
            color: white;
            padding: 0.1em 0.7em;
            text-decoration: none;
            font-size: 0.8em;
            font-weight: 600;
            background-color: #dc3232;
            border-radius: 3px !important;
        }
        #add-gallery-image {
            background-color: #2271b1 !important;
            border-radius: 0.3em;
            color: white !important;
            border-color: #2271b1;
        }
        #add-gallery-video {
            background-color: #dc3232 !important;
            border-radius: 0.3em;
            color: white !important;
            border-color: #dc3232;
        }
    </style>
    <?php
}

function save_metabox_gallery_video($post_id) {
    if (!isset($_POST['gallery_video_nonce']) || !wp_verify_nonce($_POST['gallery_video_nonce'], 'save_gallery_video')) {
        return $post_id;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    if (isset($_POST['gallery'])) {
        // Decodifica os dados JSON
        $gallery_urls = json_decode(stripslashes($_POST['gallery']), true);
        
        // Garante que temos um array válido e converte para uma string separada por vírgulas
        if (is_array($gallery_urls)) {
            // Converte os IDs em uma string separada por vírgulas
            $gallery_urls = implode(',', $gallery_urls);
        }
        
        // Atualiza os metadados do post com a string separada por vírgulas
        update_post_meta($post_id, '_product_image_gallery', $gallery_urls);
    } else {
        delete_post_meta($post_id, '_product_image_gallery');
    }

    return $post_id;
}
add_action('save_post', 'save_metabox_gallery_video');

function emu_metabox_gallery_scripts($hook) {
    if ('post.php' != $hook && 'post-new.php' != $hook ) {
        return;
    }

    wp_enqueue_media();
    
    $script_url = plugin_dir_url(__DIR__) . 'assets/js/emu-metabox.js';
    wp_enqueue_script('emu-metabox-gallery-script', $script_url, array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'emu_metabox_gallery_scripts');
