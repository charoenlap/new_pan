//SinglePage slide activator
if ($('.single-product-slider').length > 0) {





    var singlePSlider = $(".single-product-slider").owlCarousel({

        pagination: false,
        navigation: false, // hide next and prev buttons
        slideSpeed: 300,
        paginationSpeed: 400,
        singleItem: true

    });

    var gallery = singlePSlider.data('owlCarousel');





    var thumbSlider = $('.single-product-gallery .gallery-thumbs ul').owlCarousel({
        items: 5, //10 items above 1000px browser width
        itemsDesktop: [1000, 4], //5 items between 1000px and 901px
        itemsDesktopSmall: [900, 3], // betweem 900px and 601px
        itemsTablet: [400, 2], //2 items between 600 and 0

        itemsMobile: false // itemsMobile disabled - inherit from itemsTablet option
    });

    var thumbCtrls = thumbSlider.data('owlCarousel');



    $(".single-product-gallery .gallery-thumbs .horizontal-thumb").click(function(event) {
        event.preventDefault();
        $(this).parent().parent().parent().find('.active').removeClass('active');

        $(this).parent().addClass('active');

        var tid = $(this).attr('href');
        var targetSlide = $(".single-product-gallery-item" + tid);

        var targetSlide = $(targetSlide).parent().index();

        gallery.goTo(targetSlide);


    });



    if ($('.single-product-vertical-gallery').length > 0) {
        $('.single-product-vertical-gallery ul').carouFredSel({
            direction: 'up',
            auto: false,
            items: 4,
            circular: true
        });

        $(".single-product-vertical-gallery .up-btn").click(function(event) {
            event.preventDefault();
            $('.single-product-vertical-gallery ul').trigger("next", 1);

        });


        $(".single-product-vertical-gallery .down-btn").click(function(event) {
            event.preventDefault();
            $('.single-product-vertical-gallery ul').trigger("prev", 1);

        });


        $(".single-product-vertical-gallery .vertical-gallery-item").click(function(event) {
            event.preventDefault();
            tid = $(this).attr('href');
            targetSlide = $(".single-product-gallery-item" + tid);

            singlePSlider.trigger('slideTo', targetSlide);

        });

    }





    //        Horizontal Single page gallery

    if ($('.single-product-horizontal-gallery').length > 0) {
        $('.single-product-horizontal-gallery ul').carouFredSel({
            auto: false,
            circular: true
        });

        $(".single-product-horizontal-gallery .next-btn").click(function(event) {
            event.preventDefault();
            $('.single-product-horizontal-gallery ul').trigger("next", 1);

        });


        $(".single-product-horizontal-gallery .prev-btn").click(function(event) {
            event.preventDefault();
            $('.single-product-horizontal-gallery ul').trigger("prev", 1);

        });


        $(".single-product-horizontal-gallery .horizontal-gallery-item").click(function(event) {
            event.preventDefault();
            tid = $(this).attr('href');
            targetSlide = $(".single-product-gallery-item" + tid);
            console.log(targetSlide)
            singlePSlider.trigger('slideTo', targetSlide);

        });

    }
}