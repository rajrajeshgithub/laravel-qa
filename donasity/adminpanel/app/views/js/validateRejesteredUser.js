// JavaScript Document
$(document).ready(function() {	
	getStateList(jQuery("#countryAbbr").val(), jQuery("#StateAbbr").val());
	
	$.validator.addMethod("imageValidate", function() {	
	    var imgvalue = $('#R_profileImage').val() == "" ? $('#R_oldProfileImage').val() : $('#R_profileImage').val();
		if(imgvalue == '')
			return false;
		else {
			var extension = imgvalue.toLowerCase().split('.').pop();
			if(extension == 'png' || extension == 'jpeg' || extension == 'jpg' ) return true;
			else
				return false;
		}
	})
	
	$("#formSection").validate( {
		success : function(element) {
			$("#" + element[0].htmlFor).next("b.tooltip").remove();
		},
		errorPlacement : function(error, element) {
			element.next("b.tooltip").remove();
			element.after("<b class='tooltip tooltip-top-right'>" + error.text() + "</b>");
		},
		rules : {
			R_firstName	: { required : true },
			R_lastName : {required : true },
			//R_companyName : {required : true },
			R_userName : {
				remote : {
					url : SITEURL + "reguser/CheckUserName",
					data : {
						keyId : function() {
						   return $("#RegUserId").val();
						},
					},
					dataType : 'json',
					async : true,
					cache : false, 
			    }
			},
			R_addressline1 : { required : true },
			R_country : { required : true },
			R_state : { required : true },
			R_city : { required : true },
			R_zip : { required : true },
			R_userPhone	: { required : true },
			R_password : { required : true },
			//filePath : { imageValidate : true },
			filePath : { accept:"jpg,png,jpeg,gif" },
			R_emailAddress : {
				required : true,
				email : true,
				remote : {
					url : SITEURL + "reguser/CheckEmail",
					data : {
						keyId : function() {
						   return $("#RegUserId").val();
						},
					},
					dataType : 'json',
					async : true,
					cache : false, 
				}
			},
			R_facebookId : {
				remote : {
					url : SITEURL + "reguser/CheckFacebookID",
					data: {
						keyId : function() {
						   return $("#RegUserId").val();
						},
					},
					dataType : 'json',
					async : true,
					cache : false, 
				}
			},
		},
		messages : {
			R_firstName : { required : "Please enter first name" },
			R_lastName : { required : "Please enter last name" },
			//R_companyName  : { required : "Please enter company name" },
			R_userName : { remote : "User name already in use" },
			R_addressline1 : { required : "Please enter Address" },
			R_country : { required : "Please select country" },
			R_state	: { required : "Please select state" },
			R_city : { required : "Please enter city" },
			R_zip :	{ required : "Please enter zip code" },
			R_userPhone	: { required : "Please enter cell number" },
			R_password : { required : "Please enter password" },
			R_emailAddress : { required : "Please enter email id", email : "Please enter valid email", remote : "Email already in use" },
			R_facebookId : { remote : "Facebook id already in use" },
			filePath : { accept : "Only jpg, png, jpeg or gif image type are allowed." }
		}
	});			
});

function getStateList(e) {
    "" != jQuery.trim(e) && jQuery.ajax({
        type: "POST",
        url: SITEURL + "reguser/getStateList/" + e + "/" + jQuery("#StateAbbr").val(),
        cache: !1,
        beforeSend: function() {},
        success: function(e) {
            jQuery("#R_state").attr("disabled", !1).html(e)
        },
        error: function(e) {
            alert(e)
        }
    })
}

function confirmDeleteMainImg(Id) {
	if(confirm("Are you sure you want to delete this profile image ?"))
		window . location = SITEURL + 'reguser/index/deleteimage/' + Id;
	else
		return false;
}