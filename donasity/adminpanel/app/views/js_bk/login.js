jQuery(function() {
	jQuery("#password").on("paste cut",function(e){e.preventDefault()});
    	
	$("#loginfrm").validate(
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
				userName		:{required:true},
				password		:{required:true},
				
			},
			messages:
			{
				userName		:	{required:"Please enter username"},
				password		:	{required:"Please enter password"}					
			}
		});	
		$("#forgotpassForm").validate(
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
				emailAddress:
				{	
					required: true,
					email:true,
					/*remote: 
					{
						url:SITEURL+"adminusers/CheckEmail",
						data:
						{
							keyId: function() 
							{
							   return $("#AdminUserId").val();
							},
						},
					dataType: 'json',
					async:true,
					cache:false, 
				  }*/
				},
				
			},
			messages:
			{
				emailAddress	:	{required:"Please enter email id",email:"Please enter valid email"}
					
			}
		});			

	
	
	jQuery('#forgotPassContainer').hide();
		jQuery("#lostPassBtn").on("click", function() {
    		jQuery("#loginContainer").is(":visible") ? (jQuery("#loginContainer").hide(), jQuery("#forgotPassContainer").show(), jQuery("#loginContainer").find("span.error").remove("span"), jQuery("#loginContainer").find("input").hasClass("error") && jQuery("#loginContainer").find("input").removeClass("error")) : (jQuery("#loginContainer").show(), jQuery("#forgotPassContainer").hide())
		}),
		jQuery("#loginBtn").on("click", function() {
    		jQuery("#forgotPassContainer").is(":visible") ? (jQuery("#loginContainer").show(), jQuery("#forgotPassContainer").hide(), jQuery("#forgotPassContainer").find("span.error").remove("span"), jQuery("#forgotPassContainer").find("input").hasClass("error") && jQuery("#forgotPassContainer").find("input").removeClass("error")) : (jQuery("#loginContainer").hide(), jQuery("#forgotPassContainer").show())
	});
});