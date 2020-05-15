// JavaScript Document
$(document).ready(function()
{
	$('.account-section').show();
	$("#manage-profile-form").validate(
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
			fname:{required:true},
			lname:{required:true},
			Address1:{required:true},
			city:{required:true},
			state:{required:true},
			country:{required:true},
			zipCode:{required:true},
			phoneNumber:{required:true},
			profileImg:{accept:"jpg,png,jpeg,gif"}
		},
		messages:
		{
			fname:{required:"First name"},
			lname:{required:"Last name"},
			Address1:{required:"Address"},
			city:{required:"City"},
			state:{required:"State"},
			country:{required:"Select country"},
			zipCode:{required:"Zip code"},
			phoneNumber:{required:"Phone number"},
			profileImg:{accept:"Only image type jpg/png/jpeg/gif is allowed"}
		}
	});
	
	/*==========load state according country=======*/
	$('#country').change(function(){
		$.ajax(
		{
			type:'POST',
			dataType:'json',
			cache :false,
			async:true,
			data:{CountryAB:$(this).val()},
			url:SITEURL+'ut1myaccount/getstateajax',
			success:function(data)
			{
				$('#state option').remove();
				for(obj in data)
				{
					var Caption 		= data[obj].State_Name;
					var Captionvalue	= data[obj].State_Value;	
					$('#state').append($('<option>', { 
						value: Captionvalue,
						text : Caption 
					}));
					
				}		
			}
		});	
	});
	/*==========load state according country=======*/
	
	/*============== masking for phone number ====== */
	 $('#phoneNumber').inputmask("(999) 999-9999");
	   $('#altPhoneNumber').inputmask("(999) 999-9999");
/*=================== End ========================= */	 



/*=================== Datepicker ============================ */
	$('#dob').datepicker();
/*=================== End =================================== */ 
	
});