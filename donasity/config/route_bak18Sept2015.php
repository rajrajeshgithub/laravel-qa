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
	$route['ut2/ResetPassword/(:any)/(:any)/(:any)']  =   'ut2/ResetPassword/$1/$2/$3';
	$route['ut2/(:any)'] 			 = 'ut2/index/$1';
	
	/*$route['home']  = 'home';
	$route['home/(:anyPlus)']  = 'home/index/$1';*/
	
	$route['non-profits-search']  = 'npolist/index/nposearch';
	$route['fundraisers-search']  = 'fundraiserlist/index/fundeaisersearch'; //'fundraiserdetails/index/fundeaisersearch';
	
	$route['fundraisers-details/(:any)/(:any)']  = 'fundraiserdetail/FundraiserDetail/$1/$2/1'; 
	$route['preview-fundraisers-details/(:any)/(:any)'] = 'fundraiserdetail/FundraiserDetail/$1/$2/2'; 
	$route['fundraisers-comment'] = 'fundraiserdetail/FundraiserComment';
	$route['print-Fundraiserflyer/(:any)'] = 'fundraiserdetail/PrintFundraiserDetails/$1';

	/*================= user type 1 ================= */
	$route['ut1/ResetPassword/(:any)/(:any)/(:any)']  =   'ut1/ResetPassword/$1/$2/$3';
	$route['ut1/getstateajax']  =   'ut1/getstateajax';
	$route['ut1/IsDuplicateEmail'] = 'ut1/IsDuplicateEmail';	
	$route['ut1/(:any)']  =   'ut1/index/$1';
	
	/*================= user type 1 ================= */
	
	
	/*================= Donor Dashboard ================= */
	$route['ut1myaccount/getstateajax']  =   'ut1myaccount/getstateajax';
	
	$route['ut1myaccount/getFundraiserCommentBlockByAjax'] = 'ut1myaccount/getFundraiserCommentBlockByAjax';
	
	$route['ut1myaccount/getFundraiserCommentByAjax'] = 'ut1myaccount/getFundraiserCommentByAjax';
	
	$route['ut1myaccount/UpdateFundraiserBasicDetail']  = 'ut1myaccount/UpdateFundraiserBasicDetail';
	$route['ut1myaccount/updateFundraiserComment']  = 'ut1myaccount/updateFundraiserComment';
	$route['ut1myaccount/FundraiserEdit/(:any)']  = 'ut1myaccount/FundraiserEdit/$1';
	$route['ut1myaccount/FundraiserBasicDetail/(:any)']  = 'ut1myaccount/FundraiserBasicDetail/$1';
	$route['ut1myaccount/FundraiserPhotoVideo/(:any)']  = 'ut1myaccount/FundraiserPhotoVideo/$1';
	$route['ut1myaccount/FundraiserComment/(:any)']  = 'ut1myaccount/FundraiserComment/$1';
	$route['ut1myaccount/updateFundraiserComment']  = 'ut1myaccount/updateFundraiserComment';
	$route['ut1myaccount/deleteFundraiserComment']  = 'ut1myaccount/deleteFundraiserComment';
	$route['ut1myaccount/approveFundraiserComment']  = 'ut1myaccount/approveFundraiserComment';
	$route['ut1myaccount/UploadImage']  = 'ut1myaccount/UploadImage';
	$route['ut1myaccount/UploadVideo']  = 'ut1myaccount/UploadVideo';
	$route['ut1myaccount/UploadVideo']  = 'ut1myaccount/UploadVideo';
	$route['ut1myaccount/DeleteImage/(:any)']  = 'ut1myaccount/DeleteImage/$1';
	$route['ut1myaccount/DeleteVideo']  = 'ut1myaccount/DeleteVideo';
	
	$route['ut1myaccount/viewall']  = 'ut1myaccount/viewall';
	$route['ut1myaccount/printdonationlist']  = 'ut1myaccount/printdonationlist';
	
	$route['ut1myaccount/(:any)']  =   'ut1myaccount/index/$1';
	
	//$route['ut1myaccount']  =   'ut1myaccount/index/$1';
	
	/*================= Donor Dashboard ================= */
	
	
	/*================= user type 2 Dashboard ================= */
	$route['ut2myaccount/getstateajax']  =   'ut2myaccount/getstateajax';
	$route['ut2myaccount/UpdateFundraiserBasicDetail']  = 'ut2myaccount/UpdateFundraiserBasicDetail';
	$route['ut2myaccount/updateFundraiserComment']  = 'ut2myaccount/updateFundraiserComment';
	$route['ut2myaccount/FundraiserEdit/(:any)']  = 'ut2myaccount/FundraiserEdit/$1';
	
	$route['ut2myaccount/FundraiserBasicDetail/(:any)']  = 'ut2myaccount/FundraiserBasicDetail/$1';
	$route['ut2myaccount/FundraiserPhotoVideo/(:any)']  = 'ut2myaccount/FundraiserPhotoVideo/$1';
	$route['ut2myaccount/FundraiserComment/(:any)']  = 'ut2myaccount/FundraiserComment/$1';
	$route['ut2myaccount/updateFundraiserComment']  = 'ut2myaccount/updateFundraiserComment';
	$route['ut2myaccount/deleteFundraiserComment']  = 'ut2myaccount/deleteFundraiserComment';
	$route['ut2myaccount/approveFundraiserComment']  = 'ut2myaccount/approveFundraiserComment';

	$route['ut2myaccount/UploadImage']  = 'ut2myaccount/UploadImage';
	$route['ut2myaccount/UploadVideo']  = 'ut2myaccount/UploadVideo';
	$route['ut2myaccount/UploadVideo']  = 'ut2myaccount/UploadVideo';
	$route['ut2myaccount/DeleteImage/(:any)']  = 'ut2myaccount/DeleteImage/$1';
	$route['ut2myaccount/DeleteVideo']  = 'ut2myaccount/DeleteVideo';
	
	$route['ut2myaccount/viewall']  = 'ut2myaccount/viewall';
	$route['ut2myaccount/printdonationlist']  = 'ut2myaccount/printdonationlist';
	
	$route['ut2myaccount/(:any)']  =   'ut2myaccount/index/$1';
	
	/*================= user type 2 Dashboard ================= */
	
	
?>