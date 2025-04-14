<?php 

if (!defined('ABSPATH')) {
    exit;
}
class emuProductGallery
{
    private $post_id = null;
    private $oembed = null;
    private $options = [];
    

    public function __construct($post_id, $options = []) {
        
        $this->post_id = $post_id;
        $this->options = $options;
        $this->oembed = _wp_oembed_get_object();

        $enqueueScripts = isset( $options['enqueue']) ?  $options['enqueue'] : true;

        $enqueueScripts = filter_var($enqueueScripts, FILTER_VALIDATE_BOOLEAN);

        $this->enqueueScripts($enqueueScripts);
        
    }
    
    public function enqueueScripts($enqueueScripts) {
        
        if($enqueueScripts){

            wp_enqueue_script('epg-splide-script');
            wp_enqueue_script('emu-product-gallery-script');
                
        };

    }

    public function getFieldsValues() {
        $post_id = $this->post_id;
        $gallery_ids = [];
    
        foreach ($this->options as $key => $value) {
    
            // Para campos simples (valores)
            if ($value === 'thumbnail') {
                $thumbnail_id = get_post_thumbnail_id($post_id);
                if ($thumbnail_id) {
                    $gallery_ids[] = $thumbnail_id;
                }
            }
    
            if ($value === 'woocommerce') {
                if (class_exists('WooCommerce')) {
                    $product = wc_get_product($post_id);
                    if ($product) {
                        $gallery_ids = array_merge($gallery_ids, $product->get_gallery_image_ids());
                    }
                }
            }

            if (!is_array($value) && trim($value, ', ') !== '') {
                $value = array_map('trim', explode(',', $value));
            }

            if ($key === 'itemslist') {

                $items = [];
            
                if (!is_array($value)) {
                    // Transforma string separada por vírgula em array
                    $items = array_map('trim', explode(',', $value));
                } else {
                    $items = $value;
                }
            
                foreach ($items as $item) {
                    $item = trim($item);
            
                    if (is_numeric($item)) {
                        $gallery_ids[] = (int) $item;
                    } elseif (filter_var($item, FILTER_VALIDATE_URL)) {
                        $gallery_ids[] = $item;
                    }
                }
            }
            
        
            if ($key === 'fields' && is_array($value)) {

                foreach ($value as $field_key) {
    
                    // Suporte especial para campo da galeria do WooCommerce
                    if ($field_key === '_product_image_gallery') {
                        $product = wc_get_product($post_id);
                        if ($product) {
                            $gallery_ids = array_merge($gallery_ids, $product->get_gallery_image_ids());
                        }
                        continue;
                    }
    
                    $raw_value = get_post_meta($post_id, $field_key, true);
                    
                    if ($raw_value) {
                        if (is_array($raw_value)) {
                            foreach ($raw_value as $item) {
                                $item = trim($item);
                                if (is_numeric($item)) {
                                    $gallery_ids[] = (int) $item;
                                } elseif (filter_var($item, FILTER_VALIDATE_URL)) {
                                    $gallery_ids[] = $item;
                                }
                            }
                        } else {
                            // Trata string: pode ser IDs separados por vírgula ou uma URL
                            $items = explode(',', $raw_value);
                            foreach ($items as $item) {
                                $item = trim($item);
                                if (is_numeric($item)) {
                                    $gallery_ids[] = (int) $item;
                                } elseif (filter_var($item, FILTER_VALIDATE_URL)) {
                                    $gallery_ids[] = $item;
                                }
                            }
                        }
                    }
                }
            }
        }
    
        // Remove valores vazios e duplicados
        return array_filter($gallery_ids);
    }
    
    

    public function getElementType($id) {

        if (is_numeric($id)) {
            $mime_type = get_post_mime_type($id);
    
            if ($mime_type === 'oembed/external') {
                return 'youtube';
            } else {
                return 'image';
            }
        } elseif (str_contains($id, 'yout')) {
            return 'youtube';
        } else {
            return 'image';
        }
    }
    

    // gera um elemento html do elemento principal
    public function mainSliderElement($element, $type, $html = '') {

        // Garante que $attatchmenturl esteja sempre definido (pode ser útil mais adiante)
        if (is_numeric($element)) {
            $attatchmenturl = wp_get_attachment_url($element);
        } else {
            $attatchmenturl = $element;
        }
    
        if ($type === 'image') {
            // Usa $attatchmenturl, que pode ser a URL do anexo (se ID) ou a URL direta
            $html = '<img src="' . esc_url($attatchmenturl) . '" alt="Thumbnail">';
        }
    
        if ($type === 'youtube') {
            $video_url = $attatchmenturl;
    
            // Captura o ID do vídeo do YouTube
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w\-]+)/', $video_url, $matches);
            $video_id = $matches[1] ?? '';
    
