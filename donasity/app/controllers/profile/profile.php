<?php
	class Profile_Controller extends Controller
	{
		public $tpl, $loginDetail, $RegUserID, $P_status;
		
		public function __construct() { 
			$this->load_model("Profile", "objRegUser");
			$this->P_status = 1;
			$this->loginDetail = getSession('Users');
		}
		
		public function index($type='list') {
			$this->tpl = new view;
			switch(strtolower($type)) {
				case 'update':
					$this->update();
				 	break;	
				default:
					$this->Edit();
					$this->tpl->draw("profile/index");	
			}
		}
		
		private function Edit() {
			EnPException::writeProcessLog('Profile_Controller :: Edit action to edit registereduser details & RegUser_id=>'.$this->RegUserID);
			
			$this->RegUserID = getsession('DonasityLoginDetail', 'user_id');
			$field = array( "RU_ID as ID", "RU_FistName", "RU_LastName", "RU_CompanyName", "RU_EmailID", "RU_Phone", "RU_City", "RU_State", "RU_ZipCode", "RU_Country", "RU_Address1", "RU_UserType", "RU_Mobile", "RU_Address2", "RU_Gender", "RU_DOB", "RU_Password", "RU_Status", "RU_Deleted", "RU_RegDate", "RU_UpdatedDate", "RU_ProfileImage", "RU_Designation", "RU_FacebookID", "RU_UserName" );
			$where      					= array("RU_ID"=>$this->RegUserID);
			$DonorDetail					= $this->objRegUser->GetRegUserListing($field,$where);
			$DonorDetail[0]['RU_Password'] 	= PassDec($DonorDetail[0]['RU_Password']);
			$countriesList					= $this->objRegUser->getCountriesList();
			
			for($i = 1; $i <= 31; $i++)
				$Date[] = $i;
			
			$LastYear	= date('Y') - 11;
			$year 		= range($LastYear, 1900);	 
			$UserType   = $GLOBALS['usertype'];
			$Month     	= $GLOBALS['month'];
			
			$this->tpl->assign("action", 'update');
			$this->tpl->assign('Date', $Date);
			$this->tpl->assign('Year', $year);
			$this->tpl->assign('Month', $Month);
			$this->tpl->assign('UserType', $UserType);
			$this->tpl->assign("CountryList", $countriesList);
			$this->tpl->assign("RegUserID", $this->RegUserID);
			$this->tpl->assign("RegUserDetail", $DonorDetail[0]);
		}
		
		 public function getStateList($countryAbbr,$stateAbbr) {
		   $html='<option value="">--select--</option>';
		   $stateList=$this->objRegUser->getStateList($countryAbbr);
			
		   if(count($stateList) > 0) {
			   for($s = 0; $s < count($stateList); $s++) {
				   if(trim($stateAbbr) != '') {
					   if($stateList[$s]['State_Value'] == $stateAbbr)
						   $sel = 'selected';
					   else 
						   $sel = '';
					} else 
						$sel = '';
						
					$html .= '<option value="' . $stateList[$s]['State_Value'] . '" ' . $sel . '>' . $stateList[$s]['State_Name'] . '</option>';   
			   }
		   }
		   echo $html;
		   exit;
	   }
	   
	   private function Update() {
			EnPException::writeProcessLog('Profile_Controller :: Update action to Update RegUser details & RegUserID=>' . $this->RegUserID);
			try
			{	
				$this->getFormData();
				$this->ValidateFormData();
				
				$Condition = " AND RU_ID!= ".$this->RegUserID;
				if($this->P_status) 
					$this->CheckDuplicacyForEmail($Condition);
				if($this->P_status) 
					$this->CheckDuplicacyForUserName($Condition);
				if($this->P_status) 
					$this->CheckDuplicacyForFacebookID($Condition);
				
				if($this->P_status == 0) {
					$this->SetStatus(false, $this->P_ErrorCode);
					redirect(URL . "profile");
				}
					
				unset($this->FieldArr["RU_RegDate"]);
				unset($this->FieldArr["RU_UserIP"]);
				
				$Status	= $this->objRegUser->UpdateDonorDetail_DB($this->FieldArr, $this->RegUserID);
				
				if($Status) {
					if($_FILES['R_profileImage']['name'] != '') {
						if($_FILES['R_profileImage']['error'] == 0) 
							$this->ProfileImage() ? $this->SetStatus(true,'C7002') : $this->SetStatus(false,'E7020');
					} else
						$this->SetStatus(true, 'C7002');
				} else
					$this->SetStatus(false, 'E7009');
				
				/*----update process log------*/
				$userType 	= '';
				$userID 	= 0;
				$userName	= '';		
				if(isset($this->loginDetail['UserType1']['is_login'])){
					$userType 	= 'UT1';
					$userID 	= keyDecrypt($this->loginDetail['UserType1']['user_id']);
					$userName	= $this->loginDetail['UserType1']['user_fullname'];
				}
				if(isset($this->loginDetail['UserType2']['is_login'])) {
					$userType 	= 'UT2';
					$userID 	= keyDecrypt($this->loginDetail['UserType2']['user_id']);
					$userName	= $this->loginDetail['UserType2']['user_fullname'];
				}
				
				$sMessage = "Error in update donor profile.";
				$lMessage = "Error in update donor(id=$this->RegUserID) profile.";
				if($Status) {
					$sMessage = "Donor profile has updated successfully.";
					$lMessage = "Donor profile(id=$this->RegUserID) has updated successfully.";
				}
							
				$DataArray = array(	
					"UType"			=>$userType,
					"UID"			=>$userID,
					"UName"			=>$userName,
					"RecordId"		=>$this->RegUserID,
					"SMessage"		=>$sMessage,
					"LMessage"		=>$lMessage,
					"Date"			=>getDateTime(),
					"Controller"	=>get_class()."-".__FUNCTION__,
					"Model"			=>get_class($this->objRegUser));
					
				$this->objRegUser->updateProcessLog($DataArray);	
				/*-----------------------------*/
				
			} catch(Exception $e) {
				EnPException::exceptionHandler($e);	
			}
			redirect(URL . "profile");
		}
		
		private function getFormData() {
			EnPException::writeProcessLog('Profile_Controller :: getFormData action to get all data');
			$this->RegUserID		= request('post','RegUserId',1);
			//echo $this->RegUserID;exit;
			$this->userName 		= request('post','R_userName',0);
			$this->facebookID 		= request('post','R_facebookId',0);
			$this->firstName 		= request('post','R_firstName',0);
			$this->lastName 		= request('post','R_lastName',0);
			//$this->profileImage		= request('post','R_profileImage',0);
			$this->CompanyName 		= request('post','R_companyName',0);
			$this->Designation 		= request('post','R_designation',0);
			$this->Address1 		= request('post','R_addressline1',0);
			$this->Address2 		= request('post','R_addressline2',0);
			$this->userType         = request('post','R_userType',0);
			$this->Country 			= request('post','R_country',0);
			$this->State 			= request('post','R_state',0);
			$this->City 			= request('post','R_city',0);
			$this->ZipCode 			= request('post','R_zip',0);
			$this->Phone 			= request('post','R_userPhone',0);
			$this->EmailId 			= request('post','R_emailAddress',0);
			$this->Gender 			= request('post','R_gender',0);
			$DOBYear 				= request('post','R_year','1');
			$DOBDate 				= request('post','R_date','1');
			$DOBMonth 				= request('post','R_month','1');
			$this->Dob 				= "$DOBDate-$DOBMonth-$DOBYear";
			$this->Dob				= date("Y-m-d",strtotime($this->Dob));
			$this->Password 		= request('post','R_password',0);
			$this->Status 			= request('post','R_status',1);
			//$this->Deleted 			= request('post','R_deleted',1);
			$this->Mobile 			= request('post','R_mobile',0);
			
			//echo $this->Deleted;exit;
			$TodayDate				= getdatetime();
			
			$this->FieldArr = array(
				"RU_FistName"		=> $this->firstName, 
				"RU_LastName"		=> $this->lastName, 
				"RU_CompanyName"	=> $this->CompanyName, 
				"RU_EmailID"		=> $this->EmailId, 
				"RU_Phone"			=> $this->Phone, 
				"RU_City"			=> $this->City, 
				"RU_State"			=> $this->State, 
				"RU_ZipCode"		=> $this->ZipCode, 
				"RU_Country"		=> $this->Country, 
				"RU_Address1"		=> $this->Address1, 
				"RU_UserType"		=> $this->userType, 
				"RU_Address2"		=> $this->Address2, 
				"RU_Gender"			=> $this->Gender, 
				"RU_DOB"			=> $this->Dob, 
				"RU_Designation"	=> $this->Designation, 
				"RU_Mobile"			=> $this->Mobile, 
				"RU_Password"		=> PassEnc($this->Password), 
				"RU_Status"			=> $this->Status, 
				"RU_RegDate"		=> $TodayDate, 
				"RU_UpdatedDate"	=> $TodayDate, 
				"RU_UserIP"			=> get_ip(), 
				"RU_UserName"		=> $this->userName, 
				"RU_FacebookID"		=> $this->facebookID);
		}
		
		private function ValidateFormData() {
			if($this->FieldArr['RU_FistName'] == NULL) {
				$this->SetStatus(0, 'E7001');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_LastName'] == NULL) {
				$this->SetStatus(0, 'E7002');
				redirect($_SERVER['HTTP_REFERER']);
			}
			
			if($_FILES['R_profileImage']['name'] != '') {
				if( file_ext($_FILES['R_profileImage']['name']) != 'jpg' && 
					file_ext($_FILES['R_profileImage']['name']) != 'jpeg' && 
					file_ext($_FILES['R_profileImage']['name']) != 'png') {
					$this->SetStatus(0, 'E7018');
					redirect($_SERVER['HTTP_REFERER']);
				}
			} elseif($this->FieldArr['RU_Address1'] == NULL) {
				$this->SetStatus(0, '7012');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_Country'] == NULL) {
				$this->SetStatus(0, 'E7013');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_State'] == NULL) {
				$this->SetStatus(0, 'E7014');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_City'] == NULL) {
				$this->SetStatus(0, 'E7015');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_ZipCode'] == NULL) {
				$this->SetStatus(0, 'E7016');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_EmailID'] == NULL) {
				$this->SetStatus(0, 'E7003');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif(!filter_var($this->FieldArr['RU_EmailID'], FILTER_VALIDATE_EMAIL)) { 
				$this->SetStatus(0, 'E7004');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_Phone'] == NULL) {
				$this->SetStatus(0, 'E7005');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_DOB'] == NULL) {
				$this->SetStatus(0, 'E7006');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['RU_Password'] == NULL) {
				$this->SetStatus(0, 'E7007');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
		
		private function CheckDuplicacyForEmail($condition) {
			EnPException::writeProcessLog('Profile_Controller :: CheckDuplicacyForEmail Function To Check Duplicate Email');
			$KeywordStatus = TRUE;
			if(trim($this->FieldArr['RU_EmailID']) <> '') {
				$searchField = " WHERE (RU_EmailID='" . $this->FieldArr['RU_EmailID'] . "')";
				$EmailDetail = $this->objRegUser->CheckDuplicacyForEmail($condition, $searchField);
				if(count($EmailDetail) > 0) {
					$KeywordStatus = FALSE;
					$this->setErrorMsg('E7011');	
				}
			} else {
				$KeywordStatus = FALSE;
				$this->setErrorMsg('E7003');
			}
			return $KeywordStatus;	
		}
		
		public function CheckEmail() {
			EnPException::writeProcessLog('Profile_Controller :: CheckEmail action to check Email duplicacy');
			$keyId 							= request('get', 'keyId', 1);
			$this->FieldArr['RU_EmailID']	= request('get', 'R_emailAddress', 0);
			
			if(trim($keyId) <> '') 
				$condition = " and RU_ID!=" . $keyId;
				
			$Status = $this->CheckDuplicacyForEmail($condition);
			echo json_encode($Status);
			exit;
		}
		
		private function CheckDuplicacyForUserName($condition) {
			EnPException::writeProcessLog('Profile_Controller :: CheckDuplicacyForUserName Function To Check Duplicate user Name');
			$KeywordStatus = TRUE;
			if(trim($this->FieldArr['RU_UserName']) <> '') {
				$searchField = " WHERE (RU_UserName='" . $this->FieldArr['RU_UserName'] . "')";
				$UserNameDetail = $this->objRegUser->CheckDuplicacyForUserName($condition, $searchField);
				if(count($UserNameDetail) > 0) {
					$KeywordStatus = FALSE;
					$this->setErrorMsg('E7021');	
				}
			}
			return $KeywordStatus;	
		}
		
		public function CheckUserName() {
			EnPException::writeProcessLog('Profile_Controller :: CheckUserName action to check User name duplicacy');
			$keyId 							= 	request('get','keyId',1);
			$this->FieldArr['RU_UserName']	=	request('get','R_userName',0);
			
			if(trim($keyId)<>'') 
				$condition=" and RU_ID!=".$keyId;
				
			$Status = $this->CheckDuplicacyForUserName($condition);
			echo json_encode($Status);
			exit;
		}
		
		private function CheckDuplicacyForFacebookID($condition) {
			EnPException::writeProcessLog('Profile_Controller :: CheckDuplicacyForFacebookID Function To Check Duplicate facebook id');
			$KeywordStatus = TRUE;
			if(trim($this->FieldArr['RU_FacebookID']) <> '') {
				$searchField = " WHERE (RU_FacebookID='" . $this->FieldArr['RU_FacebookID'] . "')";
				$FacebookIDDetail = $this->objRegUser->CheckDuplicacyFacebookID($condition, $searchField);
				if(count($FacebookIDDetail) > 0) {
					$KeywordStatus = FALSE;
					$this->setErrorMsg('E7023');	
				}
			}
			
			return $KeywordStatus;	
		}
		
		public function CheckFacebookID() {
			EnPException::writeProcessLog('Profile_Controller :: CheckFacebookID action to check Facebook ID duplicacy');
			$keyId 								= request('get', 'keyId', 1);
			$this->FieldArr['RU_FacebookID']	= request('get', 'R_facebookId', 0);
			
			if(trim($keyId) <> '') 
				$condition = " and RU_ID!=" . $keyId;
				
			$Status = $this->CheckDuplicacyForFacebookID($condition);
			echo json_encode($Status);
			exit;
		}
		
		private function ProfileImage() {
			$this->ProfileImage		= count($_FILES['R_profileImage']) > 0 ? $_FILES['R_profileImage'] : '';
			$ImageFile  			= $this->ProfileImage;
			$Ext					= file_ext($ImageFile['name']);
			$CustomName	 			= $ImageFile['name'];
			$Image					= $this->RegUserID . "." . $Ext;
			$this->LargePhysPath	= PROFILE_LARGE_IMAGE_DIR . $this->RegUserID . "." . $Ext;
			$this->MedPhysPath		= PROFILE_MEDIUM_IMAGE_DIR . $this->RegUserID . "." . $Ext;
			$this->ThumbPhysPath	= PROFILE_THUMB_IMAGE_DIR . $this->RegUserID . "." . $Ext;
			
			$oldProflieImage		= request('post', 'R_oldProfileImage', 0);
			if($oldProflieImage != NULL) {
				$oldExt					= explode('.', $oldProflieImage);
				
				unlink(PROFILE_LARGE_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
				unlink(PROFILE_MEDIUM_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
				unlink(PROFILE_THUMB_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
			}
			
			move_uploaded_file($ImageFile["tmp_name"], $this->LargePhysPath);
			$ProfileField = array("RU_ProfileImage"=>$Image);
			$ImageStatus = $this->objRegUser->UpdateDonorDetail_DB($ProfileField, $this->RegUserID);
			$this->CreateMediumImg();
			$this->CreateThumbImg();
			
			/*----update process log------*/
			$userType 	= '';
			$userID 	= 0;
			$userName	= '';		
			if(isset($this->loginDetail['UserType1']['is_login'])){
				$userType 	= 'UT1';
				$userID 	= keyDecrypt($this->loginDetail['UserType1']['user_id']);
				$userName	= $this->loginDetail['UserType1']['user_fullname'];
			}
			if(isset($this->loginDetail['UserType2']['is_login'])) {
				$userType 	= 'UT2';
				$userID 	= keyDecrypt($this->loginDetail['UserType2']['user_id']);
				$userName	= $this->loginDetail['UserType2']['user_fullname'];
			}
			
			$sMessage = "Error in update profile image.";
			$lMessage = "Error in update profile image(id=$this->RegUserID).";
			if($Status) {
				$sMessage = "Donor profile image has updated successfully.";
				$lMessage = "Donor profile (id=$this->RegUserID) image has updated successfully.";
			}
						
			$DataArray = array(	
				"UType"			=> $userType,
				"UID"			=> $userID,
				"UName"			=> $userName,
				"RecordId"		=> $this->RegUserID,
				"SMessage"		=> $sMessage,
				"LMessage"		=> $lMessage,
				"Date"			=> getDateTime(),
				"Controller"	=> get_class()."-".__FUNCTION__,
				"Model"			=> get_class($this->objRegUser));
				
			$this->objRegUser->updateProcessLog($DataArray);	
			/*-----------------------------*/
			
			return $ImageStatus;
		}
		
		private function CreateMediumImg() {
			$objFile = LoadLib('resize_image');
			$objFile = new resize($this->LargePhysPath);
			$objFile -> resizeImage(300, 400, 'auto');
			$objFile -> saveImage($this->MedPhysPath, 100);
		}
	
		private function CreateThumbImg() {
			$objFile = LoadLib('resize_image');
			$objFile = new resize($this->MedPhysPath);
			$objFile->resizeImage(70, 70, 'crop');
			$objFile->saveImage($this->ThumbPhysPath, 100);
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1) {
			EnPException::writeProcessLog('RegUser_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status = 0;
			$this->P_ErrorCode .= $ErrCode . ",";
			$this->P_ErrorMessage = $ErrCode;
			$this->MsgType = $MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode,$MsgType=2) {
			EnPException::writeProcessLog('RegUser_Controller :: setConfirmationMsg Function To Set Confirmation Message => ' . $ConfirmCode);
			$this->P_status = 1;
			$this->P_ConfirmCode = $ConfirmCode;
			$this->P_ConfirmMsg = $ConfirmCode;
			$this->MsgType = $MsgType;
		}
		
		private function SetStatus($Status,$Code) {
			if($Status) {
				$messageParams = array(
					"msgCode"		=> $Code,
					"msg"			=> "Custom Confirmation message",
					"msgLog"		=> 0,									
					"msgDisplay"	=> 1,
					"msgType"		=> 2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode"		=> $Code,
					"errMsg"		=> "Custom Confirmation message",
					"errOriginDetails"=> basename(__FILE__),
					"errSeverity"	=> 1,
					"msgDisplay"	=> 1,
					"msgType"		=> 1);
				EnPException::setError($messageParams);
			}
		}
	}
?>