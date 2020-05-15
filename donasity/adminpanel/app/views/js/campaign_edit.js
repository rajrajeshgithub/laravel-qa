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
		
		
		$.validator.addMethod("imageValidate", function()
		{	
			var imgvalue= $('#thumbImage').val();
			if(imgvalue=="")
			{	
				return true;
			}
			else
			{
				var extension = imgvalue.toLowerCase().split('.').pop();
				if(extension == 'gif' || extension == 'png' || extension == 'jpeg' || extension == 'jpg' )
				{
					return true;
				}else{
					return false;	
				}
			}
		});
	
	
	
	$("#frm_basicdetails").validate(
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
				Title				:{required:true},
				DonationGoal		:{required:true},
				DescriptionHTML		:{required:true},
				file     			:{imageValidate:true},
			},
			messages:
			{
				Title			:{required:"Please enter title"},		
				DonationGoal	:{required:"Please enter donation goal amount"},
				DescriptionHTML	:{required:"Please enter Decription Html"},
				thumbImage		:{imageValidate:"Please upload jpg,png file only"},
			}
		});	
		
		
		$("#frm_durationdetails").validate(
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
					
					StartDate				:"required",
					
			},
			messages:
			{
				
				StartDate			: {required:"Please Enter Start Date"},
				
			},
		});	
		$("#frm_leveldetails").validate(
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
					
					level_ID				:"required",
					
			},
			messages:
			{
				
				Level_ID            : {required:"Please enter Campion Level_id"},
			},
		});
		$("#frm_statusdetails").validate(
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
					
					Status				:"required",
					
			},
			messages:
			{

				Status              : {required:"Please enter Campion Status"},
			},
		});
});




	
	$(function()
	{	
		$('#StartDate').datepicker();
		$('#StartDate').datepicker().on('changeDate', function (ev)
		{
			if(ev.viewMode == "days")
			{
				$('#StartDate').datepicker('hide');
				maxDate: jQuery('#StartDate').datepicker("getDate")

			}
		});
		
		$('#EndDate').datepicker();
		$('#EndDate').datepicker().on('changeDate', function (ev)
		{
			if(ev.viewMode == "days")
			{
				$('#EndDate').datepicker('hide');
				maxDate: jQuery('#EndDate').datepicker("getDate")
				

			}
		});
		
		
	});

				

$(document).ready(function()
	{
		$(".GenerateUrl").click(function(){
				var str = $("#Title").val();
				str = str.replace(/[^a-z\s]/gi, '');
				str = $.trim(str);				
				str = str.replace(/[_\s]/g, '-');	
				str = str.toLowerCase();			
				$("#UrlFriendlyName").val(str);
			
		})
		
	})
	
	
	
	$(document).ready(function()
{

	$('#DescriptionHTML').simplyCountable(
	{
    counter:            '#wordCounter',
    countType:          'characters',
    maxCount:           1000,
    strictMax:          true,
    countDirection:     'down'
	});
});

jQuery(function() {jQuery(".viewImage").colorbox({rel:'viewImage'})})
jQuery(function() {jQuery(".listingImage").colorbox({rel:'listingImage'})})








/*<script type="text/javascript">
	tinymce.init({
        selector: "textarea#textarea_1",
        plugins: [
                "advlist autolink autosave link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor"
        ],

        toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | inserttime preview | forecolor backcolor",
        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",

        menubar: false,
        toolbar_items_size: 'small',

        style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ],

        templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
        ]
});
</script>*/