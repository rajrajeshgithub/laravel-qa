// JavaScript Document
$(document).ready(function()
{
	var baseUrl = (window.location).href; // You can also use document.URL
	var UrlLastId = baseUrl.substring(baseUrl.lastIndexOf('#') + 1);
	
	if($('#CV_ID').val()!='')
	{
		$('.viewmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
		$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
		$('.listingmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
		$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
	}
	else
	{
		if(UrlLastId==='Compaignvideo')
		{
			$('.viewmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
			$('.listingmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
			$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
			
		}
		else
		{
			$('.viewmoreBtn i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
			$('.viewmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:none' );
			$('.listingmoreBtn i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
			$('.listingmoreBtn i').closest('#content-box').find('.content-section').attr( 'style','display:block' );
		}
	}
	/*$.validator.addMethod("imageValidate", function()
		{	
			var imgvalue= $('#CV_File').val();
			if(imgvalue=="")
			{	
				return true;
			}
			else
			{
				var extension = imgvalue.toLowerCase().split('.').pop();
				if(extension == 'wmv' || extension == 'avi' || extension == 'mpg' || extension == 'mp4' )
				{
					return true;
				}else{
					return false;	
				}
			}
		})*/
	
	/*$("#formSection").validate( {
		success: function(element) {
			$("#" + element[0].htmlFor).next("b.tooltip").remove();
		},
		errorPlacement: function(error, element) {
			element.next("b.tooltip").remove();
			element.after("<b class='tooltip tooltip-top-right'>" + error.text() + "</b>");
		},
		rules: {
			CV_Title		: {required:true},
			CV_sorting		: {digits:true},
			CV_embedCode	: {require:true},
			file     		: {imageValidate:true},
		},
		//groups: {
//			group: "CV_embedCode file"
//		},
//		submitHandler: function(form) { // for demo
//			return true;
//		},
		messages: {
			CV_Title		: {required:"Please enter video title"},
			CV_sorting		: {digits:"Please enter digits only"},
			CV_embedCode	: {required:"Please enter embed code"},
			CV_File			: {imageValidate:"Please upload jpg,png file only"},
		}
	});*/
	
	$.validator.addMethod("embedVideo", function(value, element, params) {
    	var req = true;
		if(value == '' && params.val() == '')
			req = false;
		return req;
	}, '');
	
	$.validator.addMethod('filesize', function(value, element, param) {
		// param = size (en bytes) 
		// element = element to validate (<input>)
		// value = value of the element (file name)
		var res = false;
		if(this.optional(element) || (element.files[0].size <= param))
			res = true;
			
		var Error = 'Video file size should not be more than 5mb.';
		var ErrorElement = $('#file');
		ErrorElement.next("b.tooltip").remove();
		if(!res) {
			ErrorElement.addClass('error');
			ErrorElement.after("<b class='tooltip tooltip-top-right'>" + Error + "</b>");
		} else {
			ErrorElement.removeClass('error');
			ErrorElement.next("b.tooltip").remove();
		}
		return res; 
	});
	
	$.validator.addMethod('fileType', function(value, element, param) {
		var res = true;
		if(value != '') {
			res = false;
			var ext = value.toLowerCase().split('.').pop();
			if(ext == 'wmv' || ext == 'avi' || ext == 'mpg' || ext == 'mp4' || ext == 'flv' || ext == 'f4v' || ext == 'ogv' ) res = true;
			
			var Error = 'Video file type should be wmv, avi, flv, f4v, ogv, mpg or mp4.';
			var ErrorElement = $('#file');
			ErrorElement.next("b.tooltip").remove();
			if(!res) {
				ErrorElement.addClass('error');
				ErrorElement.after("<b class='tooltip tooltip-top-right'>" + Error + "</b>");
			} else {
				ErrorElement.removeClass('error');
				ErrorElement.next("b.tooltip").remove();
			}
		}
		return res; 
	});
	
	$("#formSection").validate( {
		success: function(element) {
			$("#" + element[0].htmlFor).next("b.tooltip").remove();
		},
		errorPlacement: function(error, element) {
			element.next("b.tooltip").remove();
			element.after("<b class='tooltip tooltip-top-right'>" + error.text() + "</b>");
		},
		rules: {
			CV_Title		: { required : true },
			CV_sorting		: { digits : true },
			CV_embedCode	: { embedVideo : $('#file') },
			CV_File			: { filesize : 5000000, fileType : true },
			//CV_embedCode	: { require : true },
			//file     		: { imageValidate : true },
		},
		messages: {
			CV_Title		: { required : "Please provide title for video." },
			CV_sorting		: { digits : "Please provide digits only." },
			CV_embedCode	: { embedVideo : "Please provide atlest one from Embed Code or Upload Video." },
			CV_File			: { filesize : '', fileType : '' },
			//CV_File			: { imageValidate : "Please upload jpg,png file only." },
		}
	});			
});

function GotoPage(pageNo)
{
	$('#pageNumber').val(pageNo);
	$('#formSection').attr('action', '');
	document.formSection.submit();
	return false;
}

function Delete_Video(CV_ID,CV_CID)
{
	if(confirm("Are you sure you want to delete this campaign video ?")) 
	{
		window.location = SITEURL+'campaignvideo/index/delete-video/'+CV_CID+'/'+CV_ID;
	}
	else
	{
		return false;
	}
}


        function checkValues()
        {
            var searchtext1 = document.getElementById("file").value;
            if(searchtext1=='')
            {
				var searchtext2 = document.getElementById("CV_embedCode").value;
                if(searchtext2=='')
					{
						alert('Please enter Embed code or Browse video');
						return false;
					}
                return true;
            }
            
        }

