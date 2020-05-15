<?php
class Registration_Model extends Model
{
	function __construct()
	{
		
	}
		
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('Registration_Model :: getfieldName function called');
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
	
	public function GetRegUserListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('Registration_Model :: GetRegUserListing function called');
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
					case 'SearchCondtionLike':
					$cond=" $row ";
					break;
					case 'RU_Status':
					$cond="$key='$row'";
					break;
					default:
					$cond="$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."registeredusers";
		
		$limit 						= $this->pageLimit * ($this->pageSelectedPage-1);
		$limit						= " limit ".$limit.",".$this->pageLimit;		
		$this->DonorTotalRecord		= db::count($Sql.$filterString);
		//echo $Sql.$filterString.$limit;exit;
		if($this->DonorTotalRecord>0)
		{
			$sql_res				= db::get_all($Sql.$filterString.$limit);
		}
		if(!count($sql_res))$sql_res=array();return $sql_res;
	}
	
	public function RegUserInsertMethod_DB($Table,$DataArray)
	{
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}
	
	public function CheckDuplicacyForEmail($condition='',$searchField)
	{
		$sql="SELECT RU_EmailID FROM ".TBLPREFIX."registeredusers";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	public function UpdateDonorDetail_DB($DataArray,$RID)
	{
		EnPException::writeProcessLog('Registration_Model :: UpdateDonorDetail_DB function called');
		db::update(TBLPREFIX.'registeredusers',$DataArray,"RU_ID = ".$RID);
		return db::is_row_affected()?1:0;
	}		
}

?>