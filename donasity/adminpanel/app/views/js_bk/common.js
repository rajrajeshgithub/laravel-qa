$(document).ready(function(){
	var RESOURCES = 'http://pdc/donasity/adminpanel/app/views/';

});
function reloadPage() {
    window.location = window.location
}
function GotoPage(pageNo) {
	
	jQuery('#pageNumber').val(pageNo);
	jQuery('#formSection').submit();
	return false;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();	
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function removeCookie(cname,cvalue)
{
	var d = new Date();
    d.setTime(d.getTime() + (0*24*60*60*1000)-86400000);
    var expires = "expires="+d.toUTCString();	
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
jQuery(function()
{
/*	var wh=parseInt($(window).height());
	var bh=parseInt($('#bottomSection').height());
	var th=parseInt($('#topSection').height());

	var mh=wh-(bh+th);
	jQuery('#middleSection #leftSection').css('min-height',mh);
*/	
	var cookieRes = getCookie('leftsectionclass');
	
	if(cookieRes!="collapseSection")
	{
		$("#leftSection").removeClass("collapseSection");
		$("#leftSection .leftcollpase").find("i").removeClass("fa-arrow-circle-o-right");
		$("#leftSection .leftcollpase").find("i").addClass("fa-arrow-circle-o-left");
		$("#middleSection #rightSection").css('width','84%');
		
	}
	else
	{		
		$("#leftSection").addClass("collapseSection");
		$("#leftSection .leftcollpase").find("i").removeClass("fa-arrow-circle-o-left");
		$("#leftSection .leftcollpase").find("i").addClass("fa-arrow-circle-o-right");
		$("#middleSection #rightSection").css('width','94%');	
	}
		
	$('.accordion').on('show.bs.collapse', function () 
	{
		alert('test');return false;
		$(this).find('.collapsenewIcon').addClass("collapsenewIconClose");
		$(this).find('.collapsenewIcon').removeClass("collapsenewIconOpen");
	});
	
	$('.accordion').on('hide.bs.collapse', function ()
	{
		alert('in');return false;
		$(this).find('.collapsenewIcon').addClass("collapsenewIconOpen");
		$(this).find('.collapsenewIcon').removeClass("collapsenewIconClose");
	});


	jQuery('#middleSection #leftSection').css('min-height',jQuery('#middleSection #rightSection').height()+10);
	
	var maxHeight=Math.max.apply(null,jQuery('.contentSection .service-block').map(function()
	{
			return jQuery(this).outerHeight(true);
	}).get())
	
	jQuery('.contentSection .service-block').css('min-height',maxHeight-40);
	
	// LEFT SECTION
	// CSS FOR ACTIVE CLASS
	var activeTabID = jQuery('#activeTabID').val();
	jQuery(this).parent().find('.liActive').removeClass('liActive');
	jQuery(this).parent().find('span.activeSpan').remove();
	
	jQuery('.leftPannel ul.mainUl li#'+activeTabID).addClass('liActive');
	jQuery('.leftPannel ul.mainUl li#'+activeTabID).append('<span class="activeSpan"></span>');
	//--------------------
	// console.log(get_cookie("leftPanelCollapse"));
	//jQuery('#leftSection').css({'height':jQuery('#rightSection').height()});
	// BREADCRUMB CSS
	jQuery("#rightSection .rightPannel .breadcrumb").css("height", jQuery("#leftSection .leftPannel ul li:eq(0)").height()-10);
	
	
	//start code for collapse
	$('.viewmoreBtn i').click(function()
	{
		 $(this).toggleClass("fa-plus fa-minus");
		 $(this).closest('.row').find('.viewmorSection').slideToggle();
		
	});
	//end code for collapse
	$('.leftcollpase').click(function()
	{		
		if($("#leftSection").hasClass("collapseSection"))
		{
			removeCookie("leftsectionclass","collapseSection");
			$("#leftSection").removeClass("collapseSection");
			$(this).find("i").removeClass("fa-arrow-circle-o-right");
			$(this).find("i").addClass("fa-arrow-circle-o-left");
			$("#middleSection #rightSection").css('width','84%');
		}
		else
		{
			setCookie("leftsectionclass","collapseSection",1);			
			$("#leftSection").addClass("collapseSection");			
			$(this).find("i").removeClass("fa-arrow-circle-o-left");
			$(this).find("i").addClass("fa-arrow-circle-o-right");
			$("#middleSection #rightSection").css('width','94%');			
		}
	});
	
})
