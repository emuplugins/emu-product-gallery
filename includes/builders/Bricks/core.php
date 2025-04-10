<?php

/**
 * Register custom elements
 */


add_action( 'init', function() {
   
    $element_files = [
      'emu-product-gallery',
    ];
  
    foreach ( $element_files as $file ) {
      \Bricks\Elements::register_element(__DIR__ .'/widgets/' . $file . '/widget.php');
    }
  }, 11 );