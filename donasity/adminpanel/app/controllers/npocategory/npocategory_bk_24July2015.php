<?php
	class Npocategory_Controller extends Controller
	{
		public $CatID,$tpl,$FieldArr,$sortParam,$ParentCategory,$CategoryCode,$CategoryNameEn,$CategoryNameES,$UrlFriendlyName,$ShowOnWebsite;
		public $pageSelectedPage,$NpoCategoryTotalRecord,$pageLimit;
		public $P_ErrorCode,$P_ErrorMessage,$MsgType,$P_ConfirmCode,$P_ConfirmMsg;
		public $RelationCatParam;
		
		function __construct()
		{
			checkLogin(13);
			$this->load_model('NpoCategory','objNpoCategory');
			$this->load_model('Common','objCMN');	
			$this->P_status=1;
		}
		
		public function index($type='list',$CatID=NULL)
		{
			$this->CatID	= keyDecrypt($CatID);
			$this->tpl 			= new view;
			switch(strtolower($type))
			{	
				case 'add':
				  	$this->Add();
				  	$this->tpl->draw('npocategory/addEdit');
				  	break;	
				case 'insert':
					$this->Insert();
					break;
				case 'edit':
				    $this->Edit();
					$this->tpl->draw('npocategory/listing');
					break;
				case 'update':
				    $this->Update();
					break;
				case 'delete-categorydetail':
					$this->DeleteCategoryDetail();			
					break;
				case 'hide-categorydetail':
					$this->HideCategoryDetail();			
					break;
				case 'npo-relation-category':
					$this->NPORelationCategory();
					break;
				case 'create-category-relation':
					$this->CreateCategoryRelation();
					break;		
				default:
				   	$this->Listing();
				   	$this->tpl->draw('npocategory/listing');
				 	break;  
			}
		}
		
		private function RelationCategoryFilter()
		{
			$this->RelationCatParam	= " WHERE 1=1 AND NPO_CD <> '' ";
			$Condition	= request('post','condition',1);
			$Keyword	= request('post','keyword',0);
			
			if($Condition == 0 && $Keyword!='')
			{
				$this->RelationCatParam.=" AND (NPO_CD LIKE '%".$Keyword."%')";	
			}
			else if($Condition == 1 && $Keyword!='')
			{
				$this->RelationCatParam.=" AND (NPO_CD LIKE '".$Keyword."%')";	
			}
			else if($Condition == 2 && $Keyword!='')
			{
				$this->RelationCatParam.=" AND (NPO_CD = '".$Keyword."')";	
			}
			
			$this->tpl->assign('SearchCondition',$Condition);
			$this->tpl->assign('SearchKeyword',$Keyword);
		}
		
		private function NPORelationCategory()
		{
			$this->RelationCategoryFilter();
			/*$DataArray	= array('DISTINCT(NPO_SubSectionName)');*/
			$DataArray	= array('DISTINCT(NPO_CD)');
			$Order		= " ORDER BY NPO_CD";	
			$limit 		= " limit 0,100";
			$SubSection	= $this->objNpoCategory->GetNPOSubSectionName($DataArray,$limit,$this->RelationCatParam,$Order);
			$SortedSubSection	= array();
			foreach($SubSection as $key => &$val)
			{
				$val['CatID']	= $this->GetExistRelationID($val['NPO_CD']);	
				if($val['CatID'] == 0)
				{
					$SortedSubSection[]	= $val;
					unset($SubSection[$key]);	
				}	
			}
			$SortedSubSection	= array_merge($SortedSubSection,$SubSection);
			$this->tpl->assign('Subsection',$SortedSubSection);
			
			$Array		= array('NPOCat_ID','NPOCat_DisplayName_EN','NPOCat_DisplayName_ES');
			
			$Category	= $this->objNpoCategory->GetNpoCategoryListing($Array);
			$this->tpl->assign('Category',$Category);
			
			$this->tpl->draw('npocategory/nporelationcategorylist');
		}
		
		private function GetExistRelationID($SubSectionName)
		{
			$Param	= " AND NPO_CategoryName='".$SubSectionName."'";	
			return $this->objNpoCategory->GetExistRelationIDDB($Param);
		}
		
		private function CreateCategoryRelation()
		{
			//dump($_REQUEST);
			$CategoryID		= request('post','CategoryID',3);
			$SubSectionName	= request('post','subsectionname',3);
			$ExistCatIDArr		= request('post','ExistCatID',3);//dump($_POST);
			$Status			= true;
			/*echo "<pre>";
			print_r($CategoryID);
			print_r($SubSectionName);
			dump($CategoryID);*/
			foreach($CategoryID as $key => $CatID)
			{
				$ExistCatID	= $ExistCatIDArr[$key];
				$Subsection	= $SubSectionName[$key];
				if($CatID > 0){
				$Status	= $this->objNpoCategory->CreateCategoryRelationDB($Subsection,$CatID);
				}
				else if($CatID == 0 && $ExistCatID > 0)
				{
					$Status	= $this->objNpoCategory->RemoveCategoryRelationDB($Subsection);	
				}
			}	
			if($Status)
			{
				$this->SetStatus(true,'C10005');
			}
			else
			{
				$this->SetStatus(false,'E10011');
			}
			redirect(URL."npocategory/index/npo-relation-category");
		}
		
		private function HideCategoryDetail()
		{
			$Cat_ID = request('post','chk',3);
			$Cat_ID = implode(',',$Cat_ID);
			if($Cat_ID!=NULL)
			{
				if($this->objNpoCategory->UpdateCategory_DB($Cat_ID))
				{
					$this->SetStatus(true,'C10004');
				}
				else
				{
					$this->SetStatus(false,'E10010');
				}
			}
			else
			{
				$this->SetStatus(false,'E13010');
			}
			
			redirect(URL."npocategory");
			
		}
		
		private function DeleteCategoryDetail()
		{
			$Cat_ID = request('post','chk',3);
			$Cat_ID = implode(',',$Cat_ID);
			
			if($Cat_ID!=NULL)
			{
				if($this->objNpoCategory->DeleteCategory_DB($Cat_ID))
				{
					$this->SetStatus(true,'C10003');
				}
				else
				{
					$this->SetStatus(false,'E10009');
				}
			}
			else
			{
				$this->SetStatus(false,'E13010');
			}
			
			redirect(URL."npocategory");
		}
		
		
		private function Add()
		{
			EnPException::writeProcessLog('Npocategory_Controller :: Listing action to view all NpoCategory');
			
			$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES",
										  "NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite");
							  
			$NpoCatList 		= 	$this->objNpoCategory->GetNpoCategoryListing($DataArray,array(),$this->sortParam);
			
			$this->tpl->assign("NCategoryList",$NpoCatList);
			$this->tpl->assign("action",'insert');
		}
		
		private function Insert()
		{
			$this->getFormData();
			$this->ValidateFormData();
			if($this->P_status){$this->CheckDuplicacyForCategoryCode('');}
			if($this->P_status=='0')
			{
				$this->SetStatus(false,$this->P_ErrorCode);
				redirect(URL."npocategory");
			}
			
			$InsertNpoCategory	= $this->objNpoCategory->NCategoryInsertMethod_DB(TBLPREFIX.'npocategories',$this->FieldArr);
			if($InsertNpoCategory!=NULL && $InsertNpoCategory>0)
			{
				$this->SetStatus(true,'C10001');
				redirect(URL."npocategory/index/Edit/".keyEncrypt($InsertNpoCategory));
			}
			else
			{
				$this->SetStatus(false,'E10005');
				redirect(URL."npocategory");
			}
		}
		
		
		private function getFormData()
		{
			EnPException::writeProcessLog('Npocategory_Controller :: getFormData action to get all data');
			
			$this->CatID			= request('post','C_NpoCatId',1);
			$this->ParentCategory 	= request('post','C_parentCategory',0);
			//$this->CategoryCode 	= request('post','C_categoryCode',0);
			$this->CategoryNameEn 	= request('post','C_CategoryNameEn',0);
			$this->CategoryNameES 	= request('post','C_CategoryNameEs',0);
			$this->UrlFriendlyName 	= request('post','C_urlFriendlyName',0);
			$this->ShowOnWebsite 	= request('post','C_status',1);
			$this->SortOrder 		= request('post','C_sortingOrder',0);
			
			$this->FieldArr			= array("NPOCat_ParentID"=>$this->ParentCategory,"NPOCat_DisplayName_EN"=>$this->CategoryNameEn,"NPOCat_DisplayName_ES"=>$this->CategoryNameES,"NPOCat_CodeName"=>$this->UrlFriendlyName,"NPOCat_URLFriendlyName"=>$this->UrlFriendlyName,
											"NPOCat_ShowOnWebsite"=>$this->ShowOnWebsite,"NPOCat_SortOrder"=>$this->SortOrder);
			
										
		}
		
		private function ValidateFormData()
		{
			if($this->FieldArr['NPOCat_DisplayName_EN']==NULL){$this->SetStatus(0,'E10001');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPOCat_DisplayName_ES']==NULL){$this->SetStatus(0,'E10002');redirect($_SERVER['HTTP_REFERER']);}
		//	elseif($this->FieldArr['NPOCat_CodeName']==NULL){$this->SetStatus(0,'E10003');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['NPOCat_URLFriendlyName']==NULL){$this->SetStatus(0,'E10004');redirect($_SERVER['HTTP_REFERER']);}
		}
		
		private function Edit()
		{
			
			if(!is_numeric($this->CatID))
			{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
			}
			
			$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES",
										  "NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite","NPOCat_SortOrder");
			$FilterParam    	= 	array("NPOCat_ID"=>$this->CatID);					  
										  
			$EditCat 			= 	$this->objNpoCategory->GetNpoCategoryDetail($DataArray,$FilterParam,$this->sortParam);			
			$this->Listing();
			$this->ListCategory();
			$this->tpl->assign("action",'update'); 
			$this->tpl->assign("NpoCatDetail",$EditCat); 
		}
		
		private function Update()
		{
			try
			{
				$this->getFormData();
				$this->ValidateFormData();
				$Condition = " AND NPOCat_ID!= ".$this->CatID;
				if($this->P_status) $this->CheckDuplicacyForCategoryCode($Condition);
				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."npocategory/index/edit/".keyEncrypt($this->CatID));}
				$Status	=	$this->objNpoCategory->UpdateNpoCategory_DB($this->FieldArr,$this->CatID);
				if($Status)
				{
					$this->SetStatus(true,'C10002');
				}
				else
				{
					$this->SetStatus(false,'E10006');
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."npocategory/index/edit/".keyEncrypt($this->CatID));
		}
		
		private function Listing()
		{
			EnPException::writeProcessLog('Npocategory_Controller :: Listing action to view all NpoCategory');
			
			$this->filterParameterLists();
			
			$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES",
										  "NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite","NPOCat_SortOrder");
										  
			$NpoCatList 		= 	$this->objNpoCategory->GetNpoCategoryListing($DataArray,array(),$this->sortParam);
			
			$PagingArr			=	constructPaging($this->objNpoCategory->pageSelectedPage,$this->objNpoCategory->NpoCategoryTotalRecord,$this->objNpoCategory->pageLimit);
			$LastPage 			= 	ceil($this->objNpoCategory->NpoCategoryTotalRecord/$this->objNpoCategory->pageLimit);
			 
			$this->tpl->assign("totalRecords",$this->objNpoCategory->NpoCategoryTotalRecord);
			$this->tpl->assign("NCategoryList",$NpoCatList);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$LastPage);	
			$this->tpl->assign("action",'insert');		
		}
		
		private function filterParameterLists()
		{
			$this->Field 					=  request('post','searchFields',0);
			$this->Search 					=  request('post','searchValues',0);
			$this->Status					=  request('post','status',0);
			$this->Page_Selected			=  (int)request('post','pageNumber',1);
			$this->OrderBy					=  request('post','sortBy',0);
			$pageSelected					=  request('post','pageNumber','1');
			
			$this->objNpoCategory->pageSelectedPage	= 	$pageSelected==0?1:$pageSelected;
			//echo $this->objNpoCategory->pageSelectedPage;exit;
			if($this->Status!=NULL)
			{
				$this->filterParam['RU_Status']=$this->Status;
			}
			
			if($this->Search!=NULL)
			{
				switch($this->Field)
				{				
					case "RU_FistName":
					case "RU_LastName":
					case "RU_EmailID":
					case "RU_Status":
					$this->filterParam['SearchCondtionLike'].= $this->Field." LIKE '%".$this->Search."%'";
					break;
					default:
						$this->filterParam['SearchCondtionLike'].= "RU_FistName LIKE '%".$this->Search."%'"." OR RU_LastName LIKE '%".$this->Search."%'"." OR "." RU_EmailID LIKE '%".$this->Search."%'";
				}
			}
		}
		
		private function ListCategory()
		{
			EnPException::writeProcessLog('npocategory_Controller :: ListCategory action to view all Campaign Category');
			
			$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES",
										  "NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite");
			$filterParam 		= 	array();			 
			if($this->CatID!='')
			{
				$this->objCampCategory->CatID = $this->CatID;
				$filterParam 		= 	array("NPOCat_ID"=>$this->CatID,"NPOCat_ParentID"=>$this->CatID);			 
			}				  
			$NpoCatParentList 		= 	$this->objNpoCategory->GetNpoCategoryParentList_DB($DataArray,$filterParam);
			//dump($NpoCatParentList);
			$this->tpl->assign("NpoCatParentList",$NpoCatParentList);
		}
		
		public function CheckCategoryCode()
		{
			EnPException::writeProcessLog('Npocategory_Controller :: CheckCategoryCode action to check Category code duplicacy');
			$keyId 							= 	request('get','keyId',1);
			$this->FieldArr['NPOCat_URLFriendlyName']	=	request('get','C_urlFriendlyName',0);
			
			if(trim($keyId)<>'') $condition=" and NPOCat_ID!=".$keyId;
			$Status = $this->CheckDuplicacyForCategoryCode($condition);
			echo json_encode($Status);
			exit;
		}
		
		private function CheckDuplicacyForCategoryCode($condition)
		{
			EnPException::writeProcessLog('Npocategory_Controller :: CheckCategoryCode Function To Check Category code');
			$KeywordStatus=TRUE;
			
			if(trim($this->FieldArr['NPOCat_URLFriendlyName'])<>'')
			{
				$searchField = " WHERE (NPOCat_URLFriendlyName='".$this->FieldArr['NPOCat_URLFriendlyName']."')";
				$CategoryCode = $this->objNpoCategory->CheckDuplicacyForCategoryCode($condition,$searchField);
				
				if(count($CategoryCode)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E10012');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E10013');
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