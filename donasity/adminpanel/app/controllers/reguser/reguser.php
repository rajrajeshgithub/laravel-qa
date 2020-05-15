<?php
class Reguser_Controller extends Controller {
	public $FieldArr, $RegUserID, $filterParam, $sortParam, $Field, $Criteria, $Search, $firstName, $lastName, $Address1;
	public $Address2, $Country, $State, $City, $ZipCode, $Phone, $EmailId, $Gender, $Dob, $Password, $Status, $Ambassador;
	public $P_ErrorCode, $tpl, $Page_Selected, $OrderBy, $P_status, $P_ErrorMessage, $MsgType, $P_ConfirmCode, $P_ConfirmMsg, $loginDetails, $loginUserId;
	//export
	public $totalRowProcessed = 0, $currentCsvPosition = 0, $exportFileName;
	
	function __construct() {
		checkLogin(12);
		$this->load_model('RegUser', 'objRegUser');
		$this->load_model('Common', 'objCMN');	
		$this->P_status = 1;
		$this->loginDetails = getsession("DonasityAdminLoginDetail");
		$this->exportFileName = EXPORT_CSV_PATH . "registered_users_" . $this->loginDetails['admin_id'] . ".csv";
	}
	
	public function index($type='list', $RegUserID=NULL) {
		$this->RegUserID	= keyDecrypt($RegUserID);
		$this->tpl 			= new view;
		switch(strtolower($type)) {
			case 'add' :
				$this->Add();
				$this->tpl->draw('reguser/addEdit');
				break;	
			case 'insert' :
				$this->Insert();
				break;  
			case 'edit' :
				$this->Edit();
				$this->tpl->draw("reguser/addEdit");
				break;
			case 'update' :
				$this->update();
				break;	
			case 'delete' :
				$this->DeleteRegUser();
				break;
			case 'userdeleted' :
				$this->UserDeleted();
				break;
			case 'deleteimage' :
				$this->DeleteImageFunction();
				break;
			case 'export-user' :
				$this->ExportUser();
				break;
			default :
				$this->Listing();
				$this->tpl->draw('reguser/listing');
				break;  
		}
	}
	
	// process delete image of profile
	private function DeleteImageFunction() {
		EnPException::writeProcessLog('Reguser_Controller :: DeleteImageFunction action to delete profile image & RegUser_id=>'.$this->RegUserID);	
		if(!is_numeric($this->RegUserID)) {
			$this->SetStatus(false, 'E2001');
			redirect(URL . "home/");
		}
		
		$this->DeleteMainImage();
		
		if($this->P_status == 0) {
			$this->SetStatus(false, $this->P_ErrorCode);
			redirect(URL."reguser/index/edit/".keyEncrypt($this->RegUserID));
		}
		if($this->P_status) {
			$this->SetStatus(true,'C7004');
		}
		redirect(URL."reguser/index/edit/".keyEncrypt($this->RegUserID));
	}
	
	// delete profile image
	private function DeleteMainImage() {
		$field = array('RU_ID', 'RU_ProfileImage');
		$where = array('RU_ID'=>$this->RegUserID);
		$checkUser = $this->objRegUser->GetRegUserListing($field,  $where);
		if(count($checkUser) > 0) {
			if($checkUser[0]['RU_ProfileImage'] != '') {
				if(chkFile(PROFILE_LARGE_IMAGE_DIR, $checkUser[0]['RU_ProfileImage']))
					unlink(PROFILE_LARGE_IMAGE_DIR.$checkUser[0]['RU_ProfileImage']);
				if(chkFile(PROFILE_MEDIUM_IMAGE_DIR, $checkUser[0]['RU_ProfileImage']))
					unlink(PROFILE_MEDIUM_IMAGE_DIR.$checkUser[0]['RU_ProfileImage']);	
				if(chkFile(PROFILE_THUMB_IMAGE_DIR, $checkUser[0]['RU_ProfileImage']))
					unlink(PROFILE_THUMB_IMAGE_DIR.$checkUser[0]['RU_ProfileImage']);
				$ImgArr['RU_ProfileImage'] = '';
				$this->objRegUser->UpdateDonorDetail_DB($ImgArr, $this->RegUserID);
			}
		} else
			$this->setErrorMsg('E7019');
	}
	
