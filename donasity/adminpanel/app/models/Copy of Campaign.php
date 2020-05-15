<?php
class Campaign_Model extends Model
{
	public $CampID;
	public $StartDate,$EndDate;
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=20;
		$this->RightpageLimit=3;
		$this->pageSelectedPage=1;
		$this->CampaignListTotalRecord=0;
		$this->arrCampaignList =  array();
	}
	
	public function CampaignInsertDB($InputDataArray)
	{
		db::insert(TBLPREFIX."campaign",$InputDataArray);
		return db::get_last_id(); 
	}
	
	public function CampaignListingDB($DataArray,$Condition)
	{
		$Fields	= implode(",",$DataArray);	
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign C 
				   LEFT JOIN ".TBLPREFIX."states S ON (C.Camp_CP_State=S.State_Value)";
		$Group	= " GROUP BY C.Camp_ID";	
		$StartIndex	= ($this->pageSelectedPage-1)*$this->pageLimit;
		$Limit = " LIMIT ".$StartIndex.", ".$this->pageLimit;	   
		$Res	= db::get_all($Sql.$Condition.$Group.$Limit);//echo $Sql.$Condition.$Group.$Limit;
		$this->CampaignListTotalRecord	= db::count($Sql.$Condition.$Group);
		return (count($Res)>0)?$Res:array();		   
	}
	
	public function getCityListDB()
	{
		$Sql	= "SELECT DISTINCT Camp_CP_City FROM ".TBLPREFIX."campaign WHERE Camp_CP_City <> ''";	
		$Res	= db::get_all($Sql);
		return (count($Res)>0)?$Res:array();
	}
	
	public function getCategoryList($DataArray,$Condition)
	{		
		$Fields	= implode(",",$DataArray);
		$filterString=" Where 1=1 ";	
		if(count($Condition)>0)
		{
			foreach($Condition as  $key => $row)
			{
				$cond="";
				switch($key)
				{
					default:
					$cond="$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npocategories";
		//echo $Sql.$filterString;exit;
		$Res 	= db::get_all($Sql.$filterString);		
		return (count($Res)>0)?$Res:array();
		
	}
	
	public function GetCategory($Condition='')
	{
		$WHERE	= " WHERE CampCat_ShowOnWebsite='1'";
		$Sql	= "SELECT CampCat_ID,CampCat_ParentID,CampCat_DisplayName_EN,CampCat_DisplayName_ES,CampCat_UrlFriendlyName FROM ".TBLPREFIX."campaigncategories ";
		$Res	= db::get_all($Sql.$WHERE);	
		if(count($Res) > 0)
		{
			return $this->createHerarchy($Res);	
		}
		else
		{
			return array();	
		}
	}
	
	public function CampaignUpdateDB($InputDataArray,$CampID)
	{
		return db::update(TBLPREFIX."campaign",$InputDataArray,"Camp_ID=".$CampID);
	}
	
	public function UpdateStartEndDate()
	{
			$Sql	= "SELECT Camp_StartDate,Camp_Duration_Days FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$this->CampID;
			$Row	= db::get_row($Sql);
			if(isset($Row) && $Row['Camp_StartDate']!='')
			{
				//if start date is less than current date
				if(date('Y-m-d',strtotime($Row['Camp_StartDate']) < date('Y-m-d')))
				{
					$this->StartDate = date('Y-m-d');
				}
				else if(date('Y-m-d',strtotime($Row['Camp_StartDate']) > date('Y-m-d')))
				{
					$this->StartDate = $this->addDayswithdate($Row['Camp_StartDate'],$Row['Camp_Duration_Days']);
				}
				
			}
			else
			{
				$this->StartDate = date('Y-m-d');	
			}
			$this->EndDate = $this->addDayswithdate($this->StartDate,$Row['Camp_Duration_Days']);
	}
	
	function addDayswithdate($date,$days){
	
		$date = strtotime("+".$days." days", strtotime($date));
		return  date("Y-m-d", $date);
	
	}
	public function CampaignDetailDB($DataArray,$CampID)
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign WHERE Camp_ID=".$CampID;
		//echo $Sql;exit; 
		$Row	= db::get_row($Sql);
		return ($Row['Camp_ID']>0)?$Row:array();
	}	
	
	public function createHerarchy($arr_res,&$arrCategoryNew=array(),$level=1,$parentID=0)
	{
		$arrCategory = array();
		foreach($arr_res as $key => $value)
		{
			$spaces ='';
			for($i=0;$i<$level;$i++)
			{
				if($i==0)
				{
					$spaces ='';
				}
				else
				{
					$spaces .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; 
				}
			}
			if($value['CampCat_ParentID']==$parentID)
			{
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_ID'] = $value['CampCat_ID'];
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_ParentID'] = $value['CampCat_ParentID'];
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_DisplayName_EN'] = $value['CampCat_DisplayName_EN'];
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_DisplayName_ES'] = $value['CampCat_DisplayName_ES'];
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_UrlFriendlyName'] = $value['CampCat_UrlFriendlyName'];
				if(isset($value['NPOCat_ShowOnWebsite'])){
				$arrCategoryNew[$value['CampCat_ID']]['NPOCat_ShowOnWebsite'] = $value['NPOCat_ShowOnWebsite'];
				}
				$arrCategoryNew[$value['CampCat_ID']]['level'] = $level;
				$arrCategoryNew[$value['CampCat_ID']]['space'] = $spaces;
				$arrCategory = $this->createHerarchy($arr_res,$arrCategoryNew,$level+1,$value['CampCat_ID']);					
			}
			
		}	
			return $arrCategoryNew;
	}
	
	public function GetCampaignLevelDB()
	{
		$Sql	= "SELECT Camp_Level_ID,Camp_Level_Name	FROM ".TBLPREFIX."campaignlevel";
		$Res	= db::get_all($Sql);
		return (count($Res)>0)?$Res:array();	
	}
	
	public function CheckuserfriendlyurlDuplicacyDB($UserFriendlyUrl,$CampID)
	{
		$WHERE	= " WHERE Camp_UrlFriendlyName='".$UserFriendlyUrl."'";
		if($CampID > 0)
		{
			$WHERE.=" AND Camp_ID <> ".$CampID;	
		}	
		$Sql	= "SELECT Camp_ID FROM ".TBLPREFIX."campaign";//echo $Sql.$WHERE;exit;
		$row	= db::get_row($Sql.$WHERE);
		return (isset($row['Camp_ID']) && $row['Camp_ID'] > 0)?false:true;
	}
	
	public function DeleteCampaignDB($CampID)
	{
		return db::update(TBLPREFIX."campaign",array("Camp_Deleted"=>"1"),"Camp_ID=".$CampID);	
	}
	
	
	public function CampaignImagesDB($DataArray,$Condition)
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaignimages";
		$Res	= db::get_all($Sql.$Condition);//echo $Sql.$Condition;
		return (count($Res)>0)?$Res:array();	
	}
	
	public function CampaignVideosDB($DataArray,$Condition)
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaignvideo";
		$Order	= " ORDER BY Camp_Video_SortOrder";
		$Res	= db::get_all($Sql.$Condition.$Order);//echo $Sql.$Condition;
		return (count($Res)>0)?$Res:array();	
	}
	
	public function EINExistDB($NPO_EIN)
	{
		$Sql	= "SELECT NPO_ID FROM ".TBLPREFIX."npodetails WHERE NPO_EIN=".$NPO_EIN;
		$Row	= db::get_row($Sql);
		return (isset($Row['NPO_ID']) && $Row['NPO_ID'] > 0)?true:false;
	}
	
}

?>