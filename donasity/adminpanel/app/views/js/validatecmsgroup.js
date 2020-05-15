$(document).ready(function(){
	//for show msg on hower start
	/*$(document).on('mouseover','.error',
		function () {
		$(this).prev('span.errors').find('div.PopupBox').eq(0).css('display','block');
		}
		);
		$(document).on('mouseout','.error',
		function () {
		$(this).prev('span.errors').find('div.PopupBox').eq(0).css('display','none')
		}
		);*/
		
		$(document).on('mouseover','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','block');
		});
			
		$(document).on('mouseout','.error',function () 
		{
			$(this).next('b.tooltip').eq(0).css('display','none')
		});
	//for show msg on hower end
	
	$("#cmsgroupform").validate(
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
					language:	"required",
					grouptitle:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"static/CheckGroupName",
							data: 
							{
								GroupID: function() 
								{
								   return $("#groupid").val();
								},
							},	
							dataType: 'json',
							async:true ,
							cache:false
					  }
					},
					sortorder: {digits:true},
			},
			messages:
			{
				language		: {required:"Please select language"},
				grouptitle		: {required:"Please enter group name",remote: "Group name already in use"},
				sortorder		: {digit:"Please enter numeric value"},
			},
		});	
});