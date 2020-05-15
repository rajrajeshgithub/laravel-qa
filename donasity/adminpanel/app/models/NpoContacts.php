<?php
class NpoContacts_Model extends Model
{
	public $pageLimit,$pageSelectedPage;
	public $totalRecord;
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=20;
		$this->pageSelectedPage=1;
	}
	
	private function getfieldName($field)
	{
		EnPException::writeProcessLog('NpoContacts_Model :: getfieldName function called');
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
	
	/*public function GetNPOContactDetailsListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('NpoContacts_Model :: GetContactDetailsListing function called');
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
					case 'NPO_EIN':
					$cond="$key='$row'";
					break;
					default:
					$cond="$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."npodetails";
		
		$limit 									=	 $this->pageLimit * ($this->pageSelectedPage-1);
		$limit									=	" limit ".$limit.",".$this->pageLimit;
		
		$this->NpototalRecord						=	db::count($Sql.$filterString);
		//echo $this->NpototalRecord;exit;
		//echo $Sql.$filterString;exit;
		if($this->NpototalRecord>0)
		{
			$sql_res							=	db::get_row($Sql.$filterString.$limit);
			//dump($sql_res);
		}
		
		if(!count($sql_res))$sql_res=array();return $sql_res;
		
	}*/
	
	
	public function GetContactListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('NpoContacts_Model :: GetContactDetailsListing function called');
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
					case 'RU_Status':
					$cond="$key='$row'";
					break;
					case 'NPO_CD_ID':
					$cond="$key='$row'";
					break;
					default:
					$cond="$key=$row";
				}
				$filterString.=" AND ( $cond )  ";
			}
		}
		$Sql="Select $fieldString from ".TBLPREFIX."npocontactdetails";
		
		$limit 									=	 $this->pageLimit * ($this->pageSelectedPage-1);
		$limit									=	" limit ".$limit.",".$this->pageLimit;
		
		$this->totalRecord = db::count($Sql.$filterString);
		//echo $Sql.$filterString.$limit;exit;
		$sql_res = array();
		if($this->totalRecord > 0)
			$sql_res = db::get_all($Sql.$filterString.$limit);
		
		if(!count($sql_res))
			$sql_res = array();
			
		return $sql_res;
		
	}
	
	
	public function NpoContactInsert_DB($Table,$DataArray)
	{
		//echo "<pre>".print_r($Table).print_r($DataArray);exit;
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}
	
	public function UpdateContactDetail_DB($DataArray,$CID)
	{
		EnPException::writeProcessLog('NpoContacts_Model :: UpdateContactDetail_DB function called');
		db::update(TBLPREFIX.'npocontactdetails',$DataArray,"NPO_CD_ID = ".$CID);
		return db::is_row_affected()?1:0;
	}

	
	public function CheckDuplicacyForEmail($condition='',$searchField)
	{
		$sql="SELECT NPO_CD_EmailAddress FROM ".TBLPREFIX."npocontactdetails";
		return $row=db::get_all($sql.$searchField.$condition);
	}
	
	public function DeleteContact_DB($ContactID)
	{
		$query = "Delete from ".TBLPREFIX."npocontactdetails  where NPO_CD_ID IN(".$ContactID.")";
		db::query($query);
		return db::is_row_affected()?1:0;
		//return db::delete(TBLPREFIX."npocontactdetails","NPO_CD_ID=".$ContactID);	
	}
	
}

?>