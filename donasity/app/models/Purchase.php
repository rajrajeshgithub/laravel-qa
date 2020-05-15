<?php
	class Purchase_Model extends Model
	{
		public $PurchaseOrderID, $PurchaseOrderDetailArray,$PurchaseOrderArray;
		
		public function GetProductDetail($arrFields=array(), $filterparam='')
		{
			$fields		= implode(',',$arrFields);
			$sql = "SELECT $fields FROM ".TBLPREFIX."purchaseitems";
			$filterparam = " WHERE 1=1 ".$filterparam;
			$res = db::get_row($sql.$filterparam);
			//echo($sql.$filterparam);
			if(count($res))
			return $res;
			else 
			return array();
		}	
		
		
		public function AddPurchaseOrder($Array)
		{
			db::insert(TBLPREFIX."purchasedonation",$Array);
			$this->PurchaseOrderID=db::get_last_id();
			return $this->PurchaseOrderID;
		}	
	
	
	     
		
		
		public function SetPurchaseOrder($DataArray,$OrderID)
		{
			$temp;
			db::update(TBLPREFIX."purchasedonation",$DataArray,"PD_ID=".$OrderID);	
			if(db::is_row_affected())
			$temp=$OrderID;
			return $temp;
		}
		
		
		public function GetPurchaseOrder($DataArray=array('*'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonation PD";
			$Where	= " WHERE 1=1 " ;
			if ($this->PurchaseOrderID<>"")
			$Where	.=" AND PD_ID=".$this->PurchaseOrderID;
			if($Condition <> NULL)
			$Where	.= $Condition;

			$Res	= db::get_row($Sql.$Where);//echo $Sql.$Where;
			$this->PurchaseOrderArray= (count($Res)>0)?$Res:array();
			return $this->PurchaseOrderArray;
		}
		
		
		public function AddPurchaseOrderDetails($Array)
		 {
			$temp; 
					$Array["PDD_PD_ID"]=$this->PurchaseOrderID;
					db::insert(TBLPREFIX."purchasedonationdetails",$Array);
					$temp[]=db::get_last_id();	
					return $temp;
		 }
		
		public function SetPurchaseOrderDetails($DataArray,$OrderDetailID)
		{
			$temp;
			db::update(TBLPREFIX."purchasedonationdetails",$DataArray,"PDD_ID=".$OrderDetailID);	
			if(db::is_row_affected())
			$temp=$OrderDetailID;
			return $temp;
		}
		
		 public function GetPurchaseOrderDetails($DataArray=array('*'),$Condition=NULL)
		{
		
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails PDD";
			$Where	= " WHERE 1=1 " ;
			if ($this->PurchaseOrderID<>"")
			$Where	.=" AND PDD_PD_ID=".$this->PurchaseOrderID;
			if($Condition <> NULL)
			$Where	.= $Condition;
			$Res	= db::get_row($Sql.$Where);
			$this->PurchaseOrderDetailArray= (count($Res)>0)?$Res:array();
			return $this->PurchaseOrderDetailArray;
		}
		
		
		
		public function SetPaymentTransaction($DataArray,$PaymentTransactionID=NULL)
		{
			$temp="";
			if($PaymentTransactionID==NULL)
			{
				$temp=db::insert(TBLPREFIX."paymenttransection",$DataArray);
				$temp=db::get_last_id();
			}
			
			if($PaymentTransactionID<>NULL)
			{
				db::update(TBLPREFIX."paymenttransection",$DataArray,"PT_ID=".$PaymentTransactionID);	
				if(db::is_row_affected())
				$temp=$PaymentTransactionID;
			}
			
			return $temp;
		}
		
		
		Public function GetFundraiserLevel($ProductCode)
		{
			$LevelId=0;
			switch(strtoupper($ProductCode))
			{
				case "CP-STANDARD": $LevelId=1; break;
				case "CP-PREMIER": $LevelId=2; break;
				case "CP-PLATINUM": $LevelId=3; break;
			}
			return $LevelId;
		}
		
		
	}

?>