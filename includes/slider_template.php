<?php 

if (!defined('ABSPATH')) {
    exit;
}

function emu_product_gallery_shortcode($atts) {

    // Recupera o ID do produto e da variação se existirem
    $post_id = isset($atts['product_id']) ? intval($atts['product_id']) : get_the_ID();
    $variation_id = isset($atts['variation_id']) ? intval($atts['variation_id']) : 0;

    /* --- Helper Functions --- */

    // Função para recuperar a thumb do youtube pela url
    if (!function_exists('getYoutubeThumbnail')) {
        function getYoutubeThumbnail($url) {
            preg_match('/(?:youtube\.com\/(?:shorts\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
            return !empty($matches[1]) ? 'https://i.ytimg.com/vi/'.$matches[1].'/maxres2.jpg' : '';
        }
    }

    // função para converter o url do yotube para um embed
    if (!function_exists('convertYoutubeUrlToEmbed')) {
        function convertYoutubeUrlToEmbed($url) {
            preg_match('/(?:youtube\.com\/(?:shorts\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
            return !empty($matches[1]) ? 'https://www.youtube.com/embed/'.$matches[1] : '';
        }
    }

    // função para puxar o url da imagem pelo id
    if (!function_exists('getImageUrlFromId')) {
        function getImageUrlFromId($image_id) {
            $image = wp_get_attachment_image_src($image_id, 'full');
            return $image ? $image[0] : '';
        }
    }

    /* --- Attribute Processing --- */
    $processing_order = array();

    foreach ($atts as $key => $value) {

        if (is_numeric($key)) {
            $processing_order[] = array(
                'type' => 'option',
                'value' => strtolower(trim($value))
            );
        } elseif ($key === 'field') {
            $processing_order[] = array(
                'type' => 'field',
                'value' => $value
            );
        }

    }

    if (empty($processing_order)) {
	// Se não houver atributos, exibe a imagem destacada junto com a galeria do WooCommerce
        $processing_order[] = array(
            'type' => 'option',
            'value' => 'thumbnail'
        );
        $processing_order[] = array(
            'type' => 'field',
            'value' => '_product_image_gallery'
        );
    }

    /* --- Media List Construction --- */
    $media_list = array();

    foreach ($processing_order as $item) {
        switch ($item['type']) {
            case 'option':
                switch ($item['value']) {
                    case 'thumbnail':
                        if ($featured = get_the_post_thumbnail_url($post_id, 'full')) {
                            $media_list[] = $featured;
                        }
                        break;
                    case 'woocommerce':
                        // Main product gallery
                        $gallery = get_post_meta($post_id, '_product_image_gallery', true);
                        $gallery_ids = array_filter(explode(',', $gallery));
                        foreach ($gallery_ids as $id) {
                            // Verifica se é um ID de attachment ou URL direta
                            $url = wp_get_attachment_url($id);
                            if ($url) {
                                $media_list[] = $url;
                            } else {
                                // Adiciona diretamente se for um link de URL
                                $media_list[] = $id;
                            }
                        }

                        // Se for uma variação, adiciona sua imagem primeiro
                        if ($variation_id) {
                            $variation_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                            if ($variation_gallery) {
                                $variation_ids = array_filter(explode(',', $variation_gallery), 'is_numeric');
                                if (!empty($variation_ids)) {
                                    $first_variation_image = wp_get_attachment_url($variation_ids[0]);
                                    if ($first_variation_image) {
                                        array_unshift($media_list, $first_variation_image);
                                    } else {
                                        array_unshift($media_list, $variation_ids[0]); // Para links diretos
                                    }
                                }
                            }
                        }
                        break;
                }
                break;
            case 'field':
            $meta_values = array();
            $meta_keys = array_map('trim', explode(',', $item['value']));

            foreach ($meta_keys as $meta_key) {
                // Verificar se é o campo "_thumbnail_id" para pegar a imagem destacada
                if ($meta_key === '_thumbnail_id') {
                    // Recuperar o ID da imagem destacada
                    $thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);
                    if ($thumbnail_id) {
                        // Adicionar a URL da imagem destacada
                        $meta_values[] = wp_get_attachment_url($thumbnail_id);
                    }
                } else {
                    // Para outros campos de post_meta, o comportamento permanece o mesmo
                    if ($content = get_post_meta($post_id, $meta_key, true)) {
                        if (is_array($content)) {
                            $meta_values = array_merge($meta_values, $content);
                        } else {
                            $meta_values = array_merge($meta_values, array_map('trim', explode(',', $content)));
                        }
                    }
                }
            }

            $media_list = array_merge($media_list, $meta_values);
            break;

        }
    }

    if (empty($media_list)) {
        return '<strong>OPS!</strong> No values were provided for the gallery.';
    }

    /* --- Gallery Rendering --- */
    $slides_html = '';
    $thumbs_html = '';
    
    foreach ($media_list as $index => $item) {
    $item = trim($item);
    
    // Definir URLs de embed e thumbnail
    if (is_numeric($item)) {
        $attachment_post = get_post($item);
    
        if ($attachment_post && $attachment_post->post_type === 'attachment') {
            // Verifica se é um vídeo externo (YouTube, etc.)
            if ($attachment_post->post_mime_type === 'oembed/external') {
                $url = $attachment_post->guid;
    
                if (strpos($url, 'youtu') !== false) {
                    $embed_url = convertYoutubeUrlToEmbed($url);
                    $thumb_url = getYoutubeThumbnail($url);
                } else {
                    // Outro tipo de mídia externa
                    $embed_url = $url;
                    $thumb_url = $url;
                }
            } else {
                // É uma imagem comum
                $embed_url = getImageUrlFromId($item);
                $thumb_url = $embed_url;
            }
        } else {
            // Não é um attachment válido
            $embed_url = '';
            $thumb_url = '';
        }
    
    } elseif (strpos($item, 'youtu') !== false) {
        // Caso seja link do YouTube
        $embed_url = convertYoutubeUrlToEmbed($item);
        $thumb_url = getYoutubeThumbnail($item);  // Thumbnail do YouTube
    } else {
        // Outros links diretos (ex: imagens ou vídeos mp4)
        $embed_url = $item;
        $thumb_url = $item;
    }

    // Adiciona a URL do vídeo à lista de mídia
    if ($embed_url) {
        $media_list[] = $embed_url; // Aqui é importante adicionar a URL do vídeo à lista de mídia
    }

    // Verifica se é um vídeo
    $is_video = strpos($embed_url, 'youtube.com') !== false || strpos($embed_url, 'youtu.be') !== false || strpos($embed_url, 'youtu.be') !== false || pathinfo($embed_url, PATHINFO_EXTENSION) === 'mp4';

    // Criar slides
    $slides_html .= '<div class="swiper-slide">';
    if ($is_video) {
        if (strpos($embed_url, 'youtube.com') !== false || strpos($embed_url, 'youtu.be') !== false) {
            // Exibe iframe do YouTube
            $slides_html .= sprintf(
                '<iframe width="100%%" height="100%%" src="%s" title="Video Slide" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                esc_url($embed_url)
            );
        } elseif (pathinfo($embed_url, PATHINFO_EXTENSION) === 'mp4') {
            // Exibe vídeo MP4
            $slides_html .= sprintf(
                '<video width="100%%" height="100%%" controls><source src="%s" type="video/mp4"></video>',
                esc_url($embed_url)
            );
        }
    } else {
        // Exibe imagem
        $slides_html .= sprintf(
            '<img src="%s" alt="Slide %d">',
            esc_url($embed_url),
            $index + 1
        );
    }
    $slides_html .= '</div>';

    // Criar thumbnails com ícone de play para vídeos
    $thumbs_html .= '<div class="swiper-slide">';
    $thumbs_html .= sprintf('<img src="%s" alt="Thumb %d">', esc_url($thumb_url), $index + 1);
    if ($is_video) {
        $thumbs_html .= '<span class="video-icon" aria-label="Vídeo">▶</span>';
    }
    $thumbs_html .= '</div>';
}

$media_list_string = implode(', ', $media_list);
    return '<div class="emu-product-gallery-wrapper loading" style="display:flex; flex-direction:row;">
                <div style="overflow:hidden; position:relative; width:100px; flex-grow:1">
                    <div class="swiper-container emu-main-slider" style="position:relative">
                        <div class="swiper-wrapper">'.$slides_html.'</div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
						' . $media_list_string . '
                    </div>
                    <div class="swiper-container emu-thumb-slider">
                        <div class="swiper-wrapper">'.$thumbs_html.'</div>
                    </div>
                </div>
            </div>';
}

add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');