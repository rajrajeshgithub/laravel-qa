<?PHP
class Static_Controller extends Controller
{
	public $tpl;
	public $AdminName,$GroupID,$PageID=0;
	public $validateStaus=0;
	public $DataArray=array(),$P_Status;
	function __construct()
	{
		checkLogin(3);
		$this->load_model("Static","objcms");	
		$this->tpl	= new view;	
		$this->AdminName	= getSession('DonasityAdminLoginDetail','admin_fullname');
	}
	
	function index($type='list',$GroupID=NULL)
	{
		$this->GroupID	= keyDecrypt($GroupID);
		switch(strtolower($type))
		{
			case "add-group":
				$this->AddGroup();
				break;
			case "insert":
				$this->GroupInsert();
				break;	
			case "edit-group":
				$this->GroupEdit();
				break;	
			case "update-group";
				$this->UpdatGroup();
				break;
			case "delete-group":
				$this->DeleteGroup();
				break;		
			case "add-page":
				$this->AddCMSPage();
				break;	
			case "insertpage":
				$this->InsertCMSPage();
				break;
			case 'delete-page':
				$this->DeletePage();
				break;
			case 'edit-page':
				$this->EditPage();
				break;
			case 'update-page':
				$this->UpdatePage();
				break;				
			case "group-list":
				$this->GroupList();
				break;
			default:
				$this->PageList();
				break;	
		}
	}
	
	private function UpdatePage()
	{
		$this->PageInputData();
		$this->validatePageInput();//dump($this->DataArray);
		if($this->objcms->UpdatePage($this->DataArray,$this->PageID))
		{
			$this->setConfirmationMsg('C1003');		
		}
		else
		{
			$this->setErrorMsg('E3008');	
		}
		redirect($_SERVER['HTTP_REFERER']);
	}
	
	private function EditPage()
	{
		$this->PageID	= $this->GroupID;
		$PageDetail	= $this->PageDetail();
		$EnGroup	= $this->GetCMSGroup(" AND Language='en'");
		$EsGroup	= $this->GetCMSGroup(" AND Language='es'");
		$this->tpl->assign('engroup',$EnGroup);
		$this->tpl->assign('esgroup',$EsGroup);
		$this->tpl->assign('pagedetail',$PageDetail);
		$this->tpl->assign('case','editpage');
		$this->tpl->assign('selectedgroup',$PageDetail['CMSPageGroupID']);
		$this->showmsg();
		$this->tpl->draw('static/addpage');
	}
	
	private function PageDetail()
	{
		$Array	= array('CMSPagesID','CMSPageGroupID','internal_url','IsInternalLink','external_url','IsExternalLink','CMSPagesName','CMSPagesNameINURL','CMSPagesTitle','CMSPageHead','Content',
						'Metatitle','Metadesc','Metakeyword','LoginRequired','Status','GoogleAnalyticCode','Permission','DevelopersNote','SortBy','ShowLink');	
		$Condition	= " AND CMSPagesID=".$this->PageID;
		$PageDetail	= $this->objcms->GetPageDetail($Array,$Condition);				
		return $PageDetail;
	}
	
	private function DeletePage()
	{
		$this->PageID	= $this->GroupID;
		if($this->objcms->DeletePage($this->PageID))
		{
			$this->setConfirmationMsg('C1006');	
		}
		else
		{
			$this->setErrorMsg('E3007');
		}
		redirect(URL."static");
	}
	
	private function DeleteGroup()
	{
		if($this->objcms->DeleteGroup($this->GroupID))
		{
			$this->setConfirmationMsg('C1004');		
		}
		else
		{
			$this->setErrorMsg('E1004');		
		}
		redirect(URL."static/index/group-list");	
	}
	
	private function GroupEdit()
	{
		$GroupDetail	= $this->GroupDetail();
		$Language	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('language',$Language['language']);
		$this->tpl->assign('groupdetail',$GroupDetail);
		$this->tpl->assign('case','editgroup');
		$this->showmsg();
		$this->tpl->draw("static/addgroup");
	}
	
	private function UpdatGroup()
	{
		$this->InputData();
		$this->validateInput();
		if($this->objcms->UpdateGroup($this->DataArray,$this->GroupID))
		{
			$this->setConfirmationMsg('C1005');	
		}
		else
		{
			$this->setErrorMsg('E1007');
		}
		redirect($_SERVER['HTTP_REFERER']);	
	}
	
