  window.fbAsyncInit = function() {
    FB.init({
      appId      : '127582173937158',
	   xfbml      : true,
      version    : 'v2.4'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));


function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
  
function myFacebookLogin() {
  FB.login(function(response){
	 statusChangeCallback(response); 
	  }, {scope: 'public_profile,email'});
   //
}
function statusChangeCallback(response) {
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
	
    if (response.status === 'connected') {
      // Logged into your app and Facebook.
      testAPI();
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into this app.';
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      document.getElementById('status').innerHTML = 'Please log ' +
        'into Facebook.';
    }
  }
function testAPI() {    
    FB.api('/me',{fields: 'first_name,last_name,email,gender'}, function(response) 
	{
		$('#fb-login-form').attr('action', FORM_ACTION);
		$("#fb-login-form #email").val(response.email);
		$("#fb-login-form #fbId").val(response.id);
		$("#fb-login-form #fname").val(response.first_name);
		$("#fb-login-form #lname").val(response.last_name);
		$("#fb-login-form #gender").val(response.gender);				
		$("#fb-login-form").submit();
      	/*console.log('Successful login for: ' + response.name);
      	document.getElementById('status').innerHTML =
        'Thanks for logging in, ' + response.name + '!';*/	
    });
  }
