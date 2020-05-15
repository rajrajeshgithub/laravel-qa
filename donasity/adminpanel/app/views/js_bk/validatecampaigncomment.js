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
	
	$("#commentform").validate(
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
					comment:"required",
			},
			messages:
			{
				comment		: {required:"Please enter comment"},
			},
		});	
});