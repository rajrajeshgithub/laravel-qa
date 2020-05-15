// JavaScript Document

$(document).ready(function()
{	

	$(document).on('mouseover','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','block');
	});
		
	$(document).on('mouseout','.error',function () 
	{
		$(this).next('b.tooltip').eq(0).css('display','none')
	});
		
	
		
	$("#resetForm").validate(
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
				newPassword		:{required:true,minlength:6},
				confirmPassword	:{required:true,equalTo:"#newPassword"},
				/*confirmPassword	:{required:true,equalTo:"#newpass"},
				newPassword		:{required:true,minlength:6},
				confirmPassword	:{required:true,equalTo:"#newPass"},*/
				
			},
			messages:
			{
				newPassword	:{required:"Please enter your password",minlength:"Please enter minimum 6 characters"},
				confirmPassword	:{equalTo:"Password does not match"},
				newPassword	:{required:"Please enter your password",minlength:"Please enter minimum 6 characters"},
				confirmPassword	:{required:"Please enter your confirm password",equalTo:"Password does not match"},
			},
		});			
});

