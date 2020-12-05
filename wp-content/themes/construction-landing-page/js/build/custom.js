jQuery(document).ready(function($){
	$('.about .col .text-holder').matchHeight();
	$('.our-services .col .holder').matchHeight();
	$('.testimonial .col').matchHeight();
	$('.testimonial blockquote').matchHeight();

    if( $('.our-projects .col').length > 0 ){
        $('.text-holder').each(function(){ 
            new PerfectScrollbar($(this)[0]); 
        });
    }

    //mobile-menu
    $('.menu-opener').click(function(){
        $('body').addClass('menu-open');
        $('.mobile-menu .primary-navigation').addClass('toggled');
    });

    $('.overlay').click(function(){
        $('body').removeClass('menu-open');
        $('.mobile-menu .primary-navigation').removeClass('toggled');
    });

    // $('.mobile-menu').prepend('<div class="close-mobile-menu">Close</div>');

    $('.close-mobile-menu').click(function(){
        $('.mobile-menu .primary-navigation').removeClass('toggled');
        $('body').removeClass('menu-open');
    });

    // $('.mobile-menu .primary-navigation ul .menu-item-has-children').append('<button class="open-submenu"></button>');
    // $('.mobile-menu .primary-navigation ul li .open-submenu').click(function(){
    //     $(this).prev().slideToggle();
    //     $(this).toggleClass('active');
    // });

    $('<button class="open-submenu "></button>').insertAfter($('.mobile-menu ul .menu-item-has-children > a'));
    $('.mobile-menu ul li .open-submenu ').click(function() {
        $(this).next().slideToggle();
        $(this).toggleClass('active');
    });

    //accessible menu for edge
    $("#site-navigation ul li a").focus(function(){
       $(this).parents("li").addClass("focus");
    }).blur(function(){
       $(this).parents("li").removeClass("focus");
    });
});
