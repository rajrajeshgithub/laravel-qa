<?PHP
class Campaign_Model extends Model
{
	public $CampaignListTotalRecord,$pageLimit,$pageSelectedPage;
	public $CampaignResultArray;
	public $P_status,$thumbImage;
	public function __construct()
	{
		$this->pageLimit=50;
		$this->pageSelectedPage=1;
		$this->P_status=1;
	}
	
	public function GetCampaignLists_DB($DataArray,$Condition='')
	{
		$Fields	= implode(",",$DataArray);	
		$Sql	= "SELECT $Fields FROM " . TBLPREFIX . "campaign C LEFT JOIN " . TBLPREFIX . "states S ON (C.Camp_CP_State=S.State_Value)";
		$Condition = " WHERE 1=1 " . $Condition;
		$Group = " GROUP BY C.Camp_ID";	
		$StartIndex	= ($this->pageSelectedPage - 1) * $this->pageLimit;
		$Limit = " LIMIT " . $StartIndex . ", " . $this->pageLimit;
		//echo $Sql.$Condition.$Group.$Limit;exit;
		
		$this->CampaignResultArray	= db::get_all($Sql.$Condition.$Group.$Limit);
		$this->CampaignListTotalRecord	= db::count($Sql.$Condition.$Group);
		return (count($this->CampaignResultArray)>0)?$this->CampaignResultArray:array();	
	}
	public function GetCampaignRow_DB($DataArray,$Condition='')
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign C
				   INNER JOIN ".TBLPREFIX."npocategories CT ON (CT.NPOCat_ID=C.Camp_Cat_ID)
				   INNER JOIN ".TBLPREFIX."campaignlevel CL ON (CL.Camp_Level_ID=C.Camp_Level_ID)
				   LEFT JOIN ".TBLPREFIX."states S ON (C.Camp_CP_State=S.State_Value)";
		$Condition= " WHERE 1=1 ".$Condition;
		//dump($Sql.$Condition);
		$this->CampaignResultArray	= db::get_row($Sql.$Condition);
		return count($this->CampaignResultArray)>0?$this->CampaignResultArray:array();
	}
	
	public function GetCampaignRow($DataArray,$Condition='')
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign C WHERE 1=1";
		if($Condition!='')
			$Sql .= $Condition;
			//echo $Sql;exit;
		$listCamp = db::get_all($Sql);
		return count($listCamp)>0?$listCamp:array();
	}
	
	public function updateCampaign($DataArray,$CampID)
	{
		db::update(TBLPREFIX.'campaign',$DataArray,"Camp_ID=".$CampID);
		if(isset($this->thumbImage['name']) && $this->thumbImage['name'] != '')
			$this->uploadImage($CampID);
			
		if(db::is_row_affected())
		{
			$this->P_status=1;
				
		}else
		{
			$this->P_status=0;
		}
	}
	private function uploadImage($CampID)
	{
		$objFile=LoadLib('UploadFile');
		$objFile->phyPath=CAMPAIGN_MAIN_IMAGE_DIR;
		$objFile->ext=file_ext($this->thumbImage['name']);
		$objFile->customName=strUnique();
		$this->thumbImage['name']=$objFile->customName.'.'.strtolower($objFile->ext);
		$objFile->Uploadfile=$this->thumbImage;
		$Image=$objFile->ProcessUploadFile();
		
		$DataArray = array('camp_thumbImage'=>$Image);
		db::update(TBLPREFIX.'campaign',$DataArray,"Camp_ID=".$CampID);
	}
	private function setErrorMsg($ErrCode,$MsgType=1,$Status=0)
	{
		EnPException::writeProcessLog('Campaign_Model setErrorMsg function Call for Error Code :: '.$ErrCode);
		$this->P_ErrorCode=$ErrCode;
		$this->P_ErrorMessage=$ErrCode;
		$this->P_status=$Status;
		$this->P_MsgType=$MsgType;
	}
	private function setConfirmationMsg($ConfirmCode,$MsgType=2,$Status=1)
	{
		EnPException::writeProcessLog('Campaign_Model setConfirmationMsg function Call For Confirmation Code :: '.$ConfirmCode);
		$this->P_ConfirmCode=$ConfirmCode;
		$this->P_ConfirmMsg=$ConfirmCode;
		$this->P_status=$Status;
		$this->P_MsgType=$MsgType;
	}
	
	public function updateProcessLog($DataArray)
	{
		
		$FieldArray = array("DateTime"=>$DataArray['Date'],
							"ModelName"=>$DataArray['Model'],
							"ControllerName"=>$DataArray['Controller'],
							"UserType"=>$DataArray['UType'],
							"UserName"=>$DataArray['UName'],
							"UserID"=>$DataArray['UID'] = ($DataArray['UID']!='')?$DataArray['UID']:0,
							"RecordID"=>$DataArray['RecordId'] = ($DataArray['RecordId']!='')?$DataArray['RecordId']:0,								
							"SortMessage"=>$DataArray['SMessage'],
							"LongMessage"=>$DataArray['LMessage'],);							
		
		db::insert(TBLPREFIX."processlog",$FieldArray);
		$id = db::get_last_id();
		$id = ($id)?$id:0;
		return $id;
	}
	
	// get team by campaign code
	public function GetTeamDB($dataArray, $condition='') {
		$fields	= implode(", ", $dataArray);
		$sql = "SELECT $fields FROM " . TBLPREFIX . "campaign C LEFT JOIN " . TBLPREFIX . "registeredusers RU ON C.Camp_RUID = RU.RU_ID";
		$condition = " WHERE 1 " . $condition;
		//dump($sql.$condition);
		$teams = array();
		if(db::count($sql . $condition) > 0)
			$teams = db::get_all($sql . $condition);
		
		return $teams;
	}
}
?>