	private function PageList()
	{
		$LanguageCOnf	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('languageconf',$LanguageCOnf['language']);
		$Condition	= $this->PagesFilter();
		$Array	= array('CP.CMSPagesID','CP.CMSPagesName','CP.CMSPageGroupID','CP.external_url','CP.IsExternalLink','CP.CMSPagesNameINURL',' CPG.Title','CP.Language','CP.Status','CP.SortBy');
		$Cond	= "".$Condition;
		$Order	= " ORDER BY CMSPageGroupID,SortBy";
		$Pages	= $this->objcms->GetPages($Array,$Cond,$Order);
		for($i=0;$i<count($Pages);$i++)
		{
			$GroupIdArr[]=$Pages[$i]['GroupId'];
			if($Pages[$i]['PagesID']<>'')
			{
				$PageIdArr[]=$Pages[$i]['PagesID'];	
			}	
		}
		$this->tpl->assign('pages',$Pages);
		$this->tpl->assign('case','pagelist');
		$this->showmsg();
		$this->tpl->draw("static/pagelisting");	
	}
	
	private function GroupList()
	{
		$LanguageCOnf	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('languageconf',$LanguageCOnf['language']);
		$Condition	= $this->GroupFilter();
		$Array	= array('CMSPageGroupID','Language','Title','Status','SortingOrder');
		$Cond	= " ".$Condition;
		$Order	= " ORDER BY SortingOrder";
		$GroupList	= $this->objcms->GetGroup($Array,$Cond,$Order);
		$this->tpl->assign('case','grouplist');
		$this->tpl->assign('grouplist',$GroupList);
		$this->showmsg();
		$this->tpl->draw("static/grouplisting");	
	}
	
	
	private function GroupDetail()
	{
		$Array	= array('CMSPageGroupID','Language','Title','Status','SortingOrder');
		$Cond	= " AND CMSPageGroupID=".$this->GroupID;
		$Order	= " ORDER BY SortingOrder";
		$GroupList	= $this->objcms->GetGroup($Array,$Cond.$Order);
		return $GroupList;
	}
	
	private function GroupFilter()
	{//dump($_POST);
		$PageNumber	= request('post','pageNumber',1);
		$Title		= request('post','titlestr',0);	
		$Status		= request('post','status',3);
		$Language	= request('post','language',3);
		$Condition	= "";
		if(trim($Title)!='')
		{
			$Condition.=" AND (Title LIKE '".$Title."%')";	
		}	
		if(count($Status)>0)
		{
			$TempCond	= array();
			foreach($Status as $val)
			{
				$TempCond[]	= "Status='".$val."'";
			}
			
			$Condition.=" AND (".implode(' or ',$TempCond).")";		
		}
		if(count($Language) > 0)
		{
			$TempCond	= array();
			foreach($Language as $val)
			{
				$TempCond[]	= "Language='".$val."'";
			}
			
			$Condition.=" AND (".implode(' or ',$TempCond).")";	
		}
		$this->tpl->assign('title',$Title);
		$this->tpl->assign('status',$Status);
		$this->tpl->assign('language',$Language);
		return $Condition;
	}
	
	private function PagesFilter()
	{
		$PageNumber	= request('post','pageNumber',1);
		$Title		= request('post','titlestr',0);	
		$Status		= request('post','status',3);
		$Language	= request('post','language',3);
		$Condition	= "";
		if(trim($Title)!='')
		{
			$Condition.=" AND (CMSPagesName LIKE '".$Title."%')";	
		}	
		if(count($Status) > 0)
		{
			$TempCond	= array();
			foreach($Status as $val)
			{
				$TempCond[]	= "Status='".$val."'";
			}
			
			$Condition.=" AND (".implode(' or ',$TempCond).")";	
			//$Condition.=" AND Status='".$Status."'";		
		}
		if(count($Language) > 0)
		{
			$TempCond	= array();
			foreach($Language as $val)
			{
				$TempCond[]	= "Language='".$val."'";
			}
			
			$Condition.=" AND (".implode(' or ',$TempCond).")";	
			//$Condition.=" AND Language='".$Language."'";			
		}
		$this->tpl->assign('title',$Title);
		$this->tpl->assign('status',$Status);
		$this->tpl->assign('language',$Language);
		return $Condition;
	}
	
	private function AddGroup()
	{
		EnPException::writeProcessLog('Static_controller :: AddGroup action call');
		$Language	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('language',$Language['language']);
		$this->tpl->assign('case','addgroup');
		$this->tpl->draw("static/addgroup");	
	}
	
