<?php
// Creates the Options Page for the Emu Product Gallery plugin
function emu_product_gallery_options() {
    // Adds the options page to the menu
    add_menu_page(
        'Emu Product Gallery',             // Page title
        'Emu Product Gallery',             // Menu title
        'manage_options',                  // User permissions
        'emu-product-gallery-options',     // Page slug
        'emu_product_gallery_page',        // Function to display the page
        'dashicons-images-alt2',           // Menu icon
        100                                // Menu position
    );

    // Adds a settings section to the options page
    add_settings_section(
        'emu_product_gallery_configuration', // Section ID
        'Metabox Settings',                  // Section title
        '',                                  // Display function (not used)
        'emu-product-gallery-options'        // Page slug
    );

    // Adds the checkbox field
    add_settings_field(
        'post_types_selected',              // Field ID
        'Select Post Types',                // Field title
        'emu_product_gallery_checkbox',     // Field display function
        'emu-product-gallery-options',      // Page slug
        'emu_product_gallery_configuration' // Section to display the field
    );

    // Registers the options field to save
    register_setting(
        'emu_product_gallery_group',        // Settings group
        'selected_post_types',              // Option name
        'sanitize_text_field'               // Sanitization function
    );
}
add_action('admin_menu', 'emu_product_gallery_options');

// Displays the options page with the checkbox field
function emu_product_gallery_page() {
    ?>
    <div class="wrap">
        <h1>Emu Product Gallery</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('emu_product_gallery_group');
            do_settings_sections('emu-product-gallery-options');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Displays the checkbox field to select post types
function emu_product_gallery_checkbox() {
    // Gets all public post types
    $post_types = get_post_types(array('public' => true), 'names');

    // Retrieves the selected post types from the Options Page
    $selected = get_option('post_types_selected', array());

    foreach ($post_types as $type) {
        // Creates a checkbox for each post type
        ?>
        <label>
            <input type="checkbox" name="post_types_selected[]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $selected)); ?>>
            <?php echo esc_html($type); ?>
        </label><br>
        <?php
    }
}

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
?>
