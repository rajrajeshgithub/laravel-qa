// JavaScript Document
/*$(document).ready(function()
{
	//-- Photo gallery calling js
	$('.photo-gallery-section').magnificPopup({
		delegate: 'a',
		type: 'image',
		gallery:{enabled:true},
	});
	//--- Hide/show video js
	$('.thumb-video .fundraiser-thumb-video').click(function()
	{
		var liIndex=$(this).index();
		$('.fundraiser-video .fundraiser-video-frame').hide();
		$('.fundraiser-video .fundraiser-video-frame').eq(liIndex).show();
	});

	//-- Load more js
	$('#loadMore').click(function()
	{
		$('.comment-box').fadeIn('slow');
	});

	//-- progress circle callling js 
	$('.loader').ClassyLoader({
		start: 'top',
		percentage: 75,
		speed: 15,
		fontSize: '50px',
		diameter: 85,
		fontFamily:'quicksandbold',
		fontColor: 'rgba(255, 255, 255, 1)',
		lineColor: '#abc340',
		remainingLineColor: 'transparent',
		lineWidth: 15,
		roundedLine: true,
	});
});*/


$(document).ready(function()
{
	//-- Photo gallery calling js
	$('.photo-gallery-section').magnificPopup({
		delegate: 'a',
		type: 'image',
		gallery:{enabled:true},
	});
	//--- Hide/show video js
	$('.thumb-video .fundraiser-thumb-video').click(function()
	{
		var liIndex=$(this).index();
		$('.fundraiser-video .fundraiser-video-frame').hide();
		$('.fundraiser-video .fundraiser-video-frame').eq(liIndex).show();
	});
	//-- Load more js
	$('#loadMore').click(function()
	{
		$('.comment-box').fadeIn('slow');
	});
	//-- Sticky bar appear js
	$('.stiky-bar').hide();
	$(window).scroll(function()
	{
		if($(window).scrollTop()>500) {
			$(".stiky-bar").fadeIn(500);
		}
		else{
			$(".stiky-bar").fadeOut(500);
		}
	});	
	//-- top banner height
	var wnd = $(window);
	var wndSize = function(e){
		  return wnd.outerWidth();
	};
	var prevSize = wndSize();
	platinumBanner(prevSize);
	wnd.on("resize", function() {
		  var curSize =  wndSize();    
		  prevSize = curSize;
		  platinumBanner(prevSize);
	});
	videoFrame_onload();
});

function videoFrame(vid,vurl,vtype)
{
	$('.fundraiser-video .fundraiser-video-frame').addClass('dn');
	console.log(vid);
	if(vtype==1)
	{
		console.log(vurl);
		$('#'+vid).parent().removeClass('dn');
		jwplayer(vid).setup({
			id:vid,
			file: vurl, 
			width:'100%',
			aspectratio:"90:50", 
			autostart: true, 
			repeat: true,
		});
	}else{
		vid.removeClass('dn');		
	}	
}

function videoFrame_onload()
{
	
	var vid=$('#vid_0');

	if(vid.attr('id')!=undefined)
	{
		if(vid.attr('dtype')=='1')
		{
			var vurl=vid.attr('durl');
			jwplayer('vid_0').setup({
				id:'vid_0',
				file: vurl, 
				width:'100%', 
				aspectratio:"90:50", 
				autostart: true, 
				repeat: true,
			});
		}else
		{
			vid.removeClass('dn');		
		}
	}
		
}

function platinumBanner(pvwidth)
{
	var screenWidth=pvwidth;
	var bannerHeight=screenWidth/1.6;
	if(bannerHeight>=750)
		bannerHeight=750;
	if(bannerHeight<=400)
		bannerHeight=400;
	$('.fundraiser-platinum-page').css('height',bannerHeight);
}