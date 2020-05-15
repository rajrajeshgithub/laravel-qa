// JavaScript Document
$(document).ready(function()
{	
	jQuery("#CD_userPhone").mask('(999) 999-9999');
	jQuery("#CD_officeNumber").mask('(999) 999-9999');
	jQuery("#CD_mobile").mask('(999) 999-9999');
	
	$('.listingmoreBtn i').click(function()
		{
			 $(this).toggleClass("fa-plus fa-minus");
			 $(this).closest('.row').find('.listingmoreSection').slideToggle();
			
		});

	var baseUrl = (window.location).href; // You can also use document.URL
	var UrlLastId = baseUrl.substring(baseUrl.lastIndexOf('#') + 1);
	
	if($('#NPO_CD_ID').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus').addClass('fa-minus');
		$('.viewmoreBtn i').closest('.row').find('.viewmorSection').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus').addClass('fa-plus');
		$('.listingmoreBtn i').closest('.row').find('.listingmoreSection').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='NpoContacts')
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
	
	
	$(".GenerateUrl").click(function()
	{
		var str = $("#C_CategoryNameEn").val();
		str = str.replace(/[^a-z\s]/gi, '');
		str = $.trim(str);				
		str = str.replace(/[_\s]/g, '-');				
		$("#C_urlFriendlyName").val(str);
			
	})
	//jQuery("#R_state").attr("disabled", true),
	getStateList(jQuery("#countryAbbr").val(),jQuery("#StateAbbr").val()),


	

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
				CD_firstName		:{required:true},
				CD_lastName			:{required:true},
				CD_userPhone		:{required:true},
				CD_emailAddress		:{required:true,email:true},
				CD_websiteUrl		:{url:true},
				//CD_addressline1		:{required:true},
				//CD_country			:{required:true},
				//CD_state			:{required:true},
				//CD_city				:{required:true},
				//CD_zip				:{required:true},
				//
				//
				//CD_companyName  	:{required:true},
				/*CD_emailAddress:
				{	
					required: true,
					email:true,
					remote: 
					{
						url:SITEURL+"npocontacts/CheckEmail",
						data:
						{
							keyId: function() 
							{
							   return $("#NPO_CD_ID").val();
							},
						},
					dataType: 'json',
					async:true,
					cache:false, 
				  }
				},*/
			},
			messages:
			{
				CD_firstName	:{required:"Please enter first name"},
				CD_lastName		:{required:"Please enter last name"},
				CD_userPhone	:{required:"Please enter cell number"},
				CD_websiteUrl   :{url:"please enter valid url"},
				CD_emailAddress	:{required:"Please enter email",email:"Please enter valid email"},
				
				//CD_companyName   :   {required:"Please enter company name"},
				//CD_addressline1	:	{required:"Please enter Address"},
				//CD_country		:	{required:"Please select country"},
				//CD_state			:	{required:"Please select state"},
				//CD_city			:	{required:"Please enter city"},
				//CD_zip			:	{required:"Please enter zip code"},
				
				//CD_emailAddress	:	{required:"Please enter email id",email:"Please enter valid email",remote: "Email already in use"},
			}
		});			
});

function getStateList(e) {
    "" != jQuery.trim(e) && jQuery.ajax({
        type: "POST",
        url: SITEURL + "npocontacts/getStateList/" + e + "/" + jQuery("#StateAbbr").val(),
        cache: !1,
        beforeSend: function() {},
        success: function(e) {
            jQuery("#CD_state").attr("disabled", !1).html(e)
        },
        error: function(e) {
            alert(e)
        }
    })
}

function GotoPage(pageNo)
{
	$('#pageNumber').val(pageNo);
	$('#formSection').attr('action', '');
	document.formSection.submit();
	return false;
}

function Contact_Deleted(CID,CD_EIN)
{
	if(confirm("Are you sure you want to delete this Contact detail ?")) 
	{
		window.location = SITEURL+'npocontacts/index/delete-contactdetail/'+CD_EIN+'/'+CID;
	}
	else
	{
		return false;
	}
}

function Delete_CD()
{
	var chkValue = [];
	$(':checkbox:checked').each(function(i)
	{
	  chkValue[i] = $(this).val();
	});
	if(chkValue!=0)
	{
		if(confirm("Are you sure you want to delete this contact detail ?")) 
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
