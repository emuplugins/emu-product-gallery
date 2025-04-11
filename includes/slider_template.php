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

            if($value === 'thumbnail'){

                if ($value) {
                    $thumbnail_id = get_post_thumbnail_id($post_id);
                    if ($thumbnail_id) {
                        $gallery_ids[] = $thumbnail_id;
                    }
                }

            }

            if($value === 'woocommerce'){
                if ( !class_exists( 'WooCommerce' ) ) return;
                if ($value) {
                    $product = wc_get_product($post_id);
                    if ($product) {
                        $gallery_ids = array_merge($gallery_ids, $product->get_gallery_image_ids());
                    }
                }

            }
            
            if($key === 'fields'){

                if (is_array($value)) {
                    foreach ($value as $field_key) {
                        $raw_value = get_post_meta($post_id, $field_key, true);
                        if ($raw_value) {
                            $ids = is_array($raw_value) ? $raw_value : explode(',', $raw_value);
                            foreach ($ids as $id) {
                                $id = trim($id);
                                if (is_numeric($id)) {
                                    $gallery_ids[] = (int) $id;
                                }
                            }
                        }
                    }
                }

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