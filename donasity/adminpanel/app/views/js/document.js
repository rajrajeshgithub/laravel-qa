// JavaScript Document
$(document).ready(function() {
		
	$('.documents i').click(function() {
		 $(this).toggleClass("fa-plus fa-minus");
		 $(this).closest('.row').find('.documents').slideToggle();
	});		
});

function GotoPage(pageNo) {
	$('#pageNumber').val(pageNo);
	$('#formSection').attr('action', '');
	document.formSection.submit();
	return false;
}

function DeleteDocument(doc_ID, user) {
	if(confirm("Are you sure you want to delete this document?")) {
		window.location = SITEURL + 'documents/index/delete/' + doc_ID + '/' + user;
	} else
		return false;
}

$(document).ready(function() {
	
	$.validator.addMethod("fileValidate", function() {	
		var imgvalue = $('#DocName').val();
		if(imgvalue == '')
			return false;
		else {
			var extension = imgvalue.toLowerCase().split('.').pop();
			if(extension == 'pdf' || extension == 'docx')
				return true;
			else
				return false;
		}
	});
	
	$("#form_section").validate( {
		success: function(element) {
			$('#' + element[0].htmlFor).next("b.tooltip").remove();
		},
		errorPlacement: function(error, element) {
			element.next("b.tooltip").remove();
			element.after("<b class='tooltip tooltip-top-right'>" + error.text() + "</b>");
		},
		rules: {
			DocTitle : { required : true },
			DocSorting : { required : true },
			filePath : { required : true, accept:"pdf,doc,docx,png,jpg,ppt,pptx,txt,rtf,gif,xls,xlsx" },
		},
		messages: {
			DocTitle : { required : 'Please enter document title.' },
			DocSorting : { required : 'Please enter sorting number.' },
			filePath : { required : 'Please upload a document file.', accept : 'Please upload document file only (pdf, doc, docx, png, jpg, ppt, pptx, txt, rtf, gif, xls, xlsx).' },
		}
	});	
});