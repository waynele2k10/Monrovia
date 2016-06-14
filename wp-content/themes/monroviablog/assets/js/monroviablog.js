jQuery.resizeList = function resizeList(container) {
    if (jQuery(container).length > 0) {
        if (jQuery(window).width() <= 768) {
            jQuery(container).children().each(function () {
                var that = this;
                var itemHeight = 60;
                jQuery(this).find('.content-box').children().each(function () {
                    itemHeight += jQuery(this).outerHeight(true);
                });
                if (itemHeight > 239) {
                    jQuery(that).children('.content-box').css('height', itemHeight + 'px');
                    jQuery(that).find('.thumbnail-box > img').css({'max-height': itemHeight + 'px', 'width': 'auto', 'max-width': 'none'});
                } else {
                    jQuery(that).children('.content-box').css('height', '239px');
                    jQuery(that).find('.thumbnail-box > img').css({'max-height': '239px', 'width': 'auto', 'max-width': 'none'});
                }
            });
        } else if (jQuery(window).width() > 768 && jQuery(window).width() < 992) {
            jQuery(container).children().each(function () {
                var that = this;
                var itemHeight = 60;
                jQuery(this).find('.content-box').children().each(function () {
                    itemHeight += jQuery(this).outerHeight(true);
                });
                if (itemHeight > 275) {
                    jQuery(that).children('.content-box').css('height', itemHeight + 'px');
                    jQuery(that).find('.thumbnail-box > img').css({'height': itemHeight + 'px', 'width': 'auto', 'max-width': 'none'});
                } else {
                    jQuery(that).children('.content-box').css('height', '275px');
                    jQuery(that).find('.thumbnail-box > img').css({'max-height': '275px', 'width': 'auto', 'max-width': 'none'});
                }
            });
        } else {
            jQuery(container).children('.content-box').css('height', '');
            jQuery(container).find('.thumbnail-box > img').css({'max-height': '', 'width': '', 'max-width': ''});
        }
    }
}
jQuery(document).ready(function ($) {

    /* No custom menu? Style default */
    if ($('.navbar .menu').length > 0) {
        //$('.navbar .menu').addClass('collapse navbar-collapse').children('ul').addClass('nav navbar-nav navbar-right');
        //$('li.page_item_has_children').addClass('dropdown').children('a').addClass('dropdown-toggle').attr('data-toggle','dropdown').append(' <i class="icon-caret-down"></i>').next('ul').addClass('dropdown-menu');
    }

    /* Navbar Dropdowns */
    //if ($('nav ul.navbar-nav').length>0) {
    /* Level 1 */
    // $('nav ul.navbar-nav > li').each(function() {
    // if ($(this).children().hasClass('sub-menu')) {
    // $(this).addClass('dropdown');
    // $(this).children('a').addClass('dropdown-toggle').attr('data-toggle','dropdown').append('&nbsp;<i class="icon-caret-down"></i>');
    // $(this).children('ul').removeClass('sub-menu').addClass('dropdown-menu');
    // }
    // });

    /* Level 2 */
    // $('nav ul.navbar-nav > li li').each(function() {
    // if ($(this).children().hasClass('sub-menu')) {
    // $(this).addClass('dropdown-submenu');
    // $(this).children('ul').removeClass('sub-menu').addClass('dropdown-menu');
    // }
    // });
    // }

    /* Add "form-control" class to form elements */
    if ($('textarea').length > 0 || $('input[type="email"]').length > 0 || $('select').length > 0 || $('input[type="text"]').length > 0) {
        $('textarea,input[type="email"],input[type="text"],select').addClass('form-control');
    }

    /* Comments submit and respond buttons */
    if ($('#comment-submit').length > 0)
        $('#comment-submit').addClass('btn btn-primary btn-lg');
    if ($('.comment-reply-link').length > 0)
        $('.comment-reply-link').addClass('btn btn-primary btn-xs');

    // Adds thickbox class to WordPress default gallery images
    if ($('div.gallery .gallery-icon a').length > 0) {
        $('div.gallery .gallery-icon a').each(function () {
            $(this).addClass('thickbox').attr('rel', 'attached-to-' + $(this).closest('article').attr('id').replace(/^\D+/g, ''));
        });
    }

    // Adds thickbox class to post images
    if ($('article.media a > img').length > 0)
        $('article.media a:not(\'.media-object\') > img').addClass('img-thumbnail').parent().addClass('thickbox');

    function initSlider() {
        if ($('.bxslider .slides').length > 0) {
            if (jQuery(window).width() <= 767) {
                $('.bxslider .slides').bxSlider({
                    adaptiveHeight: true,
                    mode: 'fade',
                    captions: true,
                    pager: false,
                    prevText: "<i class='fa fa-caret-left'></i>",
                    nextText: "<i class='fa fa-caret-right'></i>"
                });
            } else {
                $('.bxslider .slides li.item').each(function (index) {
                    $("#bx-pager").append(
                            $("<a></a>").attr('data-slide-index', index).append(
                            $("<img class='bx-thumbnail'>").attr('src', $(this).attr('src-thumbnail'))
                            )
                            );
                });
                $('.bxslider .slides').bxSlider({
                    pagerCustom: '#bx-pager',
                    adaptiveHeight: true,
                    controls: false,
                    mode: 'fade',
                    captions: true,
                    onSliderLoad: function () {
                        $('#bx-pager').bxSlider({
                            pager: false,
                            //slideWidth: (parseInt($('.bxslider').width())-20)/3,
                            slideWidth: 91,
                            minSlides: 3,
                            maxSlides: 3,
                            slideMargin: 1,
                            prevText: "<i class='fa fa-caret-left'></i>",
                            nextText: "<i class='fa fa-caret-right'></i>"
                        });
                    }
                });
            }

            var slideH = $('.bxslider').outerHeight() / 2;
            $('.bx-controls .bx-controls-direction a').css('top', slideH + 'px');
        }
    }
    initSlider();
    $(window).on('resize', function () {
        var win = $(this); //this = window
        if (win.width() <= 767) {
            initSlider();
        }
    });

    // header control
    responsiveHeadControl();
    $(window).on('resize', function () {
        var win = $(this); //this = window
        if (win.width() <= 768) {
            responsiveHeadControl();
        }
    });
    function responsiveHeadControl() {
        var height = $('.head-col-logo').outerHeight();
        $('.head-control').css({
            "font-size": height,
            "line-height": height + 'px'
        });
        $('.head-control .control-item i').css("line-height", height + 'px');
    }

    $(".navbar-toggle").toggle(function () {
        $("body").animate({marginTop: '0', marginRight: '280px', marginBottom: '0', marginLeft: '-280px'}, 500);
        $(this).children('i').removeClass('fa-bars').addClass('fa-times-circle');
        var target = $(this).attr('data-target');
        $(target).addClass('in').animate({display: 'block', right: '0'}, 500);
    }, function () {
        $("body").animate({marginTop: '0', marginRight: '0', marginBottom: '0', marginLeft: '0'}, 500);
        $(this).children('i').removeClass('fa-times-circle').addClass('fa-bars');
        var target = $(this).attr('data-target');
        $(target).animate({right: '-280px'}, 500).removeClass('in');
    });

    $(".search-toggle").toggle(function () {
        $("#primary-menu").show(500);
    }, function () {
        $("#primary-menu").hide(500);
    });

    $.resizeList('.list-post > ul');
    $(window).on('resize', function () {
        $.resizeList('.list-post > ul');
    });
    function reSizeImgMap() {
        var winWidth = $('img.floorplan_image').width();
        if (winWidth < 980) {
            var size = $(window).width() * 61 / 980;
            $('.mark-feature').css({'width': size, 'height': size, 'background-size': '100%'});
        }else{
            $('.mark-feature').css({'width': '61px', 'height': '61px', 'background-size': '100%'});
        }
    }
    reSizeImgMap();
    $(window).resize(function () {
        reSizeImgMap();
    });
});