<?php 
function emu_product_gallery_shortcode($atts) {
    $post_id = get_the_ID();
    
    /* --- Funções Auxiliares --- */
    if (!function_exists('getYoutubeThumbnail')) {
        function getYoutubeThumbnail($url) {
            preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
            return !empty($matches[1]) ? 'https://img.youtube.com/vi/'.$matches[1].'/maxresdefault.jpg' : '';
        }
    }
    
    if (!function_exists('convertYoutubeUrlToEmbed')) {
        function convertYoutubeUrlToEmbed($url) {
            preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
            return !empty($matches[1]) ? 'https://www.youtube.com/embed/'.$matches[1] : '';
        }
    }
    
    if (!function_exists('getImageUrlFromId')) {
        function getImageUrlFromId($image_id) {
            $image = wp_get_attachment_image_src($image_id, 'full');
            return $image ? $image[0] : '';
        }
    }

    /* --- Processamento dos Atributos --- */
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
        $processing_order[] = array(
            'type' => 'field',
            'value' => 'emu_product_gallery_field'
        );
    }

    /* --- Construção da Lista de Mídias --- */
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
                            // Buscar imagens da galeria principal do produto
                            if ($gallery = get_post_meta($post_id, '_product_image_gallery', true)) {
                                $gallery_ids = array_filter(explode(',', $gallery), 'is_numeric');
                                foreach ($gallery_ids as $id) {
                                    if ($url = getImageUrlFromId($id)) {
                                        $media_list[] = $url;
                                    }
                                }
                            }
                        
                            // Se for uma variação do produto, buscar apenas as imagens associadas à variação
                            if (isset($atts['variation_id']) && $variation_id = $atts['variation_id']) {
                                $variation_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                                if ($variation_gallery) {
                                    $gallery_ids = array_filter(explode(',', $variation_gallery), 'is_numeric');
                                    // Adiciona apenas a imagem da variação como primeira imagem da galeria
                                    if ($gallery_ids) {
                                        $first_variation_image = getImageUrlFromId($gallery_ids[0]); // Primeira imagem da variação
                                        if ($first_variation_image) {
                                            array_unshift($media_list, $first_variation_image); // Coloca no começo da galeria
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
                    if ($content = get_post_meta($post_id, $meta_key, true)) {
                        if (is_array($content)) {
                            $meta_values = array_merge($meta_values, $content);
                        } else {
                            $meta_values = array_merge($meta_values, array_map('trim', explode(',', $content)));
                        }
                    }
                }
                
                $media_list = array_merge($media_list, $meta_values);
                break;
        }
    }

    if (empty($media_list)) {
        return '<strong>OPS!</strong> Nenhum valor foi fornecido para a galeria.';
    }

    /* --- Renderização da Galeria --- */
    $slides_html = '';
    $thumbs_html = '';
    
    foreach ($media_list as $index => $item) {
        $item = trim($item);
        $embed_url = is_numeric($item) ? getImageUrlFromId($item) : (
            strpos($item, 'youtu') !== false ? convertYoutubeUrlToEmbed($item) : $item
        );
        $thumb_url = is_numeric($item) ? getImageUrlFromId($item) : (
            strpos($item, 'youtu') !== false ? getYoutubeThumbnail($item) : $item
        );

        $slides_html .= '<div class="swiper-slide">';
        if (strpos($embed_url, 'youtube.com') !== false) {
            $slides_html .= sprintf(
                '<iframe width="100%%" height="100%%" src="%s" title="Video Slide" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                esc_url($embed_url)
            );
        } elseif (pathinfo($embed_url, PATHINFO_EXTENSION) === 'mp4') {
            $slides_html .= sprintf(
                '<video width="100%%" height="100%%" controls><source src="%s" type="video/mp4"></video>',
                esc_url($embed_url)
            );
        } elseif (!empty($embed_url)) {
            $slides_html .= sprintf(
                '<img src="%s" alt="Slide %d">',
                esc_url($embed_url),
                $index+1
            );
        } else {
            $slides_html .= '<div class="swiper-slide">Erro ao carregar imagem</div>';
        }
        $slides_html .= '</div>';

        $thumbs_html .= sprintf(
            '<div class="swiper-slide"><img src="%s" alt="Thumb %d"></div>',
            esc_url($thumb_url),
            $index+1
        );
    }

    return '<div style="overflow:hidden; position:relative">
                <div class="swiper-container emu-main-slider" style="position:relative">
                    <div class="swiper-wrapper">'.$slides_html.'                    
                    <div class="swiper-slide gambiarra"></div>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
                <div class="swiper-container emu-thumb-slider">
                    <div class="swiper-wrapper">'.$thumbs_html.'</div>
                </div>
            </div>';
}

