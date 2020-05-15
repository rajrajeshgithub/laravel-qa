<?PHP
class Cms_Controller extends Controller
{
	public $tpl;
	public $AdminName,$GroupID,$PageID=0;
	public $validateStaus=0;
	function __construct()
	{
		$this->load_model("Cms","objcms");	
		$this->tpl	= new view;	
		$this->AdminName='';
	}
	
	function index($type,$GroupID=NULL)
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
		$DataArray	= $this->PageInputData();
		$this->objcms->PageID	= $this->PageID;
		try{
			$this->objcms->UpdatePage($DataArray);
		}catch(Exception $e)
		{
			EnPException::exceptionHandler($e);	
		}	
		$pStat=EnPException::$EnP_processStatus;
		if($this->objcms->P_Status=='1') 
		{
			$this->setConfirmationMsg($this->objcms->ConfirmCode);
			redirect(URL."cms");	
		} else {
			$this->setErrorMsg($this->objcms->ErrorCode);
			redirect(URL."cms");
		}
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
		$this->tpl->draw('cms/addpage');
	}
	
	private function PageDetail()
	{
		$Array	= array('CMSPagesID','CMSPageGroupID','internal_url','IsInternalLink','external_url','IsExternalLink','CMSPagesName','CMSPagesNameINURL','CMSPagesTitle','Content',
						'Metatitle','Metadesc','Metakeyword','LoginRequired','Status','GoogleAnalyticCode','Permission','DevelopersNote','SortBy','ShowLink');	
		$Condition	= " AND CMSPagesID=".$this->PageID;
		$PageDetail	= $this->objcms->GetPageDetail($Array,$Condition);				
		return $PageDetail;
	}
	
	private function DeletePage()
	{
		$this->objcms->PageID	= $this->GroupID;
		try {
			$this->objcms->DeletePage();
		}catch(Exception $e){
			EnPException::exceptionHandler($e);
		}
		
		$pStat=EnPException::$EnP_processStatus;
		if($this->objcms->P_Status=='1') 
		{
			$this->setConfirmationMsg($this->objcms->ConfirmCode);
			redirect(URL."cms");	
		} else {
			$this->setErrorMsg($this->objcms->ErrorCode);
			redirect(URL."cms");
		}
	}
	
	private function DeleteGroup()
	{
		$this->objcms->GroupID	= $this->GroupID;
		try {
			$this->objcms->DeleteGroup();
		}catch(Exception $e){
			EnPException::exceptionHandler($e);
		}
		
		$pStat=EnPException::$EnP_processStatus;
		if($this->objcms->P_Status=='1') 
		{
			$this->setConfirmationMsg($this->objcms->ConfirmCode);
			redirect(URL."cms/index/group-list");	
		} else {
			$this->setErrorMsg($this->objcms->ErrorCode);
			redirect(URL."cms/index/group-list");
		}		
	}
	
	private function GroupEdit()
	{
		$GroupDetail	= $this->GroupDetail();
		$Language	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('language',$Language['language']);
		$this->tpl->assign('groupdetail',$GroupDetail);
		$this->tpl->assign('case','editgroup');
		$this->tpl->draw("cms/addgroup");
	}
	
	private function UpdatGroup()
	{
		$this->InputData();
		try {
			$this->objcms->UpdateGroup();
		}catch(Exception $e){
			EnPException::exceptionHandler($e);
		}	
		
		$pStat=EnPException::$EnP_processStatus;
		if($this->objcms->P_Status=='1') 
		{
			$this->setConfirmationMsg($this->objcms->ConfirmCode);
			redirect(URL."cms/index/group-list");	
		} else {
			$this->setErrorMsg($this->objcms->ErrorCode);
			redirect($_SERVER['HTTP_REFERER']);	
		}
	}
	
	private function PageList()
	{
		$LanguageCOnf	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('languageconf',$LanguageCOnf['language']);
		$msgValues=EnPException::getConfirmation();
		$this->tpl->assign("msgValues",$msgValues);
		$Condition	= $this->PagesFilter();
		$Array	= array('CMSPagesID','CMSPagesName','Status','SortBy','Language');
		$Cond	= "".$Condition;
		$Order	= " ORDER BY SortBy";
		$Pages	= $this->objcms->GetPages($Array,$Cond,$Order);
		$this->tpl->assign('pages',$Pages);
		$this->tpl->assign('case','pagelist');
		$this->tpl->draw("cms/pagelisting");	
	}
	
	private function GroupList()
	{
		$msgValues=EnPException::getConfirmation();
		$this->tpl->assign("msgValues",$msgValues);
		$LanguageCOnf	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('languageconf',$LanguageCOnf['language']);
		$Condition	= $this->GroupFilter();
		$Array	= array('CMSPageGroupID','Language','Title','Status','SortingOrder');
		$Cond	= " ".$Condition;
		$Order	= " ORDER BY SortingOrder";
		$GroupList	= $this->objcms->GetGroup($Array,$Cond,$Order);
		$this->tpl->assign('case','grouplist');
		$this->tpl->assign('grouplist',$GroupList);
		$this->tpl->draw("cms/grouplisting");	
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
		$Status		= request('post','status',0);
		$Language	= request('post','language',0);
		$Condition	= "";
		if(trim($Title)!='')
		{
			$Condition.=" AND Title='".$Title."'";	
		}	
		if($Status!='')
		{
			$Condition.=" AND Status='".$Status."'";		
		}
		if($Language !='')
		{
			$Condition.=" AND Language='".$Language."'";			
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
		$Status		= request('post','status',0);
		$Language	= request('post','language',0);
		$Condition	= "";
		if(trim($Title)!='')
		{
			$Condition.=" AND CMSPagesName='".$Title."'";	
		}	
		if($Status!='')
		{
			$Condition.=" AND Status='".$Status."'";		
		}
		if($Language !='')
		{
			$Condition.=" AND Language='".$Language."'";			
		}
		$this->tpl->assign('title',$Title);
		$this->tpl->assign('status',$Status);
		$this->tpl->assign('language',$Language);
		return $Condition;
	}
	
	private function AddGroup()
	{
		EnPException::writeProcessLog('CMS_controller :: AddGroup action call');
		$msgValues=EnPException::getConfirmation();
		//echo "<pre>";print_r($msgValues);exit;
		$this->tpl->assign("msgValues",$msgValues);
		$Language	= GetConfigurationDetail(array('language'));
		$this->tpl->assign('language',$Language['language']);
		$this->tpl->assign('case','addgroup');
		$this->tpl->draw("cms/addgroup");	
	}
	
	private function InputData()
	{
		$Language	= request('post','language',0);
		$Title		= request('post','grouptitle',0);
		$SortOrder	= request('post','sortorder',0);
		$Status		= request('post','status',1);
		$GroupID	= request('post','groupid',1);
		$DataArray	= array('Title'=>$Title,
							'SortingOrder'=>$SortOrder,
							'Status'=>$Status,
							'Language'=>$Language);	
						
		$this->objcms->Language 	= $Language;
		$this->objcms->Title 		= $Title;
		$this->objcms->SortOrder 	= $SortOrder;
		$this->objcms->Status 		= $Status; 	
		$this->objcms->GroupID		= $GroupID;			
							
		//return $DataArray;					
	}
	
	
	private function GroupInsert()
	{
		$this->InputData();
		try {
			$this->objcms->InsertGroup();
		}catch(Exception $e){
			EnPException::exceptionHandler($e);
		}
		
		$pStat=EnPException::$EnP_processStatus;
		if($this->objcms->P_Status=='1') 
		{
			$this->setConfirmationMsg($this->objcms->ConfirmCode);
			redirect(URL."cms/index/add-group");	
		} else {
			$this->setErrorMsg($this->objcms->ErrorCode);
			redirect(URL."cms/add-group");	
		}
	}
	
	
	private function GroupInsert_bk()
	{
		$DataArray	= $this->InputData();
		$GroupID	= $this->objcms->InsertGroup($DataArray);
		$status		= ($GroupID > 0)?1:0;
		if($GroupID > 0)
		{
			$this->SetStatus($status,'C1001');
		}
		else
		{
			$this->SetStatus($status,'E1001');
		}
		redirect(URL."cms/add-group");	
	}
	
	
	private function AddCMSPage()
	{
		$msgValues=EnPException::getConfirmation();
		$this->tpl->assign("msgValues",$msgValues);
		$EnGroup	= $this->GetCMSGroup(" AND Language='en'");
		$EsGroup	= $this->GetCMSGroup(" AND Language='es'");
		$this->tpl->assign('selectedgroup',$this->GroupID);
		$this->tpl->assign('case','addpage');
		$this->tpl->assign('engroup',$EnGroup);
		$this->tpl->assign('esgroup',$EsGroup);
		$this->tpl->draw("cms/addpage");	
	}
	
	private function InsertCMSPage()
	{
		$DataArray	= $this->PageInputData();
		$PageID		= $this->objcms->InsertPage($DataArray);
		$status		= ($PageID > 0)?1:0;
		if($this->objcms->P_Status=='1')
		{
			$confirmationParams=array("msgCode"=>$this->objcms->ConfirmCode,
									 "msgLog"=>1,									
									 "msgDisplay"=>1,
									 "msgType"=>$this->objcms->MsgType);
			$placeholderValues=array("placeValue1");
			EnPException::setConfirmation($confirmationParams, $placeholderValues);
			redirect(URL."cms");	
		}
		else
		{
			$errParams=array("errCode"=>$this->objcms->ErrorCode,
							 "errMsg"=>"Custom Exception message",
							 "errOriginDetails"=>basename(__FILE__),
							 "errSeverity"=>1,
							 "msgDisplay"=>1,
							 "msgType"=>$this->objcms->MsgType);
			EnPException::setError($errParams);
			redirect(URL."cms/index/add-page");		
		}
			/*if($PageID > 0)
			{
				$this->SetStatus($status,'C1002');
			}
			else
			{
				$this->SetStatus($status,'E1002');
			}*/
		
	}
	
	private function InsertCMSPagebk()
	{
		$DataArray	= $this->PageInputData();
		$PageID		= $this->objcms->InsertPage($DataArray);
		$status		= ($PageID > 0)?1:0;
		if($PageID > 0)
		{
			$this->SetStatus($status,'C1002');
		}
		else
		{
			$this->SetStatus($status,'E1002');
		}
		redirect(URL."cms/index/add-page");	
	}
	
	private function PageInputData()
	{
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
		$BottomDisplay		= request('post','displayonbottom',1);
		$IsExternalLink		= request('post','externalpageLink',1);
		$ExternalURL		= request('post','externalpageUrl',0);
		$IsInternalLink		= request('post','internalpageLink',1);
		$InternalURL		= request('post','internalpageUrl',0);
		$landingPageHtml	= request('post','landingpageHtml',0);	
		$MetaTitle			= request('post','metaTitle',0);
		$MetaDescription	= request('post','metaDescription',0);
		$MetaKeyword		= request('post','metaKeyword',0);
		$GoogleTrakingCode	= request('post','googletrackingCode',0);
		$PagePermission		= request('post','pagePermission',1);
		$DeveloperNotes		= request('post','developerNotes',0);
		
		$this->PageID				= request('post','pageid',1);
		
		$DataArray			= array('CMSPageGroupID'=>$GroupID,
									'internal_url'=>$InternalURL,
									'IsInternalLink'=>$IsInternalLink,
									'external_url'=>$ExternalURL,
									'IsExternalLink'=>$IsExternalLink,
									'CMSPagesName'=>$PageName,
									'CMSPagesNameINURL'=>$FriendlyUrl,
									'CMSPagesTitle'=>$PageTitle,
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
		if($this->PageID > 0)unset($DataArray['AddedDate']);
		return $DataArray;							
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
	
	
}
?>