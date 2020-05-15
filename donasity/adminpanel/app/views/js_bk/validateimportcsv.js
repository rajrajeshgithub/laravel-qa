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
	
	
	$.validator.addMethod('FileExt', function (value, element)
		{
			var FileValue = $('#filePath').val();
			if(FileValue=="")
			{	
				return false;
				
			}
			else
			{
				var extension = FileValue.toLowerCase().split('.').pop();
				if(extension == 'csv')
				{
					return true;
				}else{
					return false;	
				}
			}
		});
	
	$("#uploadcsv").validate(
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
				filePath		:	{required:true,FileExt:true},
			},
			messages:
			{
				filePath		: {required:"Please choose csv file",FileExt:"Only csv file can be upload"},
			},
		});	
});