add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');





















// Hook do WooCommerce para capturar a variação selecionada
function capture_variation_id_for_gallery() {
    ?>
    <script type="text/javascript">
        jQuery(function($){
            $('form.cart').on('found_variation', function(event, variation) {
                // Envia a variação selecionada para o backend
                var variation_id = variation.variation_id;
                var product_id = $('input[name="product_id"]').val();
                
                // Atualiza o shortcode com a variação correta
                if (variation_id && product_id) {
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        method: 'POST',
                        data: {
                            action: 'update_gallery_with_variation',
                            variation_id: variation_id,
                            product_id: product_id
                        },
                        success: function(response) {
                            $('.product-gallery-container').html(response);
                        }
                    });
                }
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'capture_variation_id_for_gallery');

// Função para processar a resposta do AJAX e atualizar a galeria
function update_gallery_with_variation() {
    $variation_id = isset($_POST['variation_id']) ? (int) $_POST['variation_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    
    if (!$variation_id || !$product_id) {
        wp_send_json_error('Invalid variation or product ID.');
    }

    // Captura as imagens da variação selecionada
    $media_list = array();

    // Buscar a galeria da variação
    $variation_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
    if ($variation_gallery) {
        $gallery_ids = array_filter(explode(',', $variation_gallery), 'is_numeric');
        foreach ($gallery_ids as $id) {
            if ($url = getImageUrlFromId($id)) {
                $media_list[] = $url;
            }
        }
    }

    // Caso não tenha variação, buscar as imagens do produto principal
    if (empty($media_list)) {
        $gallery = get_post_meta($product_id, '_product_image_gallery', true);
        $gallery_ids = array_filter(explode(',', $gallery), 'is_numeric');
        foreach ($gallery_ids as $id) {
            if ($url = getImageUrlFromId($id)) {
                $media_list[] = $url;
            }
        }
    }

    // Renderiza as imagens da galeria
    if (!empty($media_list)) {
        $slides_html = '';
        $thumbs_html = '';

        foreach ($media_list as $index => $item) {
            $item = trim($item);
            $thumb_url = is_numeric($item) ? getImageUrlFromId($item) : $item;

            $slides_html .= sprintf(
                '<div class="swiper-slide"><img src="%s" alt="Slide %d"></div>',
                esc_url($item),
                $index + 1
            );

            $thumbs_html .= sprintf(
                '<div class="swiper-slide"><img src="%s" alt="Thumb %d"></div>',
                esc_url($thumb_url),
                $index + 1
            );
        }

        wp_send_json_success('<div class="swiper-container emu-main-slider"><div class="swiper-wrapper">'.$slides_html.'</div></div><div class="swiper-container emu-thumb-slider"><div class="swiper-wrapper">'.$thumbs_html.'</div></div>');
    } else {
        wp_send_json_error('No images found for the selected variation.');
    }
}
add_action('wp_ajax_update_gallery_with_variation', 'update_gallery_with_variation');
add_action('wp_ajax_nopriv_update_gallery_with_variation', 'update_gallery_with_variation');
