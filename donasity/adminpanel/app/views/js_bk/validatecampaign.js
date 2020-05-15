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
	
	$("#addcampaign").validate(
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
					title			:"required",
					userfriendlyname:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"campaign/CheckuserfriendlyurlDuplicacyAjax",
							data: 
							{
								CampID: function() 
								{
								   return $("#campaignid").val();
								},
							},	
							dataType: 'json',
							async:true,
							cache:false,
					 	 },
					},
					npoein:
					{
						remote: 
					   	{
							url:SITEURL+"campaign/EINExist",
							dataType: 'json',
							async:true,
							cache:false,
					 	 }, 
					},
			},
			messages:
			{
				title				: {required:"Please enter Campaign title"},
				userfriendlyname	: {required:"Please enter Campaign user friendly name",remote:"This User friendly name is already in use"},
				npoein				: {remote:"This EIN does not exist"}
			},
		});	
		
		
		//generate user friendly url
			$(".GenerateUrl").click(function(){
				var str = $("#title").val();
				str = str.replace(/[^a-z\s]/gi, '');
				str = $.trim(str);				
				str = str.replace(/[_\s]/g, '-');
				str = str.toLowerCase();			
				$("#userfriendlyname").val(str);
		
			})
});