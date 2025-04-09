;(function($) {
    $(function() {
        // const $previewContainer = $('#oembed_in_library_preview');
        // const $previewButton = $('#oembed_in_library_btn_preview');
        const $urlInput = $('#oembed_url');

        if (!$previewButton.length || !$urlInput.length) return;

        $previewButton.on('click', function(e) {
            e.preventDefault();

            const mediaUrl = $urlInput.val().trim();
            if (!mediaUrl) {
                $previewContainer.html('<p>Por favor, insira uma URL válida.</p>');
                return;
            }

            // $previewContainer.empty().addClass('loading');

            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    action: 'oembed_in_library_preview',
                    media_url: mediaUrl
                },
                success: function(response) {
                    $previewContainer.removeClass('loading').html(response);
                },
                error: function(xhr) {
                    $previewContainer.removeClass('loading').html('<p>Erro ao carregar a pré-visualização.</p>');
                    console.error('Erro AJAX:', xhr);
                }
            });
        });
    });
})(jQuery);
