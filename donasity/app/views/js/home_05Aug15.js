// JavaScript Document
$(document).ready(function()
{
	//--- top main step section js
	$('.active-step').append('<span class="active-arrow"><i class="fa fa-caret-down"></i></span>')
	$('.banner-steps a').hover(function()
	{
		var thisIndex=$(this).parent().index();
		$('.banner-steps a').removeClass('active-step');
		$(this).addClass('active-step');
		$('.banner-steps a').find('span.active-arrow').remove();
		$('.active-step').append('<span class="active-arrow"><i class="fa fa-caret-down"></i></span>')			
		$('.banner-step-description .step-description').hide();
		$('.banner-step-description .step-description').eq(thisIndex).show();
	});

	//--- main banner height
	var bannerSectionHeight=$(window).height();
	
	var searchHeight=$('.search-section').innerHeight();
	var bannerHeight=bannerSectionHeight-searchHeight;
	if($(window).width() > 1199)
	{
		$('.main-banner').css('height',bannerHeight);
	}
	//-- Go next bottom section js
	$('.down-arrow i').click(function()
	{
		var nextSection=$(this).parents('section').next('section');
		$('html, body').animate({scrollTop: $(nextSection).offset().top}, 2000);
	}); 
	//-- Generosity meet slider	
	$('.bxslider').bxSlider({
	  pagerCustom: '.generosity-meet-step'
	});

	$(".popular").owlCarousel(
	{
		items:4,
		itemsDesktop : [1199,4],
		itemsDesktopSmall: [979,3],
		slideSpeed: 1000,
		stopOnHover: true,
		navigation: true,
		navigationText:false,
		pagination: false,
		responsive: true,
		autoHeight : true,
		autoplay:true,
	});
	$(".new-feature").owlCarousel(
	{
		items:4,
		itemsDesktop : [1199,4],
		itemsDesktopSmall: [979,3],
		slideSpeed: 1000,
		stopOnHover: true,
		navigation: true,
		navigationText:false,
		pagination: false,
		responsive: true,
		autoHeight : true,
		autoplay:true,
	});
	$(".ending-soon").owlCarousel(
	{
		items:4,
		itemsDesktop : [1199,4],
		itemsDesktopSmall: [979,3],
		slideSpeed: 1000,
		stopOnHover: true,
		navigation: true,
		navigationText:false,
		pagination: false,
		responsive: true,
		autoHeight : true,
		autoplay:true,
	});
	
	//--search validation
	$("#search-form").validate(
	{
		success: function(element)
		{
			$("#"+element[0].htmlFor).next("span.errorClass").remove();
		},
		errorPlacement: function(error, element)
		{
			element.next("span.errorClass").remove();
			element.after("<span class='errorClass'>"+error.text()+"</span>");
		},
		rules:
		{
			searching:{required:true},				
		},
		messages:
		{
			searching:{required:"Search"},		
		}
	});
	//-- Featured Fundraisers name height js
	$('.featured-fundraisers .owl-carousel').each(function()
	{
		var nameHeight=0;
		$(this).find('.owl-wrapper .owl-item').each(function()
		{
			var nameMinHight = $(this).find('.featured-box .fundraisers-name').innerHeight();
			if(nameMinHight > nameHeight)
			{
				nameHeight=nameMinHight;
			}			
		});
		$(this).find('.featured-box .fundraisers-name').css('height',nameHeight);	
	});
	
	//-- text align middle js
	//var winWidth=window.innerWidth;
	
	categoriesNamePos();
	sectionContentHeight();
	$(window).resize(function(){
		sectionContentHeight();
		categoriesNamePos();
	});
	
	//-- on scroll mobile transition
	$(window).scroll(function()
	{
		var screenWidth = window.innerWidth;
		if(screenWidth >= 1200)
		{
			connectFriends();
		}
	});
});

function sectionContentHeight()
{
	$('.section-content').each(function()
	{
		var secDesHeight=$(this).find('.section-description').innerHeight();
		var mainHeadHeight=$(this).find('.main-heading').height();
		$(this).find('.main-heading').css('margin-top',(secDesHeight-mainHeadHeight)/2);
	});			
} 

function categoriesNamePos()
{
	setTimeout(function()
	{
		$('.donation-categories-box').each(function()
		{
			var boxHeight = $(this).find('a').height();
			var nameHeight = $(this).find('a .donation-categories-name').height();
			var namePos = (boxHeight-nameHeight)/2;
			$(this).find('a .donation-categories-name').css('top',namePos);
		});			
	}, 250);
}
function connectFriends()
{
	var cwfPosition = $('.connect-with-friends').offset().top;
	var cwfHeight = $('.connect-with-friends').outerHeight();
	var scrollPosition = $(window).scrollTop() + $(window).height();
	if((scrollPosition - cwfPosition + 120) >= 0)
	{
		var rotate = (-25 + ((scrollPosition - cwfPosition + 120) / 25));
		if(rotate>0)
			rotate = 0;
		else if(rotate<-25)
			rotate = -25;
		
		//$('.social-mobile-image').css("transform","rotate("+rotate+"deg)");
	}
	else
	{
		//$('.social-mobile-image').css("transform","rotate(-30deg)");
	}
}