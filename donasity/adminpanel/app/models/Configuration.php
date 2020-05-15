<?php
	class Configuration_Model extends Model
	{
		public $ConfigID,$P_Status=1; 
		public $ErrorMessage,$ErrorCode,$ConfirmCode,$ConfirmMsg;
		public $ErrorCodes='',$totalRowCount;
		
		function __construct()
		{
					
		}
		
		function getConfigDetailDB($Array,$Condition)
		{
			$Where	= " WHERE 1=1 ".$Condition;
			$Fields	= implode(',',$Array);
			$Sql	= "SELECT $Fields FROM ".TBLPREFIX."configuration1";
			//echo $Sql.$Where;
			$Res	= db::get_all($Sql.$Where);
			$this->totalRowCount = count($Res);			
			$Res 	= count($Res>0)?$Res:NULL; 
			return $Res;
				
		}	
		
		function updateDB($DataArray)
		{
			EnPException::writeProcessLog('Configuration_Model :: updateDB Function call');			
			if(db::update(TBLPREFIX.'configuration1',$DataArray,"ConfigID=".$this->ConfigID))			
			{
				$this->setConfirmationMsg('C5001');
			}
			else{
				$this->setErrorMsg('E5001');
			}
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1,$P_Status=0)
		{
			EnPException::writeProcessLog('Configuration_Model :: setErrorMsg Function To Set Error Message => '.$ErrCode);
				$this->ErrorCode.=$ErrCode.",";
				$this->ErrorMessage=$ErrCode;
				$this->P_Status=$P_Status;
				$this->MsgType=$MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode,$MsgType=2,$P_Status=1)
		{
			EnPException::writeProcessLog('Configuration_Model :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
				$this->ConfirmCode=$ConfirmCode;
				$this->ConfirmMsg=$ConfirmCode;
				$this->P_Status=$P_Status;
				$this->MsgType=$MsgType;
		}	
	}

?>