<?php
class CampaignCategory_Model extends Model
{
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=20;
		$this->RightpageLimit=3;
		$this->pageSelectedPage=1;
		$this->MemberListTotalRecord=0;
		$this->arrCategoryList =  array();
	}
	
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('CampaignCategory_Model :: getfieldName function called');
		foreach($field  as $key =>  &$row)
		{
			switch($row)
			{
				default:
				$row="$row";
			}
		}
		return $field;
	}
	
	public function GetCampaignCategoryList_DB($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('CampaignCategory_Model :: GetCampaignCategoryList_DB function called');
		$field=$this->getfieldName($field);
		$fieldString=implode(' , ',$field);			
		$filterString=" Where 1=1 ";
		
		if(count($filterparam)>0)
		{
			foreach($filterparam as  $key => $row)
			{
				$cond="";
				switch($key)
				{		
							
					default:
					$cond = "$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."campaigncategories";
		
		$limit 									=	 $this->pageLimit * ($this->pageSelectedPage-1);
		$limit									=	" limit ".$limit.",".$this->pageLimit;
		
		$this->CampCategoryTotalRecord				=	db::count($Sql.$filterString);
		//echo $Sql.$filterString.$limit;exit;
		if($this->CampCategoryTotalRecord>0)
		{
			$sql_res							=	db::get_all($Sql.$filterString.$limit);
		}
		
		if(count($sql_res)>0)
		{
			$this->createHerarchy($sql_res);
			$sql_res = $this->arrCategoryList;
		}
		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
	}
	
	public function GetCampaignCategoryParentList_DB($field=array(),$filterparam=array())
	{
		EnPException::writeProcessLog('CampaignCategory_Model :: GetCampaignCategoryParentList_DB function called');
		$field=$this->getfieldName($field);
		$fieldString=implode(' , ',$field);			
		$filterString=" Where 1=1 ";
		
		if(count($filterparam)>0)
		{
			foreach($filterparam as  $key => $row)
			{
				$cond="";
				switch($key)
				{	
					case "CampCat_ID":
						$cond = "$key!=$row";
					break;
					case "CampCat_ParentID":
						$cond = "$key!=$row";
					break;
					default:
						$cond = "$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."campaigncategories";
		
		$sql_res	= db::get_all($Sql.$filterString.$limit);		
		if(count($sql_res)>0)
		{	
			$array = array();
			$this->createHerarchy($sql_res);
			$sql_res = $this->arrCategoryList;
		}		
		if(!count($sql_res))$sql_res=array();return $sql_res;
	}
	
	public function GetCampCategoryDetail_DB($field=array(),$filterparam=array())
	{
		EnPException::writeProcessLog('CampaignCategory_Model :: GetCampCategoryDetail_DB function called');
		$field=$this->getfieldName($field);
		$fieldString=implode(' , ',$field);			
		$filterString=" Where 1=1 ";	
		if(count($filterparam)>0)
		{
			foreach($filterparam as  $key => $row)
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
		$Sql="Select $fieldString from ".TBLPREFIX."campaigncategories";
		$sql_res	=	db::get_row($Sql.$filterString.$limit);		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
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
				$arrCategoryNew[$value['CampCat_ID']]['CampCat_ShowOnWebsite'] = $value['CampCat_ShowOnWebsite'];
				$arrCategoryNew[$value['CampCat_ID']]['level'] = $level;
				$arrCategoryNew[$value['CampCat_ID']]['space'] = $spaces;				
				$arrCategory = $this->createHerarchy($arr_res,$arrCategoryNew,$level+1,$value['CampCat_ID']);
				//echo "<pre>";print_r($arrCategoryNew);
			}			
		}
			
		$this->arrCategoryList = $arrCategoryNew;
		
	}
	
	
	public function InsertCampCatetoryDetails_DB($Table,$DataArray)
	{
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}
	
	public function UpdateCampCategoryDetails_DB($DataArray,$NCID)
	{
		EnPException::writeProcessLog('CampaignCategory_Model :: UpdateCampCategoryDetails_DB function called');
		db::update(TBLPREFIX.'campaigncategories',$DataArray,"CampCat_ID = ".$NCID);
		return db::is_row_affected()?1:0;
	}
	
	public function CheckDuplicacyForUrlFriendlyName_DB($condition='',$searchField)
	{
		$sql="SELECT CampCat_UrlFriendlyName FROM ".TBLPREFIX."campaigncategories";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	
}

?>