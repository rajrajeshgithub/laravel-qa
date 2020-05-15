<?php
class Common_Model extends Model
{
	function __construct()
	{
		
	}
			
	public function getConfigDeail($CodeArr=array())
	{
		if(count($CodeArr)>0)
		{
			$ConfigCodes=	implode("','",$CodeArr);
			$sql = "SELECT Code, Caption, ConfigValues as ConfigValues, ConfigValuesCode as ConfigValuesCode FROM ".TBLPREFIX."configuration WHERE Code IN('".$ConfigCodes."')";	
			$sql_res = db::get_all($sql);
			$arrConfiguration = array();
			if($sql_res!=NULL)
			{
				foreach($sql_res as $key => $value)
				{
					$ArrCaption 	= explode('||',$value['ConfigValues']);		
					$ArrCode		= explode('||',$value['ConfigValuesCode']);
					foreach($ArrCaption as $key1 => $value1)
					{
						$arrConfiguration[$value['Code']][$ArrCode[$key1]] = $value1;
					}
				}	
				return count($arrConfiguration)>0?$arrConfiguration:array();
			}
		}
	}
	
	public function getConfig()
	{
		$sql = "SELECT Code, Caption, ConfigValues as ConfigValues, ConfigValuesCode as ConfigValuesCode FROM ".TBLPREFIX."configuration WHERE 1";	
		$sql_res = db::get_all($sql);
		return $sql_res;
	}	
	
	public function getConfig1()
	{
		$sql = "SELECT ConfigID, ConfigKeyword, ConfigCode, ConfigValue FROM ".TBLPREFIX."configuration1 WHERE 1";	
		$sql_res = db::get_all($sql);
		return $sql_res;
	}
	
	function getCountriesList()
	{
		$sql = "SELECT Country_ID,Country_Title,Country_Abbrivation,Country_Active
				FROM ".TBLPREFIX."country ORDER BY Country_Title";
		return db::get_all($sql);
	}
	
	function getStateList($CountryID=NULL)
	{
		$WHERE	= "";
		if($CountryID != NULL)
		{
			$WHERE	= 	"WHERE State_Country='".$CountryID."'";
		}
		$sql = "SELECT State_ID, State_Country,State_Name,State_Value,State_Active,Country_ID
				FROM ".TBLPREFIX."states $WHERE ORDER BY State_Name";
		//echo $sql;exit;		
		return db::get_all($sql);
	}
	
	function getCategoryList($Condition="")
	{
		$sql	= "SELECT NPOCat_ID,NPOCat_ParentID,NPOCat_DisplayName_EN FROM ".TBLPREFIX."npocategories";
		$Order	= " ORDER BY NPOCat_DisplayName_EN";
		$WHERE	= " WHERE NPOCat_ShowOnWebsite = '1'";
		$WHERE .=$Condition;
		$Res	= db::get_all($sql.$WHERE.$Order);//echo $sql.$WHERE.$Order;
		return (count($Res)>0)?$Res:array();
	}
	
	public function getModuleListDB($field=array(),$filterparam='')
	{	
		$arrModuleList = $this->getModuleList($field,$filterparam);			
		$arrModuleAll = $this->createHirachy($arrModuleList);
		return $arrModuleAll;	
	}
	public function getModuleList($field=array(),$filterparam='')
	{	
		$fields		= implode(',',$field);
				
		$sql 		= "SELECT $fields FROM ".TBLPREFIX."modules ";
		$orderBy 	= "ORDER BY Module_SortingOrder";
		//echo "</br>";
		//echo $sql.$filterparam.$orderBy;
		$res = db::get_all($sql.$filterparam.$orderBy);
		$res = count($res)?$res:0;
		return $res;	
	}
	
	private function createHirachy($arrMearge,$parent_id =0)
	{
		$return = array();
		
			foreach($arrMearge as $key => $value)
			{
				if($value['Module_ParentID']==$parent_id)
				{
					$return[$key] = $value;
					$return[$key]['children'] = $this->createHirachy($arrMearge,$value['Module_ID']);	
				}
				
			}			
		
		return $return;			
	}
	
	function getParentID()
	{
		$arrField  = array("Module_ID","Module_ParentID","Module_Style","Module_Desc","Module_Caption","Module_Url");
		$filterWhere = " Where Module_Active='1' ";			
		$arrModule = $this->getModuleListDB($arrField,$filterWhere);	
	}
	
	public function GetNPODetail($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		$fields		= implode(',',$field);	
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
		$Sql="Select $fields from ".TBLPREFIX."npodetails";
		//echo $Sql.$filterString;exit;
		
		$this->NpototalRecord						=	db::count($Sql.$filterString);
		
		if($this->NpototalRecord>0)
		{
			$sql_res							=	db::get_row($Sql.$filterString);
		}
		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
	}
	
	public function getCountryDB()
	{
		$Sql	= "SELECT Country_ID,Country_Title,Country_Abbrivation FROM ".TBLPREFIX."country WHERE Country_Active='1'";
		$Res	= db::get_all($Sql);
		return (count($Res)>0)?$Res:array();	
	}
	
	public function checkUpdate()
	{
		$sql = "SELECT * FROM dns_npodetails where NPO_UniqueCode IS NULL limit 0,1000";
		$res = db::get_all($sql);
		return $res;
	}
	
	public function updateCheck($fields,$id)
	{
		db::update('dns_npodetails',$fields,"NPO_ID=".$id);	
	}
	
	public function getProductList()
	{
		$Sql = "SELECT PI_ID, PI_ItemCode, PI_ItemType, PI_ItemName_EN, PI_ItemDescription_EN, PI_ItemCost FROM dns_purchaseitems WHERE PI_ItemType='DS'";
		$Res = db::get_all($Sql);
		return (count($Res)>0)?$Res:array();
	}
}
?>