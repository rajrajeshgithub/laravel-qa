// JavaScript Document
$(document).ready(function()
{
	DescriptionCounter();
	$("textarea[name=npoDescription]").keyup(DescriptionCounter).change(DescriptionCounter);
	$("#manage-npo-form").validate(
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
			npoDescription:{required:true},
			logoImg:{accept:"jpg,png,jpeg,gif"},
			//existlogo:{required:true}
		},
		messages:
		{
			npoDescription:{required:"NPO Description"},
			logoImg:{accept: "Only image type jpg/png/jpeg/gif is allowed"},
			//existlogo:{required:"Upload image"}
		}
	});
	
	function DescriptionCounter() {
	var cc = $("textarea[name=npoDescription]").val().length;	
	$("#npodescriptionlength").text(Math.abs(500 - cc)+" "+(cc > 500 ? "over." : "left."));
	if(cc > 500) {
		$("#npodescriptionlength").addClass("error_msg");
	} else {
		$("#npodescriptionlength").removeClass("error_msg");
	}
}
	
	
});