<?php
class NpoCategory_Model extends Model
{
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=100;
		$this->RightpageLimit=3;
		$this->pageSelectedPage=1;
		$this->MemberListTotalRecord=0;
		$this->arrCategoryList =  array();
	}
	
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('NpoCategory_Model :: getfieldName function called');
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
	
	public function GetNpoCategoryListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('NpoCategory_Model :: GetRegUserListing function called');
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
		$Sql="Select $fieldString from ".TBLPREFIX."npocategories";
		
		$limit 									=	 $this->pageLimit * ($this->pageSelectedPage-1);
		$limit									=	" limit ".$limit.",".$this->pageLimit;
		
		$this->NpoCategoryTotalRecord				=	db::count($Sql.$filterString);
		
		$Order	= " ORDER BY NPOCat_SortOrder";
		
		$sql_res = array();
		if($this->NpoCategoryTotalRecord>0)
		{
			$sql_res							=	db::get_all($Sql.$filterString.$Order.$limit);//echo $Sql.$filterString.$Order.$limit;
		}
		
		if(count($sql_res)>0)
		{
			$this->createHerarchy($sql_res);
			$sql_res = $this->arrCategoryList;
		}
		
		if(count($sql_res)>0)
		{
			foreach($sql_res as $key => &$value)
			{
				if($value['NPOCat_ParentID'] != 0)
				{
					$value['ParentName']	= $this->GetParentName($sql_res,$value['NPOCat_ParentID']);		
				}
				else
				{
					$value['ParentName']	= "-";	
				}
			}	
		}
		//dump($sql_res);
		
		foreach($sql_res as &$val)
		{
			$val['NPOCat_DisplayName_EN']	=	str_replace('\,',',',$val['NPOCat_DisplayName_EN']);
		}
		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
	}
	
	public function GetParentName($arrayAll,$parentID=0)
	{
		$ParentName	= "";
		foreach($arrayAll as $key =>$value)
		{
			if($value['NPOCat_ID'] == $parentID)
			{
				//$ParentName	= $value['NPOCat_DisplayName_EN'];
				$ParentName	= $value;
				break;
			}						
		}
		return $ParentName;
	}
	
	public function GetNpoCategoryParentList_DB($field=array(),$filterparam=array())
	{
		EnPException::writeProcessLog('NpoCategory_Model :: GetNpoCategoryParentList_DB function called');
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
					case "NPOCat_ID":
						$cond = "$key!=$row";
					break;
					case "NPOCat_ParentID":
						$cond = "$key!=$row";
					break;
					default:
						$cond = "$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."npocategories";
		
		//$sql_res = db::get_all($Sql.$filterString.$limit);				
		$sql_res = db::get_all($Sql.$filterString);
		if(count($sql_res)>0)
		{	
			$array = array();
			$this->createHerarchy($sql_res);
			$sql_res = $this->arrCategoryList;
			
		}		
		if(!count($sql_res))$sql_res=array();return $sql_res;	
	}
	
	public function GetNpoCategoryDetail($field=array(),$filterparam=array())
	{
		EnPException::writeProcessLog('NpoCategory_Model :: GetNpoCategoryDetail function called');
		$field=$this->getfieldName($field);
		$fieldString=implode(' , ',$field);			
		$filterString=" Where 1=1 ";	
		$limit	= "";
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
		$Sql="Select $fieldString from ".TBLPREFIX."npocategories";
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
			//dump($parentID, 0);
			if($value['NPOCat_ParentID']==$parentID)
			{
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_ID'] = $value['NPOCat_ID'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_ParentID'] = $value['NPOCat_ParentID'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_DisplayName_EN'] = $value['NPOCat_DisplayName_EN'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_DisplayName_ES'] = $value['NPOCat_DisplayName_ES'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_CodeName'] = $value['NPOCat_CodeName'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_URLFriendlyName'] = $value['NPOCat_URLFriendlyName'];
				$arrCategoryNew[$value['NPOCat_ID']]['NPOCat_ShowOnWebsite'] = $value['NPOCat_ShowOnWebsite'];
				$arrCategoryNew[$value['NPOCat_ID']]['level'] = $level;
				$arrCategoryNew[$value['NPOCat_ID']]['space'] = $spaces;
				$arrCategory = $this->createHerarchy($arr_res,$arrCategoryNew,$level+1,$value['NPOCat_ID']);					
			}
			
		}	
		//echo "<pre>";
		//print_r($arrCategoryNew);
		$this->arrCategoryList = $arrCategoryNew;
		
	}
	
	public function NCategoryInsertMethod_DB($Table,$DataArray)
	{
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}
	
	public function UpdateNpoCategory_DB($DataArray,$NCID)
	{
		EnPException::writeProcessLog('NpoCategory_Model :: UpdateNpoCategory_DB function called');
		db::update(TBLPREFIX.'npocategories',$DataArray,"NPOCat_ID = ".$NCID);
		return db::is_row_affected()?1:0;
	}
	
	public function CheckDuplicacyForCategoryCode($condition='',$searchField)
	{
		$sql="SELECT NPOCat_URLFriendlyName FROM ".TBLPREFIX."npocategories";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	public function DeleteCategory_DB($CategoryID)
	{
		$query = "Delete from ".TBLPREFIX."npocategories  where NPOCat_ID IN(".$CategoryID.")";
		db::query($query);
		return db::is_row_affected()?1:0;
		//return db::delete(TBLPREFIX."npobankdetails","NPO_BD_ID=".$BankID);	
	}
	
	public function UpdateCategory_DB($CategoryID)
	{
		$query = "update ".TBLPREFIX."npocategories set NPOCat_ShowOnWebsite = '0'  where NPOCat_ID IN(".$CategoryID.")";
		//echo $query;exit;
		db::query($query);
		return db::is_row_affected()?1:0;
	}
	
	//npo category relation function 
	public function GetNPOSubSectionName($DataArray,$limit='',$Condition=NULL,$Order=NULL)
	{
		$fields	= implode(",",$DataArray);
		$Sql	= "SELECT $fields FROM ".TBLPREFIX."npodetails";//echo $Sql.$Condition.$Order.$limit;exit;
		$Res	= db::get_all($Sql.$Condition.$Order.$limit);
		return (count($Res)>0)?$Res:array();
	}
	
	public function CreateCategoryRelationDB($Subsection,$catName,$CatID)
	{
		$Sql	= "INSERT INTO ".TBLPREFIX."npocategoryrelation(NPO_CategoryName,NPOCat_ID,NPO_CatName)
					VALUES ('".$Subsection."','".$CatID."','".$catName."')ON DUPLICATE KEY UPDATE NPO_CategoryName= VALUES(NPO_CategoryName),NPOCat_ID=VALUES(NPOCat_ID),NPO_CatName=VALUES(NPO_CatName)";
		//echo $Sql;exit; 			
		db::query($Sql);
		return true;
		//return db::is_row_affected()?true:false;			
	}
	
	public function GetExistRelationIDDB($Param=NULL)
	{
		$Where	= " WHERE 1=1";
		if($Param	!= NULL)	
		{
			$Where.=$Param;	
		}
		$Sql	= "SELECT NPOCat_ID FROM ".TBLPREFIX."npocategoryrelation";
		$Row	= db::get_row($Sql.$Where);
		return (isset($Row['NPOCat_ID']))?$Row['NPOCat_ID']:0;
	}
	
	public function RemoveCategoryRelationDB($Subsection)
	{
		$query = "Delete from ".TBLPREFIX."npocategoryrelation  where NPO_CategoryName = '".$Subsection."'";
		db::query($query);
		return db::is_row_affected()?1:0;
	}
	//npo category relation function 
	
	
	public function GetNpoCategoryName($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('NpoCategory_Model :: GetRegUserListing function called');
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
		$Sql="Select $fieldString from ".TBLPREFIX."npocategories";
		$Order	= " ORDER BY NPOCat_SortOrder";
		$sql_res	= db::get_row($Sql.$filterString.$Order);//echo $Sql.$filterString.$Order.$limit;
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
	}
}

?>