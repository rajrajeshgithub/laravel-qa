// JavaScript Document
$(document).ready(function()
{
	var input = document.getElementById('location');
	  google.maps.event.addDomListener(input, 'keydown', function(e) { 
		if (e.keyCode == 13) { 
			e.preventDefault(); 
		}
	  }); 
	var autocomplete = new google.maps.places.Autocomplete(self.$('#location')[0], {types: ['geocode']});
	google.maps.event.addListener(autocomplete, 'place_changed', function() {
    var place = autocomplete.getPlace();
	console.log(place);
	var formatted_address={};
	$.each(place.address_components, function (index,object)
	{
		var name = object.types[0];
		if(name=='administrative_area_level_1')
		{
			formatted_address[name] = object.short_name;
		}
		else
		{
			formatted_address[name] = object.long_name;
		}
	});		
    $('input[name="Camp_Location_Latitude"]').val(place.geometry.location.lat());
    $('input[name="Camp_Location_Logitude"]').val(place.geometry.location.lng());
	$('input[name="Camp_Location_Zip"]').val(formatted_address['postal_code']); 
   	$('input[name="Camp_Location_Country"]').val(formatted_address['country']); 
    $('input[name="Camp_Location_State"]').val(formatted_address['administrative_area_level_1']); 
    $('input[name="Camp_Location_City"]').val(formatted_address['locality']); 
	$("#location").valid();
	
});
	//-- date picker calling js
	$('#specifiedDate').datepicker();
	$('#startImmediate').click(function()
	{
		if($(this).is(':checked'))
			$('.dateSpecified-box').hide();
	});
	$('#dateSpecified').click(function()
	{
		if($(this).is(':checked'))
			$('.dateSpecified-box').show();
	});
	$.validator.addMethod('ProperLocation',function(value,element)
	{
		var city=$('#Camp_Location_City').val();
		var state=$('#Camp_Location_State').val();
		var country=$('#Camp_Location_Country').val();
		if(city!="" && state!="" && country!="" )
		{
			return true
		}else
		{
			return false;
		}
		
	});
	$("#form-section").validate(
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
 
			title:{required:true},
			donation:{required:true, digits:true},
			uploadImg:{required:true,accept:"jpg,png,jpeg,gif"},
			location:{required:true,ProperLocation:true}
		},
		messages:
		{
			title:{required:"Title"},
			donation:{required:"Donation amount", digits:"Only digits"},
			uploadImg:{required:"Select Image to Upload",accept: "Only image type jpg/png/jpeg/gif is allowed"},
			location:{required:"Enter location",ProperLocation:"Enter Proper Location City, State, Country"}
		},
		submitHandler: function(form){
           var HoldResult=validateCategory();
		  if(HoldResult==true)
		  {
			 form.submit();
			 
		  }
        }
	});

});
function validateCategory()
	{
		var checklength=$('input[name="category"]:checked').length;
		var ret=false;
		if(checklength> 0)
		{
			$('input[name="category"]').parents('.step-content').find('span.error-text').remove();
			ret=true;
		}
		else
		{
			$('input[name="category"]').parents('.step-content').find('span.error-text').remove();
			$('input[name="category"]').parents('.step-content').find('.choose-category').append("<span class='error-text'>Select catagory</span>");				
			$('input[name="category"]').focus();
			
		}
		return ret;
	}
