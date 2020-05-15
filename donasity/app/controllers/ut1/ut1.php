<?php
		
	class Ut1_Controller extends Controller
	{
		public $tpl,$arr_pageMetaInfo;
		public $tplVarArray,$ntfVarArray;
		public $LoginUserDetail;
		public $refUrl,$APP_ID,$APP_SECRET;
		//const REDIRECT_URL = URL.'ut1/fblogin';
		function __construct()
		{			
			$this->load_model('UserType1','objutype1');
			$this->tpl	= new View;					
		}
	
		function index($type='login-registration-form', $UserID=NULL)
		{
			//dump($_REQUEST);
			if($UserID != NULL)
			{
				$this->objutype1->UserID	= keyDecrypt($UserID);	
			}
			if(isset($_GET['refurl']))
			{
				$this->refUrl = $_GET['refurl'];
			}
			switch(strtolower($type))
			{
				case 'login-registration-form':
					$this->redirectLoggedInUsers();
					$this->LoginRegistrationForm();
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
				case 'logout':
					$this->LogOut();
					break;				
				case 'login-popup':
					$this->load_model('Common','objCommon');
					$this->tpl->assign('loginUrl', URL.'ut1');
					$this->tpl->assign($this->objCommon->GetPageCMSDetails('LOGIN_POPUP'));					
					$this->tpl->draw("ut1/login-popup");	
					break;				
				default:
					$this->redirectLoggedInUsers();
					$this->LoginRegistrationForm();
					break;			
			}
		}
		
		private function handleMessage()
		{
			$messageHandler = $_GET;
			$messageParams=array("errCode"=>'000',
										 "errMsg"=>$messageHandler['error_message'],
										 "errOriginDetails"=>basename(__FILE__),
										 "errSeverity"=>1,
										 "msgDisplay"=>1,
										 "msgType"=>1);
					EnPException::setError($messageParams);	
			redirect(URL.'ut1');
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
			$this->tpl->draw("ut1/loginregistrationform");
		}
		
		private function Register()
		{
			$this->objutype1->FirstName					= request('post','fname',0);
			$this->objutype1->LastName					= request('post','lname',0);
			$this->objutype1->Address1					= request('post','Address1',0);
			$this->objutype1->Address2					= request('post','Address2',0);
			$this->objutype1->City						= request('post','city',0);
			$this->objutype1->Zip						= request('post','zipCode',0);
			$this->objutype1->Country					= request('post','country',0);
			$this->objutype1->State						= request('post','state',0);
			$this->objutype1->PhoneNumber				= request('post','phoneNumber',0);
			$this->objutype1->Mobile					= request('post','altPhoneNumber',0);
			$this->objutype1->EmailAddress				= request('post','emailAddress',0);
			$this->objutype1->Password					= request('post','signupPassword',0);
			$this->objutype1->ConfirmPassword			= request('post','confirmPassword',0);
			$this->refUrl								= request('post','refUrl',0);
			$this->objutype1->RegDate					= getDateTime();
			$this->objutype1->UpdateDate				= getDateTime();
			$this->objutype1->LastLoginDate				= getDateTime();
			$this->objutype1->UserIP					= $_SERVER['REMOTE_ADDR'];
			$this->objutype1->UserType					= 1;
			$this->objutype1->Status					= 1;
			
			if(trim($this->objutype1->Password)=="") $this->objutype1->Password=rand_str(6);
			
			$this->objutype1->RegisterDB();
			/*----update process log------*/
			$userType 	= 'UT1';					
			$userID 	= $this->objutype1->UserID ;
			$userName	= $this->objutype1->FirstName." ".$this->objutype1->LastName;
			$sMessage = "Error in user registration process";
			$lMessage = "Error in user registration process";
			if($this->objutype1->UserID>0)
			{
				$sMessage = "UT1 registered successfully";
				$lMessage = "UT1 registered successfully with ID =".$userID;	
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype1));	
				$this->objutype1->updateProcessLog($DataArray);	
				/*-------------------------------------*/
			if($this->objutype1->UserID > 0)
			{
				$this->objutype1->SetUserSession();
				$this->SendMailForRegister();
				$this->SetStatus(1,'C8001');
				/*
				func redirectURL(URL."ut1myaccount",redirecttoAlternterulr=false)
				*/
				$this->redirectURL(URL."ut1myaccount",true);
			}
			else
			{
				redirect(URL."ut1?refurl=".urlencode($this->refUrl));
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
			$CustomerDetails=$this->objutype1->GetUserDetails();
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
		
		private function Login()
		{
			$this->objutype1->FacebookID	= request('post','fbId',0);	
			$this->objutype1->EmailAddress	= request('post','email',0);
			$this->objutype1->Password		= request('post','password',0);
			$this->refUrl					= request('post','refUrl',0);
			if($this->objutype1->FacebookID!='')
			{	
				$this->objutype1->Password		= strUnique();
				$this->objutype1->FirstName		= request('post','fname',0);
				$this->objutype1->LastName		= request('post','lname',0);
				$this->objutype1->RegDate		= getDateTime();
				$this->objutype1->UpdateDate	= getDateTime();
				$this->objutype1->LastLoginDate	= getDateTime();
				$this->objutype1->UserIP		= $_SERVER['REMOTE_ADDR'];
				$this->objutype1->UserType		= 1;
				$this->objutype1->Status		= 1;
				$this->objutype1->Gender		= request('post','gender',0);
			}
			$this->objutype1->LoginDB();
			/*----update process log------*/
			$loginDetails = getSession("Users","UserType1");
			$userType 	= 'UT1';					
			$userID 	= keyDecrypt($loginDetails['user_id']);
			$userName	= $loginDetails['user_fullname'];
			$sMessage = "Error in user login";
			$lMessage = "Error in user login";
			if($this->objutype1->Pstatus)
			{
				$sMessage = "User logged In";
				$lMessage = "User logged In";
				if($this->objutype1->NewReg)
				{
					$sMessage = "User logged In with facebook credential";
					$lMessage = "User logged In with facebook credential";
					$this->SendMailForRegister();
				}
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$userID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype1));	
				$this->objutype1->updateProcessLog($DataArray);	
				/*-----------------------------*/
			if($this->objutype1->Pstatus)
			{
				$this->load_model('UserType2','objutype2');
				$this->objutype2->logoutUT2();		
				$this->redirectURL(URL."ut1myaccount",true);				
			}
			else
				redirect(URL."ut1?refurl=".urlencode($this->refUrl));
			
		}
		
		private function ForgetPassword()
		{
			$this->objutype1->EmailAddress	= request('post','email',0);
			$this->objutype1->ForgetPasswordDB();
			if($this->objutype1->Pstatus)
			{
				$this->load_model('Email','objemail');
				$Keyword='UT1_ForgotPassword';
				$where=" Where Keyword='".$Keyword."'";
				$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
				$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
				$CustomerDetail=array("Name"=>$this->objutype1->UserDetailsArray['Name'],"Email"=>$this->objutype1->EmailAddress);
				$tpl=new View;
				$tpl->assign('Link',$this->objutype1->Pageurl);
				$tpl->assign('Detail',$CustomerDetail);
				$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
				$InsertDataArray=array('FromID'=>$this->objutype1->UserDetailsArray['RU_ID'],
				'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
				'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$this->objutype1->EmailAddress,
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
				
				redirect(URL."ut1");
			}
			else
			{
				redirect(URL."ut1");
			}
		}
		
		
		
		public function ResetPassword($mailID,$userID,$Datetime)
		{
			date_default_timezone_set('America/New_York');
			$this->objutype1->MailID  		= keyDecrypt(urldecode($mailID));
			$this->objutype1->UserID  		= keyDecrypt(urldecode($userID));
			$this->objutype1->DateTime  	= keyDecrypt(urldecode($Datetime));
			$this->objutype1->VerifyResetPassURL();
			if($this->objutype1->Pstatus)
			{
				$this->load_model('Common','objCommon');
				$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
				$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
				$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
				$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1_RESETPASSWORD'));
				$this->tpl->assign("UserID",$this->objutype1->UserDetailsArray['RU_ID']);
				$this->tpl->assign("EmailID",$this->objutype1->UserDetailsArray['RU_EmailID']);
				$this->tpl->draw("ut1/resetpassword");	
			}
			else
			{
				redirect(URL."ut1");
			}
		}
		
		private function Reset()
		{
			$this->objutype1->MailID 				= keyDecrypt(request('post','emailId',0));
			$this->objutype1->UserID 				= keyDecrypt(request('post','userId',0));
			$this->objutype1->Password 				= request('post','newPassword',0);
			$this->objutype1->ConfirmPassword 		= request('post','confirmPassword',0);	
			$this->objutype1->ResetDB();
			/*----update process log------*/				
			$userType 	= 'UT1';					
			$userID 	= $this->objutype1->UserID;
			$userName	= '';
			$sMessage = "Error in password reset process";
			$lMessage = "Error in password reset process";
			if($this->objutype1->Pstatus)
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
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objutype1));	
				$this->objutype1->updateProcessLog($DataArray);	
				/*-----------------------------*/
			
			redirect(URL."ut1");
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
			if(isset($this->LoginUserDetail['UserType1']['is_login']) && $this->LoginUserDetail['UserType1']['is_login']==1)
			{
				$this->load_model('UserType2','objutype2');
				$this->objutype2->logoutUT2();									
				redirect(URL."ut1myaccount");		
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
		
		
		private function LogOut()
		{
			if($this->objutype1->checkLogin(getSession('Users')))
			{				
				$this->objutype1->logoutUT1();
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
			redirect(URL."ut1");	
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