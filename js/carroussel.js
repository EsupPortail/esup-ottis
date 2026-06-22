document.addEventListener('DOMContentLoaded', function() {
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
    freemode: false,

    // Breakpoints (optionnel)
    breakpoints: {
        640: { slidesPerView: 2, spaceBetween: 10 },
        1024: { slidesPerView: 3, spaceBetween: 20 }
    },
    on: {
                init: function() {
                    updateTouchMove(this);
                    // Masquer les flèches si le nombre total de slides <= slidesPerView
                    const totalSlides = this.slides.length;
                    const slidesPerView = this.params.slidesPerView;
                    if (totalSlides <= slidesPerView) {
                        document.querySelector('.swiper-button-prev').classList.add('hide');
                        document.querySelector('.swiper-button-next').classList.add('hide');
                    }
                },
                resize: function() {
                    updateTouchMove(this);
                }
            }
        });

    // Fonction pour vérifier si le contenu dépasse et réactiver si nécessaire
    function updateTouchMove(swiper) {
        // Vérifie si le contenu dépasse
        const hasOverflow = swiper.slides.length * (swiper.slides[0]?.offsetWidth + swiper.params.spaceBetween) > swiper.size;
        // Réactive le défilement si nécessaire
        swiper.allowTouchMove = hasOverflow;
        //swiper.freeMode = hasOverflow;
        swiper.loop = hasOverflow;
        if (!hasOverflow){
            swiper.slideTo(0);
        }
        swiper.update();
    }
});