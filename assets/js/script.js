jQuery(function($) {
  // Variables to store the Swiper instances
  var mainSwiper   = null,
      thumbSwiper  = null,
      // Stores the original gallery HTML to restore later
      originalMainGallery  = $('.emu-main-slider .swiper-wrapper').html(),
      originalThumbGallery = $('.emu-thumb-slider .swiper-wrapper').html();

  // Function to pause media (videos/iframes) on non-visible slides
  function pauseMedia() {
    $('.swiper-slide').each(function() {
      $(this).find('video').each(function() {
        if (!this.paused) this.pause();
      });
      var iframe = $(this).find('iframe');
      if (iframe.length) {
        var src = iframe.attr('src');
        iframe.attr('src', '');
        iframe.attr('src', src);
      }
    });
  }

  // Function to initialize (or reinitialize) the sliders
  function initSwipers() {
    // If an instance already exists, destroy it to avoid conflicts
    if (mainSwiper && typeof mainSwiper.destroy === 'function') {
      mainSwiper.destroy(true, true);
      mainSwiper = null;
    }
    if (thumbSwiper && typeof thumbSwiper.destroy === 'function') {
      thumbSwiper.destroy(true, true);
      thumbSwiper = null;
    }
    
    // Initialize the thumbnail slider (thumbSwiper)
    thumbSwiper = new Swiper('.emu-thumb-slider', {
      spaceBetween: 10,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesVisibility: true,
      watchSlidesProgress: true,
      loop: false
    });
    
    // Initialize the main slider without using automatic navigation
    mainSwiper = new Swiper('.emu-main-slider', {
      spaceBetween: 10,
      thumbs: {
        swiper: thumbSwiper
      },
      loop: false,
      on: {
        slideChange: function() {
          // Remove disabled classes to keep the arrows active
          $('.swiper-button-next, .swiper-button-prev').removeClass('swiper-button-disabled');
        }
      }
    });
    
    // Set up click event for the "next" arrow
    $('.swiper-button-next').off('click').on('click', function(e) {
      e.preventDefault();
      pauseMedia();
      var totalSlides = mainSwiper.slides.length;
      var currentIndex = mainSwiper.activeIndex;
      
      // If it's the last slide, go back to the first; otherwise, move to the next slide
      if (currentIndex >= totalSlides - 1) {
         mainSwiper.slideTo(0);
      } else {
         mainSwiper.slideNext();
      }
    });
    
    // Set up click event for the "previous" arrow
    $('.swiper-button-prev').off('click').on('click', function(e) {
      e.preventDefault();
      pauseMedia();
      var totalSlides = mainSwiper.slides.length;
      var currentIndex = mainSwiper.activeIndex;
      
      // If it's the first slide, go to the last; otherwise, move to the previous slide
      if (currentIndex <= 0) {
         mainSwiper.slideTo(totalSlides - 1);
      } else {
         mainSwiper.slidePrev();
      }
    });
    
    // Set up click event for thumbnails to change the main slide
    $('.emu-thumb-slider .swiper-slide').off('click').on('click', function() {
      pauseMedia();
      var index = $(this).index();
      mainSwiper.slideTo(index);
    });
  }

  // Initialize the sliders on page load
  initSwipers();

  // When a variation is selected, update the gallery:
  // The new gallery will contain the variation image first, followed by the original product images
  $('form.variations_form').on('found_variation', function(event, variation) {
    if (variation && variation.image && variation.image.src) {
      // Create the variation image slide
      var variationSlide = '<div class="swiper-slide">' +
                              '<img src="' + variation.image.src + '" alt="' + (variation.image.alt ? variation.image.alt : '') + '">' +
                           '</div>';
      
      // Concatenate the variation image with the original gallery
      var newMainGallery  = variationSlide + originalMainGallery;
      var newThumbGallery = variationSlide + originalThumbGallery;
      
      // Update the gallery HTML
      $('.emu-main-slider .swiper-wrapper').html(newMainGallery);
      $('.emu-thumb-slider .swiper-wrapper').html(newThumbGallery);
      
      // Reinitialize the Swipers to reflect the update
      initSwipers();
    }
  });

  // When the variation is reset, restore the original product gallery
  $('form.variations_form').on('reset_data', function() {
    $('.emu-main-slider .swiper-wrapper').html(originalMainGallery);
    $('.emu-thumb-slider .swiper-wrapper').html(originalThumbGallery);
    initSwipers();
  });
});
