<?php
		
	class Loginuser_Controller extends Controller
	{
		public $tpl,$arr_pageMetaInfo;
		public $tplVarArray,$ntfVarArray;
		public $LoginUserDetail;
		public $refUrl,$APP_ID,$APP_SECRET;
		const REDIRECT_URL = URL.'ut1/fblogin';
		
		function __construct()
		{			
			//$this->REDIRECT_URL ='http://dev.donasity.com/loginuser/fblogin';
			//$this->load_model('UserType1','objutype1');
			//$this->tpl	= new View;		
			//$this->APP_ID = "1453997514866192";
			//$this->APP_SECRET = "d12dbc36e7bd90c346a47d21c4bb858d";	

			$this->REDIRECT_URL ='https://www.donasity.com/loginuser/fblogin';
			$this->load_model('UserTypeX','objutype1x');
			$this->tpl	= new View;		
			$this->APP_ID = "528421657313560";
			$this->APP_SECRET = "d4f456ce0f2572c9782c3f556a8dee3a";	

			
		}
	
		function index($type,$UserID=NULL)
		{
			// confirmed got here ok
			if($UserID != NULL)
			{
				$this->objutype1x->UserID	= keyDecrypt($UserID);	
			}
			if(isset($_GET['refurl']))
			{
				$this->refUrl = $_GET['refurl'];
			}
			if(isset($_POST['refurl']))
			{
				$this->refUrl= request('post','refurl',0);
			}
			switch(strtolower($type))
			{
				case 'login-registration-form':
					$this->redirectLoggedInUsers();
					$this->LoginRegistrationForm();
					break;
				case 'userregistration':
					$this->UserRegistrationForm();
					break;
				case 'registration':
					$this->Register();
					break;
				case 'login':
					$this->Login();
					break;	
				case 'reset':
					$this->Reset();
					break;	
				case 'forget-password':
					$this->ForgetPassword();
					break;	
				case 'logout1':
					$this->LogOut(1);
					break;	
				case 'logout2':
					$this->LogOut(2);
					break;
				case 'logout':
					$this->LogOut(1);
					break;	
				case 'login-popup':
					$this->load_model('Common','objCommon');
					$this->tpl->assign($this->objCommon->GetPageCMSDetails('LOGIN_POPUP'));					
					$this->tpl->draw("loginuser/loginpopup");	
					break;
				default:
					$this->redirectLoggedInUsers();
					$this->LoginOnlyForm();
					break;			
			}
		}
		
		private function Login()
		{		
			$this->objutype1x->FacebookID	= request('post','fbId',0);	
			$this->objutype1x->EmailAddress	= request('post','email',0);
			$this->objutype1x->Password		= request('post','password',0);
			$this->refUrl					= request('post','refurl',0);
			$this->objutype1x->refUrl=$this->refUrl;
			
			if($this->objutype1x->FacebookID!='')
			{	
				$this->objutype1x->Password			= strUnique();
				$this->objutype1x->FirstName		= request('post','fname',0);
				$this->objutype1x->LastName			= request('post','lname',0);
				$this->objutype1x->RegDate			= getDateTime();
				$this->objutype1x->UpdateDate		= getDateTime();
				$this->objutype1x->LastLoginDate	= getDateTime();
				$this->objutype1x->UserIP			= $_SERVER['REMOTE_ADDR'];
				$this->objutype1x->UserType			= 1;
				$this->objutype1x->Status			= 1;
				$this->objutype1x->Gender			= request('post','gender',0);
			}
			$this->objutype1x->LoginDB();		
			
				
			if($this->objutype1x->Pstatus)
			{
				
				/*----update process log------*/
				if($this->objutype1x->UserTypeNum==1){
					$loginDetails = getSession("Users","UserType1");
					$userType 	= 1;		
				}
				if($this->objutype1x->UserTypeNum==2){
					$loginDetails = getSession("Users","UserType2");
					$userType 	= 2;		
				}							
				$userID 	= keyDecrypt($loginDetails['user_id']);
				$userName 	= $loginDetails['user_fullname'];
				$sMessage 	= "Error in user login";
				$lMessage 	= "Error in user login";
				if($this->objutype1x->Pstatus)
				{
					$sMessage = "User logged In";
					$lMessage = "User logged In";
					if($this->objutype1x->NewReg)
					{
						$sMessage = "User logged In with facebook credential";
						$lMessage = "User logged In with facebook credential";
						$this->SendMailForRegister();
					}
				}
				$DataArray = array(	"UType"=>$userType,"UID"=>$userID,"UName"=>$userName,"RecordId"=>$userID,"SMessage"=>$sMessage,"LMessage"=>$lMessage,"Date"=>getDateTime(),"Controller"=>get_class(),"Model"=>get_class($this->objutype1x));	
				$this->objutype1x->updateProcessLog($DataArray);	
				/*-----------------------------*/
				
				// redirect based on user type
				if($this->objutype1x->UserTypeNum==1){
					$this->objutype1x->UserType=1;
					$this->load_model('UserType2','objutype2'); // auto logout an NPO user if logged in
					$this->objutype2->logoutUT2();
					$this->redirectURL(URL."ut1myaccount",true);
				}
				else if($this->objutype1x->UserTypeNum==2){
					$this->objutype1x->UserType=2;
					$this->redirectURL(URL."ut2myaccount",true);					
				}
				else{
					echo 'Programmer test - Oops.... an error occurred Error 101';
					exit;
				}		
			}
			else {
				redirect(URL."loginuser?refurl=".urlencode($this->refUrl));
			}
			
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		private function handleMessage()
		{
			$messageHandler = $_GET;
			$messageParams=array("errCode"=>'000',"errMsg"=>$messageHandler['error_message'],"errOriginDetails"=>basename(__FILE__),"errSeverity"=>1,"msgDisplay"=>1,"msgType"=>1);
			EnPException::setError($messageParams);	
			redirect(URL.'loginuser');
		}
				
		public function FBRegistration($graph)
		{
			$this->objutype1x->FacebookID 	= $graph->getProperty('id');	
			$this->objutype1x->EmailAddress = $graph->getProperty('email');	
			$this->objutype1x->FirstName 	= $graph->getProperty('first_name');	
			$this->objutype1x->LastName 	= $graph->getProperty('last_name');	
			$this->objutype1x->Gender  		= $graph->getProperty('gender');	
			$this->objutype1x->UserType		= 1;
			$this->objutype1x->RegDate		= getDateTime();
			$this->objutype1x->UpdateDate	= getDateTime();
			$this->objutype1x->LastLoginDate= getDateTime();
			$this->objutype1x->UserIP		= $_SERVER['REMOTE_ADDR'];
			$this->objutype1x->FBRegistration_DB();	

			
			/*----update process log------
			$userType 	= 'UT1';					
			$userID 	= $this->objutype1x->UserID ;
			$userName	= $this->objutype1x->FirstName." ".$this->objutype1x->LastName;
			$sMessage = "Error in facebook registration process";
			$lMessage = "Error in facebook registration process";
			if($this->objutype1x->UserID>0)
			{
				$sMessage = "UT1 logged in with facebook id successfully";
				$lMessage = "UT1 logged in with facebook id successfully with ID =".$userID;	
				if($this->NewReg)
				{
					$sMessage = "UT1 registered successfully";
					$lMessage = "UT1 registered successfully with ID =".$userID;	
				}
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class(),
									"Model"=>get_class($this->objutype1x));	
				$this->objutype1x->updateProcessLog($DataArray);	
				-------------------------------------*/
				
				
			if($this->objutype1x->UserID > 0)
			{
				$this->objutype1x->SetUserSession();
				if($this->NewReg)
				{
					$this->SendMailForRegister();
					$this->SetStatus(1,'C8001');
				}
				else
				{
					$this->SetStatus(1,'C9001');	
				}
				
				
				if($this->objutype1x->UserTypeNum==1){
					$this->objutype1x->UserType=1;
					$this->load_model('UserType2','objutype2'); // auto logout an NPO user if logged in
					$this->objutype2->logoutUT2();
					$this->redirectURL(URL."ut1myaccount",true);
				}
				else if($this->objutype1x->UserTypeNum==2){
					$this->objutype1x->UserType=2;
					$this->redirectURL(URL."ut2myaccount",true);					
				}
				else{
					echo 'Programmer test - Oops.... an error occurred Error 101';
					exit;
				}	
				
				
				
				/*
				func redirectURL(URL."ut1myaccount",redirecttoAlternterulr=false)
				*/
				//redirect(URL."ut1myaccount");
			}
			else
			{
				//$this->SetStatus(0,'E8017');
				//redirect(URL."loginuser");
			}					
		}
		
		
		private function LoginOnlyForm()
		{
			$this->GetCountryList();
			$this->load_model('Common','objCommon');
			$this->tpl->assign('refUrl',$this->refUrl);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1'));
			$this->tpl->draw("loginuser/login");
		}
		
		private function LoginRegistrationForm()
		{
			$this->GetCountryList();
			$this->load_model('Common','objCommon');
			$this->tpl->assign('refUrl',$this->refUrl);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1'));
			$this->tpl->draw("loginuser/loginregistrationform");
		}
		
		private function UserRegistrationForm()
		{
			$this->GetCountryList();
			$this->load_model('Common','objCommon');
			$this->tpl->assign('refUrl',$this->refUrl);
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1'));
			$this->tpl->draw("loginuser/userregistration");
		}
		
		private function Register()
		{
			$this->objutype1x->FirstName					= request('post','fname',0);
			$this->objutype1x->LastName					= request('post','lname',0);
			$this->objutype1x->Address1					= request('post','Address1',0);
			$this->objutype1x->Address2					= request('post','Address2',0);
			$this->objutype1x->City						= request('post','city',0);
			$this->objutype1x->Zip						= request('post','zipCode',0);
			$this->objutype1x->Country					= request('post','country',0);
			$this->objutype1x->State						= request('post','state',0);
			$this->objutype1x->PhoneNumber				= request('post','phoneNumber',0);
			$this->objutype1x->Mobile					= request('post','altPhoneNumber',0);
			$this->objutype1x->EmailAddress				= request('post','emailAddress',0);
			$this->objutype1x->Password					= request('post','signupPassword',0);
			$this->objutype1x->ConfirmPassword			= request('post','confirmPassword',0);
			$this->refUrl								= request('post','refUrl',0);
			$this->objutype1x->RegDate					= getDateTime();
			$this->objutype1x->UpdateDate				= getDateTime();
			$this->objutype1x->LastLoginDate				= getDateTime();
			$this->objutype1x->UserIP					= $_SERVER['REMOTE_ADDR'];
			$this->objutype1x->UserType					= 1;
			$this->objutype1x->Status					= 1;
			
			if(trim($this->objutype1x->Password)=="") $this->objutype1x->Password=rand_str(6);
			
			$this->objutype1x->RegisterDB();		
			
				
			if($this->objutype1x->UserID > 0)
			{
				$this->objutype1x->SetUserSession();
				$this->SendMailForRegister();
				$this->SetStatus(1,'C8001');
				$this->redirectURL(URL."ut1myaccount",true);
			}
			else
			{
				//redirect(URL."loginuser?refurl=".urlencode($this->refUrl));
			}
			
			
			if($this->objutype1x->UserTypeNum==1){
				$this->objutype1x->UserType=1;
				$this->load_model('UserType2','objutype2'); // auto logout an NPO user if logged in
				$this->objutype2->logoutUT2();
				$this->redirectURL(URL."ut1myaccount",true);
			}
			else if($this->objutype1x->UserTypeNum==2){
				$this->objutype1x->UserType=2;
				$this->redirectURL(URL."ut2myaccount",true);					
			}
			else{
				echo 'Programmer test - Oops.... an error occurred Error 101';
				exit;
			}			
				
				
				
		}
		
		
		private function redirectURL($defaultUrl, $alternateUrl=false)
		{
			if($alternateUrl==true)
			{				
				if(!filter_var($this->refUrl, FILTER_VALIDATE_URL)===false)
					redirect($this->refUrl);
				else
					redirect($defaultUrl);
			}
			else
			{
				redirect($defaultUrl);
			}
		}
		
		private function SendMailForRegister()
		{
			$this->load_model('Email','objemail');
			$Keyword='UT1donorregister';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			$CustomerDetails=$this->objutype1x->GetUserDetails();
			$UserName=$CustomerDetails['RU_FistName'].' '.$CustomerDetails['RU_LastName'];
			$tpl=new View;
			$tpl->assign('UserName',$UserName);
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			
			$InsertDataArray=array('FromID'=>$CustomerDetails['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$CustomerDetails['RU_EmailID'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
			if(!$Status)
			{
				$this->SetStatus(0,'E17000');
			}
		}
		
		
		
		private function ForgetPassword()
		{
			$this->objutype1x->EmailAddress	= request('post','email',0);
			$this->objutype1x->ForgetPasswordDB();
			if($this->objutype1x->Pstatus)
			{
				$this->load_model('Email','objemail');
				$Keyword='UT1_ForgotPassword';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$CustomerDetail=array("Name"=>$this->objutype1x->UserDetailsArray['Name'],"Email"=>$this->objutype1x->EmailAddress);
				$tpl=new View;
				$tpl->assign('Link',$this->objutype1x->Pageurl);
				$tpl->assign('Detail',$CustomerDetail);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				$InsertDataArray=array('FromID'=>$this->objutype1x->UserDetailsArray['RU_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$this->objutype1x->EmailAddress,
				'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0',
'SendMode'=>'1','AddedOn'=>getDateTime());
				$id=$this->objemail->InsertEmailDetail($InsertDataArray);
				$Eobj	= LoadLib('BulkEmail');
				$Status=$Eobj->sendEmail($id);
				if($Status)
				{
					$this->SetStatus(1,'C2005');
				}else
				{
					$this->SetStatus(0,'E17000');
				}
				
				redirect(URL."loginuser");
			}
			else
			{
				redirect(URL."loginuser");
			}
		}
		
		
		
		public function ResetPassword($mailID,$userID,$Datetime)
		{
			date_default_timezone_set('America/New_York');
			$this->objutype1x->MailID  		= keyDecrypt(urldecode($mailID));
			$this->objutype1x->UserID  		= keyDecrypt(urldecode($userID));
			$this->objutype1x->DateTime  	= keyDecrypt(urldecode($Datetime));
			$this->objutype1x->VerifyResetPassURL();
			if($this->objutype1x->Pstatus)
			{
				$this->load_model('Common','objCommon');
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_RESETPASSWORD'));
				$this->tpl->assign("UserID",$this->objutype1x->UserDetailsArray['RU_ID']);
				$this->tpl->assign("EmailID",$this->objutype1x->UserDetailsArray['RU_EmailID']);
				$this->tpl->draw("loginuser/resetpassword");	
			}
			else
			{
				redirect(URL."loginuser");
			}
		}
		
		private function Reset()
		{
			$this->objutype1x->MailID 				= keyDecrypt(request('post','emailId',0));
			$this->objutype1x->UserID 				= keyDecrypt(request('post','userId',0));
			$this->objutype1x->Password 				= request('post','newPassword',0);
			$this->objutype1x->ConfirmPassword 		= request('post','confirmPassword',0);	
			$this->objutype1x->ResetDB();
			/*----update process log------*/				
			//$userType 	= 'UT1';					
			$userID 	= $this->objutype1x->UserID;
			$userName	= '';
			$sMessage = "Error in password reset process";
			$lMessage = "Error in password reset process";
			if($this->objutype1x->Pstatus)
			{
				$sMessage = "Password has been reset";
				$lMessage = "Password has been reset";
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class(),
									"Model"=>get_class($this->objutype1x));	
				$this->objutype1x->updateProcessLog($DataArray);	
				/*-----------------------------*/
			
			redirect(URL."loginuser");
		}
		
		private function GetCountryList()
		{
			$this->load_model('Common','objcommon');
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objcommon->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		public function getstateajax()
		{
			$CountryAbr	= request('post','CountryAB',0);
			$this->load_model("Common","objcommon");
			$StateList	= $this->objcommon->getStateList($CountryAbr);	
			echo json_encode($StateList);
			exit;
		}
		
		public function IsDuplicateEmail()
		{
			$this->load_model("Common","objcommon");
		   $status=false;
		   $EmailAddress = request('get','emailAddress',0);
		   $status = $this->objcommon->checkEmailDuplicacy($EmailAddress);
		   echo json_encode($status);exit;	
		}
		
		private function redirectLoggedInUsers()
		{
			$this->LoginUserDetail	= getSession('Users');
			if($this->LoginUserDetail['UserType1']['is_login']==1)
			{
				//$this->load_model('UserType2','objutype2');
				//$this->objutype2->logoutUT2();									
				//redirect(URL."ut1myaccount");		
			}
		}
		
		private function SendEmail()
		{
			$FP_Eobj	= LoadLib('Email');
			if(is_object($FP_Eobj))
			{
				$FP_Eobj->mailFlag=1;
				$FP_Eobj->tplId='Template1';
				$FP_Eobj->tplVarArr=$this->tplVarArray;
				$FP_Eobj->ntfVarArr=$this->ntfVarArray;
				if($FP_Eobj->sendEmail())
				{
					$this->SetStatus(1,"C7002");
				}
				else
				{
					$this->SetStatus(0,"E7004");	
				}
			}	
		}
		
		
		private function LogOut($utyp)
		{
			if($utyp==1){
				if($this->objutype1x->checkLogin(getSession('Users')))
				{				
					$this->objutype1x->logoutUT1();
					$confirmationParams=array("msgCode"=>'C2004',"msgLog"=>1,"msgDisplay"=>1,"msgType"=>2);
					$placeholderValues=array("placeValue1");
					EnPException::setConfirmation($confirmationParams, $placeholderValues);	
				}
				else 
				{
					$messageParams=array("errCode"=>"E9005","errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1, "msgDisplay"=>1,"msgType"=>1);
					EnPException::setError($messageParams);				
				}
				if (isset($_SESSION['facebook_token'])){
					unset($_SESSION['facebook_token']);
				}
			}
			else if($utyp==2){
				if (isset($_SESSION['facebook_token'])){
					unset($_SESSION['facebook_token']);
				}
				$this->load_model('UserType2','objutype2'); 
				$this->objutype2->logoutUT2();				
			}
			redirect(URL."loginuser");	
		}
		
		private function SetStatus($Status,$Code)
		{
			if($Status)
			{
				$messageParams=array("msgCode"=>$Code,
												 "msg"=>"Custom Confirmation message",
												 "msgLog"=>0,									
												 "msgDisplay"=>1,
												 "msgType"=>2);
					EnPException::setConfirmation($messageParams);
			}
			else
			{
				$messageParams=array("errCode"=>$Code,
										 "errMsg"=>"Custom Confirmation message",
										 "errOriginDetails"=>basename(__FILE__),
										 "errSeverity"=>1,
										 "msgDisplay"=>1,
										 "msgType"=>1);
					EnPException::setError($messageParams);
			}
		}
	}


?>