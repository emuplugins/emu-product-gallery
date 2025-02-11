document.addEventListener("DOMContentLoaded", function () {
    let emuThumbSlider; // Slider de miniaturas
    let emuMainSlider;  // Slider principal
    let emuIndices = [0, 0, 0]; // Armazena os índices para navegação personalizada

    // Função para inicializar o Swiper
    function initSwipers() {
        // Inicia o slider de miniaturas
        emuThumbSlider = new Swiper(".emu-thumb-slider", {
            spaceBetween: 0,
            slidesPerView: 4,
            watchSlidesProgress: true,
            freeMode: true,
        });

        // Inicia o slider principal
        emuMainSlider = new Swiper(".emu-main-slider", {
            spaceBetween: 0,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            thumbs: {
                swiper: emuThumbSlider,
            },
            loop: false,
        });

        // Agora que o slider foi inicializado, configure os eventos do swiper
        emuMainSlider.on('slideChange', function () {
            emuUpdateIndices();
            emuUpdateNavigationState();
        });
    }

    // Garante que as setas de navegação fiquem sempre ativas
    function emuUpdateNavigationState() {
        document.querySelector('.swiper-button-next')?.classList.remove('swiper-button-disabled');
        document.querySelector('.swiper-button-prev')?.classList.remove('swiper-button-disabled');
    }

    // Atualiza os índices dos slides
    function emuUpdateIndices() {
        emuIndices = [emuIndices[1], emuIndices[2], emuMainSlider.realIndex];
    }

    // Função para pausar a mídia dos slides que não estão visíveis
    function emuPauseMedia() {
        document.querySelectorAll('.swiper-slide').forEach(slide => {
            slide.querySelectorAll('video').forEach(video => {
                if (!video.paused) video.pause();
            });

            let iframe = slide.querySelector('iframe');
            if (iframe) {
                let iframeSrc = iframe.src;
                iframe.src = ''; // Pausa o iframe
                iframe.src = iframeSrc; // Restaura o src
            }
        });
    }

    // Inicializa os Swipers primeiro
    initSwipers();

    // Agora configure os eventos de clique nas setas
    document.querySelector('.swiper-button-next')?.addEventListener('click', function () {
        emuPauseMedia();
        // Se estiver no último slide, volta para o primeiro
        if (emuMainSlider.realIndex === emuMainSlider.slides.length - 1) {
            emuMainSlider.slideTo(0);
        }
        // Caso contrário, o próprio Swiper já avançará o slide por meio da navegação
    });

    document.querySelector('.swiper-button-prev')?.addEventListener('click', function () {
        emuPauseMedia();
        emuUpdateIndices();
        // Se estiver no primeiro slide (de acordo com seu índice personalizado), vai para o penúltimo
        if (emuIndices[0] === 0) {
            emuMainSlider.slideTo(emuMainSlider.slides.length - 2);
        }
        // Se não, o próprio Swiper já executa a navegação padrão
    });

    // E configure o evento de clique nas miniaturas
    document.querySelectorAll('.emu-thumb-slider .swiper-slide').forEach((thumb, index) => {
        thumb.addEventListener('click', function () {
            emuMainSlider.slideTo(index); // Muda o slide principal para o índice da miniatura clicada
        });
    });
});
