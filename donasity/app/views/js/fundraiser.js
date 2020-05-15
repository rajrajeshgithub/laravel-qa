// JavaScript Document
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
		/*$('.fundraiser-video .fundraiser-video-frame').hide();
		$('.fundraiser-video .fundraiser-video-frame').eq(liIndex).show();*/
	});

	//-- Load more js
	$('#loadMore').click(function()
	{
		$('.comment-box').fadeIn('slow');
	});

	//alert( $('#DonationPrice').val());
	//-- progress circle callling js 
	$val=true; 
	if($('#DonationPrice').val()==0) $val=false;
	$('.loader').ClassyLoader({
		start: 'top',
		animate:$val,
		percentage: $('#DonationPrice').val(),
		speed: 15,
		fontSize: '50px',
		diameter: 70,
		fontFamily:'quicksandbold',
		fontColor: 'rgba(255, 255, 255, 1)',
		lineColor: '#b1c851',
		remainingLineColor: 'rgba(255,255,255,0.2)',
		lineWidth: 15
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
		$(vid).removeClass('dn');		
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
			$(vid).removeClass('dn');		
		}
	}
}
