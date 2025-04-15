// Instâncias globais dos sliders principal e de thumbs
let mainInstance = null;
let thumbInstance = null;

// Função principal que inicializa e sincroniza os sliders
function mountEmuProductGallery() {
    let mainEl = document.querySelector('#emu-splide');
    let thumbsEl = document.querySelector('#emu-splide-thumbs');

    // Verifica se os elementos existem
    if (!mainEl || !thumbsEl) return;

    // Destroi instâncias existentes antes de recriar
    if (mainInstance) {
        mainInstance.destroy();
        mainInstance = null;
    }

    if (thumbInstance) {
        thumbInstance.destroy();
        thumbInstance = null;
    }

    // Cria novas instâncias dos sliders
    mainEl = new Splide(mainEl);
    thumbsEl = new Splide(thumbsEl);

    // Monta os thumbs e sincroniza com o principal
    thumbInstance = thumbsEl.mount();
    mainEl.sync(thumbsEl);

    // Evento de troca de slide
    mainEl.on('move', function (newIndex) {
        // Atualiza a classe 'is-active' nas thumbs
        document.querySelectorAll('#emu-splide-thumbs .splide__slide').forEach((el, idx) => {
            el.classList.toggle('is-active', idx === newIndex);
        });

        // Gerencia o play/stop de vídeos do tipo lite-youtube
        mainEl.Components.Slides.forEach(({ slide }, idx) => {
            const liteYoutube = slide.querySelector('lite-youtube');
            if (!liteYoutube) return;

            if (idx === newIndex) {

                const epgLightbox = document.querySelector('.epg-lightbox');

                const isVisible = window.getComputedStyle(epgLightbox).display !== 'none';
                
                if (isVisible) return;

                if (!liteYoutube.classList.contains('lyt-activated')) {
                    liteYoutube.click(); // Ativa o vídeo
                }
            } else {
                const iframe = liteYoutube.querySelector('iframe');
                if (iframe) iframe.remove(); // Remove o iframe ao sair do slide
                liteYoutube.classList.remove('lyt-activated');
            }
        });

        // Atualiza o conteúdo do lightbox apenas se ele estiver visível
        const epgLightbox = document.querySelector('.epg-lightbox');
        const isVisible = window.getComputedStyle(epgLightbox).display !== 'none';
        if (!isVisible) return;

        const indexAtual = newIndex + 1;
        const currentElement = document.querySelector('#emu-splide .splide__slide:nth-child(' + indexAtual + ')');
        if (!currentElement) return;

        const currentChild = currentElement.querySelector(':first-child');
        changeLightboxImage(currentChild);
    });

    // Evento ao montar o slider principal
    mainEl.on('mounted', function () {
        let currentIndex = mainEl.index;
        let currentElement = document.querySelectorAll('#emu-splide .splide__slide :first-child')[currentIndex];
        changeLightboxImage(currentElement);
    });

    // Monta o slider principal
    mainInstance = mainEl.mount();
}

// Inicializa a galeria ao carregar o DOM
document.addEventListener('DOMContentLoaded', () => {
    mountEmuProductGallery();

    const nextArrow = document.querySelector('.epg-lightbox .epg-lightbox-arrow.right');
    const prevArrow = document.querySelector('.epg-lightbox .epg-lightbox-arrow.left');
    const epgLightbox = document.querySelector('.epg-lightbox');

    // Navegação no lightbox
    if (nextArrow) {
        nextArrow.addEventListener('click', () => {
            mainInstance.go('>');
        });
    }

    if (prevArrow) {
        prevArrow.addEventListener('click', () => {
            mainInstance.go('<');
        });
    }

    // Fecha o lightbox ao clicar fora do conteúdo
    if (epgLightbox) {
        epgLightbox.addEventListener('click', (event) => {
            if (event.target === epgLightbox) {
                toggleLightbox();
            }
        });
    }
    
});

// Atualiza o conteúdo do lightbox com o slide atual
function changeLightboxImage(element = '', type = 'image') {
    const epgLightbox = document.querySelector('.epg-lightbox');
    const content = epgLightbox.querySelector('.epg-lightbox-content');

    const isVisible = window.getComputedStyle(epgLightbox).display !== 'none';
    if (!isVisible) return;

    content.innerHTML = '';

    if (element instanceof HTMLElement) {
        content.appendChild(element.cloneNode(true));
        content.firstElementChild.click(); // Ativa o conteúdo, se necessário
    }
}

// Alterna a visibilidade do lightbox
function toggleLightbox() {
    const epgLightbox = document.querySelector('.epg-lightbox');
    const isVisible = window.getComputedStyle(epgLightbox).display !== 'none';
    const content = epgLightbox.querySelector('.epg-lightbox-content');

    // Remove o iframe do lite-youtube (se existir)
        const liteYoutube = document.querySelector('lite-youtube');
        if (liteYoutube) {
            const iframe = liteYoutube.querySelector('iframe');
            if (iframe) iframe.remove();
        }


    if (isVisible) {
        // Oculta o lightbox e limpa o conteúdo
        epgLightbox.style.display = 'none';
        content.innerHTML = '';
    } else {
        // Mostra o lightbox
        epgLightbox.style.display = 'flex';

        // Recupera o slide atual e injeta no lightbox
        const currentIndex = mainInstance?.index ?? 0;
        const currentSlide = document.querySelectorAll('#emu-splide .splide__slide')[currentIndex];
        if (currentSlide) {
            const currentElement = currentSlide.querySelector(':first-child');
            changeLightboxImage(currentElement);
        }
    }
}



let originalMainGalleryHTML = '';
let originalThumbGalleryHTML = '';

jQuery(function($) {
  // Captura o HTML original assim que o DOM estiver pronto
  const mainWrapper = document.querySelector('#emu-splide .splide__list');
  const thumbWrapper = document.querySelector('#emu-splide-thumbs .splide__list');

  if (mainWrapper && thumbWrapper) {
    originalMainGalleryHTML = mainWrapper.innerHTML;
    originalThumbGalleryHTML = thumbWrapper.innerHTML;
  }

  $('form.variations_form').on('found_variation', function(event, variation) {
    if (variation && variation.image && variation.image.full_src) {
    const imgSrc = variation.image.full_src;
      const imgAlt = variation.image.alt || '';

      const variationSlide = `
        <li class="splide__slide">
        <div class="image">
          <img src="${imgSrc}" alt="${imgAlt}" style="width:100%">
          </div>
        </li>`;

      if (mainWrapper && thumbWrapper) {
        mainWrapper.innerHTML = variationSlide + originalMainGalleryHTML;
        thumbWrapper.innerHTML = variationSlide + originalThumbGalleryHTML;
      }

      mountEmuProductGallery();
    }
  });

  $('form.variations_form').on('reset_data', function() {
    if (mainWrapper && thumbWrapper) {
      mainWrapper.innerHTML = originalMainGalleryHTML;
      thumbWrapper.innerHTML = originalThumbGalleryHTML;
    }

    mountEmuProductGallery();
  });
});


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
            playBtnEl = document.createElement('a');
            playBtnEl.type = 'a';
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