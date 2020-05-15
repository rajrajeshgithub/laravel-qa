<?php


	global $debug;
	$debug = true;	// set true for debug mode on

	global $settings;
	
	/*//Clear Cache
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	//End Cache*/
	$SITEURL="https://www.donasity.com/";  // default value, then changed per below
	
	if(strtolower($_SERVER['HTTP_HOST'])=="pdc" || strtolower($_SERVER['HTTP_HOST'])=="localhost" || strtolower($_SERVER['HTTP_HOST'])=="192.168.0.98")
	{
		$PHYPATH=$_SERVER['DOCUMENT_ROOT'] ."/donasity/"; // website path
		$SITEURL="http://".$_SERVER['HTTP_HOST']."/donasity/";
		$STRIPE_ACCOUNT_ID='ca_6W9GzHhhrSyNaAzEC587y5U03DvtpaOk';
		$STRIPE_PRIVATE_KEY='sk_test_HYGwP26G7JnlS7P6q1AURhD7';
		$STRIPE_PUBLIC_KEY='pk_test_TjEKFgNdt8wtUS2usIt3pyH1';
		$STRIPE_CONNECT_RETURNURL='http://dev.donasity.com/ut2/Verify-Stripe-Connection/';
		$STRIPE_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_CONNECT_RETURNURL;
		$STRIPE_FUNDARISER_CONNECT_RETURNURL='http://dev.donasity.com/setup_fundraiser/VerifyStripConnection/';
		$STRIPE_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_FUNDARISER_CONNECT_RETURNURL;
		$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL='http://dev.donasity.com/team_fundraiser/VerifyStripConnection/';
		$STRIPE_TEAM_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL;
		$CC_NUMBER="4242424242424242";
		$ABA_ROUT_NUMBER="592755385";
		$DDA_NUMBER="345654345";
		$CHECK_NUMBER="999999";
		$USAEPAY_KEY="_5YK42Xa0rHijj8SWV2mZWiuxFezTpN4";
		$USAEPAY_PIN="1234";
		$USAEPAY_TESTMODE="0";
		$USAEPAY_SANDBOX=true;
		$USAEPAY_VERSION="1.7.0";
		
		
	}	
	if(strtolower($_SERVER['HTTP_HOST'])=="dev.donasity.com")
	{
		$PHYPATH=$_SERVER['DOCUMENT_ROOT'] ."/"; // website path
		$SITEURL="http://dev.donasity.com/";
		$STRIPE_ACCOUNT_ID='ca_6W9GzHhhrSyNaAzEC587y5U03DvtpaOk';
		$STRIPE_PRIVATE_KEY='sk_test_HYGwP26G7JnlS7P6q1AURhD7';
		$STRIPE_PUBLIC_KEY='pk_test_TjEKFgNdt8wtUS2usIt3pyH1';
		$STRIPE_CONNECT_RETURNURL='http://dev.donasity.com/ut2/Verify-Stripe-Connection/';
		$STRIPE_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_CONNECT_RETURNURL;
		$STRIPE_FUNDARISER_CONNECT_RETURNURL='http://dev.donasity.com/setup_fundraiser/VerifyStripConnection/';
		$STRIPE_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_FUNDARISER_CONNECT_RETURNURL;
		$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL='http://dev.donasity.com/team_fundraiser/VerifyStripConnection/';
		$STRIPE_TEAM_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL;
		$CC_NUMBER="4242424242424242";
		$ABA_ROUT_NUMBER="592755385";
		$DDA_NUMBER="345654345";
		$CHECK_NUMBER="999999";
		$USAEPAY_KEY="_5YK42Xa0rHijj8SWV2mZWiuxFezTpN4";
		$USAEPAY_PIN="1234";
		$USAEPAY_TESTMODE="0";
		$USAEPAY_SANDBOX=true;
		$USAEPAY_VERSION="1.7.0";
	}
	
	if(strtolower($_SERVER['HTTP_HOST'])=="donasity.com" || strtolower($_SERVER['HTTP_HOST'])=="www.donasity.com")
	{
		
		$PHYPATH=$_SERVER['DOCUMENT_ROOT'] ."/"; // website path
		$SITEURL="https://www.donasity.com/";
		$STRIPE_ACCOUNT_ID='ca_6W9GzHhhrSyNaAzEC587y5U03DvtpaOk';
		$STRIPE_PRIVATE_KEY='sk_test_HYGwP26G7JnlS7P6q1AURhD7';
		$STRIPE_PUBLIC_KEY='pk_test_TjEKFgNdt8wtUS2usIt3pyH1';
		$STRIPE_CONNECT_RETURNURL='https://www.donasity.com/ut2/Verify-Stripe-Connection/';
		$STRIPE_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_CONNECT_RETURNURL;
		$STRIPE_FUNDARISER_CONNECT_RETURNURL='https://www.donasity.com/setup_fundraiser/VerifyStripConnection/';
		$STRIPE_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_FUNDARISER_CONNECT_RETURNURL;
		$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL='https://www.donasity.com/team_fundraiser/VerifyStripConnection/';
		$STRIPE_TEAM_FUNDARISER_CONNECT_URL="https://connect.stripe.com/oauth/authorize?response_type=code&client_id=".$STRIPE_ACCOUNT_ID."&scope=read_write&redirect_uri=".$STRIPE_TEAM_FUNDARISER_CONNECT_RETURNURL;
		$CC_NUMBER="";
		$ABA_ROUT_NUMBER="";
		$DDA_NUMBER="";
		$CHECK_NUMBER="";
		$USAEPAY_KEY="_5YK42Xa0rHijj8SWV2mZWiuxFezTpN4";
		$USAEPAY_PIN="1234";
		$USAEPAY_TESTMODE="0";
		$USAEPAY_SANDBOX=true;
		$USAEPAY_VERSION="1.7.0";
	}
	
	
	$settings['timezone'] 			= "America/New_York";    // server timezone	
	date_default_timezone_set('America/New_York');
    $settings['url'] = $SITEURL;
	
	define("URL", $settings['url'] ); // base url
	define("ADMINURL",URL."adminpanel/");
	define("PHYPATH",$PHYPATH); // website path
	define('SITEURL',$SITEURL);
	define("RESOURCES",URL.'app/views/');
	define('PHYIMG',PHYPATH.'app/views/');

	define('LOG',1);    /*Boolean used to enables & disable lor writting*/
	
	define('PROCESS_STATUS',1);  /*Defautl status of process. Used in Exception Handler Class*/
	define("DEFAULT_ERRCODE",'000');	/*Defautl Erro Code. Used in Exception Handler Class*/
	
	define('ERRLOGFILE', APP_LOG_DIR."donasity_errors.log");
	define('MAILERRLOGFILE', APP_LOG_DIR."email_errors.log");
	define('CONFIRMATIONLOGFILE', APP_LOG_DIR."donasityconfirmations.log");
	define('PROCESSLOGFILE', APP_LOG_DIR."doansity_process.log");
	define('DEVLOGFILE', APP_LOG_DIR."donasity_devtest.log");
	define('FACEBOOK_SDK_V4_SRC_DIR', PHYPATH.'system/library/facebook/src/Facebook');
	
	define('SITETITLE','donasity');
	define("APP_KEY","donasity");  /*Prefix of Cookies & Session Variables */

	define("RECORDLIMIT","12");  /**Defautl Record limit of List & Grids*/
	
	
	define("NO_IMAGE",RESOURCES."img/nopersonimage.jpg");
	define("NO_IMAGE_FUNDRAISER",RESOURCES."img/fundraiserdefault.jpg");
	define("NO_PERSON_IMAGE", RESOURCES."img/nopersonimage.jpg");
	define("NO_PERSON_IMAGE_REGISTERED",RESOURCES."img/nopersonimageregistered.jpg");
	
	define('TBLPREFIX','dns_');  /*TO be deleted*/
	
	define("PROFILE_LARGE_IMAGE_DIR", PHYPATH."read_write/profileimage/LargeImage/");
	define("PROFILE_LARGE_IMAGE_URL", URL."read_write/profileimage/LargeImage/");
	define("PROFILE_MEDIUM_IMAGE_DIR", PHYPATH."read_write/profileimage/MediumImage/");
	define("PROFILE_MEDIUM_IMAGE_URL", URL."read_write/profileimage/MediumImage/");
	define("PROFILE_THUMB_IMAGE_DIR", PHYPATH."read_write/profileimage/ThumbImage/");
	define("PROFILE_THUMB_IMAGE_URL", URL."read_write/profileimage/ThumbImage/");
	
	define("CAMPAIGN_IMAGE_DIR",PHYPATH."read_write/campaignimages/");
	define("CAMPAIGN_IMAGE_URL",URL."read_write/campaignimages/");
	define("CAMPAIGN_VIDEO_DIR",PHYPATH."read_write/campaignvideo/");
	define("CAMPAIGN_VIDEO_URL",URL."read_write/campaignvideo/");
	define("CAMPAIGN_LARGE_IMAGE_DIR", PHYPATH."read_write/campaignimages/LargeImage/");
	define("CAMPAIGN_LARGE_IMAGE_URL", URL."read_write/campaignimages/LargeImage/");
	define("CAMPAIGN_MEDIUM_IMAGE_DIR", PHYPATH."read_write/campaignimages/MediumImage/");
	define("CAMPAIGN_MEDIUM_IMAGE_URL", URL."read_write/campaignimages/MediumImage/");
	define("CAMPAIGN_THUMB_IMAGE_DIR", PHYPATH."read_write/campaignimages/ThumbImage/");
	define("CAMPAIGN_THUMB_IMAGE_URL", URL."read_write/campaignimages/ThumbImage/");
	
	define("CAMPAIGN_MAIN_IMAGE_DIR", PHYPATH."read_write/campaignimages/main/");
	define("CAMPAIGN_MAIN_IMAGE_URL", URL."read_write/campaignimages/main/");
	
	define("CAMPAIGN_BACKGROUND_IMAGE_DIR", PHYPATH."read_write/campaignimages/Background/");
	define("CAMPAIGN_BACKGROUND_IMAGE_URL", URL."read_write/campaignimages/Background/");
	
	define("UT1PROFILE_LARGE_IMAGE_DIR", PHYPATH."read_write/ut1profile/LargeImage/");
	define("UT1PROFILE_LARGE_IMAGE_URL", URL."read_write/ut1profile/LargeImage/");
	define("UT1PROFILE_MEDIUM_IMAGE_DIR", PHYPATH."read_write/ut1profile/MediumImage/");
	define("UT1PROFILE_MEDIUM_IMAGE_URL", URL."read_write/ut1profile/MediumImage/");
	define("UT1PROFILE_THUMB_IMAGE_DIR", PHYPATH."read_write/ut1profile/ThumbImage/");
	define("UT1PROFILE_THUMB_IMAGE_URL", URL."read_write/ut1profile/ThumbImage/");
	
	define("UT3PROFILE_MEDIUM_IMAGE_DIR", PHYPATH . "read_write/ut3profile/MediumImage/");
	define("UT3PROFILE_MEDIUM_IMAGE_URL", URL . "read_write/ut3profile/MediumImage/");
	
	define("NPO_IMAGE_DIR", PHYPATH."read_write/npoimages/");
	define("NPO_IMAGE_URL", URL."read_write/npoimages/");
	
	define("EXPORT_CSV_PATH", PHYPATH."/read_write/exportcsv/");
	define("EXPORT_CSV_URL", URL."../read_write/exportcsv/");
	
	define('STRIPE_ACCOUNT_ID',$STRIPE_ACCOUNT_ID);
	define('STRIPE_PRIVATE_KEY',$STRIPE_PRIVATE_KEY);
	define('STRIPE_PUBLIC_KEY',$STRIPE_PUBLIC_KEY);
	define('STRIPE_CONNECT_RETURNURL',$STRIPE_CONNECT_RETURNURL);
	define('STRIPE_CONNECT_URL',$STRIPE_CONNECT_URL);
	define('STRIPE_FUNDARISER_CONNECT_URL',$STRIPE_FUNDARISER_CONNECT_URL);
	
	define('STRIPE_TEAM_FUNDARISER_CONNECT_URL',$STRIPE_TEAM_FUNDARISER_CONNECT_URL);
	
	define('USAEPAY_KEY',$USAEPAY_KEY);
	define('USAEPAY_PIN',$USAEPAY_PIN);
	define('USAEPAY_TESTMODE',$USAEPAY_TESTMODE); //0/1
	define('USAEPAY_SANDBOX',$USAEPAY_SANDBOX);  //true/false
	define("USAEPAY_VERSION", $USAEPAY_VERSION);
	
	
	define("TRANSACTION_FEE","4.95");

	define("BOTTOM_URL_NAVIGATION","bottominfoEN");
	define("BOTTOM_META","bottompage");
	
	define("NO_IMAGE_LOGIN_IMG",RESOURCES."images/afterloginImg.png");
	
	define("CC_NUMBER",$CC_NUMBER);
	define("ABA_ROUT_NUMBER",$ABA_ROUT_NUMBER);
	define("DDA_NUMBER",$DDA_NUMBER);
	define("CHECK_NUMBER",$CHECK_NUMBER);	
	
	define("FB_APP_ID",'1650437265169437');
	
	/*==========documents============*/
	define("DOCUMENT_PATH", PHYPATH . "read_write/documents/");
	define("DOCUMENT_URL", URL."read_write/documents/");
?>