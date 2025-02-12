document.addEventListener("DOMContentLoaded", function () {
    let emuThumbSlider; // Thumbnails slider
    let emuMainSlider;  // Main slider
    let emuIndices = [0, 0, 0]; // Stores indices for custom navigation

    // Function to initialize the Swiper
    function initSwipers() {
        // Initialize the thumbnails slider
        emuThumbSlider = new Swiper(".emu-thumb-slider", {
            spaceBetween: 0,
            slidesPerView: 4,
            watchSlidesProgress: true,
            freeMode: true,
        });

        // Initialize the main slider
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

        // Now that the slider is initialized, set up swiper events
        emuMainSlider.on('slideChange', function () {
            emuUpdateIndices();
            emuUpdateNavigationState();
        });
    }

    // Ensures the navigation arrows are always active
    function emuUpdateNavigationState() {
        // Remove the disabled class from the arrows
        document.querySelector('.swiper-button-next')?.classList.remove('swiper-button-disabled');
        document.querySelector('.swiper-button-prev')?.classList.remove('swiper-button-disabled');
    }

    // Updates the slide indices
    function emuUpdateIndices() {
        emuIndices = [emuIndices[1], emuIndices[2], emuMainSlider.realIndex];
    }

    // Function to pause media on slides that are not visible
    function emuPauseMedia() {
        document.querySelectorAll('.swiper-slide').forEach(slide => {
            slide.querySelectorAll('video').forEach(video => {
                if (!video.paused) video.pause();
            });

            let iframe = slide.querySelector('iframe');
            if (iframe) {
                let iframeSrc = iframe.src;
                iframe.src = ''; // Pauses the iframe
                iframe.src = iframeSrc; // Restores the src
            }
        });
    }

    // Initialize the Swipers first
    initSwipers();

    // Now set up click events for the arrows
    document.querySelector('.swiper-button-next')?.addEventListener('click', function () {
        emuPauseMedia();
        // If on the last slide, go back to the first
        if (emuMainSlider.realIndex === emuMainSlider.slides.length - 1) {
            emuMainSlider.slideTo(0);
        }
        // Otherwise, Swiper will handle the slide transition automatically
    });

    document.querySelector('.swiper-button-prev')?.addEventListener('click', function () {
        emuPauseMedia();
        emuUpdateIndices();
        // If on the first slide (based on your custom index), go to the second-to-last slide
        if (emuIndices[0] === 0) {
            emuMainSlider.slideTo(emuMainSlider.slides.length - 2);
        }
        // Otherwise, Swiper will handle the default navigation
    });

    // Set up click events for the thumbnails
    document.querySelectorAll('.emu-thumb-slider .swiper-slide').forEach((thumb, index) => {
        thumb.addEventListener('click', function () {
            emuPauseMedia(); // Pause media before changing the slide
            emuMainSlider.slideTo(index); // Change the main slide to the index of the clicked thumbnail
        });
    });
});
