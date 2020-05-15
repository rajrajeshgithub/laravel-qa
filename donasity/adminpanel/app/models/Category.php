<?php
class Category_Model extends Model
{
	public $arrCategoryList,$NpoCategoryTotalRecord;
	public function __construct()
	{
		$this->arrCategoryList =  array();
	}
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('Category_Model :: getfieldName function called');
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
	public function getCategoryList($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('Category_Model :: getCategoryList function called');
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
		
		
		$this->NpoCategoryTotalRecord				=	db::count($Sql.$filterString);
		
		$Order	= " ORDER BY NPOCat_SortOrder";
		if($this->NpoCategoryTotalRecord>0)
		{
			$sql_res							=	db::get_all($Sql.$filterString.$Order);
		}
		
		/*if(count($sql_res)>0)
		{
			//$this->createHerarchy($sql_res);
			//$sql_res = $this->arrCategoryList;
		}*/
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
		$this->arrCategoryList = $arrCategoryNew;
		
	}
}

?>