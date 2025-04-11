let mainInstance = null;
let thumbInstance = null;

function mountEmuProductGallery() {
    const mainEl = document.querySelector('#emu-splide');
    const thumbsEl = document.querySelector('#emu-splide-thumbs');

    if (!mainEl || !thumbsEl) return;

    // Se já estiverem montadas, destruí-las
    if (mainInstance) {
        mainInstance.destroy();
        mainInstance = null;
    }

    if (thumbInstance) {
        thumbInstance.destroy();
        thumbInstance = null;
    }

    // Criar novas instâncias
    const main = new Splide(mainEl);
    const thumbs = new Splide(thumbsEl);

    thumbInstance = thumbs.mount();
    main.sync(thumbs);

    main.on('move', function (newIndex) {
        // Atualiza o estado das thumbs
        document.querySelectorAll('#emu-splide-thumbs .splide__slide').forEach((el, idx) => {
            el.classList.toggle('is-active', idx === newIndex);
        });
    
        // Itera pelos slides principais
        main.Components.Slides.forEach(({ slide }, idx) => {
            const liteYoutube = slide.querySelector('lite-youtube');
    
            if (!liteYoutube) return;
    
            if (idx === newIndex) {

                // Dá play clicando no slide atual
                if (!liteYoutube.classList.contains('lyt-activated')) {

                    liteYoutube.click();
                }

            } else {

                iframe =  liteYoutube.querySelector('iframe');

                if (iframe){
                    iframe.remove();
                }

                liteYoutube.classList.remove('lyt-activated');
            }
        });
    });
    

    mainInstance = main.mount();
}

document.addEventListener('DOMContentLoaded', () => {
    mountEmuProductGallery();
});







// jQuery(function($) {
//   // Variables to store the Swiper instances
//   var mainSwiper   = null,
//       thumbSwiper  = null,
//       // Stores the original gallery HTML to restore later
//       originalMainGallery  = $('.emu-main-slider .swiper-wrapper').html(),
//       originalThumbGallery = $('.emu-thumb-slider .swiper-wrapper').html();

//   // Show the preloader and hide the swiper-wrapper
//   function showPreloader() {
//    // nothing to-do...
//   }

//   // Hide the preloader and make swiper-wrapper visible
//   function hidePreloader() {
//     $('.emu-product-gallery-wrapper').removeClass('loading');  // Remove loading class to hide preloader
//   }

//   // Function to pause media (videos/iframes) on non-visible slides
//   function pauseMedia() {
//     $('.swiper-slide').each(function() {
//       $(this).find('video').each(function() {
//         if (!this.paused) this.pause();
//       });
//       var iframe = $(this).find('iframe');
//       if (iframe.length) {
//         var src = iframe.attr('src');
//         iframe.attr('src', '');
//         iframe.attr('src', src);
//       }
//     });
//   }

//   // Function to initialize (or reinitialize) the sliders
//   function initSwipers() {
//     // Show preloader while initializing
//     showPreloader();

//     // If an instance already exists, destroy it to avoid conflicts
//     if (mainSwiper && typeof mainSwiper.destroy === 'function') {
//       mainSwiper.destroy(true, true);
//       mainSwiper = null;
//     }
//     if (thumbSwiper && typeof thumbSwiper.destroy === 'function') {
//       thumbSwiper.destroy(true, true);
//       thumbSwiper = null;
//     }
    
//     // Initialize the thumbnail slider (thumbSwiper)
//     thumbSwiper = new Swiper('.emu-thumb-slider', {
//       spaceBetween: 10,
//       slidesPerView: 4,
//       freeMode: true,
//       watchSlidesVisibility: true,
//       watchSlidesProgress: true,
//       loop: false
//     });
    
//     // Initialize the main slider without using automatic navigation
//     mainSwiper = new Swiper('.emu-main-slider', {
//       spaceBetween: 0,
//       thumbs: {
//         swiper: thumbSwiper
//       },
//       loop: false,
//       on: {
//         slideChange: function() {
//           // Remove disabled classes to keep the arrows active
//           $('.swiper-button-next, .swiper-button-prev').removeClass('swiper-button-disabled');
//         },
//         init: function() {
//           // Hide preloader when initialization is done
//           hidePreloader();
//         }
//       }
//     });
    
