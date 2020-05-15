<?php
	class UserType2_Model extends Model
	{
		public $userId, $userDetailsArray,$LoginUserDetail;
		public $Pstatus = 1;
		public $Ein,$NPOID,$ConfirmCode,$FirstName,$LastName,$CompanyName,$Designation,$Address1,$Address2,$City,$State,$Country,$Zip,$PhoneNumber,$Mobile,$EmailAddress,$Password,
				$ConfirmationPassword;
		public $Gender,$UserType,$DOB,$RegDate,$LastLoginDate,$UpdateDate,$Deleted,$UserIP,$Status,$UploadProfileImage,$ExistProfileImg;		
		public $EmailID,$stayLogin,$UniqueCode,$NPODetails;
		public $ExistPassword,$Pageurl,$DateTime;
		
		
		function __construct()
		{
			$this->LoginUserDetail	= getSession('Users','UserType2');
			$this->uid				= keyDecrypt($this->LoginUserDetail['user_id']);
		}
		
		
		public function ValidateAddUser()
		{
			if(trim($this->FirstName) == ""){$this->SetStatus(0,'E4003');$this->Pstatus=0;}
			if(trim($this->LastName) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4004');$this->Pstatus=0;}
			if(trim($this->EmailAddress) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4005');$this->Pstatus=0;}
			if(trim($this->CompanyName) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4006');$this->Pstatus=0;}
			if(trim($this->PhoneNumber) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4008');$this->Pstatus=0;}
			if(trim($this->City) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4009');$this->Pstatus=0;}
			if(trim($this->State) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4010');$this->Pstatus=0;}
			if(trim($this->Country) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4011');$this->Pstatus=0;}
			if(trim($this->Address1) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4012');$this->Pstatus=0;}
			if(trim($this->Password) == "" && $this->Pstatus==1){$this->SetStatus(0,'E4013');$this->Pstatus=0;}
			if((trim($this->Password) != trim($this->ConfirmPassword))  && $this->Pstatus==1){$this->SetStatus(0,'E4014');$this->Pstatus=0;}
		}
		
		public function UpdateUser($arreyFields)
		{
			if($this->userId!='')
			{
				return db::update(TBLPREFIX."npouserrelation",$arreyFields,"USERID=".$this->userId);
			}
		}
		
		public function AddUser()
		{
			$this->ValidateAddUser();
			if($this->Pstatus)
			{
				$DataArray			= array('RU_FistName'=>$this->FirstName,'RU_LastName'=>$this->LastName,'RU_EmailID'=>$this->EmailAddress,'RU_CompanyName'=>$this->CompanyName,
											'RU_Designation'=>$this->Designation,'RU_Phone'=>$this->PhoneNumber,'RU_Mobile'=>$this->Mobile,'RU_City'=>$this->City,'RU_State'=>$this->State,
											'RU_ZipCode'=>$this->Zip,'RU_Country'=>$this->Country,'RU_Address1'=>$this->Address1,'RU_Address2'=>$this->Address2,
											'RU_Password'=>PassEnc($this->Password),'RU_Status'=>$this->Status,'RU_RegDate'=>$this->RegDate,'RU_UpdatedDate'=>$this->UpdateDate,
											'RU_UserIP'=>$this->UserIP,'RU_UserType'=>$this->UserType);
				db::insert(TBLPREFIX."registeredusers",$DataArray);							
				$this->userId	= db::get_last_id();
				if($this->userId > 0)
				{
					$Log	= "User created on ".getDateTime();
					$DataArray	= array('NPOID'=>$this->NPOID,'NPOEIN'=>$this->Ein,'USERID'=>$this->userId,'NPOConfirmationCode'=>$this->ConfirmCode,'Status'=>'0',
										'RegistrationDate'=>$this->RegDate,'Log'=>$Log,'CreatedDated'=>$this->RegDate,'LastUpdatedDate'=>$this->UpdateDate,'Active'=>'1');
					db::insert(TBLPREFIX."npouserrelation",$DataArray);
					if(db::get_last_id() > 0)
					{
						$this->Pstatus	= 1;
						$this->SetStatus(1,'C4001');
					}
					else
					{
						$this->Pstatus	= 0;
						$this->SetStatus(0,'E1008');	
					}
				}
				else
				{
					$this->Pstatus	= 0;
					$this->SetStatus(0,'E1008');
				}
			}
			else
			{
				$this->Pstatus	= 0;
			}

		}
		
		
		public function GetNPOProfileDetail($DataArray,$UserId)
		{
			$Fields	= implode(",",$DataArray);	
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npouserrelation NUR
					   LEFT JOIN ".TBLPREFIX."npodetails N ON (NUR.NPOEIN=N.NPO_EIN)";
			$Where	= " WHERE NUR.USERID=".$UserId;//echo $Sql.$Where;exit;
			$Res	= db::get_row($Sql.$Where);
			return $Res;
		}
	
		public function UpdateNpoLogo($DataArray,$UserID)
		{
			return db::update(TBLPREFIX."npouserrelation",$DataArray,"USERID=".$UserID);	
		}
		
		public function AddUserFacebook()
		{
			$this->userId;				
		}
		
		public function Processlogin()
		{
			if($this->Pstatus)$this->ValidateLogin();	
			if($this->Pstatus)$this->updateAcessDate();
			if($this->Pstatus)$this->SetSession();
		}
		
		public function ValidateLogin()
		{
			if($this->EmailID=='' && $this->Pstatus==1)		  		{$this->Pstatus=0;$this->SetStatus(0,'E2001');}
			if($this->Password=='' && $this->Pstatus==1)		  	{$this->Pstatus=0;$this->SetStatus(0,'E2002');}
			if($this->VerifyLogin()==false && $this->Pstatus==1)	{$this->Pstatus=0;$this->SetStatus(0,'E2003');}	
			if($this->matchPassword()==false && $this->Pstatus==1)	{$this->Pstatus=0;$this->SetStatus(0,'E2004');}
		}
		
		private function matchPassword()
		{
			$flagProcess=true;	
			$password = PassDec($this->userDetailsArray['RU_Password']);
			if($password!=$this->Password)
			{
				$flagProcess=false;
			}
			return $flagProcess;
		}
		
		public function VerifyLogin()
		{
			$fieldArray	= array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_Password','RU.RU_Status','RU.RU_UserType','RU.RU_UserName',
								'RU.RU_FacebookID','NRU.Status');
			$Condition	= " AND RU.RU_EmailID='".$this->EmailID."' AND RU.RU_UserType=2";					
			$this->GetUserDetails($fieldArray,$Condition);
			if($this->userDetailsArray)
			{
				$this->Pstatus	= 1;
				return true;
			}
			else
			{
				$this->Pstatus	= 0;
				$this->SetStatus(0,'E2003');
				return false;
			}
		}
		
			
		
		public function SetSession()
		{
			
			//dump($this->userDetailsArray);
			//$this->userDetailsArray["RU_ID"]
			$DetailArr	= array('user_id'=>keyEncrypt($this->userDetailsArray["RU_ID"]),'user_firstname'=>$this->userDetailsArray["RU_FistName"],'user_lastname'=>$this->userDetailsArray["RU_LastName"],'user_email'=>$this->userDetailsArray["RU_EmailID"],'user_fullname'=>$this->userDetailsArray["RU_FistName"]." ".$this->userDetailsArray["RU_LastName"],
												  'user_type'=>$this->UserType,"is_login"=>1);
			setSession("Users",$DetailArr,'UserType2');
			set_Cookie("Users",getSession("Users"));
		}
		
		private function updateAcessDate()
		{
			try
			{
				$DataArray	= array('RU_LastLoginDate'=>getDateTime());
				if($this->UpdateLoginDate($DataArray,$this->userDetailsArray["RU_ID"]))
				{
					$this->Pstatus	= 1;
				}
				else
				{
					$this->Pstatus	= 0;
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);
			}
		}
		
		public function UpdateLoginDate($DataArray,$UserID)
		{
			return db::update(TBLPREFIX.'registeredusers',$DataArray,'RU_ID='.$UserID);
		}
		
		public function GetNPODetail($DataArray,$Condition=NULL)
		{
			$Where	= " WHERE 1=1";
			if($Condition != NULL)
			{
				$Where.=$Condition;	
			}	
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npodetails N
					   LEFT JOIN ".TBLPREFIX."npouserrelation NUR ON (N.NPO_EIN=NUR.NPOEIN)
					   LEFT JOIN ".TBLPREFIX."registeredusers RU ON (RU.RU_ID=NUR.USERID)";//echo $Sql.$Where;exit;
			$Res	= db::get_row($Sql.$Where);
			return $Res;		   
		}
		
		public function checkLogin($loginDetail)
		{
			$LoginUserId=0;
			
			if(is_array($loginDetail['UserType2']))
			{
				if($loginDetail['UserType2']['is_login'] == 1)
				{
					set_Cookie("Users",$loginDetail);
					$LoginUserId=KeyDecrypt($loginDetail['UserType2']["user_id"]);
				}
			}
			return $LoginUserId;
		}
		
		public function ChangePasswordDB()
		{
			$this->ValidateChangePasswordInput();
			if($this->Pstatus)
			{	
				$DataArray	= array('RU_Password'=>PassEnc($this->Password),"RU_UpdatedDate"=>$this->UpdateDate);
				db::update(TBLPREFIX."registeredusers",array('RU_Password'=>PassEnc($this->Password)),"RU_ID=".$this->UserID);
				if(db::is_row_affected())
				{
					$this->Pstatus	= 1;	
					$this->SetStatus(1,'C10001');
				}
				else
				{
					$this->Pstatus	= 0;
					$this->SetStatus(0,'E10002');
				}
			}	
		}
		
		public function ValidateChangePasswordInput()
		{
			if(trim($this->ExistPassword) == "" && $this->Pstatus == 1){$this->Pstatus=0;$this->SetStatus(0,'E10004');}
			if(trim($this->Password) 	  == "" && $this->Pstatus == 1){$this->Pstatus=0;$this->SetStatus(0,'E10005');}			
			if(trim($this->Password) != trim($this->ConfirmPassword)){$this->Pstatus=0;$this->SetStatus(0,'E10001');}	
			if(!$this->CheckCurrentPasswordExistance($this->ExistPassword) && $this->Pstatus == 1){$this->Pstatus=0;$this->SetStatus(0,'E10006');}
		}
		
		private function CheckCurrentPasswordExistance($Password)
		{
			$LoginUserDetail	= getSession('Users','UserType2');
			$this->UserID	= keyDecrypt($LoginUserDetail['user_id']);
			$Sql	= "SELECT RU_ID FROM ".TBLPREFIX."registeredusers WHERE RU_Password = '".PassEnc($Password)."' AND RU_ID=".$this->UserID;	
			$Row	= db::get_row($Sql);
			return (isset($Row['RU_ID']) && $Row['RU_ID'] > 1)?true:false;
		}
		
		/* dashboard */
		public function GetUserDetails($DataArray=array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','NRU.NPOID','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."registeredusers RU  
					   LEFT JOIN ".TBLPREFIX."npouserrelation NRU ON (RU.RU_ID=NRU.USERID)";
			$Where	= " WHERE 1=1";
			if($Condition == NULL)
			{
				$this->LoginUserDetail	= getSession('Users','UserType2');
				$this->UserID			=  keyDecrypt($this->LoginUserDetail['user_id']);
				$Condition				=  " AND RU.RU_ID=".$this->UserID; 	
			}
			$Where	.= $Condition; //echo $Sql.$Where;exit;
			$Res	= db::get_row($Sql.$Where);
			$this->userDetailsArray	= (count($Res)>0)?$Res:array();
			return $this->userDetailsArray;
		}
	
		public function UpdateDB()
		{
			$this->ValidateManageProfileInput();
			if($this->Pstatus)
			{
				$DataArray	= array('RU_FistName'=>$this->FirstName,'RU_LastName'=>$this->LastName,'RU_CompanyName'=>$this->CompanyName,'RU_Designation'=>$this->Designation,
													'RU_Phone'=>$this->PhoneNumber,'RU_Mobile'=>$this->Mobile,'RU_City'=>$this->City,'RU_State'=>$this->State,'RU_ZipCode'=>$this->Zip,
													'RU_Country'=>$this->Country,'RU_Address1'=>$this->Address1,'RU_Address2'=>$this->Address2,'RU_Gender'=>$this->Gender,
													'RU_DOB'=>$this->DOB,'RU_UpdatedDate'=>$this->UpdateDate);
				$LoginUserDetail	= getSession('Users');
				$this->UserID		= keyDecrypt($LoginUserDetail['UserType2']['user_id']);
				db::update(TBLPREFIX."registeredusers",$DataArray,"RU_ID=".$this->UserID);	
				if(db::is_row_affected())
				{
					$this->Pstatus	= 1;
					$this->SetStatus(1,'C11001');
				}
				else
				{
					$this->SetStatus(0,'E11010');
					$this->Pstatus	= 0;	
				}
				
			}	
		}
		
		
		public function ValidateManageProfileInput()
		{
			if(trim($this->FirstName)    == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11001');$this->Pstatus=0;}
			if(trim($this->LastName)     == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11002');$this->Pstatus=0;}				
			if(trim($this->Address1)     == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11003');$this->Pstatus=0;}				
			if(trim($this->City)         == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11004');$this->Pstatus=0;}				
			if(trim($this->Zip)          == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11005');$this->Pstatus=0;}					
			if(trim($this->Country)      == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11006');$this->Pstatus=0;} 				
			if(trim($this->State)        == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11007');$this->Pstatus=0;}					
			if(trim($this->PhoneNumber)  == "" && $this->Pstatus == 1){$this->SetStatus(0,'E11008');$this->Pstatus=0;}		
		}
		/* End */
		
		public function CodeVerificationDB()
		{
			$this->CodeValidate();
			if($this->Pstatus)
			{
				$this->NPODetails	= $this->GetNpoDetails($this->UniqueCode);	
			}
		}
		
		public function CodeValidate()
		{
			if(trim($this->UniqueCode) == "" && $this->Pstatus == 1){$this->SetStatus(0,'E4017');$this->Pstatus=0;}
			if($this->CheckCodeExistence($this->UniqueCode) == false && $this->Pstatus == 1){$this->SetStatus(0,'E4002');$this->Pstatus=0;}
			if($this->IsAlreadyRegistered($this->UniqueCode) && $this->Pstatus == 1){$this->SetStatus(0,'E4001');$this->Pstatus=0;}
		}
		
		public function CheckCodeExistence($UniqueCode)
		{
			$Sql	= "SELECT NPO_ID FROM ".TBLPREFIX."npodetails";
			$Where	= " WHERE NPO_UniqueCode='".$UniqueCode."' AND NPO_Status='1'";
			$Row	= db::get_row($Sql.$Where);
			if(isset($Row['NPO_ID']) && $Row['NPO_ID'] > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		public function IsAlreadyRegistered($Code)
		{
			$Sql	= "SELECT ID FROM ".TBLPREFIX."npouserrelation WHERE NPOConfirmationCode='".$Code."' AND Active='1'";	
			$Row	= db::get_row($Sql);
			if((isset($Row['ID']) && $Row['ID'] > 0))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		public function GetNpoDetails($UniqueCode)
		{
			$Sql	= "SELECT NPO_ID,NPO_EIN,NPO_Name,NPO_Street,NPO_City FROM ".TBLPREFIX."npodetails";
			$Where	= " WHERE NPO_UniqueCode='".$UniqueCode."' AND NPO_Status='1'";
			$Row	= db::get_row($Sql.$Where);
			return (count($Row)>0)?$Row:array();	
		}
		
		public function ForgetPasswordDB()
		{
			$this->ValidateForgetPasswordInput();
			if($this->Pstatus)
			{
				date_default_timezone_set('America/New_York');
				$Condition	= " AND RU.RU_EmailID='".$this->EmailID."'";
				$this->GetUserDetails(array('RU.RU_ID','CONCAT(	RU_FistName," ",RU_LastName) as Name'),$Condition);
				
				$URLDateTime	= date("Y-m-d H:i:s", strtotime('+10 minutes', strtotime(getDateTime())));
				$Enc_UserID		= keyEncrypt($this->userDetailsArray['RU_ID']);
				$Enc_MailID		= keyEncrypt($this->EmailID);
				$Enc_DateTime 	= keyEncrypt($URLDateTime);
				$QueryString	= urlencode($Enc_MailID)."/".urlencode($Enc_UserID)."/".urlencode($Enc_DateTime);
				$this->Pageurl	= URL."ut2/ResetPassword/".$QueryString; 
			}
		}
		private function ValidateForgetPasswordInput()
		{
			if(trim($this->EmailID) == "" && $this->Pstatus == 1){$this->SetStatus(0,'E7005');$this->Pstatus=0;}
			if($this->CheckEmailExistance($this->EmailID)==false){$this->SetStatus(0,'E7006');$this->Pstatus=0;}
		}
		public function CheckEmailExistance($EmailAddress)
		{
			$Sql	= "SELECT RU_ID FROM ".TBLPREFIX."registeredusers WHERE RU_EmailID='".$EmailAddress."' AND RU_UserType='2'";
			$Row	= db::get_row($Sql);
			return (isset($Row['RU_ID']) && $Row['RU_ID'] > 0)?true:false;
		}
		
		public function VerifyResetPassURL()
		{
			$CurrentDate	= getDateTime();
			$this->Pstatus	= getDateDiffernce($this->DateTime,$CurrentDate);	
			if($this->Pstatus)
			{
				$DataArray	= array('RU.RU_ID','RU.RU_EmailID');
				$Condition	= " AND RU.RU_EmailID = '".$this->EmailID."' AND RU.RU_ID = '".$this->userId."'" ;
				$this->GetUserDetails($DataArray,$Condition);
				if($this->userDetailsArray)
				{
					$this->Pstatus	= 1;
				}
				else
				{
					$this->Pstatus	= 0;
					$this->SetStatus(0,'E7002');
				}
			}
			else
			{
				$this->Pstatus	= 0;
				$this->SetStatus(0,'E7002');
			}
		}
		
			
		public function ResetDB()
		{
			if(!is_numeric($this->userId)){$this->Pstatus= 0;$this->SetStatus(0,'E7002');}
			if(trim($this->Password) == "" && $this->Pstatus == 1) {$this->SetStatus(0,'E7007');$this->Pstatus=0;}	
			if(trim($this->Password) != trim($this->ConfirmPassword) && $this->Pstatus == 1) {$this->SetStatus(0,'E7008');$this->status=0;}
			if($this->Pstatus == 1)
			{
				$DataArray	= array("RU_Password"=>PassEnc($this->Password),"RU_UpdatedDate"=>getDateTime());
				if(db::update(TBLPREFIX."registeredusers",$DataArray,"RU_ID=".$this->userId))
				{
					$this->Pstatus	= 1;	
					$this->SetStatus(1,'C7001');
				}
				else
				{
					$this->Pstatus	= 0;
					$this->SetStatus(0,'E7003');	
				}
			}
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