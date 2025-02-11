jQuery(document).ready(function($) {
    var mediaUploader;

    // Abre a galeria de mídia para selecionar imagens
    $('#add-galeria-imagem').click(function(e) {
        e.preventDefault();
        if (mediaUploader) mediaUploader.open();
        
        mediaUploader = wp.media({
            title: 'Selecione uma imagem',
            button: { text: 'Usar esta imagem' },
            multiple: true
        });

        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var urls = attachments.map(attachment => attachment.url);
            updateGaleria(urls);
            mediaUploader.close(); // Fecha a galeria após selecionar a imagem
        }).open();
    });

    // Adiciona link na galeria
    $('#add-galeria-video').click(function(e) {
        e.preventDefault();
        var videoUrl = prompt('Insira um URL válido:');

        if (videoUrl && videoUrl.startsWith('http')) {
            updateGaleria([videoUrl]);
        } else {
            alert('Por favor, insira um link que comece com "http".');
        }
    });

    // Remove item
    $('.galeria-list').on('click', '.remove-item', function(e) {
        e.preventDefault();
        removeFromGaleria($(this).data('url'));
    });

    // Função para reordenar galeria
    $('.galeria-list').sortable({
        update: function(event, ui) {
            let novaOrdem = [];
            $('.galeria-list li').each(function() {
                let url = $(this).find('.remove-item').data('url');
                novaOrdem.push(url);
            });
            $('#galeria').val(JSON.stringify(novaOrdem));
        }
    });

    // Funções auxiliares
    function updateGaleria(newUrls) {
        let galeria = JSON.parse($('#galeria').val() || '[]');
        galeria = [...galeria, ...newUrls];
        $('#galeria').val(JSON.stringify(galeria));
        renderGaleria(galeria);
    }

    function removeFromGaleria(url) {
        let galeria = JSON.parse($('#galeria').val() || '[]');
        galeria = galeria.filter(item => item !== url);
        $('#galeria').val(JSON.stringify(galeria));
        renderGaleria(galeria);
    }

    function renderGaleria(galeria) {
        let html = '';
        galeria.forEach(item => {
            const isYoutube = item.match(/(youtube\.com\/(?:[^\/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S+?[\?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            const videoId = isYoutube ? isYoutube[2] : '';
            const thumbnail = isYoutube ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg` : item;
            html += `
                <li class="${isYoutube ? 'video-item' : 'image-item'}">
                    <img src="${thumbnail}" style="width:100px;height:100px;object-fit:cover;">
                    <a href="#" class="remove-item" data-url="${item}">Excluir</a>
                </li>
            `;
        });
        $('.galeria-list').html(html);
    
    
    }
});
