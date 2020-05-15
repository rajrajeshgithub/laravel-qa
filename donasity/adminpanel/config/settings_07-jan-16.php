<?php

	global $debug;
	$debug = true;	// set true for debug mode on

	global $settings;
	
	if(strtolower($_SERVER['HTTP_HOST'])=="pdc" || strtolower($_SERVER['HTTP_HOST'])=="localhost" || strtolower($_SERVER['HTTP_HOST'])=="192.168.0.98")
	{
		define("PHYPATH",$_SERVER['DOCUMENT_ROOT'] ."/donasity/"); // website path
		define("ADMIN_PHYPATH",$_SERVER['DOCUMENT_ROOT'] ."/donasity/adminpanel/");

	}	
	else
	{
		define("PHYPATH",$_SERVER['DOCUMENT_ROOT'] ."/"); // website path
		define("ADMIN_PHYPATH",$_SERVER['DOCUMENT_ROOT'] ."/adminpanel/");

	}
	
	
	
	
    $phpself=explode("adminpanel",$_SERVER["PHP_SELF"]);
	$Frontpath=$phpself[0];
	$settings['timezone']= "America/New_York";    // server timezone
	date_default_timezone_set('America/New_York');
    $settings['url'] = str_replace(basename($_SERVER['PHP_SELF']),'','http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/" );
	define("URL", $settings['url'] ); // base url	
	//define("APP_LOG_DIR",ADMIN_PHYPATH."");
	
	define("FRONTURL",URL."../");
	define("REFRENCES",URL."app/views/");
	define("RESOURCES",URL .'app/views/');
	define('SITEURL',"http://".$_SERVER['HTTP_HOST']."/donasity");
	define('FRONTIMGURL','http://'.$_SERVER['HTTP_HOST'].$Frontpath);
	define('LOG',1);
	define('TBLPREFIX','dns_');
	define('PHYIMG',PHYPATH.'app/views/');
	define('PROCESS_STATUS',1);
	define("DEFAULT_ERRCODE",'000');	
	define('ERRLOGFILE', APP_LOG_DIR."donasity_errors.log");
	define('NPOSIMPERRLOGFILE', APP_LOG_DIR."npos_import_errors.log");
	define('NPOSIMPERRJSONFILE', APP_LOG_DIR."import_errors");
	define('MAILERRLOGFILE', APP_LOG_DIR."email_errors.log");
	define('CONFIRMATIONLOGFILE', APP_LOG_DIR."donasity_confirmations.log");
	define('PROCESSLOGFILE', APP_LOG_DIR."donasity_process.log");
	define("WEBMASTER_ID",1);
	
	define("JSON_DIR",ADMIN_PHYPATH.'json/');
	
	define('SITETITLE','Donasity Admin Panel');
	define("APP_KEY","donasityadmin");
	define("KEY","donasity");

	define("NOIMAGE",RESOURCES.'img/noimage.jpg');
	define("NO_PERSON_IMAGE",RESOURCES.'img/nopersonimage.jpg');
	define("AD_DATETIME",'%b %d, %Y %h:%i %p');
	
	//path for storing email template file
	define("EMAIL_TEMPLATE_DIR",PHYPATH."read_write/emailtempates/");
	define("EMAIL_TEMPLATE_URL",URL."../read_write/emailtempates/");
	
	define("PROFILE_LARGE_IMAGE_DIR", PHYPATH."read_write/profileimage/LargeImage/");
	define("PROFILE_LARGE_IMAGE_URL", URL."../read_write/profileimage/LargeImage/");
	define("PROFILE_MEDIUM_IMAGE_DIR", PHYPATH."read_write/profileimage/MediumImage/");
	define("PROFILE_MEDIUM_IMAGE_URL", URL."../read_write/profileimage/MediumImage/");
	define("PROFILE_THUMB_IMAGE_DIR", PHYPATH."read_write/profileimage/ThumbImage/");
	define("PROFILE_THUMB_IMAGE_URL", URL."../read_write/profileimage/ThumbImage/");
	
	
	define("CAMPAIGN_LARGE_IMAGE_DIR", PHYPATH."read_write/campaignimages/LargeImage/");
	define("CAMPAIGN_LARGE_IMAGE_URL", URL."../read_write/campaignimages/LargeImage/");
	define("CAMPAIGN_MEDIUM_IMAGE_DIR", PHYPATH."read_write/campaignimages/MediumImage/");
	define("CAMPAIGN_MEDIUM_IMAGE_URL", URL."../read_write/campaignimages/MediumImage/");
	define("CAMPAIGN_THUMB_IMAGE_DIR", PHYPATH."read_write/campaignimages/ThumbImage/");
	define("CAMPAIGN_THUMB_IMAGE_URL", URL."../read_write/campaignimages/ThumbImage/");
	
	define("CAMPAIGN_MAIN_IMAGE_DIR", PHYPATH."read_write/campaignimages/main/");
	define("CAMPAIGN_MAIN_IMAGE_URL", URL."../read_write/campaignimages/main/");
	
	define("CAMPAIGN_VIDEO_DIR",PHYPATH."read_write/campaignvideo/");
	define("CAMPAIGN_VIDEO_URL",URL."../read_write/campaignvideo/");
	
	define("UT1PROFILE_LARGE_IMAGE_DIR", PHYPATH."read_write/ut1profile/LargeImage/");
	define("UT1PROFILE_LARGE_IMAGE_URL", URL."../read_write/ut1profile/LargeImage/");
	define("UT1PROFILE_MEDIUM_IMAGE_DIR", PHYPATH."read_write/ut1profile/MediumImage/");
	define("UT1PROFILE_MEDIUM_IMAGE_URL", URL."../read_write/ut1profile/MediumImage/");
	define("UT1PROFILE_THUMB_IMAGE_DIR", PHYPATH."read_write/ut1profile/ThumbImage/");
	define("UT1PROFILE_THUMB_IMAGE_URL", URL."../read_write/ut1profile/ThumbImage/");
	
	/*==========================*/
	
	define('MAIL_TYPE',"smtp");	
	define('MAIL_HOST', "mail.qualdevtools.com");
	define('MAIL_USERNAME', "test@qualdevtools.com");
	define('MAIL_PASSWORD', "P@ssw4rd");
	define('MAIL_FROM','qualdev.test@gmail.com');
	/*===============================*/
	
	/*================================*/
	define("IMPORT_CSV_PATH", PHYPATH."read_write/importcsv/");
	define("IMPORT_CSV_URL", URL."../read_write/importcsv/");
	
	define("EXPORT_CSV_PATH", PHYPATH."/read_write/exportcsv/");
	define("EXPORT_CSV_URL", URL."../read_write/exportcsv/");
	
	define("SAMPLE_CSV_PATH", PHYPATH."read_write/samplecsv/");
	/*================================*/
	
	define("NPO_IMAGE_DIR", PHYPATH."read_write/npoimages/");
	define("NPO_IMAGE_URL", FRONTURL."read_write/npoimages/");
	$StatusArray = array('0'=>'Setup Start','1'=>'Step 1','2'=>'Step 2','3'=>'Step 3','4'=>'Step 4','5'=>'Step 5','6'=>'Step Completed','11'=>'Verified / Pending','12'=>'Verified / Denied','13'=>'Failed / Hold','15'=>'Verified / Running','16'=>'Stoped By System','31'=>'Team Joined','21'=>'Stoped By Owner','36'=>'Stoped By Captain');
	$settings['StatusArray'] = $StatusArray;
	
	/*==========documents============*/
	define("DOCUMENT_PATH", PHYPATH . "read_write/documents/");
	define("DOCUMENT_URL", URL . "../read_write/documents/");
	

?>