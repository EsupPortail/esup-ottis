/**
 * @module lib/carroussel
 * Carrousel initialization using Swiper library
 * Handles responsive breakpoints and touch movement
 */

/**
 * Update touch movement based on content overflow
 * @param {Object} swiper - Swiper instance
 */
function updateTouchMove(swiper) {
  const hasOverflow = swiper.slides.length * (swiper.slides[0]?.offsetWidth + swiper.params.spaceBetween) > swiper.size;
  swiper.allowTouchMove = hasOverflow;
  swiper.loop = hasOverflow;

  if (!hasOverflow) {
    swiper.slideTo(0);
  }
  swiper.update();
}

/**
 * Hide navigation arrows if total slides <= slidesPerView
 * @param {Object} swiper - Swiper instance
 */
function updateNavigationVisibility(swiper) {
  const totalSlides = swiper.slides.length;
  const slidesPerView = swiper.params.slidesPerView;

  if (totalSlides <= slidesPerView) {
    const prevButton = document.querySelector('.swiper-button-prev');
    const nextButton = document.querySelector('.swiper-button-next');

    if (prevButton) prevButton.classList.add('hide');
    if (nextButton) nextButton.classList.add('hide');
  }
}

/**
 * Initialize Swiper carrousel with configuration
 * Must be called after DOM is loaded and Swiper library is loaded
 */
export function initCarrousel() {
  const swiperElement = document.querySelector('.swiper');

  if (!swiperElement) {
    console.warn('Swiper container not found. Carrousel initialization skipped.');
    return null;
  }

  // Check if Swiper is available
  if (typeof Swiper === 'undefined') {
    console.error('Swiper library not loaded. Please include Swiper JS before initializing carrousel.');
    return null;
  }

  const swiper = new Swiper('.swiper', {
    direction: 'horizontal',
    loop: true,
    slidesPerView: 3,
    slidesPerGroup: 1,
    spaceBetween: 20,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    grabCursor: true,
    freeMode: false,

    // Responsive breakpoints
    breakpoints: {
      640: { slidesPerView: 2, spaceBetween: 10 },
      1024: { slidesPerView: 3, spaceBetween: 20 }
    },

    on: {
      init: function() {
        updateTouchMove(this);
        updateNavigationVisibility(this);
      },
      resize: function() {
        updateTouchMove(this);
      }
    }
  });

  return swiper;
}

/**
 * Auto-initialize on DOM ready if Swiper is available
 */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCarrousel);
} else {
  initCarrousel();
}
