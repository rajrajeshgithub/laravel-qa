// JavaScript Document
$(document).ready(function()
{	
	jQuery("#BD_phone").mask('(999) 999-9999');
	
	var baseUrl = (window.location).href; // You can also use document.URL
	var UrlLastId = baseUrl.substring(baseUrl.lastIndexOf('#') + 1);
	if($('#BD_ID').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
		$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
		$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='NpoBanks')
		{
			$('.viewmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
			$('.listingmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
			$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
			
		}
		else
		{
			$('.viewmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
			$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
			$('.listingmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
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
				BD_bankName			:{required:true},
				BD_bankAddress		:{required:true},
				BD_emailAddress		:{email:true},
				
				/*BD_accName			:{required:true},
				BD_accNumber  		:{required:true},
				BD_accType			:{required:true},*/
				
			
				
			},
			messages:
			{
				BD_bankName			:{required:"Please enter bank name"},
				BD_bankAddress		:	{required:"Please enter bank address"},
				BD_emailAddress	:	{email:"Please enter valid email"},
				
				/*BD_accName			:{required:"Please enter account  name"},
				BD_accNumber   		:{required:"Please enter account number"},
				BD_accType			:{required:"Please enter Account type"},*/
				
				
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

function BanksDetails_Deleted(BD_ID,BD_EIN)
{
	if(confirm("Are you sure you want to delete this bank detail ?")) 
	{
		window.location = SITEURL+'npobanks/index/delete-bankdetail/'+BD_EIN+'/'+BD_ID;
	}
	else
	{
		return false;
	}
}

function Delete_BD()
{
	var chkValue = [];
	$(':checkbox:checked').each(function(i)
	{
	  chkValue[i] = $(this).val();
	});
	if(chkValue!=0)
	{
		if(confirm("Are you sure you want to delete this bank detail ?")) 
		{
			document.ListingFormSetion.submit();
		}
	}
	else
	{
		alert("Please select at least one to perform this action?")
		return false;
	}	
}