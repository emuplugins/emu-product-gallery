jQuery(document).ready(function($) {
    var mediaUploader;

    // Opens the media gallery to select images
    $('#add-gallery-image').click(function(e) {
        e.preventDefault();
        if (mediaUploader) mediaUploader.open();
        
        mediaUploader = wp.media({
            title: 'Select an image',
            button: { text: 'Use this image' },
            multiple: true
        });

        mediaUploader.on('select', function() {
            var attachments = mediaUploader.state().get('selection').toJSON();
            var urls = attachments.map(attachment => attachment.url);
            updateGallery(urls);
            mediaUploader.close(); // Closes the gallery after selecting the image
        }).open();
    });

    // Adds a video link to the gallery
    $('#add-gallery-video').click(function(e) {
        e.preventDefault();
        var videoUrl = prompt('Enter a valid URL:');

        if (videoUrl && videoUrl.startsWith('http')) {
            updateGallery([videoUrl]);
        } else {
            alert('Please enter a link that starts with "http".');
        }
    });

    // Removes an item from the gallery
    $('.gallery-list').on('click', '.remove-item', function(e) {
        e.preventDefault();
        removeFromGallery($(this).data('url'));
    });

    // Reorders the gallery items
    $('.gallery-list').sortable({
        update: function(event, ui) {
            let newOrder = [];
            $('.gallery-list li').each(function() {
                let url = $(this).find('.remove-item').data('url');
                newOrder.push(url);
            });
            $('#gallery').val(JSON.stringify(newOrder));
        }
    });

    // Helper functions
    function updateGallery(newUrls) {
        let gallery = JSON.parse($('#gallery').val() || '[]');
        gallery = [...gallery, ...newUrls];
        $('#gallery').val(JSON.stringify(gallery));
        renderGallery(gallery);
    }

    function removeFromGallery(url) {
        let gallery = JSON.parse($('#gallery').val() || '[]');
        gallery = gallery.filter(item => item !== url);
        $('#gallery').val(JSON.stringify(gallery));
        renderGallery(gallery);
    }

    function renderGallery(gallery) {
        let html = '';
        gallery.forEach(item => {
            const isYoutube = item.match(/(?:youtube\.com\/(?:shorts\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
            const videoId = isYoutube ? isYoutube[2] : '';
            const thumbnail = isYoutube ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg` : item;
            html += `
                <li class="${isYoutube ? 'video-item' : 'image-item'}">
                    <img src="${thumbnail}" style="width:100px;height:100px;object-fit:cover;">
                    <a href="#" class="remove-item" data-url="${item}">Remove</a>
                </li>
            `;
        });
        $('.gallery-list').html(html);
    }

    // Gallery container resize logic
const $galleryContainer = $('#gallery-list');

const observer = new ResizeObserver(entries => {
    for (let entry of entries) {
        if (entry.contentRect.width <= 400) {
            $galleryContainer.css({
                'display': 'grid',
                'grid-template-columns': 'repeat(3, 1fr)'
            });
        } else {
            $galleryContainer.css('display', 'flex');
        }
    }
});

observer.observe($galleryContainer[0]);
});