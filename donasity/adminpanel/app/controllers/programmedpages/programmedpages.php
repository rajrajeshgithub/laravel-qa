<?php 
	class Programmedpages_Controller extends Controller
	{
		public $PMV_id,$PMV_metaTitle,$PMV_metaDesc,$PMV_metaKeyword,$PMV_googleAnaltics,$PMV_IsEditableText,$PMV_pageKeyword,$PMV_pagename,$PMV_desc;
		public $PMT_id,$PMT_PMVid,$PMT_serial,$PMT_caption,$PMT_desc,$PMT_content,$LoginUserType;
		public $Page_Limit,$Page_Selected,$Page_totalRecords;
		public $ErrMSG,$ErrCode,$ErrStatus;
		public $Field,$Criteria,$Search,$where,$SearchOption;
		public $metaPageContentId,$metaPageValueIds,$captionsArr,$detailsArr,$descArr;
		public $pageID;
		
		public function __construct()
		{
			checkLogin(9);
			$this->load_model('ProgrammedPages','objMeta');
			$this->ErrStatus=1;
			$this->Page_Limit=20;
			$this->P_status=1;
		}
		
		public function index($type="Listing",$pageID=NULL)
		{
			EnPException::writeProcessLog('Programmedpages_Controller :: index action to show actor dashboard form');			
			$this->tpl = new view();
			$this->type=$type;
			$this->pageID = $pageID;
			switch(strtolower($type))
			{
				case 'listing':
				$this->MetaListFunction();
				break;
				case 'edit':
				$this->editMetaDetail();
				break;
				case 'update':
				$this->update();
				break;
			}
		}
		
		public function deleteAdditionalContent($PMVid,$ID)
		{
			 if($ID!="" && $PMVid!="")
			 {
				$Where 	= "PMT_id =".$ID." AND PMT_PMVid=".$PMVid;
				$Status	= $this->objMeta->DeleteAdditionalContent($Where);
				if($Status)
				{
					$this->SetStatus(true,'C2002');
				}
				else
				{
					$this->SetStatus(false,'E2009');
				}
			 }
				redirect(URL."programmedpages/index/edit/".keyEncrypt($PMVid));
		}
		
		private function MetaListFunction()
		{
			EnPException::writeProcessLog('programmedpages_Controller :: Index action to get list of meta pages');
			
			$this->load_model('ProgrammedPages', 'objMeta');

			$this->Field 	= request('post','searchFields',0);
			$this->Search 	= request('post','searchValues',0);
			$this->objMeta->OrderBy=request('post','sortBy',0);
			
			if($this->objMeta->OrderBy=='sortBy')
			{
				$this->objMeta->OrderBy='';
			}
			else if($this->objMeta->OrderBy=='PMT_PMVIds')
			{
				$this->objMeta->OrderBy='PMT_PMVIds';
			}
			else
			{
				$this->objMeta->OrderBy='PMV.PMV_pagenameEN';
			}
		
			switch(strtolower($this->objMeta->OrderBy))
			{
				case 'pmv.pmv_pagenameen':
				$this->objMeta->OrderByMethod=" ASC ";
				break;
				case 'pmt_pmvids':
				$this->objMeta->OrderByMethod=" DESC ";
			}
			
			$this->Page_Selected=(int)post('pageNumber');
			if($this->Page_Selected==0) $this->Page_Selected=1;
			
			$this->objMeta->Field=$this->Field;
			$this->objMeta->Search=$this->Search;

			$this->objMeta->P_limit=$this->Page_Limit;
			$this->objMeta->P_selectedPage=$this->Page_Selected;
			
			$PageList=$this->objMeta->GetProgrammedPageList();
			$this->objMeta->Page_limit=$this->Page_Limit;
			$this->objMeta->selectedPage=$this->Page_Selected;
			
			$this->Page_totalRecords=$this->objMeta->P_recordCount;
			$PagingArr=constructPaging($this->Page_Selected,$this->Page_totalRecords,$this->Page_Limit);
			
			
			$this->tpl->assign("PageList",$PageList);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$PagingArr['LastPage']);
			$this->tpl->assign("totalRecords", $this->Page_totalRecords);
			$this->tpl->assign("Field", $this->Field);
			$this->tpl->assign("Criteria", $this->Criteria);
			$this->tpl->assign("Search", stripslashes($this->Search));
			$this->tpl->assign("UserType",$this->LoginUserType);
			$this->tpl->assign("SortBy",$this->objMeta->OrderBy);
			$this->tpl->draw("programmedPages/programedpagesList");
		}
		
		private function editMetaDetail()
		{
			$PageId=keyDecrypt($this->pageID);
			EnPException::writeProcessLog('programmedpages_Controller :: editMetaDetail action to edit meta page details & PMV_id=>'.$PageId);
			if(!is_numeric($PageId))
		  	{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
		  	}
			if($PageId<>"" && $PageId>0)
			{
				$PageValuesArr='';
				$PageContentsArr='';
				
				$this->PMV_id=$PageId;	
				
				$fields				= array('SQL_CACHE PMV.PMV_id','PMV.PMV_metaTitleEN','PMV.PMV_metaTitleES','PMV.PMV_metaDescEN','PMV.PMV_metaDescES','PMV.PMV_metaKeywordEN','PMV.PMV_metaKeywordES','PMV.PMV_descEN',
				              			'PMV.PMV_descES','PMV.PMV_pagenameEN','PMV.PMV_pagenameES');
				$where      		= array("PMV_id"=>$this->PMV_id);
			    $PageValuesArr		= $this->objMeta->getPageMetaValue($fields,$where);
				$meteTextfields 	= array('PMT.PMT_id','PMT.PMT_PMVid','PMT.PMT_Keyword','PMT.PMT_captionEN','PMT.PMT_captionES','PMT.PMT_descEN','PMT.PMT_descES','PMT.PMT_contentEN','PMT.PMT_contentES');
				$meteTextwhere  	= array("PMT.PMT_PMVid"=>$this->PMV_id);
				$OrderBY			= "Order by PMT_SortOrder";
				
				$PageContentsArr		= $this->objMeta->getPageMetaText($meteTextfields,$meteTextwhere,$OrderBY);
				//$msgValues=EnPException::getConfirmation();
				//echo "<pre>";print_r($msgValues);exit;
				
				
				$this->tpl->assign("action",'update');
				$this->tpl->assign("PageId",$this->PMV_id);
				//$this->tpl->assign("msgValues",$msgValues);
				$this->tpl->assign("PageValues",$PageValuesArr);
				$this->tpl->assign("PageContents",$PageContentsArr);
				$this->tpl->draw('programmedPages/editmetaDetail');	
			}
				
			
		}
		
		private function update()
		{
			$this->PageId=request('post','keyId',1);
			EnPException::writeProcessLog('programmedpages_Controller :: update action to update meta page details & PMV_id=>'.$this->PageId);
			$this->PMV_pagename=request('post','pageName',0);
			/*$this->PMV_pageKeyword=request('post','pageKeyword',0);
			$this->PMV_pageKeyword=preg_replace('/[^a-zA-Z0-9]/', '_', $this->PMV_pageKeyword);*/
			$this->PMV_desc=request('post','pageDescription',0);
			$this->PMV_metaTitle=request('post','metaTitle',0);
			$this->PMV_metaKeyword=request('post','metaKeyword',0);
			$this->PMV_metaDesc=request('post','metaDescription',0);
			
			
			$this->PMV_pagename_spanish			=	request('post','pageNameSpanish',0);
			$this->PMV_desc_spanish				=	request('post','pageDescriptionSpanish',0);
			$this->PMV_metaTitle_spanish		=	request('post','metaTitleSpanish',0);
			$this->PMV_metaKeyword_spanish		=	request('post','metaKeywordSpanish',0);
			$this->PMV_metaDesc_spanish			=	request('post','metaDescriptionSpanish',0);
			
			$this->PMT_IdsArr=request('post','metaPageContentId',3);
			$this->PMT_PMVIdsArr=request('post','metaPageValuesId',3);
			
			$this->captionsArr=escapeString(request('post','caption',3));
			$this->detailsArr=escapeString(request('post','metadetail',3));
			
			
			$this->detailsArr_spanish=escapeString(request('post','metadetailspanish',3));

			try
			{
				
				$this->MetaValuesField		=	array("PMV_pagenameEN"=>$this->PMV_pagename,"PMV_pagenameES"=>$this->PMV_pagename_spanish,"PMV_descEN"=>$this->PMV_desc,"PMV_descES"=>$this->PMV_desc_spanish,"PMV_metaTitleEN"=>$this->PMV_metaTitle,"PMV_metaTitleES"=>$this->PMV_metaTitle_spanish,"PMV_metaKeywordEN"=>$this->PMV_metaKeyword,"PMV_metaKeywordES"=>$this->PMV_metaKeyword_spanish,"PMV_metaDescEN"=>$this->PMV_metaDesc,"PMV_metaDescES"=>$this->PMV_metaDesc_spanish);
				$Condition = " AND PMV_id!= ".$this->PageId;
				$this->ValidateInputForAddMetaDetails();
				if($this->P_status) $this->CheckDuplicacyForMetaPageName($Condition);
				//if($this->P_status) $this->CheckDuplicacyForMetaPageKeyword($Condition);				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."programmedpages/index/edit/".keyEncrypt($this->PageId));}
				$this->Status		=	$this->UpdateMetaDetails();
				if($this->P_status==1)
				{
					$this->UpdateAdditionalContents();
				}
				if($this->P_status==1)
				{
					$this->SetStatus(true,$this->P_ConfirmCode);
					
				}else{
					$this->SetStatus(false,$this->P_ErrorCode);
				}
			}catch(Exception $e){
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."programmedpages/index/edit/".keyEncrypt($this->PageId));

		}
		
		
		private function ValidateInputForAddMetaDetails()
		{
			EnPException::writeProcessLog('programmedpages_Controller :: ValidateInputForAddMetaDetails Function To Validate Inputs for New Meta Detail');
			if(trim($this->MetaValuesField['PMV_pagenameEN'])=='')
			{
				$this->setErrorMsg('E2004');
			}
			if(trim($this->MetaValuesField['PMV_pagenameES'])=='')
			{
				$this->setErrorMsg('E2004');
			}
			/*if(trim($this->MetaValuesField['PMV_pageKeyword'])=='')
			{
				$this->setErrorMsg('E2005');
			}*/
		}
		
		
		private function UpdateAdditionalContents()
		{
			foreach($this->captionsArr as $id => $value)
			{
				$this->PMT_id		=	$this->PMT_IdsArr[$id];
				$this->PMT_PMVid	=	$this->PMT_PMVIdsArr[$id];
				$this->PMT_content	=	$this->detailsArr[$id];
				$this->PMT_contentSpanish	=	$this->detailsArr_spanish[$id];
				$PageMetaField = "PMT_contentES='".$this->PMT_contentSpanish."',PMT_contentEN='".$this->PMT_content."'";
				$Where			   = "WHERE PMT_id=".$this->PMT_id." AND PMT_PMVid=".$this->PMT_PMVid;
				if($this->objMeta->PageMetaTextUpdate_DB(TBLPREFIX."pagemetatext",$PageMetaField,$Where))
				{
					$this->P_status=1;
				}
				else
				{
					$this->P_status=0;
					$this->setErrorMsg('E2003');
					break;
				}
			}
			return $this->P_status;
		}
		
		private function UpdateMetaDetails()
		{
			$DataArray 					= $this->MetaValuesField;
			$PageID  					= $this->PageId;
			$updateMetaDetail			= $this->objMeta->UpdateMetaValues_DB($DataArray,$PageID);
			if($updateMetaDetail)
			{
				$this->setConfirmationMsg('C2001');
			}
			else
			{
				$this->setErrorMsg('E2003');
			}
		}
		
		
		
		
		public function CheckDuplicacyForMetaPageName($condition)
		{
			
			EnPException::writeProcessLog('programmedpages_Controller :: CheckDuplicacyForMetaPageTitle Function To Check Duplicate Page Name');
			$KeywordStatus=TRUE;
			
			if(trim($this->PMV_pagename)<>'' && trim($this->PMV_pagename_spanish)<>'')
			{
				$searchField = " WHERE (PMV_pagenameEN='".$this->PMV_pagename."' OR PMV_pagenameES='".$this->PMV_pagename_spanish."')";
				$MetaDetail = $this->objMeta->CheckDuplicacyForPageValue($condition,$searchField);
				if(count($MetaDetail)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E2006');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E2007');
			}
			return $KeywordStatus;			
		}
		
		public function CheckDuplicacyForMetaPageKeyword($condition='')
		{
			EnPException::writeProcessLog('programmedpages_Controller :: CheckDuplicacyForMetaPageKeyword Function To Check Duplicate Page Keyword');
			$KeywordStatus=TRUE;
			if(trim($this->PMV_pageKeyword)<>'')
			{
				$searchField = " WHERE PMV_pageKeyword='".trim($this->PMV_pageKeyword)."'";
				$MetaKeyword=$this->objMeta->CheckDuplicacyForPageValue($condition,$searchField);
				if(count($MetaKeyword)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E2008');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E2005');
			}
			return $KeywordStatus;
		}

		public function CheckPageName()
	    {
			EnPException::writeProcessLog('programmedpages_Controller :: CheckPageKeyword action to check page keyword duplicacy');
			$keyId 						= 	request('get','keyId',1);
			$this->PMV_pagename			=	request('get','pageName',0);
			$this->PMV_pagename_spanish	=	request('get','pageNameSpanish',0);
			
			if(trim($keyId)<>'') $condition=" and PMV_id!=".$keyId;
			$Status = $this->CheckDuplicacyPageName($condition);
			echo json_encode($Status);
			exit;
	    }
		
		public function CheckDuplicacyPageName($condition)
		{
			
			EnPException::writeProcessLog('programmedpages_Controller :: CheckDuplicacyForMetaPageTitle Function To Check Duplicate Page Name');
			$KeywordStatus=TRUE;
			$searchField="";
			if(trim($this->PMV_pagename)<>'' || trim($this->PMV_pagename_spanish)<>'')
			{
				$searchField = " WHERE (PMV_pagenameEN='".$this->PMV_pagename."' OR PMV_pagenameES='".$this->PMV_pagename_spanish."')";
				$MetaDetail = $this->objMeta->CheckDuplicacyForPageValue($condition,$searchField);
				if(count($MetaDetail)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E2006');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E2007');
			}
			return $KeywordStatus;			
		}
		
		public function CheckPageKeyword()
	    {
			EnPException::writeProcessLog('programmedpages_Controller :: CheckPageKeyword action to check page keyword duplicacy');
			$keyId = request('get','keyId',1);
			$this->PMV_pageKeyword=request('get','pageKeyword',0);
			if(trim($keyId)<>'') $condition=" and PMV_id!=".$keyId;
			$Status = $this->CheckDuplicacyForMetaPageKeyword($condition);
			echo json_encode($Status);
			exit;
	    }
	
	   private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Profilelanding_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status=0;
			$this->P_ErrorCode.=$ErrCode.",";
			$this->P_ErrorMessage=$ErrCode;
			$this->MsgType=$MsgType;
		}
		private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		{
			EnPException::writeProcessLog('Profilelanding_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
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