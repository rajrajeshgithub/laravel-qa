<?php
	class Npocontacts_Controller extends Controller
	{
		public  $tpl,$NPO_EIN,$ContactID,$sortParam,$filterParam,$CD_EINID,$firstName,$lastName,$Address1,$Address2,$City,$State,$Country,
				$ZipCode,$Phone,$OfficeNumber,$Mobile,$CompanyName,$Designation,$EmailId,$WebsiteUrl,$PrimaryContact,$Status,$FieldArr;
		
		public  $pageSelectedPage,$totalRecord,$pageLimit;
		public  $P_ErrorCode,$P_status,$P_ErrorMessage,$P_ConfirmCode,$P_ConfirmMsg,$MsgType;
		
		function __construct()
		{
			checkLogin(13);
			$this->load_model('NpoContacts','objCDetails');
			$this->load_model('Common','objCMN');
			$this->LastUpdateBy = getsession("DonasityAdminLoginDetail","admin_fullname");
			$this->P_status=1;
		}
		
		public function index($type='list-contacts', $npoEIN=NULL,$npoContactID='0',$Result=NULL)
		{
			if(isset($npoEIN) && $npoEIN<>NULL)
			{
				$this->NPO_EIN	= keyDecrypt($npoEIN);
			}
			//echo $npoContactID;exit;
			if(isset($npoContactID) && $npoContactID<>NULL && $npoContactID>'0')
			{
				$this->ContactID = keyDecrypt($npoContactID);
			}
			
			$this->tpl 			= new view;
			switch(strtolower($type))
			{
				case 'add-contact':
					$this->Insert();				
					break;	
				case 'edit-contact':
					$this->Listing();
					$this->Edit();
					$this->tpl->draw('npocontacts/listContacts');
					break;
				case 'update-contact':
					  $this->Update();
					  break;
				case 'list-contacts':
					$this->Listing();
					$this->tpl->assign("Result",$Result);
					$this->tpl->draw('npocontacts/listContacts');
				break;
				case 'delete-contactdetail':
					$this->DeleteContactDetails();
					break;
			}
			
		}
		
		
		private function DeleteContactDetails()
		{
			$this->NPO_EIN = request('post','NPO_EIN',0);
			$Contact_ID = request('post','chk',3);
			$Contact_ID = implode(',',$Contact_ID);
			
			if($Contact_ID!=NULL)
			{
				if($this->objCDetails->DeleteContact_DB($Contact_ID))
				{
					$this->SetStatus(true,'C12003');
				}
				else
				{
					$this->SetStatus(false,'E12014');
				}
			}
			else
			{
				$this->SetStatus(false,'E13010');
			}
			redirect(URL."npocontacts/index/list-contacts/".$this->NPO_EIN);
		}
		
		private function Listing()
		{
			EnPException::writeProcessLog('ContactDetails_Controller :: Listing action to view all ContactDetails');
			
			if($this->NPO_EIN!=NULL)
			{
				if(!is_numeric($this->NPO_EIN))
				{
					$this->SetStatus(false,'E2001');
					redirect(URL."home/");
				}
				
				$NpoDataArray       = 	array("NPO_EIN","NPO_Name","NPO_ICO","NPO_ID");
				$Where              = 	array("NPO_EIN"=>$this->NPO_EIN);
				$NpoDetail 			= 	$this->objCMN->GetNPODetail($NpoDataArray,$Where,$this->sortParam);
				
				
				$DataArray			=	array("SQL_CACHE NPO_CD_ID as ID","NPO_CD_EIN","NPO_CD_FirstName as firstName", "NPO_CD_LastName as lastName", "concat(NPO_CD_FirstName,' ',NPO_CD_LastName) as fullName",
											  "NPO_CD_Address1","NPO_CD_Address2","NPO_CD_City","NPO_CD_State","NPO_CD_Country","NPO_CD_ZipCode","NPO_CD_PhoneResidance","NPO_CD_Mobile","NPO_CD_PhoneOffice",
											  "NPO_CD_CompanyName","NPO_CD_Designation","NPO_CD_EmailAddress","NPO_CD_WebsiteUrl","NPO_CD_PrimaryContact","NPO_CD_Status");
											  
				$this->filterParam	=	array("NPO_CD_EIN"=>$NpoDetail['NPO_EIN']);
				
				$CDList 			= 	$this->objCDetails->GetContactListing($DataArray,$this->filterParam,$this->sortParam);
				
			
				
				$PagingArr			=	constructPaging($this->objCDetails->pageSelectedPage,$this->objCDetails->totalRecord,$this->objCDetails->pageLimit);		
				$LastPage 			= 	ceil($this->objCDetails->totalRecord/$this->objCDetails->pageLimit);
				
				$countriesList		=	$this->objCMN->getCountriesList();
				$stateList			=	$this->objCMN->getStateList('US');
				
				$this->tpl->assign("totalRecords",$this->objCDetails->totalRecord);
				$this->tpl->assign("CDList",$CDList);
				$this->tpl->assign("NpoDetail",$NpoDetail);
				$this->tpl->assign("action","add-contact");
				$this->tpl->assign("PagingList",$PagingArr['Pages']);
				$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
				$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
				$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
				$this->tpl->assign("lastPage",$LastPage);
				$this->tpl->assign("CountryList",$countriesList);
				$this->tpl->assign("StateList",$stateList);
				$this->tpl->assign("CD_EINID",$NpoDetail['NPO_EIN']);
				$this->tpl->assign("NPO_EIN",$this->NPO_EIN);
			}
			else
			{
				redirect(URL."home/");
			}
		}
		
		
		private function Edit()
		{
			if(!is_numeric($this->NPO_EIN) || !is_numeric($this->ContactID))
		  	{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
		  	}
			$DataArray			=	array("SQL_CACHE NPO_CD_ID as ID","NPO_CD_EIN","NPO_CD_FirstName as firstName", "NPO_CD_LastName as lastName", "concat(NPO_CD_FirstName,' ',NPO_CD_LastName) as fullName",
										  		"NPO_CD_Address1","NPO_CD_Address2","NPO_CD_City","NPO_CD_State","NPO_CD_Country","NPO_CD_ZipCode","NPO_CD_PhoneResidance","NPO_CD_Mobile","NPO_CD_PhoneOffice",
										  		"NPO_CD_CompanyName","NPO_CD_Designation","NPO_CD_EmailAddress","NPO_CD_WebsiteUrl","NPO_CD_PrimaryContact","NPO_CD_Status");
												
			$where      					= 	array("NPO_CD_ID"=>$this->ContactID);
			$ContactDetail					= 	$this->objCDetails->GetContactListing($DataArray,$where);
			
			$countriesList					=	$this->objCMN->getCountriesList();
			
			$this->tpl->assign("action",'update-contact');
			$this->tpl->assign("CountryList",$countriesList);
			$this->tpl->assign("ContactID",$this->ContactID);
			$this->tpl->assign("ContactDetail",$ContactDetail[0]);
		}
		
		
		private function Insert()
		{
			$this->getFormData();
			$this->ValidateFormData();
			///if($this->P_status){$this->CheckDuplicacyForEmail('');}
			if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."npocontacts/index/list-contacts/".keyEncrypt($this->NPO_EIN));}
			$InsertContact	= $this->objCDetails->NpoContactInsert_DB(TBLPREFIX.'npocontactdetails',$this->FieldArr);
			
			if($InsertContact!=NULL && $InsertContact>0)
			{
				$this->SetStatus(true,'C12001');
				//echo URL."npocontacts/index/edit-contact/".keyEncrypt($this->CD_EINID)."/".keyEncrypt($InsertContact);exit;
				redirect(URL."npocontacts/index/edit-contact/".keyEncrypt($this->NPO_EIN)."/".keyEncrypt($InsertContact));
			}
			else
			{
				$this->SetStatus(false,'E12012');
				redirect(URL."npocontacts/index/list-contacts/".keyEncrypt($this->NPO_EIN));
			}	
		}
		
		
		private function Update()
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: Update action to Update npo contact details');
			try
			{	
				$this->getFormData();
				$this->ValidateFormData();
				//$Condition = " AND NPO_CD_ID!= ".$this->ContactID;
				//if($this->P_status) $this->CheckDuplicacyForEmail($Condition);
				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."npocontacts/index/edit-contact/".keyEncrypt($this->NPO_EIN));}
			
				$Status	=	$this->objCDetails->UpdateContactDetail_DB($this->FieldArr,$this->ContactID);
				
				if($Status)
				{
					$this->SetStatus(true,'C12002');
											
				}
				else
				{
					$this->SetStatus(false,'E12013');
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."npocontacts/index/edit-contact/".keyEncrypt($this->NPO_EIN)."/".keyEncrypt($this->ContactID));
		}
		
		
		private function getFormData()
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: getFormData action to get all data');
			$this->ContactID		= request('post','NPO_CD_ID',1);
			$this->CD_EINID			= request('post','NPO_CD_EIN',1);
			$this->NPO_EIN			= request('post','NPO_EIN',1);
			
			//echo $this->CD_EINID;exit;
			$this->firstName 		= request('post','CD_firstName',0);
			$this->lastName 		= request('post','CD_lastName',0);
			$this->Address1 		= request('post','CD_addressline1',0);
			$this->Address2 		= request('post','CD_addressline2',0);
			$this->City 			= request('post','CD_city',0);
			$this->State 			= request('post','CD_state',0);
			$this->Country 			= request('post','CD_country',0);
			$this->ZipCode 			= request('post','CD_zip',0);
			$this->Phone 			= request('post','CD_userPhone',0);
			$this->OfficeNumber 	= request('post','CD_officeNumber',0);
			$this->Mobile 			= request('post','CD_mobile',0);
			$this->CompanyName 		= request('post','CD_companyName',0);
			$this->Designation 		= request('post','CD_designation',0);
			$this->EmailId 			= request('post','CD_emailAddress',0);
			$this->WebsiteUrl 		= request('post','CD_websiteUrl',0);
			$this->PrimaryContact	= request('post','CD_primaryContact',1);
			$this->Status 			= request('post','CD_Status',1);
			$TodayDate				= getdatetime();
			
			$this->FieldArr			= array("NPO_CD_EIN"=>$this->CD_EINID,"NPO_CD_FirstName"=>$this->firstName,"NPO_CD_LastName"=>$this->lastName,"NPO_CD_Address1"=>$this->Address1,"NPO_CD_Address2"=>$this->Address2,"NPO_CD_City"=>$this->City,
									  		"NPO_CD_State"=>$this->State,"NPO_CD_Country"=>$this->Country,"NPO_CD_ZipCode"=>$this->ZipCode,"NPO_CD_PhoneResidance"=>$this->Phone,"NPO_CD_Mobile"=>$this->Mobile,"NPO_CD_PhoneOffice"=>$this->OfficeNumber,"NPO_CD_CompanyName"=>$this->CompanyName,
											"NPO_CD_Designation"=>$this->Designation,"NPO_CD_EmailAddress"=>$this->EmailId,"NPO_CD_WebsiteUrl"=>$this->WebsiteUrl,"NPO_CD_CreatedDate"=>$TodayDate,"NPO_CD_PrimaryContact"=>$this->PrimaryContact
											,"NPO_CD_Status"=>$this->Status,"NPO_CD_LastUpdatedBy"=>$this->LastUpdateBy);
			//dump($this->FieldArr);								
			
		}
		
		private function ValidateFormData()
		{
			if($this->FieldArr['NPO_CD_FirstName']==NULL){$this->setErrorMsg('E12001');}
			if($this->FieldArr['NPO_CD_LastName']==NULL){$this->setErrorMsg('E12002');}
			if($this->FieldArr['NPO_CD_EmailAddress']!=NULL)
			{
				//elseif($this->FieldArr['NPO_CD_EmailAddress']==NULL){$this->SetStatus(0,'E12003');redirect($_SERVER['HTTP_REFERER']);}
				if(!filter_var($this->FieldArr['NPO_CD_EmailAddress'], FILTER_VALIDATE_EMAIL)){$this->setErrorMsg('E12004');}
			}
			else
			{
				if($this->FieldArr['NPO_CD_EmailAddress']==NULL){$this->setErrorMsg('E12003');}
			}
			if($this->FieldArr['NPO_CD_PhoneResidance']==NULL){$this->setErrorMsg('E12010');}
			
			/*elseif($this->FieldArr['NPO_CD_Address1']==NULL){$this->SetStatus(0,'E12005');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPO_CD_Country']==NULL){$this->SetStatus(0,'E12006');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPO_CD_State']==NULL){$this->SetStatus(0,'E12007');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPO_CD_City']==NULL){$this->SetStatus(0,'E12008');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPO_CD_ZipCode']==NULL){$this->SetStatus(0,'E12009');redirect($_SERVER['HTTP_REFERER']);}*/
			
			
		}
		
		private function CheckDuplicacyForEmail($condition)
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: CheckDuplicacyForEmail Function To Check Duplicate Email');
			$KeywordStatus=TRUE;
			if(trim($this->FieldArr['NPO_CD_EmailAddress'])<>'')
			{
				$searchField = " WHERE (NPO_CD_EmailAddress='".$this->FieldArr['NPO_CD_EmailAddress']."')";
				$EmailDetail = $this->objCDetails->CheckDuplicacyForEmail($condition,$searchField);
				if(count($EmailDetail)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E12011');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E12003');
			}
			return $KeywordStatus;	
		}
		
		public function CheckEmail()
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: CheckEmail action to check Email duplicacy');
			$keyId 							= 	request('get','keyId',1);
			$this->FieldArr['NPO_CD_EmailAddress']	=	request('get','CD_emailAddress',0);
			
			if(trim($keyId)<>'') $condition=" and NPO_CD_ID!=".$keyId;
			$Status = $this->CheckDuplicacyForEmail($condition);
			echo json_encode($Status);
			exit;
		}
		
		 public function getStateList($countryAbbr,$stateAbbr)
	   {
		   $html='<option value="">--select--</option>';
		   $stateList=$this->objCMN->getStateList($countryAbbr);
		   
		   if(count($stateList)>0)
		   {
			   for($s=0;$s<count($stateList);$s++) {
				   if(trim($stateAbbr)!='') {
					   if($stateList[$s]['State_Value']==$stateAbbr) {
						   $sel='selected';
					   }
					   else {
						   $sel='';
					   }
					}
					else {
						$sel='';
					}
					$html.='<option value="'.$stateList[$s]['State_Value'].'" '.$sel.'>'.$stateList[$s]['State_Name'].'</option>';   
			   }
		   }
		   echo $html;exit;
	   }
		
	
		private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status=0;
			$this->P_ErrorCode.=$ErrCode.",";
			$this->P_ErrorMessage=$ErrCode;
			$this->MsgType=$MsgType;
		}
		private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		{
			EnPException::writeProcessLog('Npocontacts_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->P_status=1;
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->MsgType=$MsgType;
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