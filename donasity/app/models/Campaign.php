<?php
	class Campaign_Model extends Model
	{
		function __construct()
		{
			$this->pageLimit=20;
			$this->pageSelectedPage=1;	
			
		}
		
		private function getfieldName($field)
		{
			EnPException::writeProcessLog('Campaign_Model :: getfieldName function called');
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
	
		public function GetCampaignDetail($table,$field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
		{
			EnPException::writeProcessLog('Campaign_Model :: GetCampaignListing function called');
			$field=$this->getfieldName($field);
			$fieldString=implode(' , ',$field);
			//dump($fieldString);
			$filterString=" Where 1=1 ";
			if(count($filterparam)>0)
			{
				foreach($filterparam as  $key => $row)
				{
					$cond="";
					switch($key)
					{
						default:
						$cond="$key='$row'";
					}
					$filterString.=" AND ( $cond )  ";
				}
			}
			$Sql="Select $fieldString from ".TBLPREFIX."$table";
			$limit 				=	 $this->pageLimit * ($this->pageSelectedPage-1);
			$limit				=	" limit ".$limit.",".$this->pageLimit;
			$this->TotalRecord	=	db::count($Sql.$filterString);
			//echo $Sql.$filterString.$limit;exit;
			if($this->TotalRecord>0)
			{
				$sql_res		=	db::get_all($Sql.$filterString.$limit);
			}
			if(!count($sql_res))$sql_res=array();return $sql_res;
		}
		
		public function GetCampaign($field=array(),$filterparam=array(),$arraySortParam=array(),$Pagenation=NULL)
		{
			EnPException::writeProcessLog('Campaign_Model :: GetCampaignListing function called');
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
						$cond="$key='$row'";
					}
					$filterString.=" AND ( $cond )  ";
				}
			}
			$Sql="Select $fieldString from ".TBLPREFIX."campaign C Left Join ".TBLPREFIX."campaignimages I ON C.Camp_ID=I.Camp_Image_CampID AND I.Camp_Image_Type='profile'";
			//echo $Sql.$filterString;exit;
			$limit 					=	$this->pageLimit * ($this->pageSelectedPage-1);
			$limit					=	" limit ".$limit.",".$this->pageLimit;		
			$this->TotalRecord		=	db::count($Sql.$filterString);
			
			if($this->TotalRecord>0)
			{
				$sql_res							=	db::get_row($Sql.$filterString.$limit);
				
			}		
			if(count($sql_res)>0)return $sql_res;$sql_res=array();
		}
		
		public function GetCampaignCategory($DataArray,$Condition)
		{
			$Where	= " WHERE 1=1";
			$Where	.=$Condition;
			$Fields		= implode(",",$DataArray); 
			$sql	= "SELECT  $Fields FROM ".TBLPREFIX."npocategories";
			$Res	=  db::get_all($sql.$Where);
			return (count($Res)>0)?$Res:array();   
		}
		
		public function GetCampaignsList($DataArray,$Condition='')
		{
			$Fields	= implode(",",$DataArray);
			$Where	= " WHERE 1=1";
			$Where	.=$Condition;
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign";
			$Res	= db::get_all($Sql.$Where);
			return (count($Res)>0)?$Res:array();	
		}
		
		public function GetPopularCampaigns($DataArray,$Condition)
		{
			$Fields	= implode(",",$DataArray);
			$Where	= " WHERE 1=1";
			$Where	.=$Condition;
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."campaign";
			$Order	= " ORDER BY Camp_DonationReceived DESC";
			$Limit	= " LIMIT 0,1";
			$Res	= db::get_all($Sql.$Where.$Order.$Limit);
			return (count($Res)>0)?$Res:array();	
		}
		
		public function CampaignUpdateDB($InputDataArray,$CampID)
		{
			return db::update(TBLPREFIX."campaign",$InputDataArray,"Camp_ID=".$CampID);
		}
		
		
		
		
}
?>