// JavaScript Document
$(document).ready(function()
{
	//-----------Top Search-------------
	$(".activeLanguage").click(function(){	
		var langID =	$(this).attr('id');
		$('#activeLanguage').val(langID);
		$("#languageForm").submit();
	});
	
	//-------------End-----------------
	var screenWidth=window.innerWidth;
	if(screenWidth > 1199)
	{
		$('.dropdown a').attr('data-toggle','false');
	}
	else
	{
		$('.dropdown-menu').parents('.dropdown').find('.dropdown-toggle').attr('data-toggle','dropdown');
	}
	
	//-- top nav
	$('.gray-fix-nav .navbar-brand').find('.main-logo').attr('src',RESOURCES+'images/mobilelogo.png');
	$(window).scroll(function()
	{
		var scrollPosition = $(window).scrollTop() + $(window).height();
		if ($(".navbar").offset().top > 100)
		 {
			$(".navbar-fixed-top").addClass("top-nav-collapse");
			$('.navbar-brand').find('.main-logo').attr('src',RESOURCES+'images/mobilelogo.png');
			$('.gray-fix-nav .navbar-brand').find('.main-logo').attr('src',RESOURCES+'images/mobilelogo.png');
		}
		else
		{
			didClaimPositionShows = false;
			didSocialPosition = false;
			$(".navbar-fixed-top").removeClass("top-nav-collapse");
			$('.navbar-brand').find('.main-logo').attr('src',RESOURCES+'images/logo.png');
			$('.gray-fix-nav .navbar-brand').find('.main-logo').attr('src',RESOURCES+'images/mobilelogo.png');
		}
	});
	//-- Click to focus on search
	$('.top-search-icon').click(function()
	{
		$('.top-search').show();
		$('.top-search .top-search-input').find('input[type=text]').focus();
	});
	//-- after login menu js
	$('.my-account-a').click(function()
	{
		//console.log($(this));
		$('.my-account').find('.my-account-menu').hide();
		$('.my-account-a i.fa').removeClass('active-account');
		$(this).find('i.fa').addClass('active-account');
		$(this).parent('.my-account').find('.my-account-menu').stop(true,true).show();
	});
	$(document).mouseup(function (e)
	{
		var container = $(".top-search-icon");
		if (!container.is(e.target) && container.has(e.target).length === 0)
		{
			
			$('.top-search .top-search-input').find('input[type=text]').removeClass('error');
			$('.top-search-icon .top-search').css('display','none');
		}
		
		var container = $(".my-account-menu");
		var imageBtn = $(".my-account-a");
		if (!container.is(e.target) && !container.is($(e.target).parent()) && !imageBtn.is(e.target) && !imageBtn.is($(e.target).parent()))
		{
			$('.my-account-a i.fa').removeClass('active-account');
			$('.my-account .my-account-menu').fadeOut();
		}
	});
	
	//-- top search validation
	$("#top-search-form").validate(
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
			topInput:{required:true},				
		},
		messages:
		{
			topInput:{required:"Search"},		
		}
	});	
	
	//-- popup calling
	$('.login-popup').magnificPopup({
		type: 'iframe'
	});
	
	
	jQuery("#closebtn").click(function(){
		var referrer = document.referrer;
		parent.document.location.href = referrer;
	});
	
	
	//-- top bg hover color
	if($(window).width() > 767)
	{
		$('.dropdown').hover(function()
		{
			
			$('.header').css('background','#0d2946');
		},function()
		{
			$('.header').css('background','transparent');
		});
	}
});


// ------------Top Search ---------------------
function validatetop()
{
	var title = $.trim($('.topkeyword').val());
	var element = $('.topkeyword');
	if(title == "")
	{
		SetError(element,'Please enter search text');
		//return false;
	}	
	else
	{
		RemoveError(element);
		return true;
	}	
}

function SetError(element,ErrMsg)
{
	element.next("span.errorClass").remove();
	element.after("<span class='errorClass'>"+ErrMsg+"</span>");
	element.addClass('error');	
	return false;	
}

function RemoveError(element)
{
	element.next("span.errorClass").remove();
	element.removeClass('error');	
}
	
	
function SendUrlOnEnter(event)
{
	if (event.which == 13) {
		SendTopSearchUrl();
	}
}
	
function SendTopSearchUrl()
{
	if(validatetop()){
		var Keyword		= $('.topkeyword').val();
		
		url	= SITEURL+"non-profits-search";
		
		if(Keyword != "")
		{
			url	= url+"?filter[keyword]="+Keyword;
		}
		window.location.href=url;
	}
}

function number_format(number, decimals, dec_point, thousands_sep) {
  //  discuss at: http://phpjs.org/functions/number_format/
  // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: davook
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Michael White (http://getsprink.com)
  // bugfixed by: Benjamin Lupton
  // bugfixed by: Allan Jensen (http://www.winternet.no)
  // bugfixed by: Howard Yeend
  // bugfixed by: Diogo Resende
  // bugfixed by: Rival
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  //  revised by: Luke Smith (http://lucassmith.name)
  //    input by: Kheang Hok Chin (http://www.distantia.ca/)
  //    input by: Jay Klehr
  //    input by: Amir Habibi (http://www.residence-mixte.com/)
  //    input by: Amirouche
  //   example 1: number_format(1234.56);
  //   returns 1: '1,235'
  //   example 2: number_format(1234.56, 2, ',', ' ');
  //   returns 2: '1 234,56'
  //   example 3: number_format(1234.5678, 2, '.', '');
  //   returns 3: '1234.57'
  //   example 4: number_format(67, 2, ',', '.');
  //   returns 4: '67,00'
  //   example 5: number_format(1000);
  //   returns 5: '1,000'
  //   example 6: number_format(67.311, 2);
  //   returns 6: '67.31'
  //   example 7: number_format(1000.55, 1);
  //   returns 7: '1,000.6'
  //   example 8: number_format(67000, 5, ',', '.');
  //   returns 8: '67.000,00000'
  //   example 9: number_format(0.9, 0);
  //   returns 9: '1'
  //  example 10: number_format('1.20', 2);
  //  returns 10: '1.20'
  //  example 11: number_format('1.20', 4);
  //  returns 11: '1.2000'
  //  example 12: number_format('1.2000', 3);
  //  returns 12: '1.200'
  //  example 13: number_format('1 000,50', 2, '.', ' ');
  //  returns 13: '100 050.00'
  //  example 14: number_format(1e-8, 8, '.', '');
  //  returns 14: '0.00000001'

  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}
//----------End-------------------------------