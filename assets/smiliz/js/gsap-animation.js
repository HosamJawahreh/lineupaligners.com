(function ($) {
	"use strict";

	var hasGsap = typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined";

	if (hasGsap) {
		gsap.registerPlugin(ScrollTrigger, SplitText);
		gsap.config({
			nullTargetWarn: false,
			trialWarn: false
		});
	}

	/*----  Functions  ----*/
	function getpercentage(x, y, elm) { 
		elm.find('.pbmit-fid-inner').html(y + '/' + x);
		var cal = Math.round((y * 100) / x);
		return cal;
	}

	var pbmit_sticky_header = function() {
		if (jQuery('.pbmit-header-sticky-yes').length > 0) {
			var header_html = jQuery('#masthead .pbmit-main-header-area').html();
			jQuery('.pbmit-sticky-header').append(header_html);
			jQuery('.pbmit-sticky-header #menu-toggle').attr('id', 'menu-toggle2');
			if (!window.lineupSmilizNavManaged) {
				jQuery('#menu-toggle2').on('click', function() {
					jQuery("#menu-toggle").trigger("click");
				});
			}
			jQuery('.pbmit-sticky-header .main-navigation ul, .pbmit-sticky-header .main-navigation ul li, .pbmit-sticky-header .main-navigation ul li a').removeAttr('id');
			jQuery('.pbmit-sticky-header h1').each(function() {
				var thisele = jQuery(this);
				var thisele_class = jQuery(this).attr('class');
				thisele.replaceWith('<span class="' + thisele_class + '">' + jQuery(thisele).html() + '</span>');
			});
			// For infostak header
			if (jQuery('.pbmit-main-header-area').hasClass('pbmit-infostack-header')) { // check if infostack header
				// for header style 2
				jQuery(".pbmit-sticky-header .pbmit-header-menu-area").insertAfter(".pbmit-sticky-header .site-branding");
				jQuery('.pbmit-sticky-header .pbmit-header-info, .pbmit-sticky-header .pbmit-mobile-search').remove();
			}
		}
	}
	
	var pbmit_sticky_header_class = function () {
		var lastScroll = 0; 
		if (jQuery('#wpadminbar').length > 0) {
			jQuery('#masthead').addClass('pbmit-adminbar-exists');
		} 
		jQuery(window).on('scroll', function () {
			var scroll = jQuery(window).scrollTop();
			var header_height = 0;

			if ( scroll === 0 ){
				jQuery('#masthead .pbmit-sticky-header').removeClass('pbmit-fixed-header');
			} else {
				// Calculate full header height
				if (jQuery('.pbmit-main-header-area').length > 0) {				
					header_height = jQuery('.pbmit-main-header-area').height();
				} 
				// Scroll down → hide header
				if (scroll > header_height && scroll > lastScroll) {
					jQuery('#masthead .pbmit-sticky-header').removeClass('pbmit-fixed-header');
				}
				// Scroll up → show header
				else if (scroll < lastScroll) {
					jQuery('#masthead .pbmit-sticky-header').addClass('pbmit-fixed-header');
				}
			} 
			lastScroll = scroll;
		});
	};
	var pbmit_menu_span = function() {
		jQuery('.pbmit-max-mega-menu-override #page #site-navigation .mega-menu-wrap>ul>li.mega-menu-item .mega-sub-menu a, .pbmit-navbar ul ul a').each(function(i, v) {
			jQuery(v).contents().eq(0).wrap('<span class="pbmit-span-wrapper"/>');
		});
	}

	var pbmit_toggleSidebar = function() {
		if (window.lineupSmilizNavManaged) {
			return;
		}

		jQuery('#menu-toggle').on('click', function() {
			jQuery("body:not(.mega-menu-pbminfotech-top) .pbmit-navbar > div, body:not(.mega-menu-pbminfotech-top)").toggleClass("active");
		})
		if (jQuery('.pbmit-navbar > div > .closepanel').length == 0) {
			jQuery('.pbmit-navbar > div').append('<span class="closepanel"><svg class="qodef-svg--close qodef-m" xmlns="http://www.w3.org/2000/svg" width="20.163" height="20.163" viewBox="0 0 26.163 26.163"><rect width="36" height="1" transform="translate(0.707) rotate(45)"></rect><rect width="36" height="1" transform="translate(0 25.456) rotate(-45)"></rect></svg></span>');
			jQuery('.pbmit-navbar > div > .closepanel, .mega-menu-pbminfotech-top .nav-menu-toggle').on('click', function() {
				jQuery(".pbmit-navbar > div, body, .mega-menu-wrap").toggleClass("active");
			});
			return false;
		}
	}

	function pbmit_title_animation() {
		if (!hasGsap) {
			return;
		}

		ScrollTrigger.matchMedia({
			"(min-width: 1025px)": function() {
				var pbmit_var = jQuery('.pbmit-custom-heading, .pbmit-heading-subheading');
				if (!pbmit_var.length) {
					return;
				}
				const quotes = document.querySelectorAll(".pbmit-custom-heading .pbmit-title , .pbmit-heading-subheading .pbmit-title ");
				quotes.forEach(quote => {
					var getclass = quote.closest('.pbmit-custom-heading ,.pbmit-heading-subheading').className;
					var animation = getclass.split('animation-');
					if (animation[1] == "style4") return
					//Reset if needed
					if (quote.animation) {
						quote.animation.progress(1).kill();
						quote.split.revert();
					}
					quote.split = new SplitText(quote, {
						type: "lines,words",
						linesClass: "split-line"
					});
					gsap.set(quote, { perspective: 400 });
					if (animation[1] == "style1") {
						gsap.set(quote.split.words, {
							opacity: 0,
							y: "90%",
							rotateX: "-40deg"
						});
					}
					if (animation[1] == "style2") {
						gsap.set(quote.split.words, {
							opacity: 0,
							x: "50"
						});
					}
					if (animation[1] == "style3") {
						gsap.set(quote.split.words, {
							opacity: 0,
						});
					}
				
					quote.animation = gsap.to(quote.split.words, {
						scrollTrigger: {
							trigger: quote,
							start: "top 90%",
						},
						x: "0",
						y: "0",
						rotateX: "0",
						opacity: 1,
						duration: 1,
						ease: Back.easeOut,
						stagger: .02
					});
				});
			},
		});
	}

	var pbmit_before_after = function($scope = jQuery(document)) {
		if (window.lineupSmilizBeforeAfterManaged) {
			return;
		}

		if (typeof jQuery.fn.twentytwenty == "function") {
			$scope.find(".pbmit-ele-before-after-inner").each(function () {
				var $container = jQuery(this);

				if ($container.hasClass('twentytwenty-container') || $container.closest('.twentytwenty-wrapper').length) {
					return;
				}

				$container.find('.pbmit-after-image').removeClass('pbmit-hide');
				$container.twentytwenty({
					before_label: '',
					after_label: '',
					no_overlay: true
				});
			});
		}
	}

	var pbmit_div_wrapper = function() {
	setTimeout(() => {
		const targets = document.querySelectorAll('.pbmit-portfolio-style-2 .twentytwenty-wrapper.twentytwenty-horizontal');

		targets.forEach(target => {
		// Only wrap if not already wrapped
		if (target.parentNode && !target.parentElement.classList.contains('pbminfotech-t20-wraper')) {
			const wrapper = document.createElement('div');
			wrapper.classList.add('pbminfotech-t20-wraper', 'col-md-6', 'col-lg-6');
			target.parentNode.insertBefore(wrapper, target);
			wrapper.appendChild(target);
		}
		});
	}, 100); // Adjust delay if needed
	}

	var pbmit_search_btn = function() {
		jQuery(function() {
			var search_form = jQuery(".pbmit-header-search-form");
			var search_field = jQuery('.pbmit-header-search-form .search-field');
			var $body = jQuery('body');

			jQuery(".pbmit-header-search-btn").on('click', function(e) {
				if (!search_form.hasClass('active')) {
					search_form.addClass('active');
					setTimeout(function() { search_field.get(0).focus(); }, 500);
				} else if (search_field.val() === '') {
					search_form.removeClass('active');
					search_field.get(0).focus();
				}
				e.preventDefault();
				return false;
			});

			jQuery(".pbmit-header-search-form .pbmit-search-overlay, .pbmit-header-search-form .pbmit-search-close").on('click', function (e) {
				$body.addClass('pbmit-search-animation-out');
				setTimeout(function () {
					$body.removeClass('pbmit-search-animation-out');
				}, 800);
				setTimeout(function () {
					search_form.removeClass('active');
				}, 800);
				e.preventDefault();
				return false;
			});
		});
	}

	var pbmit_img_animation = function() {
		const pbmit_img_class = jQuery('.pbmit-animation-style1, .pbmit-animation-style2, .pbmit-animation-style3, .pbmit-animation-style4, .pbmit-animation-style5, .pbmit-animation-style6, .pbmit-animation-style7');
		
		pbmit_img_class.each(function() {
		const each_box = jQuery(this);
		
		new Waypoint({
			element: this, 
			handler: function(direction) {
			if (direction === 'down') {
				each_box.addClass('active');
			}
			},
			offset: '70%',
		});
		});
	}

	var pbmit_thia_sticky = function() {
		if(typeof jQuery.fn.theiaStickySidebar == "function"){
			jQuery('.pbmit-sticky-sidebar').theiaStickySidebar({
				additionalMarginTop: 100
			});
			jQuery('.pbmit-sticky-column').theiaStickySidebar({
				additionalMarginTop: 180
			});
		}
	}

	// on ready
	jQuery(document).on('ready', function(){
		pbmit_title_animation();
	});

	// on resize
	jQuery(window).on('resize', function(){
		pbmit_title_animation();
	});

	// on load 
	jQuery(window).on('load', function(){
		pbmit_sticky_header();
		pbmit_sticky_header_class();
		pbmit_menu_span();
		pbmit_toggleSidebar();
		pbmit_div_wrapper();
		pbmit_search_btn();
		pbmit_thia_sticky();

		if (hasGsap) {
			pbmit_title_animation();
			pbmit_before_after();
			pbmit_img_animation();

			gsap.delayedCall(1, () =>
				ScrollTrigger.getAll().forEach((t) => {
					t.refresh();
				})
			);
		}
	});	
})($);

