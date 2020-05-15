<?php
class CompaignImages_Model extends Model
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
		EnPException::writeProcessLog('CompaignImages_Model :: getfieldName function called');
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

	public function GetCompaignImagesListing($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
	{
		EnPException::writeProcessLog('CompaignImages_Model :: GetCompaignImagesListing function called');
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
		$Sql="Select $fieldString from ".TBLPREFIX."campaignimages";
		
		$limit = $this->pageLimit * ($this->pageSelectedPage - 1);
		$limit = " limit " . $limit . "," . $this->pageLimit;
		
		$this->totalRecord = db::count($Sql . $filterString);
		//echo $Sql.$filterString.$limit;exit;
		$sql_res = array();
		if($this->totalRecord > 0)
			$sql_res = db::get_all($Sql . $filterString . $limit);
		
		if(!count($sql_res))
			$sql_res = array();
			
		return $sql_res;
	}
	
	public function CImagesInsert_DB($Table,$DataArray)
	{
		db::insert($Table,$DataArray);
		return db::get_last_id();
	}
	
	public function UpdateCImage_DB($DataArray,$CI_ID)
	{
		EnPException::writeProcessLog('CompaignImages_Model :: UpdateCImage_DB function called');
		db::update(TBLPREFIX.'campaignimages',$DataArray,"Camp_Image_ID = ".$CI_ID);
		return db::is_row_affected()?1:0;
	}
	
	public function DeleteDetail_DB($CI_ID)
	{
		return db::delete(TBLPREFIX."campaignimages","Camp_Image_ID=".$CI_ID);	
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

	
}

?>