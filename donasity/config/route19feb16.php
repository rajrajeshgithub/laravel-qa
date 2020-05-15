<?php

    #--------------------------------
    # Set the route configuration and the default
    # controller_dir, controller adn action
    #--------------------------------
  
    $route['controller_dir_in_route'] = false;
    $route['default_controller_dir'] = 'home';
    $route['default_controller'] = 'home';
    $route['default_action'] = 'index';

	//============cms static page ==================
	$route['(:any).html']  = 'cms/index/$1';
	//============end===============================
    #--------------------------------
    # In Rain Framework is possible to
    # configure few default route
    #--------------------------------

    /**
     * Route configuration examples:
     *
     * Get any value (eg. index.php/user/rain/  =>  index.php/user/profile/rain/ )
     * $route['user/(:any)/']   = 'user/profile/$1';
     *
     * Get any value (eg. index.php/user/10/  =>  index.php/user/edit/10/ )
     * $route['user/(:num)/']   = 'user/edit/$1';
     *
     * Convert a static URI (eg. index.php/blog/php/  =>  index.php/blog/category/php/ )
     * $route['blog/php/']  = 'blog/category/php/';
     *
     */
	
	$route['login/(:any)']			 = 'login/index/$1';
	$route['ut2/getstateajax']  	 = 'ut2/getstateajax';
	$route['ut2/IsDuplicateEmail'] 	 = 'ut2/IsDuplicateEmail';
	$route['ut2/ResetPassword/(:any)/(:any)/(:any)'] = 'ut2/ResetPassword/$1/$2/$3';
	$route['ut2/(:any)'] 			 = 'ut2/index/$1';
	
	/*$route['home']  = 'home';
	$route['home/(:anyPlus)']  = 'home/index/$1';*/
	
	$route['non-profits-search']  = 'npolist/index/nposearch';
	$route['fundraisers-search']  = 'fundraiserlist/index/fundeaisersearch'; //'fundraiserdetails/index/fundeaisersearch';
	
	//$route['fundraisers-details/(:any)/(:any)']  = 'fundraiserdetail/FundraiserDetail/$1/$2/1'; 
	//$route['preview-fundraisers-details/(:any)/(:any)'] = 'fundraiserdetail/FundraiserDetail/$1/$2/2'; 
	
	$route['fundraiser/(:any)/(:any)']  = 'fundraiserdetail/FundraiserDetail/$1/$2/1'; 
	$route['fundraiser/(:any)']  = 'fundraiserdetail/FundraiserDetail/$1/A/1'; 
	$route['fundraiser-preview/(:any)/(:any)']  = 'fundraiserdetail/FundraiserDetail/$1/$2/2'; 
	$route['fundraisers-comment'] = 'fundraiserdetail/FundraiserComment';
	$route['print-Fundraiserflyer/(:any)'] = 'fundraiserdetail/PrintFundraiserDetails/$1';

	/*================= user type 1 ================= */
	$route['ut1/ResetPassword/(:any)/(:any)/(:any)']  =   'ut1/ResetPassword/$1/$2/$3';
	$route['ut1/getstateajax']  =   'ut1/getstateajax';
	$route['ut1/IsDuplicateEmail'] = 'ut1/IsDuplicateEmail';	
	$route['ut1/(:any)']  =   'ut1/index/$1';
	$route['ut1/(:any)/(:any)']  =   'ut1/index/$1/$2';
	
	/*================= user type 1 ================= */
	
	
	
	$route['ut1myaccount/updatestyletemplate']  =   'ut1myaccount/updatestyletemplate';
	
	$route['ut1myaccount/getstateajax']  =   'ut1myaccount/getstateajax';
	
	$route['ut1myaccount/getFundraiserCommentBlockByAjax'] = 'ut1myaccount/getFundraiserCommentBlockByAjax';
	
	$route['ut1myaccount/getFundraiserCommentByAjax'] = 'ut1myaccount/getFundraiserCommentByAjax';
	
	$route['ut1myaccount/UpdateFundraiserBasicDetail']  = 'ut1myaccount/UpdateFundraiserBasicDetail';
	$route['ut1myaccount/UpdateTeamFundraiserBasicDetail']  = 'ut1myaccount/UpdateTeamFundraiserBasicDetail';
	
	$route['ut1myaccount/updateFundraiserComment']  = 'ut1myaccount/updateFundraiserComment';
	$route['ut1myaccount/FundraiserEdit/(:any)']  = 'ut1myaccount/FundraiserEdit/$1';
	$route['ut1myaccount/updateStatus/(:any)/(:any)']  = 'ut1myaccount/updateStatus/$1/$2';
	$route['ut1myaccount/stopFundraiserMultiple']  = 'ut1myaccount/stopFundraiserMultiple';
	$route['ut1myaccount/stopFundraiserScheduler']  = 'ut1myaccount/stopFundraiserScheduler';
	
	$route['ut1myaccount/FundraiserBasicDetail/(:any)']  = 'ut1myaccount/FundraiserBasicDetail/$1';
	$route['ut1myaccount/TeamFundraiserBasicDetail/(:any)'] = 'ut1myaccount/TeamFundraiserBasicDetail/$1';
	$route['ut1myaccount/FundraiserPhotoVideo/(:any)']  = 'ut1myaccount/FundraiserPhotoVideo/$1';
	$route['ut1myaccount/FundraiserComment/(:any)']  = 'ut1myaccount/FundraiserComment/$1';
	$route['ut1myaccount/updateFundraiserComment']  = 'ut1myaccount/updateFundraiserComment';
	$route['ut1myaccount/deleteFundraiserComment']  = 'ut1myaccount/deleteFundraiserComment';
	$route['ut1myaccount/approveFundraiserComment']  = 'ut1myaccount/approveFundraiserComment';
	$route['ut1myaccount/UploadImage']  = 'ut1myaccount/UploadImage';
	$route['ut1myaccount/UploadVideo']  = 'ut1myaccount/UploadVideo';	
	$route['ut1myaccount/DeleteImage/(:any)']  = 'ut1myaccount/DeleteImage/$1';
	$route['ut1myaccount/DeleteVideo/(:any)']  = 'ut1myaccount/DeleteVideo/$1';
	
	$route['ut1myaccount/viewall']  = 'ut1myaccount/viewall';
	$route['ut1myaccount/viewallfundraiser/(:any)']  = 'ut1myaccount/viewallfundraiser/$1';
	$route['ut1myaccount/printdonationlist']  = 'ut1myaccount/printdonationlist'; 
	$route['ut1myaccount/exportdonationlist']  = 'ut1myaccount/exportdonationlist'; 
	$route['ut1myaccount/RecurringProfiles']= 'ut1myaccount/RecurringProfiles';
	$route['ut1myaccount/exportRecurringTransactionList']= 'ut1myaccount/exportRecurringTransactionList';
	$route['ut1myaccount/printRecurringTransactionList']= 'ut1myaccount/printRecurringTransactionList';
	
	$route['ut1myaccount/UpdateCreditCard'] = 'ut1myaccount/UpdateCreditCard';
	$route['ut1myaccount/CancelRecurringTrans/(:any)/(:any)'] = 'ut1myaccount/CancelRecurringTrans/$1/$2';
	$route['ut1myaccount/CancelRecurring'] = 'ut1myaccount/CancelRecurring';
	$route['ut1myaccount/ChangeCreditCard/(:any)/(:any)'] = 'ut1myaccount/ChangeCreditCard/$1/$2';
	
	$route['ut1myaccount/printfundraiserdonationlist']  = 'ut1myaccount/printfundraiserdonationlist'; 
	$route['ut1myaccount/exportfundraiserlist']  = 'ut1myaccount/exportfundraiserlist'; 
	$route['ut1myaccount/manageTeamFundraisers/(:any)'] = 'ut1myaccount/manageTeamFundraisers/$1';
	$route['ut1myaccount/emailTeamFundraiser'] = 'ut1myaccount/emailTeamFundraiser';
	$route['ut1myaccount/sendInvitation'] = 'ut1myaccount/sendInvitation';
	$route['ut1myaccount/deleteTeamMember/(:any)/(:any)'] = 'ut1myaccount/deleteTeamMember/$1/$2';
	$route['ut1myaccount/(:any)']  =   'ut1myaccount/index/$1';
	
	
	//$route['ut1myaccount']  =   'ut1myaccount/index/$1';
	
	/*================= Donor Dashboard ================= */
	
	
	/*================= user type 2 Dashboard ================= */
	
	$route['ut2myaccount/updatestyletemplate']  =   'ut2myaccount/updatestyletemplate';
	
	$route['ut2myaccount/getstateajax']  =   'ut2myaccount/getstateajax';
	$route['ut2myaccount/UpdateFundraiserBasicDetail']  = 'ut2myaccount/UpdateFundraiserBasicDetail';
	$route['ut2myaccount/UpdateTeamFundraiserBasicDetail']  = 'ut2myaccount/UpdateTeamFundraiserBasicDetail';
	$route['ut2myaccount/updateFundraiserComment']  = 'ut2myaccount/updateFundraiserComment';
	$route['ut2myaccount/FundraiserEdit/(:any)']  = 'ut2myaccount/FundraiserEdit/$1';
	$route['ut2myaccount/updateStatus/(:any)/(:any)']  = 'ut2myaccount/updateStatus/$1/$2';
	$route['ut2myaccount/stopFundraiserMultiple']  = 'ut2myaccount/stopFundraiserMultiple';
	$route['ut2myaccount/stopFundraiserScheduler']  = 'ut2myaccount/stopFundraiserScheduler';
	
	$route['ut2myaccount/FundraiserBasicDetail/(:any)']  = 'ut2myaccount/FundraiserBasicDetail/$1';
	$route['ut2myaccount/TeamFundraiserBasicDetail/(:any)'] = 'ut2myaccount/TeamFundraiserBasicDetail/$1';
	$route['ut2myaccount/FundraiserPhotoVideo/(:any)']  = 'ut2myaccount/FundraiserPhotoVideo/$1';
	$route['ut2myaccount/FundraiserComment/(:any)']  = 'ut2myaccount/FundraiserComment/$1';
	$route['ut2myaccount/updateFundraiserComment']  = 'ut2myaccount/updateFundraiserComment';
	$route['ut2myaccount/deleteFundraiserComment']  = 'ut2myaccount/deleteFundraiserComment';
	$route['ut2myaccount/approveFundraiserComment']  = 'ut2myaccount/approveFundraiserComment';

	$route['ut2myaccount/UploadImage']  = 'ut2myaccount/UploadImage';
	$route['ut2myaccount/UploadVideo']  = 'ut2myaccount/UploadVideo';
	$route['ut2myaccount/getChartData']  = 'ut2myaccount/getChartData';	
	$route['ut2myaccount/DeleteImage/(:any)']  = 'ut2myaccount/DeleteImage/$1';
	$route['ut2myaccount/DeleteVideo/(:any)']  = 'ut2myaccount/DeleteVideo/$1';
	
	$route['ut2myaccount/viewall']  = 'ut2myaccount/viewall';
	$route['ut2myaccount/viewallfundraiser/(:any)']  = 'ut2myaccount/viewallfundraiser/$1';
	$route['ut2myaccount/printdonationlist']  = 'ut2myaccount/printdonationlist';
	$route['ut2myaccount/exportdonationlist']  = 'ut2myaccount/exportdonationlist';
	
	$route['ut2myaccount/printfundraiserdonationlist']  = 'ut2myaccount/printfundraiserdonationlist'; 
	$route['ut2myaccount/exportfundraiserlist']  = 'ut2myaccount/exportfundraiserlist';
	
	$route['ut2myaccount/manageTeamFundraisers/(:any)'] = 'ut2myaccount/manageTeamFundraisers/$1';
	$route['ut2myaccount/emailTeamFundraiser'] = 'ut2myaccount/emailTeamFundraiser';
	$route['ut2myaccount/sendInvitation'] = 'ut2myaccount/sendInvitation';
	$route['ut2myaccount/deleteTeamMember/(:any)/(:any)'] = 'ut2myaccount/deleteTeamMember/$1/$2';
	$route['ut2myaccount/(:any)']  =   'ut2myaccount/index/$1';
	
	$route['ut2myaccount/(:any)']  =   'ut2myaccount/index/$1';
	
	/*================= user type 2 Dashboard ================= */
	$route['ambassador-portal/viewall']  	= 'ambassadorportal/viewall';
	$route['ambassador-portal/printdonationlist']  = 'ambassadorportal/printdonationlist';
	$route['ambassador-portal/exportdonationlist']  = 'ambassadorportal/exportdonationlist';
	$route['ambassador-portal/updateWidget/(:any)/(:any)']			= 'ambassadorportal/updateWidget/$1/$2';
	$route['ambassador-portal/(:any)']  	=   'ambassadorportal/index/$1';
	/*$route['team_fundraiser/team_fundraiser_verification'] = 'team_fundraiser/team_fundraiser_verification';
	$route['team_fundraiser/joinFundraiser'] = 'team_fundraiser/joinFundraiser';
	
	$route['team_fundraiser/(:any)/(:any)']  	=   'team_fundraiser/index/$1/$2';
	$route['team_fundraiser/(:any)']  	=   'team_fundraiser/index/$1';*/
	
?>