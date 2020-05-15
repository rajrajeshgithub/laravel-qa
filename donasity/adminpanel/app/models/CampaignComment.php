<?php
class CampaignComment_Model extends Model
{
	public function __construct()
	{
		//$this->pageLimit=RECORD_LIMIT;
		$this->pageLimit=20;
		$this->pageSelectedPage=1;
		$this->TotalRecord=0;
	}
	
	public function InsertDB($DataArray)
	{
		db::insert(TBLPREFIX."campaigncomments",$DataArray);	
		return db::get_last_id();
	}
	
	public function CampaignCommentDetailDB($DataArray,$ComID)
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaigncomments WHERE Camp_Cmt_ID=".$ComID;
		//echo $Sql;exit; 
		$Row	= db::get_row($Sql);
		return ($Row['Camp_Cmt_ID']>0)?$Row:array();
	}
	
	public function CampaignCommentListingDB($DataArray,$Condition)
	{
		$Fields	= implode(",",$DataArray);
		$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaigncomments";
		$Order	= " ORDER BY Camp_Cmt_LastUpdatedDate DESC";
		$Res	= db::get_all($Sql.$Condition.$Order);//echo $Sql.$Condition;exit;
		return (count($Res)>0)?$Res:array();
	}
	
	public function UpdateDB($InputDataArray,$ComID)
	{
		return db::update(TBLPREFIX."campaigncomments",$InputDataArray,"Camp_Cmt_ID=".$ComID);	
	}
	
	public function DeleteDB($ComID)
	{
		return db::delete(TBLPREFIX."campaigncomments","Camp_Cmt_ID=".$ComID);	
	}
}

?>