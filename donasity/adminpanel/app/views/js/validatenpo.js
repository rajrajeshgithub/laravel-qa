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
	
	$.validator.addMethod('IsNumeric',function ()
	{
		var DedCode	= $('#npodedcode').val();
		if(isNaN(DedCode))
		{
			return false;	
		}
		else
		{
			return true;	
		}
	});
	
	//validation code for multiselect category start
	/*$('#nposform').submit(function(){
		if($( "#nposubsectionname option:selected" ).length > 0)
		{
			$("#nposubsectionname").next("b.tooltip").remove();
			return true;	
		}
		else
		{
			$("#nposubsectionname").next("b.tooltip").remove();
			$("#nposubsectionname").after("<b class='tooltip tooltip-top-right'>Please select minimum one category</b>");
			return false;	
		}
	});*/
	//validation code for multiselect category start
	
	
	$("#nposform").validate(
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
					npoein:
					{	
						required: true,
					   	remote: 
					   	{
							url:SITEURL+"npos/CheckEINDuplicacy",
							data: 
							{
								NPOID: function() 
								{
								   return $("#npoID").val();
								},
							},	
							dataType: 'json',
							async:true,
							cache:false,
					  }
					},
					nponame				:"required",
					npostreet			:"required",
					npodedcode			:{required:true,IsNumeric:"true"},
					nposubsectionname   :"required",
			},
			messages:
			{
				npoein				: {required:"Please enter EIN",remote:"EIN number can't be duplicate"},
				nponame				: {required:"Please enter NPOs name"},
				npostreet			: {required:"Please enter street address"},
				npodedcode			: {required:"Deduction code must be enter",IsNumeric:"Please enter only numeric value"},
				nposubsectionname   : {required:"Please enter npo subsection name"},
			},
		});	
});