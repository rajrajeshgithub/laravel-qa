<?php

/*
	Configuring Path To Facebook SDK
*/
	require_once 'facebook/autoload.php';
	use Facebook\FacebookSession;
	use Facebook\FacebookRedirectLoginHelper;
	use Facebook\FacebookRequest;
	use Facebook\FacebookResponse;
	use Facebook\FacebookSDKException;
	use Facebook\FacebookRequestException;
	use Facebook\FacebookAuthorizationException;
	use Facebook\GraphObject;
	use Facebook\GraphUser;
	use Facebook\Entities\AccessToken;
	use Facebook\HttpClients\FacebookCurlHttpClient;
	use Facebook\HttpClients\FacebookHttpable;

/*
	Intializing App: You have create an app at www.developer.facebook.com fromthere get an 
	APP_ID and APP_SECRET
	Make sure your app is active; (Green Round symbol appears bext to app name.)
*/
	FacebookSession::setDefaultApplication( '1650437265169437','d8194943f21d5a2f43213c3c81be4842');
	$helper = new FacebookRedirectLoginHelper('http://dev.donasity.com/fb/index.php'); //main page
	try{
	   	$session = $helper->getSessionFromRedirect();
	}
	catch(Exception $e){
	  	echo $e->getMessage();
	}

/*
	If user clicked on Logout that current unset facebook session.
*/
	if (isset($_REQUEST['logout_From_Facebook'])){
	    unset($_SESSION['facebook_token']);
	}
/*
	If user is already login by facebook (at the time of facebook login we keep a session 
	variable here 'facebook_token') we validate current session that it is actually session of
	given app.
*/
	if(isset($_SESSION['facebook_token'])){
	  $session = new FacebookSession($_SESSION['facebook_token']);
	  try{
	       $session->Validate('1650437265169437','d8194943f21d5a2f43213c3c81be4842');
	  }catch(FacebookAuthorizationException $e){
	        $session ="";
	  }
	}
/*
	if facebook session has been set successfully, now we can get a new facebook token and set it into 
	a session variable so that we can keep track that user is logged in with facebook;
	if facebook is not set that we request for a Login URL with permissions (what user info app can 
	access );
*/
	if(isset($session) && $session) {  
	  $_SESSION['facebook_token']=$session->getToken();
	  $request = new FacebookRequest($session, 'GET', '/me');
	  $response = $request->execute();
	  $graph = $response->getGraphObject(GraphUser::className());
	  $logoutURL = $helper->getLogoutUrl( $session, 'http://dev.donasity.com/fb/index.php?logout_From_Facebook' );
	}
	else{
	  $permissions = array(
	          'email',
	          'public_profile',
	      /*    'user_location',
	          'user_birthday',          
	          'user_friends',
	          'user_about_me',
	          'user_actions.books',
	          'user_actions.fitness',
	          'user_actions.music',
	          'user_actions.news',
	          'user_actions.video',
	          'user_activities',
	          'user_education_history',
	          'user_events',
	          'user_games_activity',
	          'user_groups',
	          'user_hometown',
	          'user_interests',
	          'user_likes',
	          'user_location',
	          'user_photos',
	          'user_relationships',
	          'user_relationship_details',
	          'user_religion_politics',
	          'user_status',
	          'user_tagged_places',
	          'user_videos',
	          'user_website',
	          'user_work_history'*/
	        );
	  $loginUrl = $helper->getLoginUrl($permissions); 
	}
?>
