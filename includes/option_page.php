<?php

if (!defined('ABSPATH')) {
    exit;
}

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
        'emu_product_gallery_posttypes',              // Field ID
        'Select Post Types',                // Field title
        'emu_product_gallery_checkbox',     // Field display function
        'emu-product-gallery-options',      // Page slug
        'emu_product_gallery_configuration' // Section to display the field
    );

    // Registers the options field to save
    register_setting(
        'emu_product_gallery_group',        // Settings group
        'emu_product_gallery_posttypes',    // Option name
        'sanitize_emu_product_gallery_posttypes' // Custom sanitization function
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

    // Retrieves the selected post types from the Options Page, ensuring it's an array
    $selected = (array) get_option('emu_product_gallery_posttypes', array());

    foreach ($post_types as $type) {
        // Creates a checkbox for each post type
        ?>
        <label>
            <input type="checkbox" name="emu_product_gallery_posttypes[]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $selected)); ?>>
            <?php echo esc_html($type); ?>
        </label><br>
        <?php
    }
}

// Custom sanitization function to filter and save only selected values
function sanitize_emu_product_gallery_posttypes($input) {
    // Ensure we only keep valid post types in the array
    $valid_post_types = get_post_types(array('public' => true), 'names');
    
    // If no post types are selected, delete the option from the database
    if (empty($input)) {
        delete_option('emu_product_gallery_posttypes');
        return $input; // Return an empty array to avoid saving anything
    }

    // Filter the input to keep only valid post types
    $input = array_filter($input, function($type) use ($valid_post_types) {
        return in_array($type, $valid_post_types);
    });

    // Return the sanitized array
    return array_values($input); // Re-index the array to avoid any gaps
}
