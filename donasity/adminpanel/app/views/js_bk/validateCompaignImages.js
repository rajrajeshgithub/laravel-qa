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
	
	if($('#CI_ID').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
		$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
		$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='Compaignimages')
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
	
	$.validator.addMethod("imageValidate", function()
	{	
	    var imgvalue= $('#CI_imageName').val()=="" ? $('#CI_oldImage').val(): $('#CI_imageName').val();
		
		if(imgvalue=="")
		{	
			return false;
		}
		else
		{
			var extension = imgvalue.toLowerCase().split('.').pop();
			if(extension == 'png' || extension == 'jpeg' || extension == 'jpg' )
			{
				return true;
			}else{
				return false;	
			}
		}
	})

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
				filePath		:{imageValidate:true},
				CI_imageTitle	:{required:true},
				CI_imageType  	:{required:true},
				sortOrder		:	{digits:true},
				
			},
			messages:
			{
				CI_imageName		:{required:"Please enter image name",imageValidate:"Please upload jpg,png file only"},
				CI_imageTitle		:{required:"Please enter image title"},
				CI_imageType   		:{required:"Please enter image type"},
				sortOrder		:	{digits:"Please enter digits only"},
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

function confirmDeleteMainImg(C_ID,I_ID)
{
	if(confirm("Are you sure you want to delete this campaign image ?")) 
	{
		window.location = SITEURL+'campaignimages/index/deleteimage/'+C_ID+'/'+I_ID;
	}
	else
	{
		return false;
	}
}

function Delete_Image(CI_ID,CI_CID)
{
	if(confirm("Are you sure you want to delete this campaign image ?")) 
	{
		window.location = SITEURL+'campaignimages/index/delete-detail/'+CI_CID+'/'+CI_ID;
	}
	else
	{
		return false;
	}
}