            if ($video_id) {
                $thumb_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
                $html = '<lite-youtube videoid="' . esc_attr($video_id) . '" poster="' . esc_url($thumb_url) . '" style="background:black;display:block;height:100%;" params="autoplay=1&rel=0"></lite-youtube>';
            } else {
                $html = '';
            }
        }
    
        return $html;
    }    
    
    // gera um elemento html da thumbnail
    public function thumbSliderElement($thumbnail_id, $type) {

        $thumb_url = '';
        $class = '';
    
        if (is_numeric($thumbnail_id)) {
    
            if ($type === 'image') {
                $thumb_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
            }
    
            if ($type === 'youtube') {
                $thumb_url = $this->getYoutubeThumbnail($thumbnail_id);
                $class = 'youtube-thumb';
            }
    
        } else {
           
    
            if ($type === 'youtube') {
                $thumb_url =  $this->getYoutubeThumbnail($thumbnail_id);
                $class = 'youtube-thumb';
            }else{
                $thumb_url =  $thumbnail_id;
            }
        }
    
        $html = '<div class="image ' . esc_attr($class) . '"><img src="' . esc_url($thumb_url) . '" alt="Thumbnail"></div>';
    
        return $html;
    }
    
    
    public function getYoutubeThumbnail($video_id) {

        // Se não for numérico, assume que é uma URL
        if (!is_numeric($video_id)) {
    
            // Remove parâmetros após o ID (ex: &t=10882s)
            $video_url = strtok($video_id, '&');
    
            // Captura o ID do vídeo
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w\-]+)/', $video_url, $matches);
            $video_id = $matches[1] ?? '';
    
            if ($video_id) {
                return "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
            }
    
            return ''; // fallback seguro se não encontrar o ID
        }
    
        // Caso seja o ID de um post que contenha um embed
        return get_post_meta($video_id, '_oembed_thumbnail_url', true);
    }
    

    function getImageUrlFromId($image_id) {

        $image = wp_get_attachment_image_src($image_id, 'full');
        return $image ? $image[0] : '';
    }

}

