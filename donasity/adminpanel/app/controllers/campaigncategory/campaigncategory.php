<?php
	class Campaigncategory_Controller extends Controller
	{
		public $FieldArr,$filterParam,$sortParam,$CampID;
		public $P_ErrorCode,$tpl,$OrderBy,$P_status,$P_ErrorMessage,$MsgType,$P_ConfirmCode,$P_ConfirmMsg;
		
		function __construct()
		{
			//checkLogin(12);
			$this->load_model('CampaignCategory','objCampCategory');
			$this->P_status=1;
		}
		
		public function index($type='list',$CatID=NULL)
		{
			$this->CampID	= keyDecrypt($CatID);
			$this->tpl 			= new view;
			switch(strtolower($type))
			{				
				case 'insert':
					$this->Insert();
					break;
				case 'edit':
				    $this->Edit();
					$this->tpl->draw('campaignCategory/listing');
					break;
				case 'update':
				    $this->Update();
					break;		
				default:
				   	$this->Listing();
				   	$this->tpl->draw('campaignCategory/listing');
				 	break;  
			}
		}
		
		
		private function Insert()
		{
			
			$this->getFormData();
			$this->ValidateFormData();
			if($this->P_status){$this->CheckDuplicacyUrlFriendlyName('');}
			if($this->P_status=='0')
			{
				$this->SetStatus(false,$this->P_ErrorCode);
				redirect(URL."campaigncategory");
			}
			$InsertCampCategory	= $this->objCampCategory->InsertCampCatetoryDetails_DB(TBLPREFIX.'campaigncategories',$this->FieldArr);
			if($InsertCampCategory!=NULL && $InsertCampCategory>0)
			{
				$this->SetStatus(true,'C14001');
				redirect(URL."campaigncategory/index/edit/".keyEncrypt($InsertCampCategory));
			}
			else
			{
				$this->SetStatus(false,'E14003');
				redirect(URL."campaigncategory");
			}
		}
		
		
		private function getFormData()
		{				
			EnPException::writeProcessLog('Campcategory_Controller :: getFormData action to get all data');
			
			$this->CampID			= request('post','C_CampCatId',1);
			$this->ParentCategory 	= request('post','C_parentCategory',0);		
			$this->CategoryNameEn 	= request('post','C_CategoryNameEn',0);
			$this->CategoryNameES 	= request('post','C_CategoryNameEs',0);
			$this->UrlFriendlyName 	= request('post','C_urlFriendlyName',0);
			$this->ShowOnWebsite 	= request('post','C_status',1);
			
			$this->FieldArr			= array("CampCat_ParentID"=>$this->ParentCategory,"CampCat_DisplayName_EN"=>$this->CategoryNameEn,"CampCat_DisplayName_ES"=>$this->CategoryNameES,"CampCat_UrlFriendlyName"=>$this->UrlFriendlyName,
											"CampCat_ShowOnWebsite"=>$this->ShowOnWebsite);
										
		}
		
		private function ValidateFormData()
		{
			if($this->FieldArr['CampCat_DisplayName_EN']==NULL){$this->SetStatus(0,'E10001');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['CampCat_DisplayName_ES']==NULL){$this->SetStatus(0,'E10002');redirect($_SERVER['HTTP_REFERER']);}			
			elseif($this->FieldArr['CampCat_UrlFriendlyName']==NULL){$this->SetStatus(0,'E10004');redirect($_SERVER['HTTP_REFERER']);}
		}
		
		private function Edit()
		{			
			if(!is_numeric($this->CampID))
			{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
			}
			
			$DataArray			=	array("SQL_CACHE CampCat_ID", "CampCat_ParentID", "CampCat_DisplayName_EN", "CampCat_DisplayName_ES",
										 "CampCat_UrlFriendlyName","CampCat_ShowOnWebsite");
			$FilterParam    	= 	array("CampCat_ID"=>$this->CampID);					  
										  
			$CampDetails 			= 	$this->objCampCategory->GetCampCategoryDetail_DB($DataArray,$FilterParam,$this->sortParam);						
			$this->Listing();
			$this->ListCategory();
			$this->tpl->assign("action",'update'); 
			$this->tpl->assign("CampDetails",$CampDetails); 
		}
		
		private function Update()
		{
			try
			{
				$this->getFormData();
				$this->ValidateFormData();
				$Condition = " AND CampCat_ID!= ".$this->CampID;
				if($this->P_status) $this->CheckDuplicacyUrlfriendlyName($Condition);
				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."campaigncategory/index/edit/".keyEncrypt($this->CampID));}
				$Status	=	$this->objCampCategory->UpdateCampCategoryDetails_DB($this->FieldArr,$this->CampID);
				if($Status)
				{
					$this->SetStatus(true,'C14002');
				}
				else
				{
					$this->SetStatus(false,'E14004');
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."campaigncategory/index/edit/".keyEncrypt($this->CampID));
		}
		
		private function Listing()
		{
			EnPException::writeProcessLog('Campaigncategory_Controller :: Listing action to view all Campaign Category');
			
			$DataArray			=	array("SQL_CACHE CampCat_ID", "CampCat_ParentID", "CampCat_DisplayName_EN", "CampCat_DisplayName_ES",
										  "CampCat_UrlFriendlyName","CampCat_ShowOnWebsite");
			$filterParam 		= 	array();			 
										  
			$CampCatList 		= 	$this->objCampCategory->GetCampaignCategoryList_DB($DataArray,$filterParam,$this->sortParam);
			
			$PagingArr			=	constructPaging($this->objCampCategory->pageSelectedPage,$this->objCampCategory->CampCategoryTotalRecord,$this->objCampCategory->pageLimit);		
			$LastPage 			= 	ceil($this->objCampCategory->CampCategoryTotalRecord/$this->objCampCategory->pageLimit);
			 
			$this->tpl->assign("totalRecords",$this->objCampCategory->CampCategoryTotalRecord);
			$this->tpl->assign("CampCategoryList",$CampCatList);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$LastPage);
			$this->tpl->assign("action",'insert');
		}
		
		private function ListCategory()
		{
			EnPException::writeProcessLog('Campaigncategory_Controller :: ListCategory action to view all Campaign Category');
			
			$DataArray			=	array("SQL_CACHE CampCat_ID", "CampCat_ParentID", "CampCat_DisplayName_EN", "CampCat_DisplayName_ES",
										  "CampCat_UrlFriendlyName","CampCat_ShowOnWebsite");
			$filterParam 		= 	array();			 
			if($this->CampID!='')
			{
				$this->objCampCategory->CampID = $this->CampID;
				$filterParam 		= 	array("CampCat_ID"=>$this->CampID,"CampCat_ParentID"=>$this->CampID);			 
			}				  
			$CampCatParentList 		= 	$this->objCampCategory->GetCampaignCategoryParentList_DB($DataArray,$filterParam);
			$this->tpl->assign("CampCatParentList",$CampCatParentList);
		}
		
		public function CheckCategoryCode()
		{
			EnPException::writeProcessLog('Npocategory_Controller :: CheckCategoryCode action to check Category code duplicacy');
			$keyId 							= 	request('get','keyId',1);
			$this->FieldArr['NPOCat_CodeName']	=	request('get','C_categoryCode',0);
			
			if(trim($keyId)<>'') $condition=" and NPOCat_ID!=".$keyId;
			$Status = $this->CheckDuplicacyForCategoryCode($condition);
			echo json_encode($Status);
			exit;
		}
		
		private function CheckDuplicacyUrlFriendlyName($condition)
		{
			EnPException::writeProcessLog('Npocategory_Controller :: CheckCategoryCode Function To Check Category code');
			$KeywordStatus=TRUE;
			
			if(trim($this->FieldArr['CampCat_UrlFriendlyName'])<>'')
			{
				$searchField = " WHERE (CampCat_UrlFriendlyName='".$this->FieldArr['CampCat_UrlFriendlyName']."')";
				$CategoryCode = $this->objCampCategory->CheckDuplicacyForUrlFriendlyName_DB($condition,$searchField);
				
				if(count($CategoryCode)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E14001');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E14002');
			}
			return $KeywordStatus;
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Npocategory_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status=0;
			$this->P_ErrorCode.=$ErrCode.",";
			$this->P_ErrorMessage=$ErrCode;
			$this->MsgType=$MsgType;
		}
		private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		{
			EnPException::writeProcessLog('Npocategory_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
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