//     // Set up click event for the "next" arrow
//     $('.swiper-button-next').off('click').on('click', function(e) {
//       e.preventDefault();
//       pauseMedia();
//       var totalSlides = mainSwiper.slides.length;
//       var currentIndex = mainSwiper.activeIndex;
      
//       // If it's the last slide, go back to the first; otherwise, move to the next slide
//       if (currentIndex >= totalSlides - 1) {
//          mainSwiper.slideTo(0);
//       } else {
//          mainSwiper.slideNext();
//       }
//     });
    
//     // Set up click event for the "previous" arrow
//     $('.swiper-button-prev').off('click').on('click', function(e) {
//       e.preventDefault();
//       pauseMedia();
//       var totalSlides = mainSwiper.slides.length;
//       var currentIndex = mainSwiper.activeIndex;
      
//       // If it's the first slide, go to the last; otherwise, move to the previous slide
//       if (currentIndex <= 0) {
//          mainSwiper.slideTo(totalSlides - 1);
//       } else {
//          mainSwiper.slidePrev();
//       }
//     });
    
//     // Set up click event for thumbnails to change the main slide
//     $('.emu-thumb-slider .swiper-slide').off('click').on('click', function() {
//       pauseMedia();
//       var index = $(this).index();
//       mainSwiper.slideTo(index);
//     });
//   }

//   // Initialize the sliders on page load
//   initSwipers();

//   // When a variation is selected, update the gallery:
//   // The new gallery will contain the variation image first, followed by the original product images
//   $('form.variations_form').on('found_variation', function(event, variation) {
//     if (variation && variation.image && variation.image.src) {
//       // Create the variation image slide
//       var variationSlide = '<div class="swiper-slide">' +
//                               '<img src="' + variation.image.src + '" alt="' + (variation.image.alt ? variation.image.alt : '') + '">' +
//                            '</div>';
      
//       // Concatenate the variation image with the original gallery
//       var newMainGallery  = variationSlide + originalMainGallery;
//       var newThumbGallery = variationSlide + originalThumbGallery;
      
//       // Update the gallery HTML
//       $('.emu-main-slider .swiper-wrapper').html(newMainGallery);
//       $('.emu-thumb-slider .swiper-wrapper').html(newThumbGallery);
      
//       // Reinitialize the Swipers to reflect the update
//       initSwipers();
//     }
//   });

//   // When the variation is reset, restore the original product gallery
//   $('form.variations_form').on('reset_data', function() {
//     $('.emu-main-slider .swiper-wrapper').html(originalMainGallery);
//     $('.emu-thumb-slider .swiper-wrapper').html(originalThumbGallery);
//     initSwipers();
//   });
// });



// ================ Youtbe Lite ================= //


/**
 * A lightweight youtube embed. Still should feel the same to the user, just MUCH faster to initialize and paint.
 *
 * Thx to these as the inspiration
 *   https://storage.googleapis.com/amp-vs-non-amp/youtube-lazy.html
 *   https://autoplay-youtube-player.glitch.me/
 *
 * Once built it, I also found these:
 *   https://github.com/ampproject/amphtml/blob/master/extensions/amp-youtube (👍👍)
 *   https://github.com/Daugilas/lazyYT
 *   https://github.com/vb/lazyframe
 */
