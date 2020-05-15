<?php
	class SaleSubscription_Model extends Model
	{
		public $pageSelectedPage, $pageLimit, $result = array(), $totalRecord;
		public $SS_Id,$P_Status,$SaleSubscriptionArray,$SaleSubscriptionTransactionArray,$SSPT_Id, $SaleSubListTotalRecord = 0;
		
		function __construct()
		{
			$this->pageLimit=20;
			$this->pageSelectedPage=1;
			$this->P_status=1;	
		}
		
		public function getSaleSubscriptionList($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);	
			$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscription WHERE 1=1 ";
				if($condition!='')
					$Sql .= $condition;
			$StartIndex	= ($this->pageSelectedPage - 1) * $this->pageLimit;
			$Limit = " LIMIT " . $StartIndex . ", " . $this->pageLimit;
			$OrderBy = " order by SS_DateTime DESC";
			//echo $Sql.$Limit;exit;
			$this->SaleSubscriptionArray	= db::get_all($Sql.$OrderBy.$Limit);
			$this->SaleSubListTotalRecord	= db::count($Sql);
			return (count($this->SaleSubscriptionArray)>0)?$this->SaleSubscriptionArray:array();
		}
		
		public function getSaleSubscriptionListSimple($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);	
			$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscription WHERE 1=1 ";
				if($condition!='')
					$Sql .= $condition;
			//echo $Sql;exit;
			$this->SaleSubscriptionArray	= db::get_all($Sql);
			if(!count($this->SaleSubscriptionArray)) $this->SaleSubscriptionArray = array();return $this->SaleSubscriptionArray;
			
		}
		
		public function getSaleSubscriptionTransactionListSimple($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);	
			$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscriptionpaymenttransaction LEFT JOIN ".TBLPREFIX."salesubscription ON(SSPT_SSID=SS_ID) WHERE 1=1 ";
				if($condition!='')
					$Sql .= $condition;
			//echo $Sql;exit;
			$this->SaleSubscriptionTransactionArray	= db::get_all($Sql);						
			if(!count($this->SaleSubscriptionTransactionArray)) $this->SaleSubscriptionTransactionArray = array();			
			return $this->SaleSubscriptionTransactionArray;
			
		}
		
		public function getSalSubscriptionTransactionDetails($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);	
			if($this->SSPT_Id)
			{
				$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscriptionpaymenttransaction WHERE 1=1 ";
				if($condition!='')
					$Sql .= $condition;
				else
					$Sql .= " AND SSPT_ID=".$this->SSPT_Id;
					//echo $Sql;exit;
				$this->SaleSubscriptionTransactionDetails = db::get_row($Sql);		
			}
			if(!count($this->SaleSubscriptionTransactionDetails))$this->SaleSubscriptionTransactionDetails = array();return $this->SaleSubscriptionTransactionDetails;
		}
		
		
		public function insertSaleSubscriptionDetails($dataArray)
		{
			db::insert(TBLPREFIX.'salesubscription',$dataArray);			
			return db::get_last_id();	
		}
		
		public function insertSaleSubscriptionTransactionDetails($dataArray)
		{
			db::insert(TBLPREFIX.'salesubscriptionpaymenttransaction',$dataArray);
			return db::get_last_id();	
		}
		
		public function getSaleSubscriptionDetails($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);
			if($this->SS_Id)
			{
				$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscription WHERE 1=1 AND SS_ID=".$this->SS_Id." ";
				if($condition!='')
					$Sql .= $condition;
				//echo $Sql;	
				$sql_res = db::get_row($Sql);
				if(!count($sql_res))$sql_res=array();return $sql_res;
			}	
		}
		
		public function getSalesubscriptionTransDetails($FieldsArray,$condition='')
		{
			$strFields = implode(",",$FieldsArray);
			if($this->SS_Id)
			{
				$Sql = "SELECT $strFields FROM ".TBLPREFIX."salesubscription 
						LEFT JOIN ".TBLPREFIX."salesubscriptionpaymenttransaction ON(SS_ID=SSPT_SSID) WHERE 1=1 AND SS_ID=".$this->SS_Id." ";
				if($condition!='')
					$Sql .= $condition;
				//echo $Sql;	
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
		
		public function updateSaleSubscriptionTransactionDetails($DataArray,$condition=NULL)
		{
			$where = '';
			if($condition!=NULL)
				$where .= $condition; 
			else
				$where = " SSPT_ID=".$this->SSPT_Id;
			db::update(TBLPREFIX.'salesubscriptionpaymenttransaction',$DataArray,$where);
			if(db::is_row_affected())
			{
				$this->P_Status=1;
				return true;
			}
			else
			{
				$this->P_Status=0;
				return false;
			}
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
		
		
		
		// get transaction details of sale subscription payment
		public function GetTransactions($fieldsArray=array('*'), $condition='') {
			$strFields = implode(", ", $fieldsArray);
			$sql = "SELECT $strFields FROM " . TBLPREFIX . "salesubscriptionpaymenttransaction WHERE 1 ";
			
			if($condition != '')
				$sql .= $condition;
			
			$startIndex	= ($this->pageSelectedPage - 1) * $this->pageLimit;	
			$limit = " LIMIT " . $startIndex . ", " . $this->pageLimit;
			$orderBy = " ORDER BY SSPT_CreatedDate DESC";
			
			$this->totalRecord = db::count($sql);
			//echo $sql . $orderBy . $limit;exit;
			if($this->totalRecord)
				$this->result = db::get_all($sql . $orderBy . $limit);				
			return $this->result;
		}
		
	}
?>