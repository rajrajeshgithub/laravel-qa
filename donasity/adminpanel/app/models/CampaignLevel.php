<?php
class CampaignLevel_Model extends Model
{
	public $arrCategoryList,$CampaignLevelTotalRecord;
	public function __construct()
	{
		$this->arrCategoryList =  array();
	}
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('CampaignLevel_Model :: getfieldName function called');
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
	
	public function getLevelList($field=array(), $filterparam=array())
	{
		EnPException::writeProcessLog('CampaignLevel_Model :: getCategoryList function called');
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
		$Sql="Select $fieldString from ".TBLPREFIX."campaignlevel";
				
		$this->CampaignLevelTotalRecord	= db::count($Sql.$filterString);
		
		$sql_res = array();
		if($this->CampaignLevelTotalRecord>0)
		{
			//$sql_res = db::get_all($Sql.$filterString.$Order);
			$sql_res = db::get_all($Sql.$filterString);
		}
		
		if(count($sql_res) > 0)
		{
			$levelArr;
			foreach($sql_res as $key=>$val)
			{
				$levelArr[]=array('Camp_Level_ID'=>$val['Camp_Level_ID'],'Camp_Level_CampID'=>$val['Camp_Level_CampID'],'Camp_Level'=>$val['Camp_Level'],'Camp_Level_Name'=>$val['Camp_Level_Name'],'Camp_Level_Desc'=>$val['Camp_Level_Desc'],'Camp_Level_DetailJSON'=>json_decode($val['Camp_Level_DetailJSON']));
			}
			return $levelArr;
		}
		else 
		{
			return $sql_res;
		}
	}
}
?>