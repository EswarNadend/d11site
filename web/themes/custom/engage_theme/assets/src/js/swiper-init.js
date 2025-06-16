(function (Drupal, once) {
  Drupal.behaviors.swiperInit = {
    attach: function (context) {
      once('swiper-init', '.mySwiper', context).forEach(function (el) {
        new Swiper(el, {
          spaceBetween: 1,
          slidesPerView: 1,
          autoplay: {
            delay: 2500,
            disableOnInteraction: false,
          },
          breakpoints: {
            640: { slidesPerView: 1, spaceBetween: 2 },
            768: { slidesPerView: 2, spaceBetween: 4 },
            1024: { slidesPerView: 3, spaceBetween: 30 },
          },
          pagination: {
            el: ".swiper-pagination",
            clickable: true,
          },
        });
      });
    }
  };
})(Drupal, once);
