<?php
	class SaleSubscription_Model extends Model
	{
		
		public $SS_Id,$P_Status;
		
		function __construct()
		{	
			$this->P_status=1;	
		}
		
		
		public function getSaleSubscriptionDetails($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);
			if($this->SS_Id)
			{
				$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscription WHERE 1=1 AND SS_ID=".$this->SS_Id." ";
				if($condition!='')
					$Sql .= $condition;
					
				$sql_res = db::get_row($Sql);
				if(!count($sql_res))$sql_res=array();return $sql_res;
			}	
		}
		
		public function updateSaleSubscriptionDetails($DataArray,$condition=NULL)
		{
			$where = '';
			if($condition!=NULL)
				$where .= $condition; 
			else
				$where = "SS_ID=".$this->SS_Id;
			db::update(TBLPREFIX.'salesubscription',$DataArray,$where);
			if(db::is_row_affected())
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;
			}
		}
		
		public function GetSaleTransactionDetails($FieldsArray=array('*'), $condition='')
		{
			$strFields = implode(",",$FieldsArray);
			if($this->SSPT_Id)
			{
				$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscriptionpaymenttransaction SSP 
						LEFT JOIN ".TBLPREFIX."salesubscription SS ON(SS.SS_ID=SSP.SSPT_SSID) WHERE 1=1 ";
				if($condition!='')
					$Sql .= $condition;
					else
						$Sql .= " AND SSPT_ID=".$this->SSPT_Id;
						
						//echo $Sql;exit;
						$this->SaleSubscriptionTransactionDetails = db::get_row($Sql);
			}
			if(!count($this->SaleSubscriptionTransactionDetails))$this->SaleSubscriptionTransactionDetails = array();return $this->SaleSubscriptionTransactionDetails;
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