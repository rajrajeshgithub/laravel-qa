<?PHP
class Campaign_Controller extends Controller
{
	public $tpl,$loginDetails;
	public $wherCondition;
	public $startDate,$keyWord,$campaign_status,$CampStatus;
	public $campID,$arrCampainDetails;
	public $P_ErrorCode,$P_ErrorMessage,$P_status,$P_MsgType,$P_ConfirmCode,$P_ConfirmMsg;
	public $Title='',$UrlFriendlyName='',$ShortDescription='',$DescriptionHTML='',$DonationGoal='',$DonationReceived='',$MinimumDonationAmount='',$EndDate='',$Level_ID='',$WebMasterComment='',$thumbImage='',$IsPrivate, $campaignType = '';

	function __construct()
	{
		checkLogin(16);
		$this->P_status = 1;
		$this->tpl	= new view;
		$this->load_model("Campaign","objcampaign");
		$this->objcampaign = new Campaign_Model();
		
		$this->load_model("Category","objcategory");
		$this->objcategory = new Category_Model();
		
		$this->load_model("CampaignLevel","objlevel");
		$this->objlevel = new CampaignLevel_Model();
		
		$this->load_model("CompaignImages","objimages");
		$this->objimages = new CompaignImages_Model();
		
		$this->load_model("CompaignVideo","objvideos");
		$this->objvideos = new CompaignVideo_Model();
		$this->loginDetails = getsession("DonasityAdminLoginDetail");		
	}
	
	public function index($type="List",$campID='')
	{
		if($campID<>'')
			$this->campID = $campID;
			
		switch(strtolower($type))
		{
			case 'edit':
				$this->edit();
			break;
			case 'update-basic':
				$this->basicDetailsUpdate();
			break;
			case 'update-duration':
				$this->durationUpdate();
			break;
			case 'update-level':
				$this->levelUpdate();
			break;
			case 'update-status':
				$this->statusUpdate();
			break;
			default:
				$this->CampaignList();
			break;	
		}
	}
	
	private function edit()
	{		
		checkLogin('16');
		
		$this->campID = keyDecrypt($this->campID);
		
		//get category list
		$this->getCategoryList();
		
		//get all level
		$this->getLevel();
		
		//get campaign images
		$this->getImages();
		
		//get campaign video
		$this->getVideos();
		
		//get campaign edit data
		$DataArray	= array('C.Camp_ID',
		'C.Camp_Cat_ID',
		'C.Camp_Level_ID',
		'C.Camp_Duration_Days',
		'C.Camp_Title',
		'C.camp_thumbImage',
		'C.camp_bgImage',
		'C.Camp_UrlFriendlyName',
		'C.Camp_ShortDescription',
		'C.Camp_Description',
		'C.Camp_DescriptionHTML',
		'C.Camp_DonationGoal',
		'C.Camp_DonationReceived',
		'C.Camp_StartDate',
		'C.Camp_EndDate',
		'C.Camp_CP_FirstName',
		'C.Camp_CP_LastName',
		'C.Camp_CP_Address1',
		'C.Camp_CP_Address2',
		'C.Camp_CP_City',
		'C.Camp_CP_State',
		'C.Camp_CP_Country',
		'C.Camp_CP_ZipCode',
		'C.Camp_CP_Email',
		'C.Camp_CP_Phone',
		'C.Camp_UserBio',
		'C.Camp_Location_City',
		'C.Camp_Location_State',
		'C.Camp_Location_Zip',
		'C.Camp_Location_Country',
		'C.Camp_Location_Logitude',
		'C.Camp_Location_Latitude',
		'C.Camp_Stripe_Status',
		'C.Camp_Stripe_ConnectedID',
		'C.Camp_Stripe_Response',
		'C.Camp_PaymentMode',
		'C.Camp_Status',
		'C.Camp_SearchTags',
		'C.Camp_WebMasterComment',
		'C.Camp_RUID',
		'C.Camp_NPO_EIN',
		'C.Camp_SocialMediaUrl',
		'C.Camp_SalesForceID',
		'C.Camp_TaxExempt',
		'C.Camp_IsPrivate',
		'C.Camp_StylingTemplateName',
		'C.Camp_StylingDetails',
		'C.Camp_MinimumDonationAmount',
		'C.Camp_Tags',
		'C.Camp_CreatedDate',
		'C.Camp_LastUpdatedDate',
		'C.camp_ProcessLog',
		'C.Camp_Locale',
		'C.Camp_Deleted',
		'C.Camp_Code',
		'C.Camp_TeamUserType',
		'CT.NPOCat_DisplayName_EN',
		'CT.NPOCat_DisplayName_ES',
		'CT.NPOCat_URLFriendlyName',
		'CT.NPOCat_ID',
		'CL.Camp_Level_Name'		
		);
		
		$this->wherCondition = " AND C.Camp_ID='".$this->campID."'";
		
		$arrCampaignList = $this->objcampaign->GetCampaignRow_DB($DataArray, $this->wherCondition);
		
		$arrCampaignList['Camp_SocialMediaUrl'] = json_decode($arrCampaignList['Camp_SocialMediaUrl']);
		$arrCampaignList['Camp_DonationReceivedPercent'] = ($arrCampaignList['Camp_DonationReceived'] / $arrCampaignList['Camp_DonationGoal'])*100;
		
		$teams = $this->GetTeamCaptain($arrCampaignList['Camp_Code'], $arrCampaignList['Camp_TeamUserType']);
		//dump($arrCampaignList['Camp_SocialMediaUrl']);
		//dump($arrCampaignList);
		$this->tpl->assign("CampaignStatus", get_setting('StatusArrayNew'));
		$this->tpl->assign("CampaignResultArray", $arrCampaignList);
		$this->tpl->assign("teams", $teams);
		$this->tpl->draw("campaign/edit");
	}
	
