<?php 
// element-test.php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Prefix_Element_Test extends \Bricks\Element {
  // Element properties
  public $category     = 'general'; // Use predefined element category 'general'
  public $name         = 'emu-product-gallery'; // Make sure to prefix your elements
  public $icon         = 'ti-bolt-alt'; // Themify icon font class
  public $css_selector = '.emu-product-gallery-wrapper'; // Default CSS selector
  public $scripts      = ['']; // Script(s) run when element is rendered on frontend or updated in builder

  // Return localised element label
  public function get_label() {
    return esc_html__( 'Emu Product Gallery', 'bricks' );
  }

  // Set builder control groups
  public function set_control_groups() {
   
    $this->control_groups['styles'] = [ // Unique group identifier (lowercase, no spaces)
      'title' => esc_html__( 'Styles', 'bricks' ), // Localized control group title
      'tab' => 'content', // Set to either "content" or "style"
    ];

    $this->control_groups['settings'] = [
      'title' => esc_html__( 'Settings', 'bricks' ),
      'tab' => 'content',
    ];
    
  }
 
  // Set builder controls
  public function set_controls() {

    $this->controls['content'] = [ // Unique control identifier (lowercase, no spaces)
      'tab' => 'content', // Control tab: content/style
      'group' => 'text', // Show under control group
      'label' => esc_html__( 'Content', 'bricks' ), // Control label
      'type' => 'text', // Control type 
      'default' => esc_html__( 'Content goes here ..', 'bricks' ), // Default setting
    ];
    
  }

  // Enqueue element styles and scripts
  public function enqueue_scripts() {
    wp_enqueue_script( 'prefix-test-script' );
  }

  // Render element HTML
  public function render() {

    // Set element attributes
    $root_classes[] = 'prefix-test-wrapper';


    // Add 'class' attribute to element root tag

    $this->set_attribute( '_root', 'class', $root_classes );

    // Render element HTML

    // '_root' attribute is required (contains element ID, class, etc.)

    echo "<div {$this->render_attributes( '_root' )}>"; // Element root attributes

    echo do_shortcode('[emu_product_gallery thumbnail woocommerce]');
    
    echo '</div>';
    
  }
}