function emu_product_gallery_shortcode($atts) {
 
    $post_id = get_the_ID();

    if (!$post_id) {
        $post_id = get_queried_object_id();
    }

    $emuProductGallery = new emuProductGallery($post_id, $atts);

    $gallery_ids = $emuProductGallery->getFieldsValues();

    if (!is_array($gallery_ids) || empty($gallery_ids)) {
        return 'Nenhuma mídia.';
    }

    $direction = isset($atts['direction']) ? $atts['direction'] : 'ltr';
    
    $customAttr = isset($atts['customattr']) ? $atts['customattr'] : false;

    $fdRow = '';
    $fdColumn = '';
    $thumbsHeight = '';
    $mainSliderWidth = '';

    if($direction && $direction != 'ttb'){
        $fdRow = 'flex-direction: row';
        $fdColumn = 'flex-direction: column';
    }
    if($direction && $direction == 'ltr'){
        $fdRow = 'flex-direction: row';
        $fdColumn = 'flex-direction: column';
        $thumbsHeight = 'min-width: auto; min-height:auto';
        $mainSliderWidth = 'min-width: 100%;';
    }
    
    ob_start(); ?>

    

    <?php 
        if(empty($customAttr)) :
    ?>

    <div style="<?= $fdColumn ?>;" class="emu-splide-wrapper">

    <?php endif;?>


        <div class="splide" id="emu-splide-thumbs" data-splide='{"direction": "<?= $direction ?>", "height": "auto", "fixedWidth": "auto", "fixedHeight": "auto", "isNavigation": true, "pagination": false, "arrows": false, "focus": 0}' style="<?= $thumbsHeight ?>">
            <div class="splide__track">
                <ul class="splide__list" style="<?= $fdRow ?>;<?= $thumbsHeight ?>">
                    <?php foreach($gallery_ids as $item):
                        $type = $emuProductGallery->getElementType($item);
                        echo '<li class="splide__slide">'.$emuProductGallery->thumbSliderElement($item, $type).'</li>';
                    endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="splide" id="emu-splide" data-splide='{"pagination": false, "height": "auto", "cover": true, "rewind": true}' style="<?= $mainSliderWidth ?>">
            <div class="splide__track">

                <div class="epg-lightbox-toogle epg-lightbox-clickable" onclick="toggleLightbox()">
            
                    <svg fill="#000000" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" viewBox="0 0 31.812 31.906"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M31.728,31.291 C31.628,31.535 31.434,31.729 31.190,31.830 C31.069,31.881 30.940,31.907 30.811,31.907 L23.851,31.907 C23.301,31.907 22.856,31.461 22.856,30.910 C22.856,30.359 23.301,29.913 23.851,29.913 L28.405,29.908 L19.171,20.646 C18.782,20.257 18.782,19.626 19.171,19.236 C19.559,18.847 20.188,18.847 20.577,19.236 L29.906,28.593 L29.906,23.906 C29.906,23.355 30.261,22.933 30.811,22.933 C31.360,22.933 31.805,23.379 31.805,23.930 L31.805,30.910 C31.805,31.040 31.779,31.169 31.728,31.291 ZM30.811,8.973 C30.261,8.973 29.906,8.457 29.906,7.906 L29.906,3.313 L20.577,12.669 C20.382,12.864 20.128,12.962 19.874,12.962 C19.619,12.962 19.365,12.864 19.171,12.669 C18.782,12.280 18.782,11.649 19.171,11.259 L28.497,1.906 L23.906,1.906 C23.356,1.906 22.856,1.546 22.856,0.996 C22.856,0.445 23.301,-0.001 23.851,-0.001 L30.811,-0.001 C30.811,-0.001 30.811,-0.001 30.812,-0.001 C30.941,-0.001 31.069,0.025 31.190,0.076 C31.434,0.177 31.628,0.371 31.728,0.615 C31.779,0.737 31.805,0.866 31.805,0.996 L31.805,7.976 C31.805,8.526 31.360,8.973 30.811,8.973 ZM3.387,29.908 L7.942,29.913 C8.492,29.913 8.936,30.359 8.936,30.910 C8.936,31.461 8.492,31.907 7.942,31.907 L0.982,31.907 C0.853,31.907 0.724,31.881 0.602,31.830 C0.359,31.729 0.165,31.535 0.064,31.291 C0.014,31.169 -0.012,31.040 -0.012,30.910 L-0.012,23.930 C-0.012,23.379 0.433,22.933 0.982,22.933 C1.532,22.933 1.906,23.355 1.906,23.906 L1.906,28.573 L11.216,19.236 C11.605,18.847 12.234,18.847 12.622,19.236 C13.011,19.626 13.011,20.257 12.622,20.646 L3.387,29.908 ZM11.919,12.962 C11.665,12.962 11.410,12.864 11.216,12.669 L1.906,3.332 L1.906,7.906 C1.906,8.457 1.532,8.973 0.982,8.973 C0.433,8.973 -0.012,8.526 -0.012,7.976 L-0.012,0.996 C-0.012,0.866 0.014,0.737 0.064,0.615 C0.165,0.371 0.359,0.177 0.602,0.076 C0.723,0.025 0.852,-0.001 0.980,-0.001 C0.981,-0.001 0.981,-0.001 0.982,-0.001 L7.942,-0.001 C8.492,-0.001 8.936,0.445 8.936,0.996 C8.936,1.546 8.456,1.906 7.906,1.906 L3.296,1.906 L12.622,11.259 C13.011,11.649 13.011,12.280 12.622,12.669 C12.428,12.864 12.174,12.962 11.919,12.962 Z"></path> </g></svg>

                </div>
                
                <ul class="splide__list">
                    <?php foreach($gallery_ids as $item):
                        $type = $emuProductGallery->getElementType($item);
                        echo '<li class="splide__slide">'.$emuProductGallery->mainSliderElement($item, $type).'</li>';
                    endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="epg-lightbox epg-lightbox-clickable">

            <div class="epg-lightbox-arrows">

                <div class="epg-lightbox-arrow left epg-lightbox-clickable" stroke="/* #000 */">
                    <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none"><g id="SVGRepo_iconCarrier"> <path d="M9 20L17 12L9 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></svg>
                </div>

                <div class="epg-lightbox-arrow right epg-lightbox-clickable">
                    <svg xmlns="http://www.w3.org/2000/svg" width="800px" height="800px" viewBox="0 0 24 24" fill="none"><g id="SVGRepo_iconCarrier"> <path d="M9 20L17 12L9 4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></g></svg>   
                </div>

            </div>

            <div class="epg-lightbox-content epg-lightbox-clickable"> <!-- Content here! --></div>
            
        </div>
    

    <?php 
    if(empty($customAttr)) :
    ?>

    </div>

    <?php endif;?>

    <?php
    return ob_get_clean();
}

add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');