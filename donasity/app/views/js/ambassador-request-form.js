// JavaScript Document
$(document).ready(function()
{
	$("#ambassador-form").validate(
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
			organizationsName:{required:true},
			einNumber:{required:true},
			contactName:{required:true},
			billingAddress:{required:true},
			zipCode:{required:true},
			contactNumber:{required:true},
			emailAddress:{required:true,email:true},
		},
		messages:
		{
			organizationsName:{required:"Organization name"},
			einNumber:{required:"EIN number"},
			contactName:{required:"Contact name"},
			billingAddress:{required:"Billing Address"},
			zipCode:{required:"Zip Code"},
			contactNumber:{required:"Contact Number"},
			phoneNumber:{required:"Phone number"},
			emailAddress:{required:"Email Address",email:"Enter the valid  email"},
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
			url:SITEURL+'ut1/getstateajax',
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
	
	
	
	/*============== masking for phone number ====== */
	 $('#contactNumber').inputmask("(999) 999-9999");
/*=================== End ========================= */	   
	
});

