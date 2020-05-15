<?php
class Login_Controller extends Controller
{
	private $L_username,$L_password,$L_message,$L_errorCode,$L_status=1;
	private $FP_email;
	public $Email,$Password,$arrLoginDetail,$adminDetail,$L_ErrorCode,$L_ErrorMessage,$L_ConfirmCode,$L_ConfirmMsg;

	public function __construct	()
	{
		if(is_array(getSession('DonasityAdminLoginDetail'))) redirect(URL.'home');		
		$this->tpl=new View;
		$this->load_model("Login","L_obj");
		$this->P_Status=1;
	}
	
	public function index($loginAs='')
	{
		switch(strtolower($loginAs))
		{
			case 'login':
					$this->Processlogin();
					break;
			case 'forgotpassword':
					$this->forgotPassword();
			 		break;
			case 'processreset':
					$this->ProcessResetPassword();
					break;		
			default:
					$this->ShowLogin();
					break;
		}
		
	}
	
	
	public function ShowLogin()
	{
		//CheckLogin();
		EnPException::writeProcessLog('Login_Controller :: index action to show admin user login');		
		$this->tpl->assign("Url",URL);
		$this->tpl->draw('login/login');
	}
	
	private function init()
	{
		$this->UserName			= request('post','userName',0);
		$this->Password			= request('post','password',0);
	    $this->checkboxinline	= request('post','checkboxInline',1);
		//var_dump($this->UserName);exit;
	}
	
	private function loginValidate()
	{
		if($this->UserName=='')$this->setErrorMsg('E9001');
		if($this->Password=='')$this->setErrorMsg('E9002');
		if($this->CheckLogin()==false)$this->setErrorMsg('E9003');
		if($this->matchPassword()==false)$this->setErrorMsg('E9004');
	}
	
	function CheckLogin()
	{			
			$flagProcess = true;
			$fields = array('Admin_ID','Admin_FirstName','Admin_LastName','Admin_EmailID','Admin_UserName','Admin_Password','Admin_AccessModuleIDs','Admin_AccessPeriod',
							'Admin_LoginWithIP','Admin_AccessIPAddress','Admin_Status','Admin_LastUpdatedBy','Admin_AddedDate','Admin_LastUpdatedDate',
							'DATEDIFF(NOW(),Admin_AddedDate) as difference ');
			$condition  = 	" and Admin_UserName='".$this->UserName."' AND Admin_Status='1'";		
			$orderby	= 	" order by Admin_ID";
			//echo $fields.$condition.$orderby;exit;
			if($this->P_Status==1)
			{
				$this->arrLoginDetail = $this->L_obj->processLogin_DB($fields,$condition,$orderby);
			
				if(isset($this->arrLoginDetail) && $this->arrLoginDetail['Admin_Status']=='1')
				{
					$this->adminDetail["id"]=$this->arrLoginDetail['Admin_ID'];
					$this->L_userid=$this->adminDetail["id"];
					$this->adminDetail["username"]=$this->arrLoginDetail['Admin_UserName'];
					$this->adminDetail["firstname"]=$this->arrLoginDetail['Admin_FirstName'];
					$this->adminDetail["lastname"]=$this->arrLoginDetail['Admin_LastName'];
					$this->adminDetail["fullname"]=$this->arrLoginDetail['Admin_FirstName']." ".$this->arrLoginDetail['Admin_LastName'];
					$this->adminDetail["moduleIds"]=$this->arrLoginDetail['Admin_AccessModuleIDs'];
					$this->adminDetail["email"]=$this->arrLoginDetail['Admin_EmailID'];
					$this->adminDetail["accessperiod"]=$this->arrLoginDetail['Admin_AccessPeriod'];
					$this->adminDetail["addeddate"]=$this->arrLoginDetail['Admin_AddedDate'];
					$this->adminDetail["difference"]=$this->arrLoginDetail['difference'];
					$this->adminDetail['CheckWithIP']=$this->arrLoginDetail['Admin_LoginWithIP'];
					$this->adminDetail['IPAddresses']=$this->arrLoginDetail['Admin_AccessIPAddress'];
					$flagProcess = true;					
				}
				else
				{
					$flagProcess = false;
				}
			}
			return $flagProcess;
	}
	
	
	private function getAssignedModuleID()
	{
		$Feilds = array('AssignedModuleID');
		$Where  = array('AdminUserID'=>$this->adminDetail["id"]);
		
		$GetAssignModule = $this->L_obj->GetAssignModuleID($Feilds,$Where);
		
		if(count($GetAssignModule)<=0)
		{
			$this->setErrorMsg('E1003');
		}
		else
		{
			return $this->arrLoginDetail['AssignedModuleID'];
		}
	}
	