	private function Add() {
		$countriesList		=	$this->objCMN->getCountriesList();
		$stateList			=	$this->objCMN->getStateList('US');
		
		for($i = 1; $i <= 31; $i++)
			$Date[] = $i;
		
		$LastYear			= 	date('Y') - 11;
		$year 				= 	range($LastYear, 1900); 
		$UserType     		= 	$GLOBALS['usertype'];
		$Month     			= 	$GLOBALS['month'];
		$Gender     		= 	$GLOBALS['gender'];
		
		$this->tpl->assign("action", 'insert');
		$this->tpl->assign('Date', $Date);
		$this->tpl->assign('Year', $year);
		$this->tpl->assign('Month', $Month);
		$this->tpl->assign('UserType', $UserType);
		$this->tpl->assign('Gender', $Gender);
		$this->tpl->assign("CountryList", $countriesList);
		$this->tpl->assign("StateList", $stateList);
	}
			
	private function Insert() {
		$this->getFormData();
		$this->ValidateFormData();
		if($this->P_status)
			$this->CheckDuplicacyForEmail('');
			
		if($this->P_status) 
			$this->CheckDuplicacyForUserName('');
			
		if($this->P_status)
			$this->CheckDuplicacyForFacebookID('');
			
		if($this->P_status == 0) {
			$this->SetStatus(false, $this->P_ErrorCode);
			redirect(URL."reguser/index/add/");
		}
		$InsertDonor = $this->objRegUser->RegUserInsertMethod_DB(TBLPREFIX.'registeredusers', $this->FieldArr);
		if($InsertDonor != NULL && $InsertDonor > 0) {
			if($_FILES['R_profileImage']['name'] != '') {
				$this->RegUserID = $InsertDonor;
				$this->ProfileImage() ? $this->SetStatus(true, 'C7001') : $this->SetStatus(false, 'E7019');
			} else
				$this->SetStatus(true,'C7001');
				
			redirect(URL . "reguser/index/edit/" . keyEncrypt($InsertDonor));
		} else {
			$this->SetStatus(false, 'E7008');
			redirect(URL . "reguser/index/add");
		}	
	}
	