	private function getCategoryList()
	{
		$this->sortParam	=  "";
		$DataArray			=	array("SQL_CACHE NPOCat_ID", "NPOCat_ParentID", "NPOCat_DisplayName_EN", "NPOCat_DisplayName_ES","NPOCat_CodeName","NPOCat_URLFriendlyName","NPOCat_ShowOnWebsite");
		$CatList 		= 	$this->objcategory->getCategoryList($DataArray,array(),$this->sortParam);
		$this->objcategory->createHerarchy($CatList);
		$this->tpl->assign("category",$this->objcategory->arrCategoryList);
	}
	
	private function getLevel()
	{
		$DataArray			=	array("Camp_Level_ID", "Camp_Level_CampID", "Camp_Level", "Camp_Level_Name","Camp_Level_Desc","Camp_Level_DetailJSON");
		$LevelList 			= 	$this->objlevel->getLevelList($DataArray);
		$this->tpl->assign("level",$LevelList);
	}
	
	private function getImages()
	{
		$this->wherCondition = array("Camp_Image_CampID"=>$this->campID);
		$DataArray			 =	array("Camp_Image_ID", "Camp_Image_Name", "Camp_Image_Title");
		$ImagesList 		 = 	$this->objimages->GetCompaignImagesListing($DataArray,$this->wherCondition);
		$this->tpl->assign("images", $ImagesList);
	}
	
	private function getVideos()
	{
		$this->wherCondition = array("Camp_Video_CampID"=>$this->campID);
		$DataArray			 =	array("Camp_Video_ID", "Camp_Video_Title", "Camp_Video_File","Camp_Video_EmbedCode","Camp_Video_CampID");
		$VideosList 		 = 	$this->objvideos->GetCompaignVideoListing($DataArray,$this->wherCondition);
		$this->tpl->assign("videos",$VideosList);
	}
	
