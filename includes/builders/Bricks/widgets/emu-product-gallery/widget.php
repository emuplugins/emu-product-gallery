<?php 
// element-test.php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Prefix_Element_Test extends \Bricks\Element {
  // Element properties
  public $category     = 'general'; // Use predefined element category 'general'
  public $name         = 'emu-product-gallery'; // Make sure to prefix your elements
  public $icon         = 'ti-bolt-alt'; // Themify icon font class
  public $css_selector = ''; // Default CSS selector
  public $scripts      = ['mountEmuProductGallery']; // Script(s) run when element is rendered on frontend or updated in builder

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

    
    $this->controls['GeneralInfo'] = [
      'tab' => 'content',
      'group' => 'styles',
      'content' => esc_html__( 'General', 'bricks' ),
      'type' => 'info',
    ];

    // Example CSS
    $this->controls['fullScreenButton'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Full Screen', 'bricks' ),
      'type' => 'select',
      'options' => [
        'block' => esc_html__( 'Show', 'bricks' ),
        'none' => esc_html__( 'Hide', 'bricks' ),
      ],
      'css' => [
        [
          'property' => 'display',
          'selector' => '.epg-lightbox-toogle',
          'value' => '%s !important',
        ],
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Select', 'bricks' ),
      'default' => 'block', // Option key
    ];


    $this->controls['sliderHeight'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Slider height', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => '--slider-height',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 300,
          'max' => 800,
          'step' => 1,
        ],
      ],
      'default' => '500px',
      'description' => esc_html__( 'Altura do slider', 'bricks' ),
    ];

    $this->controls['sliderGap'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Slider gap', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'gap',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '10px',
      'description' => esc_html__( 'Espaço entre o slider e a thumbnail.', 'bricks' ),
    ];


    $this->controls['slidesInfo'] = [
      'tab' => 'content',
      'group' => 'styles',
      'content' => esc_html__( 'Slides', 'bricks' ),
      'type' => 'info',
    ];

    // Example CSS
    $this->controls['slideImageSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Image Size', 'bricks' ),
      'type' => 'select',
      'options' => [
        'cover' => esc_html__( 'Cover', 'bricks' ),
        '100% 100%' => esc_html__( 'Stretch', 'bricks' ),
        'contain' => esc_html__( 'Contain', 'bricks' ),
      ],
      'css' => [
        [
          'property' => 'background-size',
          'selector' => '#emu-splide .splide__slide',
          'value' => ' %s !important;'
        ],
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Select', 'bricks' ),
      'default' => 'cover',
    ];

    // Example CSS
    $this->controls['slideVideoSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Video Image Size', 'bricks' ),
      'type' => 'select',
      'options' => [
        'cover' => esc_html__( 'Cover', 'bricks' ),
        '100% 100%' => esc_html__( 'Stretch', 'bricks' ),
        'contain' => esc_html__( 'Contain', 'bricks' ),
      ],
      'css' => [
        [
          'property' => 'background-size',
          'selector' => 'lite-youtube',
          'value' => ' %s !important;'
        ],
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Select', 'bricks' ),
      'default' => 'contain',
    ];

    
    $this->controls['slideBorder'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Borda', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '#emu-splide .splide__track',
        ],
      ],
      'inline' => true,
      'small' => true,

    ];

    $this->controls['thumbsInfo'] = [
      'tab' => 'content',
      'group' => 'styles',
      'content' => esc_html__( 'Thumbnails', 'bricks' ),
      'type' => 'info',
    ];

       // Example CSS
       $this->controls['thumbImageSize'] = [
        'tab' => 'content',
        'group' => 'styles',
        'label' => esc_html__( 'Image Size', 'bricks' ),
        'type' => 'select',
        'options' => [
          'cover' => esc_html__( 'Cover', 'bricks' ),
          'fill' => esc_html__( 'Stretch', 'bricks' ),
          'contain' => esc_html__( 'Contain', 'bricks' ),
        ],
        'css' => [
          [
            'property' => 'object-fit',
            'selector' => '#emu-splide-thumbs .image img',
            'value' => ' %s !important;'
          ],
        ],
        'inline' => true,
        'placeholder' => esc_html__( 'Select', 'bricks' ),
        'default' => 'cover',
        'description' => esc_html__( 'Como as thumbnails são quadradas, isso só surtirá efeito quando o elemento img estiver em um formato que não seja quadrado.', 'bricks' ),
      ];
  
      // Example CSS
      $this->controls['thumbVideoImageSize'] = [
        'tab' => 'content',
        'group' => 'styles',
        'label' => esc_html__( 'Video image Size', 'bricks' ),
        'type' => 'select',
        'options' => [
          'cover' => esc_html__( 'Cover', 'bricks' ),
          'fill' => esc_html__( 'Stretch', 'bricks' ),
          'contain' => esc_html__( 'Contain', 'bricks' ),
        ],
        'css' => [
          [
            'property' => 'object-fit',
            'selector' => '#emu-splide-thumbs .youtube-thumb img',
            'value' => ' %s !important;'
          ],
        ],
        'inline' => true,
        'placeholder' => esc_html__( 'Select', 'bricks' ),
        'default' => 'cover',
      ];

    

      $this->controls['thumbnailswidth'] = [
        'tab' => 'content',
        'group' => 'styles',
        'label' => esc_html__( 'width', 'bricks' ),
        'type' => 'slider',
        'css' => [
          [
            'property' => 'width',
            'selector' => '#emu-splide-thumbs ul, #emu-splide-thumbs .image',
            'value' => '%s !important',
          ],
        ],
        'units' => [
          'px' => [
            'min' => 0,
            'max' => 100,
            'step' => 1,
          ],
        ],
        'default' => '100px',
        'description' => esc_html__( 'Espaço entre as thumbnails.', 'bricks' ),
      ];
      
      $this->controls['thumbnailsheight'] = [
        'tab' => 'content',
        'group' => 'styles',
        'label' => esc_html__( 'height', 'bricks' ),
        'type' => 'slider',
        'css' => [
          [
            'property' => 'height',
            'selector' => '#emu-splide-thumbs .image',
            'value' => '%s !important',
          ],
        ],
        'units' => [
          'px' => [
            'min' => 0,
            'max' => 100,
            'step' => 1,
          ],
        ],
        'default' => '100px',
        'description' => esc_html__( 'Espaço entre as thumbnails.', 'bricks' ),
      ];


    $this->controls['thumbnailsGap'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Gap', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'gap',
          'selector' => '#emu-splide-thumbs ul',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '10px',
      'description' => esc_html__( 'Espaço entre as thumbnails.', 'bricks' ),
    ];

    $this->controls['thumbBorder'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Borda', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '#emu-splide-thumbs ul li',
        ],
      ],
      'inline' => true,
      'small' => true,

    ];

    $this->controls['arrowsInfo'] = [
      'tab' => 'content',
      'group' => 'styles',
      'content' => esc_html__( 'Arrows', 'bricks' ),
      'type' => 'info',
    ];

    $this->controls['arrowIconColor'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Background color', 'bricks' ),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'fill',
          'selector' => '.splide__arrow svg',
        ]
      ],
      'default' => [
        'hex' => '#000',
      ],
    ];

    $this->controls['arrowBackgroundColor'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Background color', 'bricks' ),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '.splide__arrow',
        ]
      ],
      'default' => [
        'hex' => '#fff',
      ],
    ];

    $this->controls['arrowsSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Size', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.splide__arrow',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '40px',
      'description' => esc_html__( 'Arrows size', 'bricks' ),
    ];
    
    $this->controls['arrowsIconSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Icon Size', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.splide__arrow svg',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '20px',
      'description' => esc_html__( 'Arrows size', 'bricks' ),
    ];

    $this->controls['arrowsBorder'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Borda', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.splide__arrow',
        ],
      ],
      'inline' => true,
      'small' => true,

    ];

    

    $this->controls['lightboxInfo'] = [
      'tab' => 'content',
      'group' => 'styles',
      'content' => esc_html__( 'Lightbox', 'bricks' ),
      'type' => 'info',
    ];

    $this->controls['lightboxBorder'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Borda', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.epg-lightbox-content',
        ],
      ],
      'inline' => true,
      'small' => true,

    ];

    $this->controls['lightboxAspectRatio'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Aspect Ratio', 'bricks' ),
      'type' => 'text',
      'spellcheck' => true, // Default: false
      // 'trigger' => 'enter', // Default: 'enter'
      'css' => [
        [
          'property' => 'aspect-ratio',
          'selector' => '.epg-lightbox-content',
        ],
      ],
      'default' => '16/9'
    ];

    $this->controls['lightboxWidth'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Width', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.epg-lightbox-content',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 500,
          'max' => 1500,
          'step' => 1,
        ],
      ],
      'default' => '1200px',
      'description' => esc_html__( 'lightbox width', 'bricks' ),
    ];

    $this->controls['LightboxArrowsSpace'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Arrows Space', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.epg-lightbox-arrows',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 500,
          'max' => 1500,
          'step' => 1,
        ],
      ],
      'default' => '1400px',
      'description' => esc_html__( 'lightbox width', 'bricks' ),
    ];
    

    $this->controls['lightboxArrowIconColor'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Background color', 'bricks' ),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'stroke',
          'selector' => '.epg-lightbox-arrow svg',
        ]
      ],
      'default' => [
        'hex' => '#000',
      ],
    ];

    $this->controls['lightboxArrowBackgroundColor'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Background color', 'bricks' ),
      'type' => 'color',
      'inline' => true,
      'css' => [
        [
          'property' => 'background-color',
          'selector' => '.epg-lightbox-arrow',
        ]
      ],
      'default' => [
        'hex' => '#fff',
      ],
    ];

    $this->controls['lightboxArrowsSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Size', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.epg-lightbox-arrow',
          'value' => '%s !important; height: %s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '40px',
      'description' => esc_html__( 'Arrows size', 'bricks' ),
    ];
    
    $this->controls['lightboxArrowsIconSize'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Icon Size', 'bricks' ),
      'type' => 'slider',
      'css' => [
        [
          'property' => 'width',
          'selector' => '.epg-lightbox-arrow svg',
          'value' => '%s !important',
        ],
      ],
      'units' => [
        'px' => [
          'min' => 0,
          'max' => 100,
          'step' => 1,
        ],
      ],
      'default' => '20px',
      'description' => esc_html__( 'Arrows size', 'bricks' ),
    ];

    $this->controls['lightboxArrowsBorder'] = [
      'tab' => 'content',
      'group' => 'styles',
      'label' => esc_html__( 'Borda', 'bricks' ),
      'type' => 'border',
      'css' => [
        [
          'property' => 'border',
          'selector' => '.epg-lightbox-arrow',
        ],
      ],
      'inline' => true,
      'small' => true,

    ];






    // Example CSS
    $this->controls['thumbPos'] = [
      'tab' => 'content',
      'group' => 'settings',
      'label' => esc_html__( 'Thumbs Position', 'bricks' ),
      'type' => 'select',
      'options' => [
        'top' => esc_html__( 'Top', 'bricks' ),
        'left' => esc_html__( 'Left', 'bricks' ),
        'bottom' => esc_html__( 'Bottom', 'bricks' ),
        'right' => esc_html__( 'Right', 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Select', 'bricks' ),
      'default' => 'left', // Option key
    ];


    // Example CSS
    $this->controls['providers'] = [
      'tab' => 'content',
      'group' => 'settings',
      'label' => esc_html__( 'Providers', 'bricks' ),
      'type' => 'select',
      'options' => [
        'woocommerce' => esc_html__( 'Woo Gallery', 'bricks' ),
        'thumbnail' => esc_html__( 'Thumbnail', 'bricks' ),
        'fieldKey' => esc_html__( 'Field Key', 'bricks' ),
        'itemsList' => esc_html__( "ID's | Url's", 'bricks' ),
      ],
      'inline' => true,
      'placeholder' => esc_html__( 'Select', 'bricks' ),
      'multiple' => true, 
      'clearable' => true,
      'default' => ['thumbnail','woocommerce']
    ];

    $this->controls['fields'] = [
      'tab' => 'content',
      'tab' => 'settings',
      'label' => esc_html__( 'Fields', 'bricks' ),
      'type' => 'text',
      'spellcheck' => true, // Default: false
      // 'trigger' => 'enter', // Default: 'enter'
      'inlineEditing' => true,
      'placeholder' => 'field_1, field_2',
    ];


  }
  // Enqueue element styles and scripts
  public function enqueue_scripts() {

    wp_enqueue_script('epg-splide-script');
    wp_enqueue_script('emu-product-gallery-script');
  }

  // Render element HTML
  public function render() {

    // Set element attributes
    $root_classes[] = 'emu-splide-wrapper';

    
    
    // Add 'class' attribute to element root tag
    
    $this->set_attribute( '_root', 'class', $root_classes );

    $styles = '';
    $direction = 'ttb'; // valor padrão

    if (!empty($this->settings['thumbPos'])) {
        $thumbPos = $this->settings['thumbPos'];

        // Define o estilo e a direção conforme a posição do thumbnail
        switch ($thumbPos) {

            case 'left':
                $styles = 'flex-direction: row!important;';
                $direction = 'ttb';
                break;

            case 'right':
                $styles = 'flex-direction: row-reverse!important;';
                $direction = 'ttb';
                break;

            case 'top':
                $styles = 'flex-direction: column;';
                $direction = 'ltr';
                break;
            case 'bottom':
                $styles = 'flex-direction: column-reverse;';
                $direction = 'ltr';
                break;
        }
    }

    $shortcodeAttrs = [];
   
    // Render element HTML
    if(isset($this->settings['providers'])){

      $providers = $this->settings['providers'];
      

    foreach($providers as $provider){

        if($provider === 'woocommerce'){
            $shortcodeAttrs[] = 'woocommerce';
        }
    
        if($provider === 'thumbnail'){
            $shortcodeAttrs[] = 'thumbnail';
        }
    
        if ($provider === 'fieldKey') {

          $fields = isset($this->settings['fields']) ? $this->settings['fields'] : '';

          $shortcodeAttrs[] = 'fields="' . $fields . '"';
        }
    }
    }
    
    // Converte o array em uma string de atributos
    $shortcodeAttrString = implode(' ', $shortcodeAttrs);
    
    // Exibe o shortcode
    echo '<div ' . $this->render_attributes('_root') . ' style="' . $styles . '">';
    echo do_shortcode('[emu_product_gallery ' . $shortcodeAttrString . ' direction="' . $direction . '" customattr="true" enqueue="false"]');
    echo '</div>';
    
    
  }
}
