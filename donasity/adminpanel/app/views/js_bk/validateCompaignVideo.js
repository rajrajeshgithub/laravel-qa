// JavaScript Document
$(document).ready(function()
{	
	$('.listingmoreBtn i').click(function()
	{
		 $(this).toggleClass("fa-plus fa-minus");
		 $(this).closest('.row').find('.listingmoreSection').slideToggle();
		
	});

	var baseUrl = (window.location).href; // You can also use document.URL
	var UrlLastId = baseUrl.substring(baseUrl.lastIndexOf('#') + 1);
	
	if($('#CV_ID').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
		$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
		$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='Compaignvideo')
		{
			$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
			$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
			$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
			$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
			
		}
		else
		{
			$('.viewmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
			$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:none' );
			$('.listingmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
			$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:block' );
		}
	}
	
	$("#formSection").validate(
		{
			
			success: function(element)
			{
				$("#"+element[0].htmlFor).next("b.tooltip").remove();
			},
			errorPlacement: function(error, element)
			{
				element.next("b.tooltip").remove();
				element.after("<b class='tooltip tooltip-top-right'>"+error.text()+"</b>");
			},
			
			rules:
			{
				CV_Title		:	{required:true},
				CV_sorting		:	{digits:true},
			},
			messages:
			{
				CV_Title		:	{required:"Please enter video title"},
				CV_sorting		:	{digits:"Please enter digits only"},
			}
		});			
});

function GotoPage(pageNo)
{
	$('#pageNumber').val(pageNo);
	$('#formSection').attr('action', '');
	document.formSection.submit();
	return false;
}

function Delete_Video(CV_ID,CV_CID)
{
	if(confirm("Are you sure you want to delete this campaign video ?")) 
	{
		window.location = SITEURL+'campaignvideo/index/delete-video/'+CV_CID+'/'+CV_ID;
	}
	else
	{
		return false;
	}
}