	private function matchPassword()
	{
			$flagProcess=true;			
			$password = PassDec($this->arrLoginDetail['Admin_Password']);
			//echo $password;exit;
			if($password!=$this->Password)
			{
				$flagProcess=false;
			}
			return $flagProcess;
				
	}
	
	private function updateAcessDate()
	{
		try
		{
			//$DataArray	= array('LastAccessDate'=>getDateTime(),'PsdStatus'=>'1');
			$DataArray	= array('Admin_LastLoginDate'=>getDateTime());
			if($this->L_obj->UpdateLoginDate($DataArray,$this->arrLoginDetail["Admin_ID"]))
			{
				$this->setConfirmationMsg('C9001');
			}
			else
			{
				$this->setErrorMsg('E9007');
			}
		}
		catch(Exception $e)
		{
			EnPException::exceptionHandler($e);
		}
	}
	
	
	private function AccessPeriodChecking()
	{
		$AccessPeriod = $this->arrLoginDetail['Admin_AccessPeriod'];
		if($AccessPeriod>0)
		{
			if($this->arrLoginDetail['difference']<=$AccessPeriod)
			{
				$this->setConfirmationMsg('C9001');
			}
			else
			{
				$this->setErrorMsg('E9005');
			}
		}
		else
		{
			$this->setConfirmationMsg('C9001');
		}
	}
	
