<?php
session_start();
echo 'test';exit;
$objfb=LoadLib('facebook');
set_time_limit(0);
echo 'test';exit;
FacebookSession::setDefaultApplication('1633715326865600', '143c0c88698949714ca538389903b442'); 
session_start();
$helper = new FacebookRedirectLoginHelper('http://dev.donasity.com/redirect.php');
echo $helper;exit;
$loginUrl = $helper->getLoginUrl();
echo $loginUrl;exit;
//header('location:'.$loginUrl); 


$helper = new FacebookRedirectLoginHelper();
try {
  $session = $helper->getSessionFromRedirect();
} catch(FacebookRequestException $ex) {
   // When Facebook returns an error
} catch(\Exception $ex) {
  // When validation fails or other local issues
}
if ($session) {
  // Logged in
}

$session = new FacebookSession('access token here');

$request = new FacebookRequest($session, 'GET', '/me');
$response = $request->execute();
$graphObject = $response->getGraphObject();



?>