	private function GetTeamCaptain($campCode, $campType) {
		$team = array();
		if($campCode != '') {
			$DataArray = array(
				'C.Camp_ID',
  				'C.Camp_Code',
  				'C.Camp_TeamUserType',
  				'C.Camp_Title',
				'RU.RU_FistName',
  				'RU.RU_LastName',
  				'C.camp_thumbImage',
  				'C.Camp_DonationGoal',
  				'C.Camp_DonationReceived');
				
			if($campType == 'C')
				$this->wherCondition = " AND C.Camp_Code = '$campCode' AND C.Camp_TeamUserType = 'T'";
			
			if($campType == 'T')
				$this->wherCondition = " AND C.Camp_Code = '$campCode' AND C.Camp_TeamUserType IN('T','C') AND C.Camp_ID != $this->campID";
				
			$team = $this->objcampaign->GetTeamDB($DataArray, $this->wherCondition);
		}
		return $team;
	}
	
	private function FilterData()
	{
		$this->campaign_status		= request('get','campaign_status',0,0);		
		//var_dump($this->campaign_status);exit;
		$this->startDate			= request('get','StartDate',0)?request('get','StartDate',0):'';
		$this->keyWord              = request('get','keyword',0)?request('get','keyword',0):'';
		$this->campaignType = request('get', 'campaign_type', 0);
		
		if(count($_GET) < 1)
			$this->campaign_status = 6;	
			
		if($this->startDate != '') 
			$this->wherCondition = " AND DATE_FORMAT(C.Camp_StartDate,'%Y-%m-%d')<='".formatDate($this->startDate,'Y-m-d')."' AND (DATE_FORMAT(C.Camp_EndDate,'%Y-%m-%d')>='".formatDate($this->startDate,'Y-m-d')."' or C.Camp_EndDate is NULL)";
			
		if(trim($this->keyWord) && $this->keyWord!='') {
			$this->wherCondition.=" AND ((C.Camp_Title LIKE '%".$this->keyWord."%') 
									OR (C.Camp_CP_FirstName LIKE '%".$this->keyWord."%')
									OR (C.Camp_CP_City LIKE '%".$this->keyWord."%')
									OR (C.Camp_CP_State LIKE '%".$this->keyWord."%')
									OR (C.Camp_CP_Email LIKE '%".$this->keyWord."%'))";
		}
		
		if($this->campaign_status!='')
			$this->wherCondition.=" AND C.Camp_Status=$this->campaign_status";
			
