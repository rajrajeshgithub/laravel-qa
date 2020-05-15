<?php
/*inclusion of library files*/
require_once('src/Facebook/FacebookSession.php');
require_once('src/Facebook/FacebookRequest.php');
require_once('src/Facebook/FacebookResponse.php');
require_once('src/Facebook/FacebookSDKException.php');
require_once('src/Facebook/FacebookRequestException.php');
require_once('src/Facebook/FacebookRedirectLoginHelper.php');
require_once('src/Facebook/FacebookAuthorizationException.php');
require_once('src/Facebook/GraphObject.php');
require_once('src/Facebook/GraphUser.php');
require_once('src/Facebook/GraphSessionInfo.php');
require_once('src/Facebook/Entities/AccessToken.php');
require_once('src/Facebook/HttpClients/FacebookCurl.php');
require_once('src/Facebook/HttpClients/FacebookHttpable.php');
require_once('src/Facebook/HttpClients/FacebookCurlHttpClient.php');

/*use namespaces*/
use Facebook/FacebookSession;
use Facebook/FacebookRedirectLoginHelper;
use Facebook/FacebookRequest;
use Facebook/FacebookResponse;
use Facebook/FacebookSDKException;
use Facebook/FacebookRequestException;
use Facebook/FacebookAuthorizationException;
use Facebook/GraphObject;
use Facebook/GraphUser;
use Facebook/GraphSessionInfo;
use Facebook/FacebookHttpable;
use Facebook/FacebookCurlHttpClient;
use Facebook/FacebookCurl;

/*  Process*/
// 1.start a session
session_start();
//check user wants to logout
if(isset($_REQUEST['logout'])
{
    unset($_SESSION['fb_token']);
}

//2. use app id, secret and redirect url
$app_id ='1650437265169437';
$app_secret='d8194943f21d5a2f43213c3c81be4842';
$redirect_url='http://dev.donasity.com/fblogin/';

//3. Initilize application, create helper object and get fb sess
FacebookSession::setDefaultApplication($app_id, $app_secret);
$helper = new FacebookRedirectLoginHelper($redirect_url);
$sess = $helper->getSessionFromRedirect();

$logout='http://dev.donasity.com/fblogin&logout=true';
//4. if fb session exist echo name

if(isset($sess))
{
	
    //stroke the token in the php session
    $_SESSION['fb_token']=$sess->getToken();
    //create request object, execute and capture response
    $request = new FacebookRequest($sess,'GET','/me');
    //from response get graph object
    $response = $request>execute();
    $graph = $response->getGraphObject(GraphUser::classname());
    //use graph object methods to get user details.
    $name=$graph->getNeme();
    $id=$graph->getId();
    $image = 'http://graph.facebook.com/'.$id.'/picture?width=300';
    $email = $graph->getProperty('email');
    echo $name;
    echo '<a href="'.$logout.'"><button>Logout</button></a>';
    
}
else
{
    //else echo login
    echo '<a href="'.$helper->getLoginUrl(array('email')).'">Login with Facebook</a>';
    
}
?>