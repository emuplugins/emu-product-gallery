<?php 
function emu_product_gallery_shortcode($atts) {
    // Obtém o valor do atributo 'media' (links ou IDs separados por vírgula)
    $atts = shortcode_atts(array(
        'media' => '', // Valor padrão vazio
    ), $atts);

    // Divide os links ou IDs passados por vírgula
    $media = explode(',', $atts['media']);

    // Função para gerar a thumbnail do YouTube
    function getYoutubeThumbnail($url) {
        preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
        if (!empty($matches[1])) {
            return 'https://img.youtube.com/vi/' . $matches[1] . '/maxresdefault.jpg';
        }
        return ''; // Caso não seja um vídeo válido
    }

    // Função para converter URL do YouTube para o formato embed
    function convertYoutubeUrlToEmbed($url) {
        if (strpos($url, 'youtube.com/embed/') !== false) {
            return $url; // Já está no formato correto
        }
        if (strpos($url, 'youtube.com/watch?v=') !== false) {
            preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
            if (!empty($matches[1])) {
                return 'https://www.youtube.com/embed/' . $matches[1];
            }
        }
        if (strpos($url, 'youtu.be/') !== false) {
            preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $matches);
            if (!empty($matches[1])) {
                return 'https://www.youtube.com/embed/' . $matches[1];
            }
        }
        return $url; // Retorna o URL original caso não seja do YouTube ou não consiga converter
    }

    // Função para verificar se o valor é um ID de imagem e retornar a URL
    function getImageUrlFromId($image_id) {
        $image_id = intval($image_id); // Garantir que o valor seja um número inteiro
        if ($image_id > 0) {
            $image = wp_get_attachment_image_src($image_id, 'full'); // Pega a URL da imagem pelo ID
            return $image ? $image[0] : ''; // Retorna a URL ou uma string vazia caso não encontre
        }
        return ''; // Retorna vazio caso o ID não seja válido
    }

    // Gerar thumbnails
    $thumb = [];
    foreach ($media as $url) {
        $url = trim($url); // Remove espaços em branco extra
        // Verifica se é um ID de imagem
        if (is_numeric($url)) {
            $thumb[] = getImageUrlFromId($url); // Pega a URL da imagem pelo ID
        } elseif (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            $thumb[] = getYoutubeThumbnail($url); // Capa do YouTube
        } else {
            $thumb[] = $url; // Usa a própria imagem como thumbnail
        }
    }

    // Gerar slides principais
    $slides_html = '';
    foreach ($media as $index => $url) {
        ob_start();
        $url = trim($url); // Remove espaços em branco extra
        if (is_numeric($url)) {
            $url = getImageUrlFromId($url); // Pega a URL da imagem pelo ID
        }
        $embedUrl = convertYoutubeUrlToEmbed($url);
        ?>
        <div class="swiper-slide">
            <?php 
                if (strpos($embedUrl, 'youtube.com') !== false): 
            ?>
                <iframe width="100%" height="100%" src="<?php echo $embedUrl; ?>" title="Video Slide" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            <?php 
                elseif (strpos($embedUrl, '.mp4') !== false): 
            ?>
                <video width="100%" height="100%" controls>
                    <source src="<?php echo $embedUrl; ?>" type="video/mp4">
                    Seu navegador não suporta o vídeo.
                </video>
            <?php 
                else: 
            ?>
                <img src="<?php echo $embedUrl; ?>" alt="Slide <?php echo ($index + 1); ?>">
            <?php 
                endif; 
            ?>
        </div>
        <?php
        $slides_html .= ob_get_clean();
    }
    // Adiciona um slide vazio se necessário (opcional)
    $slides_html .= '<div class="swiper-slide"></div>';

    // Gerar HTML das thumbnails
    $thumbs_html = '';
    foreach ($thumb as $index => $thumb_url) {
        ob_start();
        ?>
        <div class="swiper-slide">
            <img src="<?php echo $thumb_url; ?>" alt="Thumb <?php echo ($index + 1); ?>">
        </div>
        <?php
        $thumbs_html .= ob_get_clean();
    }

    // Retorna o HTML e o script para inicializar os sliders com thumbs
    return '<div style="overflow:hidden; position:relative">

        <div class="swiper-container emu-main-slider">
            <div class="swiper-wrapper">
                ' . $slides_html . '
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="swiper-container emu-thumb-slider">
            <div class="swiper-wrapper">
                ' . $thumbs_html . '
            </div>
        </div></div>
    ';
}

// Registra o shortcode
add_shortcode('emu_product_gallery', 'emu_product_gallery_shortcode');