	private function InputData()
	{
		$Language			= request('post','language',0);
		$Title				= request('post','grouptitle',0);
		$SortOrder			= request('post','sortorder',0);
		$Status				= request('post','status',1);
		$this->GroupID		= request('post','groupid',1);
		$this->DataArray	= array('Title'=>$Title,
									'SortingOrder'=>$SortOrder,
									'Status'=>$Status,
									'Language'=>$Language);	
	}
	
	
	private function GroupInsert()
	{
		$this->InputData();
		$this->validateInput();
		$this->GroupID	= $this->objcms->InsertGroup($this->DataArray);
		if($this->GroupID > 0)
		{
			$this->setConfirmationMsg('C1001');
			redirect(URL."static/index/edit-group/".keyEncrypt($this->GroupID));	
		}
		else
		{
			$this->setErrorMsg('E1006');
			redirect(URL."static/index/add-group");	
		}
	}
	
	private function validateInput()
	{
		EnPException::writeProcessLog('static_Controller :: validateInput Function call');	
		if(trim($this->DataArray['Language'])=='' && $this->P_Status==1)
		{
			$this->setErrorMsg('E1005');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if(trim($this->DataArray['Title'])=='' )
		{
			$this->setErrorMsg('E1001');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	
	
	private function AddCMSPage()
	{
		$EnGroup	= $this->GetCMSGroup(" AND Language='en'");
		$EsGroup	= $this->GetCMSGroup(" AND Language='es'");
		$this->tpl->assign('selectedgroup',$this->GroupID);
		$this->tpl->assign('case','addpage');
		$this->tpl->assign('engroup',$EnGroup);
		$this->tpl->assign('esgroup',$EsGroup);
		$this->tpl->draw("static/addpage");	
	}
	
	private function InsertCMSPage()
	{
		$this->PageInputData();
		$this->validatePageInput();
		$PageID		= $this->objcms->InsertPage($this->DataArray);
		if($PageID > 0)
		{
			$this->setConfirmationMsg('C1002');	
			redirect(URL."static/index/edit-page/".keyEncrypt($PageID));	
		}
		else
		{
			$this->setErrorMsg('E3001');	
			redirect(URL."static/index/add-page");		
		}
	}
	
	public function validatePageInput()
	{
		EnPException::writeProcessLog('static_Model :: validateInput Function call');	
		if($this->DataArray['CMSPageGroupID']=='' || $this->DataArray['CMSPageGroupID']==0)
		{
			$this->setErrorMsg('E3006');
			redirect($_SERVER['HTTP_REFERER']);
		}
		
		if(trim($this->DataArray['CMSPagesName'])== '' )
		{
			$this->setErrorMsg('E3003');
			redirect($_SERVER['HTTP_REFERER']);
		}
		if(($this->DataArray['IsInternalLink']!=1 && $this->DataArray['internal_url']!='') || ($this->DataArray['IsExternalLink']!=1 && $this->DataArray['external_url']!=''))
		{
			if(trim($this->DataArray['CMSPagesNameINURL'])== '' )
			{
				$this->setErrorMsg('E3004');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
		if(trim($this->DataArray['CMSPagesTitle'])== '' )
		{
			$this->setErrorMsg('E3005');
			redirect($_SERVER['HTTP_REFERER']);
		}
	}
	
	private function PageInputData()
	{
		//dump($_REQUEST);
		$LanguageGroupID	= request('post','cmsgroup',0);	
		$LanguageGroupID	= explode('|',$LanguageGroupID);
		$GroupID			= $LanguageGroupID[0];
		$Language			= $LanguageGroupID[1];
		$PageName			= request('post','pagename',0);
		$FriendlyUrl		= request('post','friendlyurl',0);
		$PageTitle			= request('post','pagetitle',0);
		$SortOrder			= request('post','sortorder',1);
		$LoginRequired		= request('post','requiredlogintoBrowse',1);
		$WebsiteDisplay		= request('post','displayonWebsite',1);
		//$BottomDisplay		= request('post','displayonbottom',1);
		$BottomDisplay		= 1;
		$IsExternalLink		= request('post','externalpageLink',1);
		$ExternalURL		= request('post','externalpageUrl',0);
		$IsInternalLink		= request('post','internalpageLink',1);
		$InternalURL		= request('post','internalpageUrl',0);
		$CmsPageHead        = request('post','CmsPageHead',0,0);
		$landingPageHtml	= request('post','landingpageHtml',0,0);	
		
		$MetaTitle			= request('post','metaTitle',0);
		$MetaDescription	= request('post','metaDescription',0);
		$MetaKeyword		= request('post','metaKeyword',0);
		$GoogleTrakingCode	= request('post','googletrackingCode',0);
		$PagePermission		= request('post','pagePermission',1);
		$DeveloperNotes		= request('post','developerNotes',0);
		
		$this->PageID				= request('post','pageid',1);
		
		$this->DataArray			= array('CMSPageGroupID'=>$GroupID,
									'internal_url'=>$InternalURL,
									'IsInternalLink'=>$IsInternalLink,
									'external_url'=>$ExternalURL,
									'IsExternalLink'=>$IsExternalLink,
									'CMSPagesName'=>$PageName,
									'CMSPagesNameINURL'=>$FriendlyUrl,
									'CMSPagesTitle'=>$PageTitle,
									'CMSPageHead'=>$CmsPageHead,
									'Content'=>$landingPageHtml,
									'Metatitle'=>$MetaTitle,
									'Metadesc'=>$MetaDescription,
									'Metakeyword'=>$MetaKeyword,
									'LoginRequired'=>$LoginRequired,
									'Status'=>$WebsiteDisplay,
									'GoogleAnalyticCode'=>$GoogleTrakingCode,
									'CreatedBy'=>$this->AdminName,
									'AddedDate'=>getDateTime(),
									'ModifiedDate'=>getDateTime(),
									'LastModifiedBy'=>'',
									'Permission'=>$PagePermission,
									'DevelopersNote'=>$DeveloperNotes,
									'SortBy'=>$SortOrder,
									'ShowLink'=>$BottomDisplay,
									'Language'=>$Language);
		if($this->PageID > 0)unset($this->DataArray['AddedDate']);
	}
	
	private function GetCMSGroup($where='')
	{
		$Array	= array('CMSPageGroupID','Language','Title');
		$Cond	= " AND Status='1'".$where;
		return $this->objcms->GetGroup($Array,$Cond);
	}
	
	private function setErrorMsg($ErrCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$errParams=array("errCode"=>$ErrCode,
						 "errMsg"=>"Custom Exception message",
						 "errOriginDetails"=>basename(__FILE__),
						 "errSeverity"=>1,
						 "msgDisplay"=>1,
						 "msgType"=>$this->objcms->MsgType);
		EnPException::setError($errParams);
	}
	private function setConfirmationMsg($ConfirmCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
		$confirmationParams=array("msgCode"=>$ConfirmCode,
									 "msgLog"=>1,									
									 "msgDisplay"=>1,
									 "msgType"=>$this->objcms->MsgType);
		$placeholderValues=array("placeValue1");
		EnPException::setConfirmation($confirmationParams, $placeholderValues);
	}
	
	public function CheckGroupName()
   {
	   $this->load_model("Static","objcms");	
		$status=false;
		$condition='';
		$GroupID = request('get','GroupID',1);
		$GroupTitle	= request('get','grouptitle',0);
		$condition	= " AND Title ='".$GroupTitle."'";
		if(trim($GroupID)<>0) $condition.=" and CMSPageGroupID!=".$GroupID;
		$status	= $this->objcms->CheckDuplicacyForCMSGroup($condition);
		echo json_encode($status);
		exit;
   }
   
   public function CheckPageName()
   {
		$this->load_model("Static","objcms");	
		$status=false;
		$condition='';
		$PageID = request('get','PageID',1);
		$PageTitle	= request('get','pagename',0);
		$condition=" AND CMSPagesName='".$PageTitle."'";
		if(trim($PageID)<>0) $condition.=" and CMSPagesID!=".$PageID;
		$status=$this->objcms->CheckPageNameDuplicacy($condition);
		echo json_encode($status);
		exit;
   }
   
   public function CheckUrl()
   {
		$this->load_model("Static","objcms");	
		$status=false;
		$condition='';
		$PageID = request('get','PageID',1);
		$FriendlyURL	= request('get','friendlyurl',0);
		$condition=" AND CMSPagesNameINURL='".$FriendlyURL."'";
		if(trim($PageID)<>0) $condition.=" and CMSPagesID!=".$PageID;
		$status=$this->objcms->CheckURLDuplicacy($condition);
		echo json_encode($status);
		exit;
   }
	
	private function showmsg()
	{
		$msgValues=EnPException::getConfirmation(false);			
		$this->tpl->assign('msgValues',$msgValues);
	}
	
	private function sendemail()
	{
		$Emailobj=LoadLib('Email');
		$Emailobj->tplId='Template2';
		$Emailobj->tplVarArr=array('tpl_message'=>'test message');
		$Emailobj->ntfVarArr=array('ntf_EmailTo'=>'qualdev.test@gmail.com');
		$Emailobj->sendEmail(); 
	} 
}
?>