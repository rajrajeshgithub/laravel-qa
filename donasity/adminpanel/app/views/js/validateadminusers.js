$(document).ready(function(){
	//for show msg on hower start
	$(document).on('mouseover','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','block');
		});
			
		$(document).on('mouseout','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','none')
		});
	//for show msg on hower end
	$("#adminuserform").validate(
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
				firstName		:	"required",
				lastName		:	"required",
				emailAddress:
				{	
					required: true,
					remote: 
					{
						url:SITEURL+"adminusers/CheckEmailAddress",
						data: 
						{
							UserID: function() 
							{
							   return $("#adminuserid").val();
							},
						},	
						dataType: 'json',
						async:true ,
						cache:false
				  }
				},
				userName:
				{	
					required: true,
					remote: 
					{
						url:SITEURL+"adminusers/CheckUserName",
						data: 
						{
							UserID: function() 
							{
							   return $("#adminuserid").val();
							},
						},	
						dataType: 'json',
						async:true ,
						cache:false
				  }
				},
				password:{required: true,minlength: 6,maxlength: 20},
			},
			messages:
			{
				firstName		: {required:"Please enter first name"},
				lastName		: {required:"Please enter last name"},
				emailAddress	: {required:"Please enter email address",remote:"This email address already in use"},
				userName		: {required:"Please enter user name",remote:"This username already in use"},
				password		: {required:"Please enter password"},
			},
		});	
});