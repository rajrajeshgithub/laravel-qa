<?php
	class UserType1_Model extends Model
	{
		public $UserID, $UserDetailsArray,$Pageurl,$LoginUserDetail;
		public $Pstatus = 1;
		public $FirstName,$LastName,$Address1,$Address2,$City,$State,$Country,$Zip,$PhoneNumber,$Mobile,$EmailAddress,$Password,$ConfirmPassword,$Gender,$UserType,$DOB,
			   $RegDate,$LastLoginDate,$UpdateDate,$Deleted,$UserIP,$Status,$UploadProfileImage,$CompanyName,$Designation,$ExistProfileImg,$UpdatableDataArray,$FacebookID;
		public $ExistPassword;
		public $MailID,$DateTime;
		public $UT1ProfilePhysPath;
		public $NewReg;
		
		
		function __construct()
		{
			
		}
		public function GetNPOProfileDetail($DataArray,$UserId)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npouserrelation NUR
					   LEFT JOIN ".TBLPREFIX."npodetails N ON (NUR.NPOEIN=N.NPO_EIN)";
			$Where	= " WHERE NUR.USERID=".$UserId;
			$Res	= db::get_row($Sql.$Where);
			return $Res;
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
					   LEFT JOIN ".TBLPREFIX."registeredusers RU ON (RU.RU_ID=NUR.USERID)";
			$Res	= db::get_row($Sql.$Where);
			return $Res;
		}
		
		public function RegisterDB()
		{
			$this->ValidateLoginRegisterInput();
			if($this->Pstatus)
			{	
				$this->UserID	= $this->AddUser();
				return $this->UserID;
			}
		}
		
		public function ValidateLoginRegisterInput()
		{
			if(trim($this->FirstName)    == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8001');$this->Pstatus=0;}
			if(trim($this->LastName)     == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8002');$this->Pstatus=0;}				
			if(trim($this->Address1)     == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8003');$this->Pstatus=0;}				
			if(trim($this->City)         == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8004');$this->Pstatus=0;}				
			if(trim($this->Zip)          == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8005');$this->Pstatus=0;}					
			if(trim($this->Country)      == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8006');$this->Pstatus=0;} 				
			if(trim($this->State)        == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8007');$this->Pstatus=0;}					
			//if(trim($this->PhoneNumber)  == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8008');$this->Pstatus=0;}		
			if(trim($this->EmailAddress) == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8009');$this->Pstatus=0;}			
			//if(trim($this->Password)     == "" && $this->Pstatus == 1){$this->SetStatus(0,'E8010');$this->Pstatus=0;}				
			//if(trim($this->Password)     != trim($this->ConfirmPassword) && $this->Pstatus == 1) {$this->SetStatus(0,'E8011');$this->status=0;}
			if(trim($this->Password)=="") $this->Password=rand_str(); //"Pass@123";
		}
		
		public function AddUser()
		{
			$DataArray	= array("RU_FistName"=>$this->FirstName,"RU_LastName"=>$this->LastName,"RU_EmailID"=>$this->EmailAddress,"RU_Phone"=>$this->PhoneNumber,"RU_Mobile"=>$this->Mobile,
								"RU_City"=>$this->City,"RU_State"=>$this->State,"RU_ZipCode"=>$this->Zip,"RU_Country"=>$this->Country,"RU_Address1"=>$this->Address1,
								"RU_Address2"=>$this->Address2,"RU_Password"=>PassEnc($this->Password),"RU_Status"=>$this->Status,"RU_UserType"=>$this->UserType,"RU_RegDate"=>$this->RegDate,			
								"RU_UpdatedDate"=>$this->UpdateDate,"RU_UserIP"=>$this->UserIP);
			db::insert(TBLPREFIX."registeredusers",$DataArray);							
			return db::get_last_id();	
		}
		
		public function LoginDB()
		{
			if($this->FacebookID!='' && $this->EmailAddress!='')
			{
				$this->UserID = $this->CheckEmailFBID();
				if(!$this->UserID && $this->Pstatus == 1)
				{	
					$this->UserID = $this->AddUserFB();	
					$this->NewReg = 1;
					$this->SetStatus(1,'C8001');					
				}
				else
				{
					$this->NewReg = 0;
					$this->SetStatus(1,'C9001');
				}
				$this->SetUserSession();
			}
			else
			{
				$this->ValidateLoginInput();
				if($this->Pstatus)
				{
					$Condition	= " AND RU.RU_EmailID='".$this->EmailAddress."' AND RU.RU_Password='".PassEnc($this->Password)."' AND RU_UserType='1'";
					$this->GetUserDetails(array('RU.RU_Status','RU.RU_Deleted','RU_ID'),$Condition);
					
					if($this->UserDetailsArray)
					{
						if($this->IsActiveAccount())
						{
							$this->Pstatus= 1;
							$this->UserID	= $this->UserDetailsArray['RU_ID'];
							$this->SetUserSession();
							$this->UpdateLastLogin();
							$this->SetStatus(1,'C9001');		
						}
						else
						{
							$this->Pstatus= 0;	
							$this->SetStatus(0,'E9004');
						}
					}
					else
					{
						$this->Pstatus= 0;		
						$this->SetStatus(0,'E9003');
					}	
				}
			}			
		}
		
		public function ValidateLoginInput()
		{
			if(trim($this->EmailAddress) == "" && $this->Pstatus == 1){$this->Pstatus=0;$this->SetStatus(0,'E9001');}
			if(trim($this->Password) 	 == "" && $this->Pstatus == 1){$this->Pstatus=0;$this->SetStatus(0,'E9002');} 	
		}
		
		public function GetUserDetails($DataArray=array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','RU.RU_State','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."registeredusers RU";
			$Where	= " WHERE 1=1";
			if($Condition == NULL)
			{
				$this->LoginUserDetail	= getSession('Users','UserType1');
				$this->UserID			=  keyDecrypt($this->LoginUserDetail['user_id']);
				if(isset($this->UserID) && $this->UserID!='')
				{
					$Condition				=  " AND RU.RU_ID=".$this->UserID; 	
				}
			}
			if($Condition !=NULL && $Condition!='')
			{
				$Where	.= $Condition;
			//echo $Sql.$Where; exit;
				$Res	= db::get_row($Sql.$Where);
			}
			$this->UserDetailsArray	= (count($Res)>0)?$Res:array();
			
			return $this->UserDetailsArray;
		}
		
		public function SetUserSession()
		{
			$DataArray	= array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_Password','RU.RU_Status','RU.RU_UserType');
			$Condition	= " AND RU.RU_ID=".$this->UserID;
			$this->GetUserDetails($DataArray,$Condition);	
			if($this->UserDetailsArray)
			{
				$DetailArr	= array('user_id'=>keyEncrypt($this->UserDetailsArray['RU_ID']),'user_firstname'=>$this->UserDetailsArray['RU_FistName'],
												  'user_lastname'=>$this->UserDetailsArray['RU_LastName'],'user_email'=>$this->UserDetailsArray['RU_EmailID'],
												  'user_fullname'=>$this->UserDetailsArray["RU_FistName"]." ".$this->UserDetailsArray["RU_LastName"],
												  'user_type'=>$this->UserDetailsArray['RU_UserType'],"is_login"=>1);
				setSession("Users",$DetailArr,"UserType1");
				set_Cookie("Users",getSession("Users"));
			}
		}
		
		public function UpdateLastLogin()
		{
			db::update(TBLPREFIX."registeredusers",array('RU_LastLoginDate'=>$this->LastLoginDate),"RU_ID=".$this->UserID);	
		}
		
		public function IsActiveAccount()
		{
			if($this->UserDetailsArray['RU_Status']==1 && $this->UserDetailsArray['RU_Deleted']==0)
			{
				return true;	
			}
			else
			{
				return false;	
			}
		}
		
		public function ChangePasswordDB()
		{
			$this->ValidateChangePasswordInput();
			if($this->Pstatus)
			{	
				$DataArray	= array('RU_Password'=>PassEnc($this->Password),"RU_UpdatedDate"=>$this->UpdateDate);
				db::update(TBLPREFIX."registeredusers",$DataArray,"RU_ID=".$this->UserID);
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
			$LoginUserDetail	= getSession('Users','UserType1');
			$this->UserID	= keyDecrypt($LoginUserDetail['user_id']);
			$Sql	= "SELECT RU_ID FROM ".TBLPREFIX."registeredusers WHERE RU_Password = '".PassEnc($Password)."' AND RU_ID=".$this->UserID;	
			$Row	= db::get_row($Sql);
			return (isset($Row['RU_ID']) && $Row['RU_ID'] > 1)?true:false;
		}
		
		public function ForgetPasswordDB()
		{
			$this->ValidateForgetPasswordInput();
			if($this->Pstatus)
			{
				date_default_timezone_set('America/New_York');
				$Condition	= " AND RU.RU_EmailID='".$this->EmailAddress."'";
				$this->GetUserDetails(array('RU.RU_ID','CONCAT(	RU_FistName," ",RU_LastName) as Name'),$Condition);
				
				$URLDateTime	= date("Y-m-d H:i:s", strtotime('+10 minutes', strtotime(getDateTime())));
				$Enc_UserID		= keyEncrypt($this->UserDetailsArray['RU_ID']);
				$Enc_MailID		= keyEncrypt($this->EmailAddress);
				$Enc_DateTime 	= keyEncrypt($URLDateTime);
				$QueryString	= urlencode($Enc_MailID)."/".urlencode($Enc_UserID)."/".urlencode($Enc_DateTime);
				 $this->Pageurl	= URL."ut1/ResetPassword/".$QueryString; 
			}
		}
		
		private function ValidateForgetPasswordInput()
		{
			if(trim($this->EmailAddress) == "" && $this->Pstatus == 1){$this->SetStatus(0,'E7005');$this->Pstatus=0;}
			if($this->CheckEmailExistance($this->EmailAddress)==false){$this->SetStatus(0,'E7006');$this->Pstatus=0;}
		}
		
		public function VerifyResetPassURL()
		{
			$CurrentDate	= getDateTime();
			$this->Pstatus	= getDateDiffernce($this->DateTime,$CurrentDate);	
			if($this->Pstatus)
			{
				$DataArray	= array('RU.RU_ID','RU.RU_EmailID');
				$Condition	= " AND RU.RU_EmailID = '".$this->MailID."' AND RU.RU_ID = '".$this->UserID."'" ;
				$this->GetUserDetails($DataArray,$Condition);
				if($this->UserDetailsArray)
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
			if(!is_numeric($this->UserID))			  {$this->Pstatus= 0;$this->SetStatus(0,'E7002');}
			if(trim($this->Password) == "" && $this->Pstatus == 1) {$this->SetStatus(0,'E7007');$this->Pstatus=0;}	
			if(trim($this->Password)     != trim($this->ConfirmPassword) && $this->Pstatus == 1) {$this->SetStatus(0,'E7008');$this->status=0;}
			if($this->Pstatus == 1)
			{
				$DataArray	= array("RU_Password"=>PassEnc($this->Password),"RU_UpdatedDate"=>getDateTime());
				if(db::update(TBLPREFIX."registeredusers",$DataArray,"RU_ID=".$this->UserID))
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
		
		public function UpdateDB()
		{
			$this->ValidateManageProfileInput();
			if($this->Pstatus)
			{
				$this->UpdatableDataArray	= array('RU_FistName'=>$this->FirstName,'RU_LastName'=>$this->LastName,'RU_CompanyName'=>$this->CompanyName,'RU_Designation'=>$this->Designation,
													'RU_Phone'=>$this->PhoneNumber,'RU_Mobile'=>$this->Mobile,'RU_City'=>$this->City,'RU_State'=>$this->State,'RU_ZipCode'=>$this->Zip,
													'RU_Country'=>$this->Country,'RU_Address1'=>$this->Address1,'RU_Address2'=>$this->Address2,'RU_Gender'=>$this->Gender,
													'RU_DOB'=>$this->DOB,'RU_UpdatedDate'=>$this->UpdateDate);
													
				$this->UploadImage();									
				if($this->Pstatus == 1)
				{
					$LoginUserDetail	= getSession('Users');
					$this->UserID		= keyDecrypt($LoginUserDetail['UserType1']['user_id']);
					db::update(TBLPREFIX."registeredusers",$this->UpdatableDataArray,"RU_ID=".$this->UserID);	
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
		}
		
		private function UploadImage()
		{
			$this->UploadProfileImage	= count($this->UploadProfileImage)>0?$this->UploadProfileImage:'';
			$ImageFile  				= $this->UploadProfileImage;
			$Ext						= file_ext($ImageFile['name']);
			$CustomName	 				= $ImageFile['name'];
			$Image						= strUnique().".".$Ext;
			
			$this->UT1ProfilePhysPath	= UT1PROFILE_LARGE_IMAGE_DIR.$Image;			
			if($this->ExistProfileImg!=NULL && $ImageFile['name'] != NULL)
			{
				$oldExt				= explode('.',$this->ExistProfileImg);
				unlink(UT1PROFILE_LARGE_IMAGE_DIR.$this->ExistProfileImg);
				unlink(UT1PROFILE_MEDIUM_IMAGE_DIR.$this->ExistProfileImg);
				unlink(UT1PROFILE_THUMB_IMAGE_DIR.$this->ExistProfileImg);
			}
			
			if($ImageFile['name'] != "")
			{
				if(move_uploaded_file($ImageFile["tmp_name"],$this->UT1ProfilePhysPath))
				{
					$this->UpdatableDataArray['RU_ProfileImage']	= $Image;
					$this->Pstatus	= 1;
					$this->CreateMediumImg($Image);
					$this->CreateThumbImg($Image);
				}
				else
				{
					$this->Pstatus	= 0;
					$this->SetStatus(0,'E11009');	
				}
			}
		}
		
		 private function CreateMediumImg($Image)
		 {
			 $objFile	= LoadLib('resize_image');	
			 $MediumImage		= UT1PROFILE_MEDIUM_IMAGE_DIR.$Image;
			 $objFile	= new resize_image($this->UT1ProfilePhysPath);
			 $objFile ->resizeImage(300, 400, 'auto');
			 $objFile ->saveImage($MediumImage, 100);
		}
	
		private function CreateThumbImg($Image)
		{
			$objFile	= LoadLib('resize_image');
			$ThumbImage	= UT1PROFILE_THUMB_IMAGE_DIR.$Image;
			$objFile	= new resize_image($this->UT1ProfilePhysPath);
			$objFile ->resizeImage(70, 70, 'crop');
			$objFile ->saveImage($ThumbImage, 100);
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
		
		public function FBRegistration_DB()
		{
			if(trim($this->EmailAddress) =="" && $this->Pstatus == 1){$this->SetStatus(0,'E11011');$this->Pstatus=0;}	
			if(!$this->checkEmailFBid() && $this->Pstatus == 1)
			{
				$this->UserID = $this->AddUserFB();	
				$this->NewReg = 1;	
			}
			else
			{
				$this->UserID = $this->checkEmailFBid();
				$this->NewReg = 0;	
			}
		}
		
		public function AddUserFB()
		{
			$DataArray	= array("RU_FistName"=>$this->FirstName,"RU_LastName"=>$this->LastName,"RU_EmailID"=>$this->EmailAddress,"RU_UserType"=>$this->UserType,"RU_Status"=>$this->Status,
								"RU_RegDate"=>$this->RegDate,"RU_Password"=>PassEnc($this->Password),"RU_UpdatedDate"=>$this->UpdateDate,"RU_UserIP"=>$this->UserIP,"RU_Gender"=>$this->Gender,
								"RU_LastLoginDate"=>$this->LastLoginDate,"RU_FacebookID"=>$this->FacebookID);
			db::insert(TBLPREFIX."registeredusers",$DataArray);							
			return db::get_last_id();	
		}
		
		private function CheckEmailFBID()
		{
			$Sql	= "SELECT RU_ID FROM ".TBLPREFIX."registeredusers WHERE 1=1 and (RU_EmailID='".$this->EmailAddress."' or RU_FacebookID='".$this->FacebookID."') AND RU_UserType='1'";
			$Row	= db::get_row($Sql);
			return (isset($Row['RU_ID']) && $Row['RU_ID'] > 0)?$Row['RU_ID']:false;		
		}
		
		public function CheckEmailExistance($EmailAddress)
		{
			$Sql	= "SELECT RU_ID FROM ".TBLPREFIX."registeredusers WHERE RU_EmailID='".$EmailAddress."' AND RU_UserType='1'";
			$Row	= db::get_row($Sql);
			return (isset($Row['RU_ID']) && $Row['RU_ID'] > 0)?true:false;
		}
		
		public function checkLogin($loginDetail)
		{
			$LoginUserId = 0;
			if(isset($loginDetail['UserType1']))
			{
				if(is_array($loginDetail['UserType1']))
				{
					if(isset($loginDetail['UserType1']['is_login']) && $loginDetail['UserType1']['is_login'] == 1)
					{
						set_Cookie("Users", $loginDetail);
						$LoginUserId = KeyDecrypt($loginDetail['UserType1']["user_id"]);
					}
				}
			}
			return $LoginUserId;
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
		
		public function logoutUT1()
		{
			setSession("Users",array(),"UserType1");
			set_cookie("Users",array(),"UserType1"); 
			clearstatcache();		
		}
		public function updateProcessLog($DataArray)
		{
			
			$FieldArray = array("DateTime"=>$DataArray['Date'],
								"ModelName"=>$DataArray['Model'],
								"ControllerName"=>$DataArray['Controller'],
								"UserType"=>$DataArray['UType'],
								"UserName"=>$DataArray['UName'],
								"UserID"=>$DataArray['UID'] = ($DataArray['UID']!='')?$DataArray['UID']:0,
								"RecordID"=>$DataArray['RecordId'] = ($DataArray['RecordId']!='')?$DataArray['RecordId']:0,								
								"SortMessage"=>$DataArray['SMessage'],
								"LongMessage"=>$DataArray['LMessage'],);							
			
			db::insert(TBLPREFIX."processlog",$FieldArray);
			$id = db::get_last_id();
			$id = ($id)?$id:0;
			return $id;
		}
	}
?>