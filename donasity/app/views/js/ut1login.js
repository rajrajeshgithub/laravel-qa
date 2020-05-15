// JavaScript Document
$(document).ready(function()
{
	$('.forget-password').click(function()
	{
		$('.login-section').hide();
		$('.login-heading').hide();
		$('.account-section').hide();
		$('.forget-password-heading').show();
		$('.forget-password-section').show();
	});
	$('.go-back').click(function()
	{
		$('.forget-password-section').hide();
		$('.forget-password-heading').hide();
		$('.login-heading').show();
		$('.login-section').show();
	});
	$('.sign-up-btn').click(function()
	{
		$('.account-section').show();
	});
	
	/* ========= strong password validation for registration page ===== */
	
	$.validator.addMethod('IsStrongPassword', function (value, element)
	{
		var Pass=$('#signupPassword').val();
		if(Pass!="")
		{
			var Status	= 1;
			//validate character
			if ( Pass.match(/[A-z]/) == null) {
			Status = 0;
			} 
			//validate capital letter
			if ( Pass.match(/[A-Z]/) == null ) {
				Status = 0;
			} 
			
			//validate number
			if ( Pass.match(/\d/) == null) {
				Status = 0;
			}
			 if ( Pass.match(/[@]/) == null){
				Status = 0;	 
			}
			if(Status == 0)
			{
				return false;	
			}
			else
			{
				return true;	
			}
		}
		else
		{
			return true;	
		}
		
	});
	/* =============================== End ========================== */
	
	
	//-- login validation
	$("#login-form").validate(
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
			email:{required:true,email: true},
			password:{required:true},				
		},
		messages:
		{
			email:{required:"Email Address",email:"Enter the valid  email"},
			password:{required:"Password"},			
		}
	});
	$("#forget-password-form").validate(
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
			email:{required:true,email: true},			
		},
		messages:
		{
			email:{required:"Email Address",email:"Enter the valid  email"},		
		}
	});
	$("#signup-form").validate(
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
			emailAddress:
			{
				required:true,
				email: true,
				remote: 
					{
						url:SITEURL+"ut1/IsDuplicateEmail",
						dataType:'json',
						async:true,						
					}
			},
			bankName:{required:true},
			ddaNumber:{required:true},
			abaRoutNumber:{required:true},
			checkNumber:{required:true, digits:true},
			signupPassword:{required:true, minlength:6,maxlength:20,IsStrongPassword:true},
			confirmPassword:{required:true, equalTo:"#signupPassword"}
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
			emailAddress:{required:"Email Address",email:"Enter the valid  email",remote:"Email already in use"},
			bankName:{required:"Bank name"},
			ddaNumber:{required:"DDA number"},
			abaRoutNumber:{required:"ABA routing number"},
			checkNumber:{required:"Check number", digits:"Only digits"},
			signupPassword:{required:"Password", minlength:"Atleast 6 characters required",maxlength:"Password maximum limit reached",IsStrongPassword:"Password not strong"},
			confirmPassword:{required:"Confirm Password", equalTo:"Password does not match"}
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
	/*==========load state according country=======*/
	
	/*============== check term condition ==========*/
	$('#signup-form').submit(function(){
		if($("#term").is(':checked')==false)
		{
			alert("If you want to continue, please check term & condition");
			return false;	
		}
	});	
	/*============== check term condition ==========*/
	
	/*============== masking for phone number ====== */
	 $('#phoneNumber').inputmask("(999) 999-9999");
	   $('#altPhoneNumber').inputmask("(999) 999-9999");
/*=================== End ========================= */	   
	
});

