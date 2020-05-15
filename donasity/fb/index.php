<?php
	session_start();
	require_once 'handle.php';
/*
	here we check that user is already logged in with facebook or not; 
	if yes than show user's info
*/
  	if (isset($_SESSION['facebook_token'])) {
    	//print_r($graph);
		$graph->getProperty('email');
    	echo '<br><a href="index.php?logout_From_Facebook"> Logout from Facebook</a>';
  	}
  	else{
    	echo '<a href="'.$loginUrl.'">Login with Facebook</a>';
  	}
?>