<?php
class Logout_Controller extends Controller
{
	private $loginBy,$LoginUserDetail;
	public function index()
	{
		$this->LoginUserDetail	= getSession('User');
		$UserType				= $this->LoginUserDetail['UserType1'];
		switch(strtolower($UserType['user_type']))
		{
			case '1':
				$RedirectTo	= "uts1";
				break;	
			case '2':
				$RedirectTo	= "ut2/npo-login";
				
				break;	
			default:
				$RedirectTo	= "login/npo-login";
				break;
		}	
		unsetSession("User");
		setcookie("User","",time()-3600,"/"); 
		clearstatcache();
		$confirmationParams=array("msgCode"=>'C2004',
										 "msgLog"=>1,									
										 "msgDisplay"=>1,
										 "msgType"=>2
		);
		$placeholderValues=array("placeValue1");
		EnPException::setConfirmation($confirmationParams, $placeholderValues);	
		
		
		/*-----------------------------------*/
		
		redirect(URL.$RedirectTo);
	}
}
?>