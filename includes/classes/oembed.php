<?php

if ( ! class_exists('OEmbedInLibrary') ) :

class EPGEmbedInLibrary{

    // definindo a variável da instancia
    private static $_instance;

    // função para verificar se a instancia já existe, pra evitar duplicações da classe
    public static function getInstance() {

        // se já existir, ele retorna a si mesmo
        if ( self::$_instance instanceof self ) return self::$_instance;
        
        // se não, cria uma nova instância de si mesmo
        self::$_instance = new self();
        
        // e retorna a instância por fim
        return self::$_instance;
    }

    private function __construct() {
        
        // adicionando os scripts necessários 
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

        // adicionando o option page e a página pra enviar o embed pro wp
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    // enfilera os scripts que serão usados
    public function admin_scripts( $hook ) {
        
        $plugin_url = plugin_dir_url( EPG_DIR ) . 'emu-product-gallery/';

        $screen = get_current_screen();
        
        // caso estejamos na tela de edição de produtos
        if ( $screen && $screen->base === 'post' ) {
            wp_enqueue_script(
                'custom-media-form',
                $plugin_url . '/assets/js/add-embed.js',
                ['media-views','jquery'],
                null,
                true
            );
        }        

        // caso estejamos na tela de adicionar midia
        if ( $hook !== 'media_page_oembedinlibrary' ){
            
            wp_enqueue_script(
                'addmbed_js', $plugin_url . '/assets/js/add-embed.js',
                [ 'jquery' ],
                '1.1',
                true
            );

            wp_localize_script('addmbed_js', 'custom_embed_data', [
                'rest_url' => rest_url('epg/v1/add-embed'),
                'nonce'    => wp_create_nonce('wp_rest')
            ]);

        }

        // enfilerando os estilos, são poucos
        wp_enqueue_style(
            'oembed_css', $plugin_url . 'assets/css/oembed.css',
            [],
            '1.1'
        ); 
        
    }

    // retorna o html com o embed
    public function ajax_preview() {

        // verifica o nonce
        check_ajax_referer( 'ajax_preview_nonce', 'security' );

        if ( ! current_user_can( 'upload_files' ) ) wp_die();

        try {
            echo $this->get_preview( sanitize_text_field( $_POST['media_url'] ) );
        } catch ( Exception $e ) {
            echo esc_html( $e->getMessage() );
        }

        wp_die();
    }

    private function get_preview( $url ) {

        $oembed = _wp_oembed_get_object();

        return $oembed->get_html( $url, [
            'width' => 400,
            'height' => 300
        ]);

    }

    private function get_embed_data( $url ) {

        $oembed = _wp_oembed_get_object();

        return $oembed->get_data( $url );

    }

    // adiciona no menu a página de opções, para enviar os embeds para o site
    public function admin_menu() {

        add_submenu_page(
            'upload.php',
            __( 'Embed in Library', 'oembed-in-library' ),
            __( 'oEmbed', 'oembed-in-library' ),
            'upload_files',
            'oembedinlibrary',
            [ $this, 'page_embed' ]
        );

        add_action( 'admin_action_oembed_add_in_library', [ $this, 'add_in_library' ] );

        add_filter( 'icon_dirs', [ $this, 'add_icons_dir' ] );

    }

    // retorna o html da página para adicionar os embeds no site
    public function page_embed() {
    ?>

    <div class="wrap">

        <h2><?php _e('Embed in Library', 'oembed-in-library'); ?></h2>
        <p><?php _e('Use this form to add an external media in your library.', 'oembed-in-library'); ?></p>

        <form action="<?php echo esc_url( admin_url('admin.php') ); ?>" method="post">

            <label for="oembed_url"><?php _e('URL', 'oembed-in-library') ?></label>
            <input type="text" name="oembed_url" id="oembed_url"/>
            <input type="hidden" name="action" value="oembed_add_in_library" />                
            <input type="submit" value="<?php _e('Add in library', 'oembed-in-library') ?>" class="button button-primary"/>

        </form>

    </div>

    <?php   
    }

    // Função para adicionar o embed como attatchment
    public function add_in_library() {

        // guarda da submissão do formulário o url
        $url = esc_url_raw( $_POST['oembed_url'] );

        if ( ! $url ) wp_die( __('No URL provided.', 'oembed-in-library') );

        // recupera o iframe html
        $html = $this->get_preview( $url );
        
        // recupera os dados separados
        $data = $this->get_embed_data( $url );

        $descricao = sanitize_text_field( 'Para que este vídeo funcione, o plugin Emu Product Gallery deve estar instalado!' );

        $request = new WP_REST_Request('POST', '/epg/v1/add-embed');
        $request->set_param('oembed_url', $url);
        $request->set_param('description', $descricao);

        // Executa diretamente o callback
        $response = rest_do_request($request);
        
        // redireciona pra lista de mídias do wordpress, no modo grid
        wp_redirect( admin_url('upload.php?mode=grid') );

        exit;
    }

    // isso é necessário por algum motivo 🙂
    public function add_icons_dir( $dirs ) {
        $dirs[ EPG_DIR ] = untrailingslashit( EPG_DIR );
        return $dirs;
    }
};

endif;

// ativando!
EPGEmbedInLibrary::getInstance();

// Por padrão, as thumbnails não são mostradas na lista de mídias... 
// Por isso precisamos interceptar no momento certo a exibição delas pra que tudo ocorra como esperado.
// Claro que faremos isso em um filtro, pra previnir que o código seja executado muitas vezes

// Galeria em modo Grid

add_filter( 'wp_prepare_attachment_for_js', function( $response, $attachment ) {

    // Existem muitos arquivos de mídia, e por isso só deve ser
    // alterado o url do thumbnail se o attatchment for um embed
	if ( $attachment->post_mime_type === 'oembed/external' ) {

		$thumbnail_url = get_post_meta( $attachment->ID, '_oembed_thumbnail_url', true );

		if ( $thumbnail_url ) {
			$response['sizes'] = [
				'full' => [
					'url'    => esc_url( $thumbnail_url ),
					'width'  => 600,
					'height' => 400,
					'orientation' => 'landscape',
				],
			];

			$response['icon'] = esc_url( $thumbnail_url );
			$response['image'] = esc_url( $thumbnail_url ); // <- necessário
		}
	}

	return $response;

}, 10, 2 );

add_filter( 'wp_get_attachment_image_src', function( $image, $attachment_id, $size ) {

    $post = get_post( $attachment_id );

    if ( $post && $post->post_mime_type === 'oembed/external' ) {
        $thumb = get_post_meta( $attachment_id, '_oembed_thumbnail_url', true );

        if ( $thumb ) {
            return [
                esc_url( $thumb ),
                600,
                400,
                false
            ];
        } else {
            // Função local para extrair o ID do YouTube
            if ( ! function_exists( 'get_youtube_id' ) ) {
				function get_youtube_id( $url ) {
					preg_match(
						'~(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([^\?&"\'<> #]+)~',
						$url,
						$matches
					);
					return $matches[1] ?? null;
				}
			}

            $guid = get_post_field('guid', $attachment_id);
            $youtube_id = get_youtube_id($guid);

            if ( $youtube_id ) {
                $thumbnail_url = "https://img.youtube.com/vi/{$youtube_id}/maxresdefault.jpg";
                return [
                    esc_url( $thumbnail_url ),
                    600,
                    400,
                    false
                ];
            }
        }
    }

    return $image;

}, 10, 3 );



add_action('rest_api_init', function () {
    register_rest_route('epg/v1', '/add-embed', [
        'methods'  => 'POST',
        'callback' => 'custom_add_embed_to_library',
        'permission_callback' => function () {
            return current_user_can('upload_files');
        }
    ]);
});

function custom_add_embed_to_library(WP_REST_Request $request) {

    function get_preview($url) {
        $oembed = _wp_oembed_get_object();
        return $oembed->get_html($url, [
            'width' => 400,
            'height' => 300
        ]);
    }

    function get_embed_data($url) {
        $oembed = _wp_oembed_get_object();
        return $oembed->get_data($url);
    }

    if ( ! function_exists( 'get_youtube_id' ) ) {
		function get_youtube_id( $url ) {
			preg_match(
				'~(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([^\?&"\'<> #]+)~',
				$url,
				$matches
			);
			return $matches[1] ?? null;
		}
	}

    $original_url = esc_url_raw($request->get_param('oembed_url'));
	$parsed_url = parse_url($original_url);
	$url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];


