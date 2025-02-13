<?php 

if (!defined('ABSPATH')) {
    exit;
}

function emu_product_gallery_shortcode($atts) {
    // Get product_id and variation_id from attributes, if available
    $post_id = isset($atts['product_id']) ? intval($atts['product_id']) : get_the_ID();
    $variation_id = isset($atts['variation_id']) ? intval($atts['variation_id']) : 0;
    
    /* --- Helper Functions --- */
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
        $processing_order[] = array(
            'type' => 'field',
            'value' => 'emu_product_gallery_field'
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
                        $gallery_ids = array_filter(explode(',', $gallery), 'is_numeric');
                        foreach ($gallery_ids as $id) {
                            if ($url = getImageUrlFromId($id)) {
                                $media_list[] = $url;
                            }
                        }
                        
                        // If it's a variation, add its image first
                        if ($variation_id) {
                            $variation_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                            if ($variation_gallery) {
                                $variation_ids = array_filter(explode(',', $variation_gallery), 'is_numeric');
                                if (!empty($variation_ids)) {
                                    $first_variation_image = getImageUrlFromId($variation_ids[0]);
                                    if ($first_variation_image) {
                                        array_unshift($media_list, $first_variation_image);
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
        return '<strong>OPS!</strong> No values were provided for the gallery.';
    }

    /* --- Gallery Rendering --- */
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
            $slides_html .= '<div class="swiper-slide">Error loading image</div>';
        }
        $slides_html .= '</div>';

        $thumbs_html .= sprintf(
            '<div class="swiper-slide"><img src="%s" alt="Thumb %d"></div>',
            esc_url($thumb_url),
            $index+1
        );
    }

    return '<div class="emu-product-gallery-wrapper" style="display:flex; flex-direction:row;">
                <div style="overflow:hidden; position:relative; width:100px; flex-grow:1">
                    <div class="swiper-container emu-main-slider" style="position:relative">
                        <div class="swiper-wrapper">'.$slides_html.'                    
                        
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="swiper-container emu-thumb-slider">
                        <div class="swiper-wrapper">'.$thumbs_html.'</div>
                    </div>
                </div>
            </div>';
}

add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');
