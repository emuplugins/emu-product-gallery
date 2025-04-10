<?php 

if (!defined('ABSPATH')) {
    exit;
}

class emuProductGallery
{
    private $post_id = null;
    private $post_type = null;
    private $fields = [];
    private $oembed = null;

    public function __construct($post_id, $post_type, $fields = []) {
        $this->post_id = $post_id;
        $this->post_type = $post_type;
        $this->fields = $fields;
        $this->oembed = _wp_oembed_get_object();

    }

    public function getFieldsValues() {

        $post_id = $this->post_id;
        $post_type = $this->post_type;

        $gallery_ids = '';

        if($post_type === 'product'){

            $product = wc_get_product($post_id);

            if($product){
                $gallery_ids = $product->get_gallery_image_ids();
            }

        }

        return $gallery_ids;

    }

    public function getElementType($id) {

        $mime_type = get_post_mime_type($id);
    
        if ($mime_type === 'oembed/external') {
            return 'youtube';
        }else{
            return 'image';
        }
    
        // Você pode adicionar mais tipos conforme a necessidade
        return 'unknown';
    }

    // gera um elemento html do elemento principal
    public function mainSliderElement($element, $type, $html = ''){

        if($type === 'image'){
            $html = '<img src="'. wp_get_attachment_url($element) .'" alt="Thumbnail">';
        }

        if ($type === 'youtube') {

            $video_url = wp_get_attachment_url($element);
        
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w\-]+)/', $video_url, $matches);
            $video_id = $matches[1] ?? '';
        
            // Define manualmente a thumbnail
            $thumb_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";

            if ($video_id) {
                $html = '<lite-youtube videoid="' . esc_attr($video_id) . '" poster="' . esc_url($thumb_url) . '" style="background:black;display:block;height:100%;" params="autoplay=1&rel=0"></lite-youtube>';
            } else {
                $html = '';
            }
        }
        
        

        return $html;    
    }
    
    // gera um elemento html da thumbnail
    public function thumbSliderElement($thumbnail_id, $type){

        $class = '';

        if($type === 'image'){
            $thumb_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
        }
        if($type === 'youtube'){
            $thumb_url = $this->getYoutubeThumbnail( $thumbnail_id );
            $class = 'youtube-thumb';
        }

        $html = '<div class="image '.$class.'"><img src="'. $thumb_url .'" alt="Thumbnail"></div>';
        
        return $html;
    }
    
    public function getYoutubeThumbnail($video_id) {
        return get_post_meta($video_id, '_oembed_thumbnail_url', true);
    }

    function getImageUrlFromId($image_id) {

        $image = wp_get_attachment_image_src($image_id, 'full');
        return $image ? $image[0] : '';
    }

}


function emu_product_gallery_shortcode($atts) {

    $post_id = get_the_ID();

    $emuProductGallery = new emuProductGallery($post_id, 'product');

    $gallery_ids = $emuProductGallery->getFieldsValues(); // retorna um array de IDs

    if( ! is_array($gallery_ids)){
        return 'Nenhuma mídia.';
    }

    ob_start(); ?>

    <div class="emu-splide-wrapper" style="display: flex; gap: 20px;">
        
        <!-- Thumbnails -->
        <div class="splide" id="emu-splide-thumbs">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php 
                    
                    // Aqui vamos buscar o tipo do elemento, e depois buscar o elemento em si

                    foreach($gallery_ids as $item){

                        $type = $emuProductGallery->getElementType($item);

                        echo '<li class="splide__slide">'.$emuProductGallery->thumbSliderElement($item, $type).'</li>';
                    }
                    
                    ?>
                </ul>
            </div>
        </div>

        <!-- Principal -->
        <div class="splide" id="emu-splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php 
                    
                    // Aqui vamos buscar o tipo do elemento, e depois buscar o elemento em si

                    foreach($gallery_ids as $item){

                        $type = $emuProductGallery->getElementType($item);

                        echo '<li class="splide__slide">'.$emuProductGallery->mainSliderElement($item, $type).'</li>';
                    }
                    
                    ?>
                </ul>
            </div>
        </div>

    </div>

    <?php
    return ob_get_clean(); // <- importante
}

add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');