    if (!$url) {
        return new WP_Error('no_url', __('No URL provided.', 'oembed-in-library'), ['status' => 400]);
    }

    if (strpos($url, 'youtu') === false) {
        return new WP_Error('invalid_url', __('Envie um vídeo do youtube.', 'oembed-in-library'), ['status' => 400]);
    }

    $html = get_preview($url);
    $data = get_embed_data($url);

    if (!$data) {
        return new WP_Error('invalid_url', __('Envie um vídeo do youtube.', 'oembed-in-library'), ['status' => 400]);
    }

    $descricao = sanitize_text_field('Para que este vídeo funcione, o plugin Emu Product Gallery deve estar instalado!');

    $post_id = wp_insert_post([
        'post_title'     => isset($data->title) ? sanitize_text_field($data->title) : $url,
        'post_content'   => $descricao,
        'post_status'    => 'inherit',
        'post_author'    => get_current_user_id(),
        'post_type'      => 'attachment',
        'guid'           => $url,
        'post_mime_type' => 'oembed/external'
    ]);

    if (is_wp_error($post_id)) {
        return new WP_Error('insert_failed', __('Failed to insert embed.', 'oembed-in-library'), ['status' => 500]);
    }

    // Força o uso da thumbnail em alta resolução
    $video_id = get_youtube_id($url);
    if ($video_id) {
		// Detecta se é um short
		if (strpos($url, '/shorts/') !== false) {
			$thumbnail_url = "https://i.ytimg.com/vi/{$video_id}/frame0.jpg";
		} else {
			$thumbnail_url = "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg";
		}

		update_post_meta($post_id, '_oembed_thumbnail_url', esc_url_raw($thumbnail_url));
	}
	
    return [
        'success' => true,
        'post_id' => $post_id,
        'message' => __('Embed added to library.', 'oembed-in-library'),
    ];
}
