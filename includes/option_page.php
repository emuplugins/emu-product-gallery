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
        'selected_post_types',              // Field ID
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
    $selected = get_option('selected_post_types', array());

    foreach ($post_types as $type) {
        // Creates a checkbox for each post type
        ?>
        <label>
            <input type="checkbox" name="selected_post_types[]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $selected)); ?>>
            <?php echo esc_html($type); ?>
        </label><br>
        <?php
    }
}
