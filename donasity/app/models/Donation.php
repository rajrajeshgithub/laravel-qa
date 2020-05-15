<?php
	class Donation_Model extends Model
	{
		public $OrderArray,$OrderDetailArray,$ConfirmationArray,$OrderDetailTransactionArray;
		public $orderID,$recurringID,$recurringDetails,$TotalRecords,$WebhookLogRecords,$WebhookLogArray,$webhookLogID;
		public $orderDetailID;
			
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
		
		
		public function SetDonationOrder($DataArray,$OrderID)
		{
			$temp;
			db::update(TBLPREFIX."purchasedonation",$DataArray,"PD_ID=".$OrderID);	
			if(db::is_row_affected())
			$temp=$OrderID;
			return $temp;
		}
		
		public function SetDonationOrderDetails($DataArray,$OrderDetailID)
		{
			$temp;
			db::update(TBLPREFIX."purchasedonationdetails",$DataArray,"PDD_ID=".$OrderDetailID);	
			if(db::is_row_affected())
			$temp=$OrderDetailID;
			return $temp;
		}
		
		
		
		public function AddDonationOrder()
		{
			db::insert(TBLPREFIX."purchasedonation",$this->OrderArray);
			$this->orderID=db::get_last_id();
			return $this->orderID;
		}	
	
		
		public function AddDonationOrderDetails()
		{
			$temp;
			foreach($this->OrderDetailArray as $key)
			{
				if($key['PDD_PaymentType']=='FRP')
				{
					$recurringMode = $key['RecurringMode'];
				}
				unset($key['RecurringMode']);
				$key["PDD_PD_ID"]=$this->orderID;
				db::insert(TBLPREFIX."purchasedonationdetails",$key);
				$PDDID = db::get_last_id();
				$temp[]= $PDDID;
				if($key['PDD_PaymentType']=='FRP')
				{
					$dateArray = array("RP_PDDID"=>$PDDID,
									"RP_RUID"=>$key['PDD_RUID'],
									"RP_StartDate"=>getDateTime(0,'Y-m-d'),
									"RP_RecurringCycle"=>$recurringMode,
									"RP_RecurringAmount"=>$key['PDD_Cost'],
									"RP_CreatedDate"=>getDateTime(),
									"RP_LastUpdatedDate"=>getDateTime(),
									"RP_Status"=>'0'
									);
					db::insert(TBLPREFIX."recuringprofiles",$dateArray);
					//db::get_last_id();
				}
			}
			return $temp;
		}
	
		public function GetRecurringDetails($DataArray,$condition)
		{
			$Fields	= implode(",",$DataArray);
			$sql = "select $Fields from ".TBLPREFIX."recuringprofiles where 1=1 AND ";
			//echo $sql.$condition;exit;
			$res = db::get_row($sql.$condition);
			$this->recurringDetails = $res;
			return $this->recurringDetails?$this->recurringDetails:array();
		}
		
		public function GetRecurringUserList($DataArray,$condition)
		{
			$Fields	= implode(",",$DataArray);
			$sql = "select $Fields from ".TBLPREFIX."recuringprofiles rf
					LEFT JOIN ".TBLPREFIX."purchasedonationdetails pdd ON(rf.RP_PDDID=pdd.PDD_ID)
					LEFT JOIN ".TBLPREFIX."registeredusers ru ON(rf.RP_RUID=ru.RU_ID)";
			$where = " where 1=1 ".$condition;
			//echo $sql.$where;exit;
			$res = db::get_all($sql.$where);
			$this->recurringDetails = $res;
			return $this->recurringDetails?$this->recurringDetails:array();
		}
		
		public function GetRecurringDetails_DB($DataArray,$condition)
		{
			$Fields	= implode(",",$DataArray);
			$sql = "select $Fields from ".TBLPREFIX."recuringprofiles 
					LEFT JOIN ".TBLPREFIX."purchasedonationdetails ON(PDD_ID==RP_PDDID) where 1=1 AND ";
			//echo $sql.$condition;exit;
			$res = db::get_row($sql.$condition);
			$this->recurringDetails = $res;
			return $this->recurringDetails?$this->recurringDetails:array();
		}
		
	    public function GetDonationOrder($DataArray=array('*'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonation PD";
			$Where	= " WHERE 1=1 " ;
			if ($this->orderID<>"")
			$Where	.=" AND PD_ID=".$this->orderID;
			if($Condition <> NULL)
			$Where	.= $Condition;

			$Res	= db::get_row($Sql.$Where);//echo $Sql.$Where;
			$this->OrderArray= (count($Res)>0)?$Res:array();
			return $this->OrderArray;
		}
		public function GetUT3WudgetRecord($DataArray=array('*'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."ut3widgets";
			$Where	= " WHERE 1=1 " ;
			if($Condition <> NULL)
			$Where	.= $Condition;
			$Res	= db::get_row($Sql.$Where); //echo $Sql.$Where;
			return (count($Res)>0)?$Res:array();
		}
		 
		 public function GetDonationOrderDetails($DataArray=array('*'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails PDD";
			$Where	= " WHERE 1=1 " ;
			if ($this->orderID<>"")
			$Where	.=" AND PDD_PD_ID=".$this->orderID;
			if($Condition <> NULL)
			$Where	.= $Condition;
			//echo $Sql.$Where."</br>";
			$this->TotalRecords = db::count($Sql.$Where);
			$Res	= db::get_all($Sql.$Where);
			//echo($Sql.$Where);			
			$this->OrderArray= (count($Res)>0)?$Res:array();			
			return $this->OrderArray;
		}
		
		
		public function GetDonationOrderTransactionDetails($DataArray=array('*'),$Condition=NULL)
		{
		 /*Put Join of PDD & PT on PDD.PDD_PaymentTransactionID & PT_IT*/
		 	$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails PDD LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PDD.PDD_PaymentTransactionID=PT.PT_ID)";
			$Where	= " WHERE 1=1 " ;

			if ($this->orderID<>"")
			$Where	.=" AND PDD.PDD_PD_ID=".$this->orderID;
			if($Condition <> NULL)
			$Where	.= $Condition;
			$GroupBY	= " GROUP BY PDD.PDD_ID";
			$Res	= db::get_all($Sql.$Where.$GroupBY);//echo $Sql.$Where.$GroupBY;exit;
			$this->OrderDetailTransactionArray	= (count($Res)>0)?$Res:array();

			return $this->OrderDetailTransactionArray;
		}
		
		
		
		public function GetPaymentStatus($DataArray=array('*'),$Condition=NULL)
		{
			$Fields	= implode(",",$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails PDD
						LEFT JOIN ".TBLPREFIX."paymenttransection PT ON (PDD.PDD_PD_ID=PT.PT_PDID)";
			$Where	= " WHERE 1=1 " ;
			if ($this->orderID<>"")
			$Where	.=" AND PDD.PDD_PD_ID=".$this->orderID;
			if($Condition <> NULL)
			$Where	.= $Condition;

			$Res	= db::get_all($Sql.$Where);
			$this->ConfirmationArray	= (count($Res)>0)?$Res:array();
			return $this->ConfirmationArray;
		}
		
		
		public function GetNpoDetails($DataArray,$Condition=NULL)
		{
			$Where="";
			$DataArray=array('N.NPO_ID','N.NPO_EIN','N.NPO_Name','NU.USERID','NU.Stripe_ClientID','N.NPO_DedCode','NU.Status as Stripe_status');
			if($Condition <> NULL)
			$Where	.=" where 1=1 AND ". $Condition;
			
			$Fields	= implode(',',$DataArray);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."npodetails N
					   LEFT JOIN ".TBLPREFIX."npocategoryrelation NCR ON (NCR.NPO_CategoryName=N.NPO_CD)
					   LEFT JOIN ".TBLPREFIX."npouserrelation NU ON (N.NPO_ID=NU.NPOID) ";
			$Res	= db::get_row($Sql.$Where);
			//echo $Sql.$Where;exit;		   
			return $Res;
		}

		public function GetPayableDonation_SUM_COUNT($Fields,$Condition=NULL)
		{
			$Where="";
			$Where	.=" WHERE PDD_PD_ID=".$this->orderID;
			
			if($Condition <> NULL)
			$Where	.= $Condition;
			
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."purchasedonationdetails  ";
			//echo $Sql.$Where;exit;
			$Res	= db::get_field($Sql.$Where);
			$Res==NULL?$Res=0:$Res=$Res;
			return $Res;
		}
		public function addRecurringDetails($DataArray)
		{
			db::insert(TBLPREFIX."recuringprofiles",$DataArray);
			$this->recurringID=db::get_last_id();
			return $this->recurringID;
		}
		
		public function updateRecurringDetails($DataArray,$recurringID)
		{
			$temp;
			db::update(TBLPREFIX."recuringprofiles",$DataArray,"RP_ID=".$recurringID);	
			if(db::is_row_affected())
			$temp=$recurringID;
			return $temp;		
		}
		
		public function updateRecurringStatus($DataArray,$condition)
		{			
			db::update(TBLPREFIX."recuringprofiles",$DataArray,$condition);	
			if(db::is_row_affected())
				return true;
			else
				return false;
		}
		
		public function addWebhookLogDetails($DataArray)
		{
			db::insert(TBLPREFIX."stripewebhooklog",$DataArray);
			$this->webhookLogID=db::get_last_id();
			return $this->webhookLogID;
		}
		
		public function getWebhookLogDetails($DataArray=array('*'), $Condition=NULL)
		{
			$Fields = implode(",",$DataArray);
			$Sql = "SELECT $Fields FROM ".TBLPREFIX."stripewebhooklog SWL";
			$Where = " WHERE 1=1 ";
			if($Condition!=NULL)
				$Where	.= $Condition;
				//echo $Sql.$Where;exit;
			$Res	= db::get_row($Sql.$Where);
			$this->WebhookLogRecords = db::count($Sql.$Where);
			$this->WebhookLogArray = (count($Res)>0)?$Res:array();
			return $this->WebhookLogArray;
		}
		
		public function addOrderDetails($DataArray)
		{
			db::insert(TBLPREFIX."purchasedonationdetails",$DataArray);
			$this->orderDetailID = db::get_last_id();
			return $this->orderDetailID;
		}
		
		public function UpdateWebhookLogDetails($DataArray,$webhookLogID)
		{
			db::update(TBLPREFIX."stripewebhooklog",$DataArray,"SWL_ID=".$webhookLogID);
			if(db::is_row_affected())
			return true;
			else
			return false;	
		}
		
	}
	
?>