		//dump($this->campaignType);
		switch($this->campaignType) {
			case 'C' :
				$this->wherCondition .= " AND C.Camp_Code!='' AND C.Camp_TeamUserType='C' ";
			break;
			case 'T' :
				$this->wherCondition .= " AND C.Camp_Code!='' AND C.Camp_TeamUserType='T' ";
			break;
			case 'O' :
				$this->wherCondition .= " AND C.Camp_TeamUserType!='T' AND C.Camp_TeamUserType!='C' ";
			break;
			default :
			break;
		}
	}
	
	private function CampaignList()
	{
		$Condition 	= $this->FilterData();
		//var_dump($this->campaign_status);exit;
		$pageSelected = (int)request('get', 'pageNumber', 1);
		$this->objcampaign->pageSelectedPage = ($pageSelected == 0) ? 1 : $pageSelected;
		
		$DataArray	= array(
			'C.Camp_Title',
			'C.Camp_ID',
			'C.Camp_StartDate',
			'C.Camp_NPO_EIN',
			'C.Camp_EndDate',
			'C.Camp_DonationGoal',
			'C.Camp_DonationReceived',
			'C.Camp_CP_FirstName',
			'C.Camp_CP_LastName',
			'C.Camp_CP_City',
			'C.Camp_CP_State',
			'C.Camp_CP_Email',
			'C.Camp_Status',
			'C.Camp_IsPrivate', 
			'C.Camp_Code', 
			'C.Camp_TeamUserType', 
			'S.State_Name',
			'C.Camp_CreatedDate',
			'C.Camp_LastUpdatedDate');
		
		$CampaignList	= $this->objcampaign->GetCampaignLists_DB($DataArray,$this->wherCondition);
		//dump($CampaignList);
		//================= pagination code start =================
		$Page_totalRecords = $this->objcampaign->CampaignListTotalRecord;
		$PagingArr=constructPaging($pageSelected, $Page_totalRecords,$this->objcampaign->pageLimit);
		$LastPage = ceil($Page_totalRecords/$this->objcampaign->pageLimit);
		
		$this->tpl->assign("pageSelected",$pageSelected);
		$this->tpl->assign("PagingList",$PagingArr['Pages']);
		$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
		$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
		$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
		$this->tpl->assign("lastPage",$LastPage);
		//================= pagination code end ===================
		
		$this->tpl->assign('totalcampaign',$this->objcampaign->CampaignListTotalRecord);
		$this->tpl->assign('CampaignList',$CampaignList);
		$this->tpl->assign('StartDate',$this->startDate);
		$this->tpl->assign('EndDate',$this->EndDate);
		$this->tpl->assign('keyword',$this->keyWord);
		$this->tpl->assign('campaign_status',$this->campaign_status);
		$this->tpl->assign('StatusArray', get_setting('StatusArrayNew'));
		$this->tpl->assign('campaign_type', $this->campaignType);
		$this->tpl->draw('campaign/listing');
	}
	private function inputData()
	{
		$this->Title = request('post','Title',0);
		$this->UrlFriendlyName = request('post','UrlFriendlyName',0);
		$this->ShortDescription = strip_tags(request('post','ShortDescription',0));
		$this->DescriptionHTML = strip_tags(request('post','DescriptionHTML',0));
		$this->DonationGoal = request('post','DonationGoal',0);
		$this->DonationReceived = request('post','DonationReceived',0);
		$this->MinimumDonationAmount = request('post','MinimumDonationAmount',0);
		$this->startDate = formatDate(request('post','StartDate',0),'Y-m-d');
		$this->EndDate = formatDate(request('post','EndDate',0),'Y-m-d');
		$this->Level_ID = request('post','Level_ID',0);
		$this->CampStatus = request('post','Status',1);
		$this->WebMasterComment = request('post','WebMasterComment',0);
		$this->IsPrivate = request('post','IsPrivate',0);		
		$this->thumbImage = isset($_FILES['thumbImage'])?$_FILES['thumbImage']:array();
		$this->objcampaign->thumbImage = isset($_FILES['thumbImage'])?$_FILES['thumbImage']:array();
		$campID = request('post','campID',0);
		if(isset($campID) && $campID!='')$this->campID = keyDecrypt($campID);
	}
	public function basicDetailsUpdate()
	{
		$this->inputData();
		$this->ValidateBasicInputData();
		if($this->P_status==0)redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
		$this->setConfirmationMsg('C15001');
		$dataArray = array('Camp_Title'=>$this->Title,'Camp_UrlFriendlyName'=>$this->UrlFriendlyName,'Camp_ShortDescription'=>$this->ShortDescription,
						   'Camp_DescriptionHTML'=>$this->DescriptionHTML,'Camp_DonationGoal'=>$this->DonationGoal,'Camp_DonationReceived'=>$this->DonationReceived,
				  		   'Camp_MinimumDonationAmount'=>$this->MinimumDonationAmount,'Camp_IsPrivate'=>$this->IsPrivate);
		$this->objcampaign->updateCampaign($dataArray,$this->campID);
		
		/*----update process log------*/
		$userType 	= 'ADMIN';			
		$userID		= $this->loginDetails['admin_id'];
		$userName	= $this->loginDetails['admin_fullname'];				
		$sMessage 	= "Error in fundraiser basic details updation";
		$lMessage 	= "Error in fundraiser basic details updation";
		if($this->objcampaign->P_status)
		{
			$sMessage = "Fundraiser basic details updated";
			$lMessage = "Fundraiser basic details updated";
		}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$this->campID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objcampaign));	
			$this->objcampaign->updateProcessLog($DataArray);	
			/*-----------------------------*/	
		
		redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
	}
	
	public function durationUpdate()
	{
		$this->inputData();
		$this->ValidateDurationInputData();
		
		if($this->P_status == 0)
			redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
			
		$this->setConfirmationMsg('C15002');
		$dataArray = array('Camp_StartDate'=>$this->startDate,'Camp_EndDate'=>$this->EndDate,'Camp_LastUpdatedDate'=>getDateTime());
		//dump($this->campID);
		$this->objcampaign->updateCampaign($dataArray, $this->campID);
		
		/*----update process log------*/
		$userType 	= 'ADMIN';			
		$userID		= $this->loginDetails['admin_id'];
		$userName	= $this->loginDetails['admin_fullname'];				
		$sMessage 	= "Error in fundraiser duration details updation";
		$lMessage 	= "Error in fundraiser duration details updation";
		if($this->objcampaign->P_status)
		{
			$sMessage = "Fundraiser details updated";
			$lMessage = "Fundraiser details updated";
		}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$this->campID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objcampaign));	
			$this->objcampaign->updateProcessLog($DataArray);	
			/*-----------------------------*/
		redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
	}
	public function levelUpdate()
	{
		$this->inputData();
		$this->setConfirmationMsg('C15003');
		$dataArray = array('Camp_Level_ID'=>$this->Level_ID);
		$this->objcampaign->updateCampaign($dataArray,$this->campID);
		/*----update process log------*/
		$userType 	= 'ADMIN';			
		$userID		= $this->loginDetails['admin_id'];
		$userName	= $this->loginDetails['admin_fullname'];				
		$sMessage 	= "Error in fundraiser level updation";
		$lMessage 	= "Error in fundraiser level updation";
		if($this->objcampaign->P_status)
		{
			$sMessage = "Fundraiser level updated";
			$lMessage = "Fundraiser level updated";
		}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$this->campID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objcampaign));	
			$this->objcampaign->updateProcessLog($DataArray);	
			/*-----------------------------*/
		redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
	}
	public function statusUpdate()
	{
		$this->inputData();		
		$this->setConfirmationMsg('C15004');
		$dataArray = array('Camp_Status'=>$this->CampStatus,'Camp_WebMasterComment'=>$this->WebMasterComment);
		$this->objcampaign->updateCampaign($dataArray,$this->campID);
		if($this->CampStatus==15 && $this->objcampaign->P_status==1)
		{
			$DataArray	= array('C.Camp_ID',
								'C.Camp_Title',
								'C.Camp_UrlFriendlyName',
								'C.Camp_StartDate',
								'C.Camp_EndDate',
								'C.Camp_CP_FirstName',
								'C.Camp_CP_LastName',
								'C.Camp_CP_Email',
								'C.Camp_Status',
								'C.Camp_RUID',
								'C.Camp_Code',
								'C.Camp_TeamUserType'
								);
		
			$Condition = " AND C.Camp_ID='".$this->campID."'";
			$arrCampainDetails = $this->objcampaign->GetCampaignRow($DataArray,$Condition);
			$this->arrCampainDetails = $arrCampainDetails[0];
			//dump($this->arrCampainDetails);			
			$this->SendEmail();
		}
		
		/*----update process log------*/
		$userType 	= 'ADMIN';
		$userID		= $this->loginDetails['admin_id'];
		$userName	= $this->loginDetails['admin_fullname'];
		$sMessage 	= "Error in fundraiser status updation";
		$lMessage 	= "Error in fundraiser status updation";
		if($this->objcampaign->P_status)
		{
			$sMessage = "Fundraiser status changed";
			$lMessage = "Fundraiser status changed";
		}
			$DataArray = array(	"UType"=>$userType,
								"UID"=>$userID,
								"UName"=>$userName,
								"RecordId"=>$this->campID,
								"SMessage"=>$sMessage,
								"LMessage"=>$lMessage,
								"Date"=>getDateTime(),
								"Controller"=>get_class()."-".__FUNCTION__,
								"Model"=>get_class($this->objcampaign));	
			$this->objcampaign->updateProcessLog($DataArray);	
			/*-----------------------------*/
		//echo $this->FR_status;exit;
		redirect(URL."campaign/index/edit/".keyEncrypt($this->campID));
	}
	
	private function SendEmail()
	{
		//dump($this->arrCampainDetails);
		$uname=$this->arrCampainDetails['Camp_CP_FirstName'].' '.$this->arrCampainDetails['Camp_CP_LastName'];
		$this->load_model('Email','objemail');
		$Keyword='VerifiedEmail';
		$where=" Where Keyword='".$Keyword."'";
		$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
		$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
		//dump($GetTemplate);
		$tpl=new View;
		$link=FRONTURL.'fundraiser/'.keyEncryptFront($this->arrCampainDetails['Camp_ID']).'/'.RemoveSpecialChars($this->arrCampainDetails['Camp_UrlFriendlyName']);
		$tpl->assign('link',$link);
		$tpl->assign('uname',$uname);
		$tpl->assign('name',$this->arrCampainDetails['Camp_Title']);
		$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
		//dump($HTML);		
		$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
		'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
		'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$this->arrCampainDetails['Camp_CP_Email'],
		'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
		$id=$this->objemail->InsertEmailDetail($InsertDataArray);
		$Eobj	= LoadLib('BulkEmail');
		
		$Status=$Eobj->sendEmail($id);
		if($Status)
		{
			$this->FR_status=1;
		}
		else
		{
			$this->FR_status=0;			
		}
		unset($Eobj);
	}
	
	private function ValidateBasicInputData()
	{
		if($this->P_status!=0)
			if(isset($this->Title) && trim($this->Title)=="")$this->setErrorMsg('E15003');
		if($this->P_status!=0)
			if($this->checkImageExtension())$this->setErrorMsg('E15009');
		if($this->P_status!=0)
			if(isset($this->DescriptionHTML) && trim($this->DescriptionHTML)=="")$this->setErrorMsg('E15006');
		if($this->P_status!=0)
			if(isset($this->DonationGoal) && trim($this->DonationGoal) == "")$this->setErrorMsg('E15007');
	}
	
	private function ValidateDurationInputData() {
		if($this->P_status != '0') {
			if(trim($this->startDate) == "")
				$this->setErrorMsg('E15008');
		}
		$this->startDate = formatDate($this->startDate, 'Y-m-d');
		$this->EndDate = formatDate($this->EndDate, 'Y-m-d');
	}
	
	private function checkImageExtension()
	{
		if(isset($_FILES['thumbImage']['name']) && $_FILES['thumbImage']['name']!='')
		{
			if(strtolower(file_ext($_FILES['thumbImage']['name']))<>'jpg' && strtolower(file_ext($_FILES['thumbImage']['name']))<>'jpeg' && strtolower(file_ext($_FILES['thumbImage']['name']))!='png' && strtolower(file_ext($_FILES['thumbImage']['name']))!='gif')
			{
				return true;
			}
		}	
		
	}
	private function setErrorMsg($ErrCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
		$errParams=array("errCode"=>$ErrCode,
						 "errMsg"=>"Custom Exception message",
						 "errOriginDetails"=>basename(__FILE__),
						 "errSeverity"=>1,
						 "msgDisplay"=>1,
						 "msgType"=>1);
		EnPException::setError($errParams);
		$this->P_status = 0;
	}
	
	private function setConfirmationMsg($ConfirmCode)
	{
		EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
		$confirmationParams=array("msgCode"=>$ConfirmCode,
								  "msgLog"=>1,									
								  "msgDisplay"=>1,
								  "msgType"=>2);
		$placeholderValues=array("placeValue1");
		EnPException::setConfirmation($confirmationParams, $placeholderValues);
		$this->P_status = 1;
	}
	
	public function getUpdateDescription()
	{
		$this->wherCondition = " ";
		$DataArray = array('C.Camp_ID','C.Camp_DescriptionHTML');
		$arrCampaignList = $this->objcampaign->GetCampaignRow($DataArray,$this->wherCondition);	
		//dump($arrCampaignList);
		if(count($arrCampaignList)>0)
		{
			foreach($arrCampaignList as $key => $value)
			{
				$descHtml = strip_tags($value['Camp_DescriptionHTML']);
				$descHtml = addslashes($descHtml);
				$campId = $value['Camp_ID'];
				$dataArray = array('Camp_DescriptionHTML'=>$descHtml);
				$this->objcampaign->updateCampaign($dataArray,$campId);
			}
		}
		redirect(URL."home");
	}
}
?>