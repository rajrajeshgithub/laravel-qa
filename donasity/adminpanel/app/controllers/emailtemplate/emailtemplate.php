<?php
	class Emailtemplate_Controller extends Controller
	{
		
		public $TempID,$tpl,$P_status,$P_ErrorCode,$P_ConfirmCode;
		public $keyword,$mailBody,$mailBodySpanish,$TemplateDetail,$keyId,$templateName,$receiver,$receiverCc,$receiverBcc,$sender,$subject,$subjectSpanish,$description,$filterParam,$sortParam;
		public $IsActive,$currentDate,$Field,$Criteria,$Page_Selected,$OrderBy,$Search,$P_ErrorMessage,$MsgType;
		
		function __construct()
		{
			checkLogin(9);
			$this->load_model('EmailTemplate','objTpl');	
			$this->P_status=1;
		}
		
		public function index($type='list',$TempID=NULL)
		{
			$this->TempID	= keyDecrypt($TempID);	
			$this->tpl = new view;
			switch(strtolower($type))
			{				
				case 'edit':
					$this->edit();
					$this->tpl->draw("emailtemplate/editTemplate");
				break;
				case 'update':
					$this->update();
				break;
				default:
					$this->showList();
					$this->tpl->draw("emailtemplate/listing");
				break;	
			}			
		}
		
		public function update()
		{
			try
			{
				$this->getFormData();
				$this->validateMemberData();
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."emailtemplate/index/edit/".keyEncrypt($this->keyId));}
				
				/*--------------Get Template Keyword-----------------------*/
				$field				=	array("Keyword as keyword");
				$where      		= 	array("TemplateID"=>$this->keyId);
				$GetKeyword			= 	$this->objTpl->GetTemplateListing($field,$where);
				$this->keyword		=	$GetKeyword[0]['keyword'];
				/*--------------END-----------------------*/
				
				
				file_put_contents(EMAIL_TEMPLATE_DIR.$this->keyword."_EN.html", stripslashes($this->mailBody));
				
				
				file_put_contents(EMAIL_TEMPLATE_DIR.$this->keyword."_ES.html", stripslashes($this->mailBodySpanish));
				
				
				$Status	=	$this->objTpl->UpdateMetaDetail_DB($this->TemplateDetail,$this->keyId);
				
				if($Status)
				{
					$this->setConfirmationMsg('C4001');
					$this->SetStatus(true,$this->P_ConfirmCode);
				}
				else
				{
					$this->setErrorMsg('E4002');
					$this->SetStatus(false,$this->P_ErrorCode);
				}
				
				
			}catch(Exception $e){
				EnPException::exceptionHandler($e);	
			}	
			redirect(URL."emailtemplate/index/edit/".keyEncrypt($this->keyId));
				
		}
		
		public function CheckDuplicacyForTemplateName($condition)
		{
			
			EnPException::writeProcessLog('EmaiTemplate_Model :: CheckDuplicacyForTemplateName Function To Check Duplicat TemplateName');
			$KeywordStatus=TRUE;
			if(trim($this->templateName)<>'')
			{
				$searchField = " WHERE (TemplateName='".$this->templateName.")";
				$TemplateName = $this->objTpl->CheckDuplicacyForTemplalteName($searchField,$condition);
				if(count($TemplateName)>0)
				{
					$KeywordStatus=FALSE;
					$this->setErrorMsg('E4103');	
				}
			}else{
				$KeywordStatus=FALSE;
				$this->setErrorMsg('E4003');
			}
			return $KeywordStatus;			
		}
		
		
		private function validateMemberData()
		{
			if($this->TemplateDetail['TemplateName'] =='' && $this->P_status == 1)	$this->setErrorMsg('E4003');
			if($this->TemplateDetail['EmailTo']!='')
			{
				if(!filter_var($this->TemplateDetail['EmailTo'], FILTER_VALIDATE_EMAIL))
				{
					$this->setErrorMsg('E4005');	
				}
			}
			if($this->TemplateDetail['EmailToCc']!='')
			{
				if(!filter_var($this->TemplateDetail['EmailToCc'], FILTER_VALIDATE_EMAIL))
				{
					$this->setErrorMsg('E4101');	
				}
			}
			if($this->TemplateDetail['EmailToBcc']!='')
			{
				if(!filter_var($this->TemplateDetail['EmailToBcc'], FILTER_VALIDATE_EMAIL))
				{
					$this->setErrorMsg('E4102');	
				}	
			}
			if(trim($this->TemplateDetail['EmailFrom'])=='' && $this->P_status==1)
			{
				$this->setErrorMsg('E4009');	
	
			}
			if(!filter_var($this->TemplateDetail['EmailFrom'], FILTER_VALIDATE_EMAIL))
			{
				$this->setErrorMsg('E4006');	
			}						
		}
		
		private function getFormData()
		{
			$this->keyId 		= request('post','tplId',2);
			EnPException::writeProcessLog('Emailtemplate_Controller :: update action to update Email Template & TemplateID=>'.$this->keyId);
			$this->templateName 	= request('post','templateName',0);
			/*$this->keyword 			= request('post','hKeyword',0);*/
			/*$this->keyword			= request('post','keyword',0);
			$this->keyword			= preg_replace('/[^a-zA-Z0-9]/', '_', $this->keyword);*/
			$this->receiver			= request('post','receiver',0);	
			$this->receiverCc		= request('post','receiverCc',0);
			$this->receiverBcc		= request('post','receiverBcc',0);
			$this->sender			= request('post','sender',0);
			$this->subject			= request('post','subject',0);
			$this->subjectSpanish	= request('post','subjectSpanish',0);
			$this->description		= request('post','description',0);
			//$this->TagDesc			= request('post','tagDescription',0);	
			$this->mailBody			= request('post','mailBody',0);
			$this->mailBodySpanish	= request('post','mailBodySpanish',0);
			//$this->LastModifiedBy   = getSession('OneOnOneAdminLoginDetail','admin_fullname');	
			$this->IsActive			= request('post','isactive',1);
			$this->currentDate		= getdatetime();
			
			$this->TemplateDetail   = array("TemplateName"=>$this->templateName,"EmailTo"=>$this->receiver,"EmailToCc"=>$this->receiverCc,"EmailToBcc"=>$this->receiverBcc,"EmailFrom"=>$this->sender,"Subject_EN"=>$this->subject,"Subject_ES"=>$this->subjectSpanish,"Description"=>$this->description,"CreatedDate"=>$this->currentDate,"LastUpdateOn"=>$this->currentDate,"Status"=>$this->IsActive);
			
		}
		
		public function edit()
		{
			EnPException::writeProcessLog('Metadetail_Controller :: editMetaDetail action to edit meta page details & PMV_id=>'.$this->TempID);
			if(!is_numeric($this->TempID))
		  	{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
		  	}
			if($this->TempID<>"" && $this->TempID>0)
			{
				$field					=	array("SQL_CACHE TemplateID as ID","TemplateName AS tplName","EmailTo as mailTo","EmailToCc as mailToCc","EmailToBcc as mailToBcc","EmailFrom as mailFrom", "Subject_EN","Subject_ES", "Description as description","DATE_FORMAT(CreatedDate,'".AD_DATETIME."') as cDate", "DATE_FORMAT(LastUpdateOn,'".AD_DATETIME."') as uDate","Keyword as keyword","TagDescription as TagDesc", "LastModifiedBy as lastModify","Status");
				$where      			= 	array("TemplateID"=>$this->TempID);
				$TemplateDetail			= 	$this->objTpl->GetTemplateListing($field,$where);
				$TemplateDetail[0]['TagDesc'] = htmlentities($TemplateDetail[0]['TagDesc']);
				
				$TemplateDetail[0]['Body'] = '';
				$file_name_en = EMAIL_TEMPLATE_DIR . $TemplateDetail[0]['keyword'] . "_EN.html";
				if (file_exists($file_name_en))
					$TemplateDetail[0]['Body'] = file_get_contents($file_name_en);
					
				$TemplateDetail[0]['Body'] = stripslashes($TemplateDetail[0]['Body']);
				
				$TemplateDetail[0]['BodySpanish'] = '';
				$file_name_sp = EMAIL_TEMPLATE_DIR . $TemplateDetail[0]['keyword'] . "_ES.html";
				if(file_exists($file_name_sp))
					$TemplateDetail[0]['BodySpanish'] = file_get_contents($file_name_sp);
				
				$TemplateDetail[0]['BodySpanish'] = stripslashes($TemplateDetail[0]['BodySpanish']);
				
				//$msgValues=EnPException::getConfirmation();
				
				$this->tpl->assign("action",'update');
				$this->tpl->assign("TempId",$this->TempID);
				//$this->tpl->assign("msgValues",$msgValues);
				$this->tpl->assign("arrTplDetail",$TemplateDetail[0]);
			}
			
		}
	
		
		public function showList()
		{
			EnPException::writeProcessLog('Emailtemplate_Controller :: showList action to view all Email Templates');
			$this->filterParameterLists();
			$DataArray			=	array("SQL_CACHE TemplateID as ID", "Subject_EN as subject", "Description as description","TemplateName","EmailTo","EmailFrom","Keyword as keyword");
		    $TplList 			= 	$this->objTpl->GetTemplateListing($DataArray,$this->filterParam,$this->sortParam);
			
			$PagingArr = constructPaging($this->objTpl->pageSelectedPage,$this->objTpl->ETemplateTotalRecord,$this->objTpl->pageLimit);		
			$LastPage = ceil($this->objTpl->ETemplateTotalRecord/$this->objTpl->pageLimit);
			
			$this->tpl->assign("Field",$this->Field);
			$this->tpl->assign("Criteria",$this->Criteria);
			$this->tpl->assign("Search",stripslashes($this->Search));
			$this->tpl->assign("totalRecords",$this->objTpl->ETemplateTotalRecord);
			$this->tpl->assign("TplList",$TplList);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("lastPage",$LastPage);
		}
		
		private function filterParameterLists()
		{
			$this->Field 					=  request('post','searchFields',0);
			$this->Search 					=  request('post','searchValues',0);
			$this->Page_Selected			=  (int)request('post','pageNumber',1);
			$this->OrderBy					=  request('post','sortBy',0);
			$pageSelected					=  request('post','pageNumber','1');
			$this->objTpl->pageSelectedPage	= $pageSelected == 0 ? 1 : $pageSelected;
			$this->filterParam['SearchCondtionLike'] = '';
			if($this->Search!=NULL)
			{
				switch($this->Field)
				{				
					case "Subject_EN":
					case "EmailTo":
					case "EmailFrom":
					case "TemplateName":
						$this->filterParam['SearchCondtionLike'].= $this->Field." LIKE '%".$this->Search."%'";
					break;
					default:
						$this->filterParam['SearchCondtionLike'].= "Subject_EN LIKE '%".$this->Search."%'"." OR Subject_ES LIKE '%".$this->Search."%'"." OR "." EmailTo LIKE '%".$this->Search."%'"." OR "."Keyword ='".$this->Search."'"
						." OR "." EmailFrom LIKE '%".$this->Search."%'"." OR "." TemplateName LIKE '%".$this->Search."%'";
				}
			}
		}
		
		
		/*Check Template Name function used for validate Template Name and check duplicacy of Template Name */
		public function CheckTemplateName()
		{
			$this->templateName	=	get('templateName','');
			$this->TemplateID 	= 	get('tplId','');
			
			if(trim($this->TemplateID)<>'') $condition=" and TemplateID != ".$this->TemplateID;
		   	$status = $this->CheckDuplicacyForTemplateName($condition);
			echo json_encode($status);exit;	
		}
		
		
		private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Emailtemplate_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
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