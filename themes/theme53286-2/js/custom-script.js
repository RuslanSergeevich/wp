(function($) {
    $(function(){
    	//Dropdown cart in header
		$('.cart-holder > h3').click(function(){
			if($(this).hasClass('cart-opened')) {
				$(this).removeClass('cart-opened').next().slideUp(300);
			} else {
				$(this).addClass('cart-opened').next().slideDown(300);
			}
		});
		//Popup rating content
		$('.star-rating').each(function(){
			rate_cont = $(this).attr('title');
			$(this).append('<b class="rate_content">' + rate_cont + '</b>');
		});
		//Fix contact form not valid messages errors
		jQuery(window).load(function() {
			jQuery('.wpcf7-not-valid-tip').live('mouseover', function(){
				jQuery(this).fadeOut();
			});

			jQuery('.wpcf7-form input[type="reset"]').live('click', function(){
				jQuery('.wpcf7-not-valid-tip, .wpcf7-response-output').fadeOut();
			});
		});

		// compare trigger
		$(document).on('click', '.cherry-compare', function(event) {
			event.preventDefault();
			button = $(this);
			$('body').trigger( 'yith_woocompare_open_popup', { response: compare_data.table_url, button: button } )
		});

    });
    $(window).resize(
      function(){
       $('.header_wrapper').width($(window).width());
       $('.header_wrapper').css({width: $(window).width(), "margin-left": ($(window).width()/-2)});
      }
     ).trigger('resize');
     
     $(window).resize(
      function(){
       $('.banner_box').width($(window).width());
       $('.banner_box').css({width: $(window).width(), "margin-left": ($(window).width()/-2)});
      }
     ).trigger('resize');
     
     $(window).resize(
      function(){
       $('.g_map').width($(window).width());
       $('.g_map').css({width: $(window).width(), "margin-left": ($(window).width()/-2)});
      }
     ).trigger('resize');
     
     $(".banner-wrap.banner_box1").addClass("zoomIn wow");  $('.banner-wrap.banner_box1').attr( 'data-wow-duration', '1.5s' );
     $(".banner-wrap.banner_box2").addClass("zoomIn wow"); $('.banner-wrap.banner_box2').attr( 'data-wow-delay', '0.2s'); $('.banner-wrap.banner_box2').attr( 'data-wow-duration', '1.5s' );
     $(".banner-wrap.banner_box3").addClass("zoomIn wow"); $('.banner-wrap.banner_box3').attr( 'data-wow-delay', '0.4s'); $('.banner-wrap.banner_box3').attr( 'data-wow-duration', '1.5s' );
     $(".banner-wrap.banner_box4").addClass("zoomIn wow"); $('.banner-wrap.banner_box4').attr( 'data-wow-delay', '0.6s'); $('.banner-wrap.banner_box4').attr( 'data-wow-duration', '1.5s' );
     
     $(".welcome_box h1").addClass("fadeInUp wow");
     $(".welcome_box h2").addClass("fadeInUp wow"); $('.welcome_box h2').attr( 'data-wow-delay', '0.1s');
     $(".welcome_box p").addClass("fadeInUp wow"); $('.welcome_box p').attr( 'data-wow-delay', '0.2s');
     
     $(".parallax-content h1").addClass("fadeInRight wow"); $('.parallax-content h1').attr( 'data-wow-duration', '1.5s' );
     $(".parallax-content .row .span6:first-child").addClass("fadeInLeft wow");
     $(".parallax-content .row .span6:first-child+.span6").addClass("fadeInRight wow");
     $(".parallax-content .row .span6:first-child+.span6+.span6").addClass("fadeInLeft wow"); $('.parallax-content .row .span6:first-child+.span6+.span6').attr( 'data-wow-delay', '0.1s');
     $(".parallax-content .row .span6:first-child+.span6+.span6+.span6").addClass("fadeInRight wow"); $('.parallax-content .row .span6:first-child+.span6+.span6+.span6').attr( 'data-wow-delay', '0.1s');
     $(".product_box .woocommerce .products").addClass("fadeInUp wow"); $(".product_box .woocommerce .products").attr( 'data-wow-delay', '0.2s');
     $(".product_box h1").addClass("zoomIn wow");
     
     $(".parallax-box .posts-grid li:first-child").addClass("fadeInLeft wow"); $(".parallax-box .posts-grid li:first-child").attr( 'data-wow-delay', '0.2s');
     $(".parallax-box .posts-grid li:first-child+li").addClass("fadeInLeft wow"); $(".parallax-box .posts-grid li:first-child+li").attr( 'data-wow-delay', '0.1s');
     $(".parallax-box .posts-grid li:first-child+li+li").addClass("fadeInLeft wow");
     
     $(".g_map_content_wrap_inner").addClass("fadeInDown wow"); $('g_map_content_wrap_inner').attr( 'data-wow-duration', '1.5s' ); $(".g_map_content_wrap_inner").attr( 'data-wow-delay', '0.2s');
     

     
})(jQuery);