	private function ProfileImage() {
		$this->ProfileImage	= count($_FILES['R_profileImage']) > 0 ? $_FILES['R_profileImage'] : '';
		$ImageFile  			= $this->ProfileImage;
		$Ext					= file_ext($ImageFile['name']);
		$CustomName	 			= $ImageFile['name'];
		$Image					= $this->RegUserID . "." . $Ext;
		$this->LargePhysPath	= PROFILE_LARGE_IMAGE_DIR . $this->RegUserID . "." . $Ext;
		$this->MedPhysPath		= PROFILE_MEDIUM_IMAGE_DIR . $this->RegUserID . "." . $Ext;
		$this->ThumbPhysPath	= PROFILE_THUMB_IMAGE_DIR . $this->RegUserID . "." . $Ext;
		
		$oldProflieImage		= request('post', 'R_oldProfileImage', 0);
		if($oldProflieImage != NULL) {
			$oldExt = explode('.', $oldProflieImage);
			unlink(PROFILE_LARGE_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
			unlink(PROFILE_MEDIUM_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
			unlink(PROFILE_THUMB_IMAGE_DIR . $this->RegUserID . "." . $oldExt[1]);
		}
		
		move_uploaded_file($ImageFile["tmp_name"], $this->LargePhysPath);
		$ProfileField = array("RU_ProfileImage"=>$Image);
		$ImageStatus = $this->objRegUser->UpdateDonorDetail_DB($ProfileField, $this->RegUserID);
		$this->CreateMediumImg();
		$this->CreateThumbImg();
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
	
	private function ValidateFormData() {
		if($this->FieldArr['RU_FistName'] == NULL) {
			$this->SetStatus(0, 'E7001');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_LastName'] == NULL) {
			$this->SetStatus(0, 'E7002');
			redirect($_SERVER['HTTP_REFERER']);
		}
		
		if($_FILES['R_profileImage']['name'] != '') {
			if(file_ext($_FILES['R_profileImage']['name']) != 'jpg' && file_ext($_FILES['R_profileImage']['name']) != 'jpeg' && file_ext($_FILES['R_profileImage']['name']) != 'png') {
				$this->SetStatus(0, 'E7018');
				redirect($_SERVER['HTTP_REFERER']);
			}
		} elseif ($this->FieldArr['RU_Address1'] == NULL) {
			$this->SetStatus(0, '7012');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_Country'] == NULL) {
			$this->SetStatus(0, 'E7013');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_State'] == NULL) {
			$this->SetStatus(0, 'E7014');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_City'] == NULL) {
			$this->SetStatus(0, 'E7015');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_ZipCode'] == NULL) {
			$this->SetStatus(0, 'E7016');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_EmailID'] == NULL) {
			$this->SetStatus(0, 'E7003');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif (!filter_var($this->FieldArr['RU_EmailID'], FILTER_VALIDATE_EMAIL)) {
			$this->SetStatus(0, 'E7004');
			redirect($_SERVER['HTTP_REFERER']);
		} elseif ($this->FieldArr['RU_Phone'] == NULL) {
			$this->SetStatus(0, 'E7005');
			redirect($_SERVER['HTTP_REFERER']);
		} 
		elseif ($this->FieldArr['RU_Password'] == NULL) {
			$this->SetStatus(0, 'E7007');
			redirect($_SERVER['HTTP_REFERER']);
		}	
	}
	
	private function Edit() {
		EnPException::writeProcessLog('Reguser_Controller :: Edit action to edit registereduser details & RegUser_id=>' . $this->RegUserID);	
		if(!is_numeric($this->RegUserID)) {
			$this->SetStatus(false, 'E2001');
			redirect(URL . "home/");
		}
		
		$field = array(
			"RU_ID as ID", 
			"RU_FistName", 
			"RU_LastName", 
			"RU_CompanyName", 
			"RU_EmailID", 
			"RU_Phone", 
			"RU_City", 
			"RU_State", 
			"RU_ZipCode", 
			"RU_Country", 
			"RU_Address1",
			"RU_UserType",
			"RU_Mobile",
			"RU_Address2",
			"RU_Gender",
			"RU_DOB",
			"RU_Password",
			"RU_Status",
			"RU_AllowAmbassador",
			"RU_Deleted",
			"RU_RegDate",
			"RU_UpdatedDate",
			"RU_ProfileImage",
			"RU_Designation",
			"RU_FacebookID",
			"RU_UserName");
		$where      					= 	array("RU_ID"=>$this->RegUserID);
		$DonorDetail					= 	$this->objRegUser->GetRegUserListing($field, $where);
		
		$DonorDetail[0]['RU_Password'] 	= 	PassDec($DonorDetail[0]['RU_Password']);
		
		$countriesList					=	$this->objCMN->getCountriesList();
		for($i = 1; $i <= 31; $i++)
			$Date[] = $i;
		
		$LastYear	= date('Y') - 11;
		$year 		= range($LastYear, 1900);	 
		$UserType   = $GLOBALS['usertype'];
		$Month     	= $GLOBALS['month'];
		$Gender     = $GLOBALS['gender'];
		
		$this->tpl->assign("action", 'update');
		$this->tpl->assign('Date', $Date);
		$this->tpl->assign('Year', $year);
		$this->tpl->assign('Month', $Month);
		$this->tpl->assign('Gender', $Gender);
		$this->tpl->assign('UserType', $UserType);
		$this->tpl->assign("CountryList", $countriesList);
		$this->tpl->assign("RegUserID", $this->RegUserID);
		$this->tpl->assign("RegUserIDEnCr", keyEncrypt($this->RegUserID));
		$this->tpl->assign("RegUserDetail", $DonorDetail[0]);
	}
	
	private function CheckDuplicacyForEmail($condition) {
		EnPException::writeProcessLog('Reguser_Controller :: CheckDuplicacyForEmail Function To Check Duplicate Email');
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
		EnPException::writeProcessLog('RegUser_Controller :: CheckEmail action to check Email duplicacy');
		$keyId 							= 	request('get', 'keyId', 1);
		$this->FieldArr['RU_EmailID']	=	request('get', 'R_emailAddress', 0);
		
		if(trim($keyId) <> '') $condition = " and RU_ID!=" . $keyId;
		$Status = $this->CheckDuplicacyForEmail($condition);
		echo json_encode($Status);
		exit;
	}
	
	private function CheckDuplicacyForUserName($condition) {
		EnPException::writeProcessLog('Reguser_Controller :: CheckDuplicacyForUserName Function To Check Duplicate user Name');
		$KeywordStatus = TRUE;
		if(trim($this->FieldArr['RU_UserName']) <> '') {
			$searchField = " WHERE (RU_UserName='" . $this->FieldArr['RU_UserName'] . "')";
			$UserNameDetail = $this->objRegUser->CheckDuplicacyForUserName($condition, $searchField);
			if(count($UserNameDetail) > 0) {
				$KeywordStatus = FALSE;
				$this->setErrorMsg('E7021');	
			}
		}/*else{
			$KeywordStatus=FALSE;
			$this->setErrorMsg('E7022');
		}*/
		return $KeywordStatus;	
	}
	
	public function CheckUserName() {
		EnPException::writeProcessLog('RegUser_Controller :: CheckUserName action to check User name duplicacy');
		$keyId 							= 	request('get', 'keyId', 1);
		$this->FieldArr['RU_UserName']	=	request('get', 'R_userName', 0);
		
		if(trim($keyId) <> '') $condition = " and RU_ID!=" . $keyId;
		$Status = $this->CheckDuplicacyForUserName($condition);
		echo json_encode($Status);
		exit;
	}
	
	private function CheckDuplicacyForFacebookID($condition) {
		EnPException::writeProcessLog('Reguser_Controller :: CheckDuplicacyForFacebookID Function To Check Duplicate facebook id');
		$KeywordStatus = TRUE;
		if(trim($this->FieldArr['RU_FacebookID']) <> ''){
			$searchField = " WHERE (RU_FacebookID='" . $this->FieldArr['RU_FacebookID'] . "')";
			$FacebookIDDetail = $this->objRegUser->CheckDuplicacyFacebookID($condition, $searchField);
			if(count($FacebookIDDetail) > 0) {
				$KeywordStatus = FALSE;
				$this->setErrorMsg('E7023');	
			}
		}/*else{
			$KeywordStatus=FALSE;
			$this->setErrorMsg('E7024');
		}*/
		return $KeywordStatus;	
	}
	
	public function CheckFacebookID() {
		EnPException::writeProcessLog('RegUser_Controller :: CheckFacebookID action to check Facebook ID duplicacy');
		$keyId = request('get', 'keyId', 1);
		$this->FieldArr['RU_FacebookID'] = request('get', 'R_facebookId', 0);
		
		if(trim($keyId) <> '') $condition = " and RU_ID!=" . $keyId;
		$Status = $this->CheckDuplicacyForFacebookID($condition);
		echo json_encode($Status);
		exit;
	}
	
	private function Update() {
		EnPException::writeProcessLog('Reguser_Controller :: Update action to Update RegUser details & RegUserID=>' . $this->RegUserID);
		try
		{	
			$this->getFormData();
			$this->ValidateFormData();
			
			$Condition = " AND RU_ID!= " . $this->RegUserID;
			
			if($this->P_status) 
				$this->CheckDuplicacyForEmail($Condition);
				
			if($this->P_status) 
				$this->CheckDuplicacyForUserName($Condition);
				
			if($this->P_status) $this->CheckDuplicacyForFacebookID($Condition);
			
			if($this->P_status == 0) {
				$this->SetStatus(false, $this->P_ErrorCode);
				redirect(URL."reguser/index/edit/".keyEncrypt($this->RegUserID));
			}
			unset($this->FieldArr["RU_RegDate"]);
			unset($this->FieldArr["RU_UserIP"]);
			//$this->FieldArr["RU_UpdatedDate"]	=	getdatetime();
			$Status	=	$this->objRegUser->UpdateDonorDetail_DB($this->FieldArr,$this->RegUserID);
			
			if($Status) {
				if($_FILES['R_profileImage']['name'] != '') {
					if($_FILES['R_profileImage']['error'] == 0) {
						$this->ProfileImage() ? $this->SetStatus(true,'C7002') : $this->SetStatus(false,'E7020');
						/*$ImageStatus = $this->ProfileImage();
						
						if($ImageStatus) {
							$this->SetStatus(true,'C7002');
						}
						else
						{
							$this->SetStatus(false,'E7020');
						}*/
					}
				} else
					$this->SetStatus(true,'C7002');
			} else
				$this->SetStatus(false,'E7009');
		}
		catch(Exception $e)
		{
			EnPException::exceptionHandler($e);	
		}
		redirect(URL . "reguser/index/edit/" . keyEncrypt($this->RegUserID));
	}
	
/*	private function UpdateProflie()
	{
		$ImageStatus = $this->ProfileImage();
		//unlink(PROFILE_LARGE_IMAGE_DIR.$this->RegUserID);
		//unlink(PROFILE_MEDIUM_IMAGE_DIR.$this->RegUserID);
		//unlink(PROFILE_THUMB_IMAGE_DIR.$this->RegUserID);
		return $ImageStatus;		
	}*/
	
	private function Listing() {
		EnPException::writeProcessLog('RegUser_Controller :: Listing action to view all RegUser');
		$this->filterParameterLists();
		
		$DataArray = array(
			"RU_ID as ID", 
			"RU_FistName as firstName", 
			"RU_LastName as lastName", 
			"concat(RU_FistName, ' ', RU_LastName) as fullName",
			"RU_EmailID",
			"RU_Phone",
			"RU_City",
			"RU_State",
			"RU_ZipCode",
			"RU_Country",
			"RU_Address1",
			"RU_Address2",
			"RU_Gender",
			"RU_DOB",
			"RU_Password",
			"RU_Status",
			"RU_Deleted",
			"S.State_Name");
		//dump($this->filterParam);	
		//$this->filterParam = array("RU_Deleted"=>'0');						   
		$this->filterParam['RU_Deleted'] = '0';
		$this->filterParam['RU_UserType'] = '1';
		
		$DnrList = $this->objRegUser->GetRegUserListing($DataArray, $this->filterParam, $this->sortParam);
		
		$PagingArr = constructPaging($this->objRegUser->pageSelectedPage, $this->objRegUser->DonorTotalRecord, $this->objRegUser->pageLimit);		
		$LastPage = ceil($this->objRegUser->DonorTotalRecord / $this->objRegUser->pageLimit);
		
		$this->tpl->assign("Field", $this->Field);
		$this->tpl->assign("status", $this->Status);
		$this->tpl->assign("Criteria", $this->Criteria);
		$this->tpl->assign("Search", stripslashes($this->Search));
		$this->tpl->assign("totalRecords", $this->objRegUser->DonorTotalRecord);
		$this->tpl->assign("DnrList", $DnrList);
		$this->tpl->assign("PagingList", $PagingArr['Pages']);
		$this->tpl->assign("PageSelected", $PagingArr['PageSel']);
		$this->tpl->assign("startRecord", $PagingArr['StartPoint']);
		$this->tpl->assign("endRecord", $PagingArr['EndPoint']);
		$this->tpl->assign("lastPage", $LastPage);
	}
	
	private function filterParameterLists() {
		$this->Field 			=  request('post', 'searchFields', 0);
		$this->Search 			=  request('post', 'searchValues', 0);
		$this->Status			=  request('post', 'status', 0);
		$this->Page_Selected	=  (int)request('post', 'pageNumber', 1);
		$this->OrderBy			=  request('post', 'sortBy', 0);
		$pageSelected			=  request('post', 'pageNumber', '1');
		
		$this->objRegUser->pageSelectedPage	= $pageSelected == 0 ? 1 : $pageSelected;
		
		if($this->Status != NULL)
			$this->filterParam['RU_Status']	= $this->Status;
		
		if($this->Search != NULL) {
			switch($this->Field) {
				case "RU_FistName" :
				case "RU_LastName" :
				case "RU_EmailID" :
				case "RU_Status" :
				$this->filterParam['SearchCondtionLike'] = '';
				$this->filterParam['SearchCondtionLike'] .= $this->Field . " LIKE '%" . $this->Search . "%'";
				break;
				default:
					$this->filterParam['SearchCondtionLike'] = '';
					$this->filterParam['SearchCondtionLike'] .=  "RU_FistName LIKE '%" . $this->Search . "%'" . " OR RU_LastName LIKE '%" . $this->Search . "%'" . " OR " . " RU_EmailID LIKE '%" . $this->Search . "%'";
			}
		}
	}
	
	private function getFormData() {
		EnPException::writeProcessLog('RegUser_Controller :: getFormData action to get all data');
		$this->RegUserID		= request('post', 'RegUserId', 1);
		$this->userName 		= request('post', 'R_userName', 0);
		$this->facebookID 		= request('post', 'R_facebookId', 0);
		$this->firstName 		= request('post', 'R_firstName', 0);
		$this->lastName 		= request('post', 'R_lastName', 0);
		$this->CompanyName 		= request('post', 'R_companyName', 0);
		$this->Designation 		= request('post', 'R_designation', 0);
		$this->Address1 		= request('post', 'R_addressline1', 0);
		$this->Address2 		= request('post', 'R_addressline2', 0);
		$this->userType         = request('post', 'R_userType', 0);
		$this->Country 			= request('post', 'R_country', 0);
		$this->State 			= request('post', 'R_state', 0);
		$this->City 			= request('post', 'R_city', 0);
		$this->ZipCode 			= request('post', 'R_zip', 0);
		$this->Phone 			= request('post', 'R_userPhone', 0);
		$this->EmailId 			= request('post', 'R_emailAddress', 0);
		$this->Gender 			= request('post', 'R_gender', 0);
		$DOBYear 				= request('post', 'R_year', '1');
		$DOBDate 				= request('post', 'R_date', '1');
		$DOBMonth 				= request('post', 'R_month', '1');
		$this->Dob 				= "$DOBDate - $DOBMonth - $DOBYear";
		$this->Dob				= date("Y-m-d", strtotime($this->Dob));
		$this->Password 		= request('post', 'R_password', 0);
		$this->Status 			= request('post', 'R_status', 1);
		$this->Ambassador 		= request('post', 'R_Ambassador', 1);
		$this->Mobile 			= request('post', 'R_mobile', 0);
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
			"RU_AllowAmbassador"=> $this->Ambassador,
			"RU_RegDate"		=> $TodayDate,
			"RU_UpdatedDate"	=> $TodayDate,
			"RU_UserIP"			=> get_ip(),
			"RU_UserName"		=> $this->userName,
			"RU_FacebookID"		=> $this->facebookID);
	}
	
	private function DeleteRegUser() {
		if(!is_numeric($this->RegUserID)) {
			$this->SetStatus(false, 'E2001');
			redirect(URL . "home/");
		}
		if($this->RegUserID != '' && $this->RegUserID > 0) {
			$Where = "RU_ID =" . $this->RegUserID;
			//$Status	= $this->objRegUser->DeleteDonorDetail($Where);
			$this->objRegUser->DeleteDonorDetail($Where) ? $this->SetStatus(true,'C7003') : $this->SetStatus(false,'E7010');
			//if($Status)
			//{
				//$this->SetStatus(true,'C7003');
			//}
			//else
			//{
				//$this->SetStatus(false,'E7010');
			//}
		}
		redirect(URL."reguser");
	}
	
	private function UserDeleted() {
		EnPException::writeProcessLog('Reguser_Controller :: UserDeleted action to Deleted registereduser details & RegUser_id=>'.$this->RegUserID);
		if(!is_numeric($this->RegUserID)) {
			$this->SetStatus(false, 'E2001');
			redirect(URL . "home/");
		}
		$this->FieldArr = array("RU_Deleted"=>1);
		
		$this->objRegUser->UpdateDonorDetail_DB($this->FieldArr, $this->RegUserID) ? $this->SetStatus(true, 'C7003') : $this->SetStatus(false, 'E7010');
		
		//$Status	= $this->objRegUser->UpdateDonorDetail_DB($this->FieldArr,$this->RegUserID);
		//if($Status)
		//{
		//$this->SetStatus(true,'C7003');
		//}
		//else
		//{
		//$this->SetStatus(false,'E7010');
		//}
		redirect(URL . "reguser");
	}

	public function getStateList($countryAbbr, $stateAbbr) {
		$html = '<option value="">--select--</option>';
		$stateList = $this->objCMN->getStateList($countryAbbr);
		
		if(count($stateList) > 0) {
			for($s = 0; $s < count($stateList); $s++) {
				if(trim($stateAbbr) != '') {
					if($stateList[$s]['State_Value'] == $stateAbbr)
						$sel='selected';
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
	
	// export registered user data to csv file
	private function ExportUser() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$this->GetExportConstant();
		$CsvData = $this->GetCsvData();
		
		if(count($CsvData) == 0) {
			$this->SetStatus(0, 'ECSV01');
			redirect(URL . 'reguser');
			return;
		}
		
		$csvHeader = array('Name User', 'Email', 'City', 'State', 'Status');
		if($this->currentCsvPosition == 0)
			$this->CreateCsvFile($csvHeader);
			
		$fp = fopen($this->exportFileName, 'a+');
		foreach($CsvData as $val) {
			if($val['RU_Status'] == '1')
				$val['RU_Status'] = 'Active';
			if($val['RU_Status'] == '0')
				$val['RU_Status'] = 'Inactive';
			
			fputcsv($fp, $val);		
			$this->totalRowProcessed++;
		}
		
		setSession('arrCsvExp', $this->totalRowProcessed, 'CURCSVPOS');
		setSession('arrCsvExp', $this->totalRowProcessed, 'TOTALROWPROCESSED');
		
		fclose($fp);
		$this->ViewRedirectExpCsv();
	}
	
	// get export constant
	private function GetExportConstant() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		$this->objRegUser->isExport = 1;
		
		$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		
		$this->currentCsvPosition = (is_array($this->currentCsvPosition) || $this->currentCsvPosition == '') ? 0 : $this->currentCsvPosition;
		$this->totalRowProcessed = (is_array($this->totalRowProcessed) || $this->totalRowProcessed == '') ? 0 : $this->totalRowProcessed;
		
		$this->objRegUser->currentCsvPosition = $this->currentCsvPosition;
	}
	
	// prepare data from table to export into csv
	private function GetCsvData() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$this->filterParameterLists();
		
		$DataArray = array(
			"concat(RU_FistName, ' ', RU_LastName) as fullName",
			"RU_EmailID",
			"RU_City",
			"S.State_Name",
			"RU_Status",
		);
								   
		$this->filterParam['RU_Deleted'] = '0';
		$this->filterParam['RU_UserType'] = '1';
		
		return $this->objRegUser->GetRegUserListing($DataArray, $this->filterParam);
	}
	
	// create csv file
	private function CreateCsvFile($headerArr) {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		
		$fp = fopen($this->exportFileName, 'w+');
		if($fp) {
			$stringArray = implode(",", $headerArr) . "\r\n";
			fwrite($fp, $stringArray);
		}
	}
	
	// export progress bar
	public function ViewRedirectExpCsv() {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		$this->totalRowProcessed = getSession('arrCsvExp', 'TOTALROWPROCESSED');
		$this->currentCsvPosition = getSession('arrCsvExp', 'CURCSVPOS');
		
		$totalRows = $this->objRegUser->DonorTotalRecord;
		if($this->currentCsvPosition >= $totalRows) {
			$this->P_status = 0;
			unsetSession("arrCsvExp");
		}
		
		$totalper = (int)(($this->currentCsvPosition / $totalRows) * 100);
		$this->tpl->assign('rowProcessed', $this->totalRowProcessed);
		$this->tpl->assign('totalPer', $totalper);
		$this->tpl->assign('Pstatus', $this->P_status);
		$this->tpl->draw('reguser/exportstatus');
	}
	
	// download csv file
	public function downloadfile($title='registered_users') {
		EnPException :: writeProcessLog(get_class() . ' :: ' . __FUNCTION__ . ' action called.');
		LoadLib("Download_file");
		$dFile = new Download_file();
		$dFile->Downloadfile(EXPORT_CSV_PATH, "registered_users_" . $this->loginDetails['admin_id'] . '.csv', $title);
	}
	
	/*private function ExportUser() {
		EnPException::writeProcessLog('RegUser_Controller :: ExportUser function called.');
		
		$this->loginUserId = $this->loginDetails['admin_id'];
		
		$this->filterParameterLists();
		
		$DataArray = array(
			'RU_FistName',
			'RU_Phone',
			'RU_Gender',
			'RU_DOB');
									   
		$this->filterParam['RU_Deleted'] = '0';
		$this->filterParam['RU_UserType'] = '1';
		
		$DnrList = $this->objRegUser->GetRegUserListing($DataArray, $this->filterParam, $this->sortParam);
		
		if(count($DnrList) <= 0) {
			$messageParams = array(
				"errCode"	=> 'E18000',
				"errMsg"	=> "Custom Confirmation message",
				"errOriginDetails"=> basename(__FILE__),
				"errSeverity"=> 1,
				"msgDisplay"=> 1,
				"msgType"	=> 1);
			EnPException :: setError($messageParams);
			redirect($_SERVER['HTTP_REFERER']);
		} else {
			$this->ExportCSVFileName = EXPORT_CSV_PATH . "users_" . $this->loginUserId . ".csv";
			
			$this->CreateCsvFile();
		
			$fp = fopen($this->ExportCSVFileName, 'a+');
			$i = 0;
			
			foreach($DnrList as $val) {
				fputcsv($fp, $val);
				$i++;
			}			
			$this->downloadfile();
		}			
	}
	
	private function CreateCsvFile() {
		$fp = fopen($this->ExportCSVFileName, 'w+');
		
		if($fp) {
			$HeaderArr = array(
				"First Name",
				"Phone",
				"Gender",
				"DOB");
			$StringArray = implode(",", $HeaderArr) . "\r\n";
			fwrite($fp, $StringArray);
		}
	}
	
	private function downloadfile($title='users') {
		LoadLib("Download_file");
		$dFile = new Download_file();
		$dFile->Downloadfile(EXPORT_CSV_PATH, "users_" . $this->loginUserId . ".csv", $title);
	}*/
	
	private function SetStatus($Status, $Code, $custom=NULL) {
		
		$Msg = "Custom Confirmation message";
		if($custom!=NULL){
			$Msg = $custom;
			$Code = '000';
		}
		
		if($Status) {			
			$messageParams = array(
				"msgCode"=>$Code,
				"msg"			=> $Msg,
				"msgLog"		=> 0,									
				"msgDisplay"	=> 1,
				"msgType"		=> 2);
			EnPException::setConfirmation($messageParams);
		} else {
			$messageParams = array(
				"errCode" 			=> $Code,
				"errMsg"			=> $Msg,
				"errOriginDetails"	=> basename(__FILE__),
				"errSeverity"		=> 1,
				"msgDisplay"		=> 1,
				"msgType"			=> 1);
			EnPException::setError($messageParams);
		}
	}
		
	private function setErrorMsg($ErrCode, $MsgType=1) {
		EnPException::writeProcessLog('RegUser_Controller :: setErrorMsg Function To Set Error Message => ' . $ErrCode);
		$this->P_status = 0;
		$this->P_ErrorCode .= $ErrCode . ",";
		$this->P_ErrorMessage = $ErrCode;
		$this->MsgType = $MsgType;
	}
	
	private function setConfirmationMsg($ConfirmCode, $MsgType=2) {
		EnPException::writeProcessLog('RegUser_Controller :: setConfirmationMsg Function To Set Confirmation Message => ' . $ConfirmCode);
		$this->P_status = 1;
		$this->P_ConfirmCode = $ConfirmCode;
		$this->P_ConfirmMsg = $ConfirmCode;
		$this->MsgType = $MsgType;
	}
}
?>	