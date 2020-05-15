<?php
	class Ut1_Controller extends Controller
	{
		public $tpl,$arr_pageMetaInfo;
		public $tplVarArray,$ntfVarArray;
		public $LoginUserDetail;
		
		function __construct()
		{
			$this->load_model('UserType1','objutype1');
			$this->tpl	= new View;		
		}
	
		function index($type,$UserID=NULL)
		{
			if($UserID != NULL)
			{
				$this->objutype1->UserID	= keyDecrypt($UserID);	
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
					$this->tpl->assign($this->objCommon->GetPageCMSDetails('LOGIN_POPUP'));
					$this->tpl->draw("ut1/login-popup");	
					break;	
				default:
					$this->redirectLoggedInUsers();
					$this->LoginRegistrationForm();
					break;			
			}
		}
		
		private function LoginRegistrationForm()
		{
			$msgValues=EnPException::getConfirmation();
			$this->GetCountryList();
			$this->load_model('Common','objCommon');
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('UT1'));
			$this->tpl->assign("msgValues",$msgValues);
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
			$this->objutype1->RegDate					= getDateTime();
			$this->objutype1->UpdateDate				= getDateTime();
			$this->objutype1->LastLoginDate				= getDateTime();
			$this->objutype1->UserIP					= $_SERVER['REMOTE_ADDR'];
			$this->objutype1->UserType					= 1;
			$this->objutype1->Status					= 1;
			
			if(trim($this->objutype1->Password)=="") $this->objutype1->Password=rand_str(6);
			
			$this->objutype1->RegisterDB();
			if($this->objutype1->UserID > 0)
			{
				$this->objutype1->SetUserSession();
				$this->SetStatus(1,'C8001');
				redirect(URL."ut1myaccount");
			}
			else
			{
				redirect(URL."ut1");
			}
		}
		
		private function Login()
		{
			$this->objutype1->EmailAddress				= request('post','email',0);
			$this->objutype1->Password					= request('post','password',0);
			
			$this->objutype1->LoginDB();
			if($this->objutype1->Pstatus)
			{
				redirect(URL."ut1myaccount");
			}
			else
			{
				redirect(URL."ut1");
			}
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
				$Eobj->sendEmail();
				$this->SetStatus(1,'C2005');
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
			if($this->LoginUserDetail['UserType1']['is_login']==1)
				redirect(URL."ut1myaccount");		
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
				setSession("Users",array(),"UserType1");
				set_cookie("Users",array(),"UserType1"); 
				clearstatcache();
				$confirmationParams=array("msgCode"=>'C2004',"msgLog"=>1,"msgDisplay"=>1,"msgType"=>2);
				$placeholderValues=array("placeValue1");
				EnPException::setConfirmation($confirmationParams, $placeholderValues);	
			}
			else 
			{
			$messageParams=array("errCode"=>"E9005","errMsg"=>"Custom Confirmation message","errOriginDetails"=>basename(__FILE__),"errSeverity"=>1, "msgDisplay"=>1,"msgType"=>1);
			EnPException::setError($messageParams);
				
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