	private function setSession()
	{
		setSession("DonasityAdminLoginDetail",$this->adminDetail['id'],"admin_id");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['email'],"admin_username");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['firstname'],"admin_firstname");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['lastname'],"admin_lastname");
		setSession("DonasityAdminLoginDetail",ucwords($this->adminDetail["fullname"]),"admin_fullname");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['email'],"admin_email");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['accessperiod'],"admin_accessperiod");
		setSession("DonasityAdminLoginDetail",$this->adminDetail['moduleIds'],"admin_moduleIds");
		if($this->checkboxinline)
		{
			set_Cookie("DonasityAdminLoginDetail",getSession("DonasityAdminLoginDetail"),time() + (86400 * 7));
		}
		else
		{
			set_Cookie("DonasityAdminLoginDetail",getSession("DonasityAdminLoginDetail"));
		}
	}
	
	
	public function IPAddressChecking()
	{
		$IPCheck= $this->adminDetail['CheckWithIP'];
		
		$IPAddress=$this->adminDetail['IPAddresses'];
		//echo "</br>";
		$ActiveIP=$_SERVER['REMOTE_ADDR'];
		
		if($IPCheck==1)
		{
			if($IPAddress<>NULL)
			{
				$getIP=explode(",",$IPAddress);				
				foreach($getIP as $IPs)
				{
					//echo trim($IPs)."==".trim($ActiveIP);echo "</br>";
					if(trim($IPs)==trim($ActiveIP))
					{
						$IP=1;
						break;
					}
					else
					{
						$IP=0;
						
					}
 				}

				if($IP==1) $this->setConfirmationMsg('C9001');
				else{
						$this->P_Status=0;
						$this->setErrorMsg('E9006');}
			}
			else
			{
				$this->P_Status=0;
				$this->setErrorMsg('E9006');
			}
		}
	}
	
	
	private function Processlogin()
	{
		try{
			if($this->P_Status==1)	$this->init();
			if($this->P_Status==1)	$this->loginValidate();			
			if($this->P_Status==1)	$this->AccessPeriodChecking();
			if($this->P_Status==1) $this->IPAddressChecking();
			if($this->P_Status==1)	$this->updateAcessDate();
			if($this->P_Status==1)	$this->setSession();
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);
				$errParams=array("errCode"=>'E9007',"errMsg"=>"Custom Exception message", "errOriginDetails"=>basename(__FILE__), "errSeverity"=>1, "msgDisplay"=>1, "msgType"=>1);
			 	EnPException::setError($errParams);
				$this->P_Status=0;
			}
			
			if($this->P_Status==1)
			{
				$confirmationParams=array("msgCode"=>$this->L_ConfirmCode,
										 "msgLog"=>1,
										 "msgDisplay"=>1,
										 "msgType"=>2
				);
				
				$placeholderValues=array("placeValue1");
				EnPException::setConfirmation($confirmationParams, $placeholderValues);
				$refer=getSession("Referer");
				if(trim($refer)<>'')
				{
					redirect(URL.$refer);
				}
                redirect(URL."home");
			}
			else
			{
				$errParams=array("errCode"=>$this->L_ErrorCode,
								 "errMsg"=>"Custom Exception message",
								 "errOriginDetails"=>basename(__FILE__),
								 "errSeverity"=>1,
								 "msgDisplay"=>1,
								 "msgType"=>1
				);
				EnPException::setError($errParams);
                redirect(URL."login");
			}
	}
	
	
	private function setErrorMsg($ErrCode)
	{
		EnPException::writeProcessLog('Login Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$this->L_ErrorCode=$ErrCode;
		$this->L_ErrorMessage=$ErrCode;
		$this->P_Status=0;
	}
	
	private function setConfirmationMsg($ConfirmCode)
	{
		EnPException::writeProcessLog('Login Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
		$this->L_ConfirmCode=$ConfirmCode;
		$this->L_ConfirmMsg=$ConfirmCode;
		$this->P_Status=1;
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
	
	
	
	
	private function ForgotValidate()
	{
		if($this->P_Status==1 && $this->FP_email=='') $this->setErrorMsg('E9012');
		if($this->P_Status==1 && !filter_var($this->FP_email,FILTER_VALIDATE_EMAIL)) $this->setErrorMsg('E9019');
	}
	
	private function sendEmailForForgotPassword()
	{
		date_default_timezone_set('America/New_York');
		$urlDateTime = date("Y-m-d H:i:s", strtotime('+10 minutes', strtotime(getDateTime())));
	    $this->FP_sendDate = getDateTime();
		$enc_UserId=keyEncrypt($this->FP_userId);
		$enc_mailId=keyEncrypt($this->FP_emailID);
		$enc_DateTime = keyEncrypt($urlDateTime);
		
		$qs=urlencode($enc_mailId)."/".urlencode($enc_UserId)."/".urlencode($enc_DateTime);
		$this->Pageurl=URL."login/resetPassword/".$qs;
		$this->url='<a href="'.$this->Pageurl.'">Click to reset your password</a>';
		
		$this->FP_Eobj=LoadLib('Email');
		if(is_object($this->FP_Eobj))
		{
			$this->FP_Eobj->mailFlag=1;
			$this->FP_Eobj->tplId='Template1';
			$this->FP_Eobj->tplVarArr=array('tpl_name'=>$this->FP_userName,'tpl_link'=>$this->Pageurl,'tpl_url'=>$this->url,'tpl_mail'=>$this->FP_email);
			$this->FP_Eobj->ntfVarArr=array('ntf_EmailTo'=>$this->FP_email);
			$status=$this->FP_Eobj->sendEmail();
		}
		if($status)
			$this->setConfirmationMsg('C9002');
		else	
			$this->setErrorMsg('E9010');
	}
	
	private function getPasswordDetail() {
		$fields = array('Admin_ID', 'concat(Admin_FirstName," ",Admin_LastName) as fullname', 'Admin_EmailID', 'Admin_Password');
		
		$where = " AND Admin_EmailID = '" . $this->FP_email . "'" ;				
		$PasswordDetail = $this->L_obj->processLogin_DB($fields, $where, '');
		if($PasswordDetail != '') {
			$this->FP_emailID = $PasswordDetail['Admin_EmailID'];
			$this->FP_userId = $PasswordDetail['Admin_ID'];
			$this->P_Status = 1;
		} else
			$this->setErrorMsg('E9009');
	}
	
	/*Forgot pass function used for check forgot password detail */
	private function forgotPassword()
	{
		EnPException::writeProcessLog('Login_Controller :: forgotPassword action called');
		try
		{
			//echo 'Comming Soon';exit;
			$this->FP_email= request('post','fpEmailAddress',0);
			$this->ForgotValidate();
			if($this->P_Status){$this->getPasswordDetail();}
			if($this->P_Status){$this->sendEmailForForgotPassword();}
			if($this->P_Status==0){$this->SetStatus(false,$this->L_ErrorCode);redirect(URL."login");}
			
		}
		catch(Exception $e)
		{
			EnPException::exceptionHandler($e);
		}
		$this->SetStatus(true,$this->L_ConfirmCode);redirect(URL."login");
	}
		
	public function resetPassword($mailID,$userID,$Datetime)
	{
		date_default_timezone_set('America/New_York');
		$this->mailID  		= keyDecrypt(urldecode($mailID));
		$this->userID  		= keyDecrypt(urldecode($userID));
		$this->DateTime  	= keyDecrypt(urldecode($Datetime));
		$CurrentDate		= getDateTime();
		//echo $CurrentDate."Url Link ".$this->DateTime;exit;
		$Status 			= getDateDiffernce($this->DateTime,$CurrentDate);
		if($Status)
		{
			$fields = array('Admin_ID','Admin_EmailID');
			$where = " AND Admin_EmailID = '".$this->mailID."' AND Admin_ID = '".$this->userID."'" ;
			$this->CheckUserExist = $this->L_obj->ResetPassword_DB($fields,$where);
			if(count($this->CheckUserExist>0))
			{
				$this->tpl->assign("UserID",$this->CheckUserExist[0]['Admin_ID']);
				$this->tpl->assign("EmailID",$this->CheckUserExist[0]['Admin_EmailID']);
				$this->tpl->draw("login/resetpassword");
			}
			else
			{
				$this->SetStatus(false,'E9008');redirect(URL."login");
			}
		}
		else
		{
			$this->SetStatus(false,'E9018');redirect(URL."login");
		}
		
		
	}
	
	private function ProcessResetPassword()
	{
		$this->EmailID 			= keyDecrypt(request('post','emailId',0));
		$this->UserId 			= keyDecrypt(request('post','userId',0));
		$this->NewPassword 		= request('post','newPassword',0);
		$this->ConformPassword 	= request('post','confirmPassword',0);
		if(!is_numeric($this->UserId))
		{
			$this->SetStatus(false,'E2001');
			redirect(URL."login");
		}
		$fields = array('Admin_ID','Admin_EmailID','Admin_UserName');
		$where = " AND Admin_EmailID = '".$this->EmailID."' AND Admin_ID = '".$this->UserId."'" ;
		$this->CheckUserExist = $this->L_obj->ResetPassword_DB($fields,$this->EmailID,$this->UserId);
		if(count($this->CheckUserExist>0))
		{
			$this->ResetPasswordValidate();
			if($this->P_Status){$this->UpdateResetPassword();}
			if($this->P_Status){$this->SendEmail($this->CheckUserExist[0]['Admin_UserName']);}
			if($this->P_Status==0){$this->SetStatus(false,$this->L_ErrorCode);redirect(URL."login/resetPassword/".keyEncrypt($this->EmailID)."/".keyEncrypt($this->UserId));}
			if($this->P_Status)
			{
				$this->SetStatus(true,'C9005');
			}
			else
			{
				$this->SetStatus(false,'E9017');
			}
			
		}
		redirect(URL."login");
	}
	
	private function ResetPasswordValidate()
	{
		if($this->P_Status == 1 && $this->UserId == NULL)		$this->setErrorMsg('E9011');
		if($this->P_Status == 1 && $this->EmailID == NULL)	$this->setErrorMsg('E9012');
		if($this->P_Status == 1 && $this->NewPassword == NULL)		$this->setErrorMsg('E9013');
		if($this->P_Status == 1 && $this->ConformPassword == NULL)	    $this->setErrorMsg('E9014');
		if($this->P_Status == 1 && $this->ConformPassword != $this->NewPassword )	$this->setErrorMsg('E9015');
	}
	
	private function UpdateResetPassword()
	{
		
		$DataArray	= array('Admin_Password'=>db::PassEnc($this->NewPassword));
		$where = " Admin_EmailID = '".$this->EmailID."' AND Admin_ID = ".$this->UserId ;
		if($this->L_obj->UpdatePassword($DataArray,$where))
		{
			$this->setConfirmationMsg('C9003');
		}
		else
		{
			$this->setErrorMsg('E9016');
		}
	}
	
	public function SendEmail($userName)
	{
		$this->senddate = getDateTime();
		
		 $enc_mailId=base64_encode($this->EmailID);
		 $enc_UserId=base64_encode($this->UserId);
		 $date = date('Y-m-d H:i:s');
		 $ts=strtotime($date);
		
		$this->FP_Eobj=LoadLib('Email');
		if(is_object($this->FP_Eobj))
		{
			$this->FP_Eobj->tplId='Template2';				
			$this->FP_Eobj->tplVarArr=array('tpl_name'=>$userName);
			$this->FP_Eobj->ntfVarArr=array('ntf_EmailTo'=>$this->EmailID);
			$status=$this->FP_Eobj->sendEmail();
		}
		if(!$status) {
			$this->setErrorMsg('E9010');
		} else {
			$this->setConfirmationMsg('C9004');
		}
	}
}
?>