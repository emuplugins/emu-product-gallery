<?php

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
    // Retrieves saved data
    $gallery = get_post_meta($post->ID, 'emu_product_gallery_field', true);

    // Ensures $gallery is an array
    if (!is_array($gallery)) {
        $gallery = [];
    }

    // Nonce field for security
    wp_nonce_field('save_gallery_video', 'gallery_video_nonce');
    
    ?>
    
    <div id="gallery-container">
        
        <!-- Preview list -->
        <ul class="gallery-list">
                <?php foreach ($gallery as $item): 
             // Checks if it's a YouTube video
             $is_youtube = preg_match('/(?:youtube\.com\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $item, $matches);
             $video_id = $is_youtube ? $matches[1] : '';
             $thumbnail = $video_id ? 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg' : $item;
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
        <!-- Buttons to add image and video -->
                <button type="button" id="add-gallery-image" class="button">Add Image</button>
                <button type="button" id="add-gallery-video" class="button">Add Video</button>

        <!-- Hidden field to store the URLs -->
        <input type="hidden" name="gallery" id="gallery" value="<?php echo esc_attr(json_encode($gallery)); ?>" />
    </div>
    
    <style>

#gallery-container{
    display:flex;
    flex-direction:row;
    flex-wrap:wrap;
    gap:0px 10px
}
.gallery-list {
    display: flex;
    gap: 10px;
    width:100%!important;
}

.gallery-list li {
    list-style: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap:5px;
    width:30%;
    max-width:100px
}

.gallery-list img {
    max-width: 100%!important;
    height: auto!important;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius:5px
}

.gallery-list .remove-item {
    color: white;
    padding:0.1em 0.7em;
    text-decoration: none;
    font-size: 0.8em;
    font-weight:600;
    background-color: #dc3232;
    border-radius:3px!important
}
#add-gallery-image{
    background-color:#2271b1!important;
    border-radius:0.3em;
    color:white!important;
    border-color:#2271b1;
}
#add-gallery-video{
    background-color:#dc3232!important;
    border-radius:0.3em;
    color:white!important;
    border-color:#dc3232;
}

    </style>

    <?php
}

// Saves the data from the metabox (unchanged)
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
        $gallery_urls = json_decode(stripslashes($_POST['gallery']), true);
        update_post_meta($post_id, 'emu_product_gallery_field', $gallery_urls);
    } else {
        delete_post_meta($post_id, 'emu_product_gallery_field');
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
