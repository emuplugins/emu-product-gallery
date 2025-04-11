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
                <ul class="splide__list">
                    <?php foreach($gallery_ids as $item):
                        $type = $emuProductGallery->getElementType($item);
                        echo '<li class="splide__slide">'.$emuProductGallery->mainSliderElement($item, $type).'</li>';
                    endforeach; ?>
                </ul>
            </div>
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