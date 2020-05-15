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
	
	$("#cmspageform").validate(
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
					cmsgroup:	"required",
					pagename:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"static/CheckPageName",
							data: 
							{
								PageID: function() 
								{
								   return $("#pageid").val();
								},
							},	
							dataType: 'json',
							async:true,
							cache:false 
					  }
					},
					friendlyurl:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"static/CheckUrl",
							data: 
							{
								PageID: function() 
								{
								   return $("#pageid").val();
								},
								externalpageUrl:function()
								{
									return $("#externalpageUrl").val();
								},
								internalpageUrl:function()
								{
									return $("#internalpageUrl").val();
								}
							},	
							dataType: 'json',
							async:true ,
							cache:false
					  }
					},
					pagetitle_EN:	"required",
					pagetitle_ES:	"required",
					sortorder: {digits:true},
			},
			messages:
			{
				cmsgroup		: {required:"Please select static pages group"},
				pagename		: {required:"Please enter page name",remote: "Page name already in use"},
				friendlyurl		: {required:"Please enter url friendly page name",remote: "URL friendly page is already in use"},
				pagetitle_EN	: {required:"Please enter english page title"},
				pagetitle_ES	: {required:"Please enter spanish page title"},
				sortorder		: {digit:"Please enter numeric value"},
			},
		});	
});