class LiteYTEmbed extends HTMLElement {
    connectedCallback() {
        this.videoId = this.getAttribute('videoid');

        let playBtnEl = this.querySelector('.lty-playbtn');
        // A label for the button takes priority over a [playlabel] attribute on the custom-element
        this.playLabel = (playBtnEl && playBtnEl.textContent.trim()) || this.getAttribute('playlabel') || 'Play';

        /**
         * Lo, the youtube placeholder image!  (aka the thumbnail, poster image, etc)
         *
         * See https://github.com/paulirish/lite-youtube-embed/blob/master/youtube-thumbnail-urls.md
         *
         * TODO: Do the sddefault->hqdefault fallback
         *       - When doing this, apply referrerpolicy (https://github.com/ampproject/amphtml/pull/3940)
         * TODO: Consider using webp if supported, falling back to jpg
         */
        
        let poster =  this.getAttribute('poster');

        // Warm the connection for the poster image
        LiteYTEmbed.addPrefetch('preload', poster, 'image');

        this.style.backgroundImage = `url("${poster}")`;

        // Set up play button, and its visually hidden label
        if (!playBtnEl) {
            playBtnEl = document.createElement('button');
            playBtnEl.type = 'button';
            playBtnEl.classList.add('lty-playbtn');
            this.append(playBtnEl);
        }
        if (!playBtnEl.textContent) {
            const playBtnLabelEl = document.createElement('span');
            playBtnLabelEl.className = 'lyt-visually-hidden';
            playBtnLabelEl.textContent = this.playLabel;
            playBtnEl.append(playBtnLabelEl);
        }

        // On hover (or tap), warm up the TCP connections we're (likely) about to use.
        this.addEventListener('pointerover', LiteYTEmbed.warmConnections, {once: true});

        // Once the user clicks, add the real iframe and drop our play button
        // TODO: In the future we could be like amp-youtube and silently swap in the iframe during idle time
        //   We'd want to only do this for in-viewport or near-viewport ones: https://github.com/ampproject/amphtml/pull/5003
        this.addEventListener('click', e => this.addIframe());
    }

    // // TODO: Support the the user changing the [videoid] attribute
    // attributeChangedCallback() {
    // }

    /**
     * Add a <link rel={preload | preconnect} ...> to the head
     */
    static addPrefetch(kind, url, as) {
        const linkEl = document.createElement('link');
        linkEl.rel = kind;
        linkEl.href = url;
        if (as) {
            linkEl.as = as;
        }
        document.head.append(linkEl);
    }

    /**
     * Begin pre-connecting to warm up the iframe load
     * Since the embed's network requests load within its iframe,
     *   preload/prefetch'ing them outside the iframe will only cause double-downloads.
     * So, the best we can do is warm up a few connections to origins that are in the critical path.
     *
     * Maybe `<link rel=preload as=document>` would work, but it's unsupported: http://crbug.com/593267
     * But TBH, I don't think it'll happen soon with Site Isolation and split caches adding serious complexity.
     */
    static warmConnections() {
        if (LiteYTEmbed.preconnected) return;

        // The iframe document and most of its subresources come right off youtube.com
        LiteYTEmbed.addPrefetch('preconnect', 'https://www.youtube-nocookie.com');
        // The botguard script is fetched off from google.com
        LiteYTEmbed.addPrefetch('preconnect', 'https://www.google.com');

        // Not certain if these ad related domains are in the critical path. Could verify with domain-specific throttling.
        LiteYTEmbed.addPrefetch('preconnect', 'https://googleads.g.doubleclick.net');
        LiteYTEmbed.addPrefetch('preconnect', 'https://static.doubleclick.net');

        LiteYTEmbed.preconnected = true;
    }

    addIframe() {
        const params = new URLSearchParams(this.getAttribute('params') || []);
        params.append('autoplay', '1');

        const iframeEl = document.createElement('iframe');
        iframeEl.width = '100%';
        iframeEl.height = '100%';
        // No encoding necessary as [title] is safe. https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html#:~:text=Safe%20HTML%20Attributes%20include
        iframeEl.title = this.playLabel;
        iframeEl.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
        iframeEl.allowFullscreen = true;
        // AFAIK, the encoding here isn't necessary for XSS, but we'll do it only because this is a URL
        // https://stackoverflow.com/q/64959723/89484
        iframeEl.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(this.videoId)}?${params.toString()}`;
        this.append(iframeEl);

        this.classList.add('lyt-activated');

        // Set focus for a11y
        this.querySelector('iframe').focus();
    }
}
// Register custom element
customElements.define('lite-youtube', LiteYTEmbed);