const swiper = new Swiper('.swiper-container', {
    slidesPerView: 4,
    spaceBetween: 30,
    loop: true,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },

    breakpoints: {
        320: {
            slidesPerView: 1,
        },
        640: {
            slidesPerView: 2,
        },
        960: {
            slidesPerView: 3,
        },
        1200: {
            slidesPerView: 4,
        }
    }
});