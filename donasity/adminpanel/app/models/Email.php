<?php
	class Email_Model extends Model
	{
	
		public function __construct()
		{
			$this->P_status=1;
		}

		public function GetTemplateDetail($DataArray=array('TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom'),$where)
		{
			EnPException::writeProcessLog('Email_Model'.__FUNCTION__.'called');
			$Fields=implode(",",$DataArray);
			$sql="SELECT $Fields,Subject_EN FROM ".TBLPREFIX."emailtemplate ";
			//echo $sql.$where;exit;
			$row = db::get_row($sql.$where);
			return $row;
		}
		
		public function InsertEmailDetail($DataInsert)
		{
			EnPException::writeProcessLog('Email_Model'.__FUNCTION__.'called');
			db::insert(TBLPREFIX."emaillog",$DataInsert);
			$id = db::get_last_id();
			return $id;	
		}
		
	
		//----------------- Email section code start here-----------------
		private function setErrorMsg($ErrCode,$MsgType=1,$Status=0)
		{
			EnPException::writeProcessLog('Fundraisers_Model setErrorMsg function Call for Error Code :: '.$ErrCode);
			$this->P_ErrorCode=$ErrCode;
			$this->P_ErrorMessage=$ErrCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}
		private function setConfirmationMsg($ConfirmCode,$MsgType=2,$Status=1)
		{
			EnPException::writeProcessLog('Fundraisers_Model setConfirmationMsg function Call For Confirmation Code :: '.$ConfirmCode);
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->P_status=$Status;
			$this->P_MsgType=$MsgType;
		}
		
		
		public function SetFundraiserDetails($DataArray,$FundID)
		{
			$temp;
			db::update(TBLPREFIX."campaign",$DataArray,"Camp_ID=".$FundID);	
			if(db::is_row_affected())
			$temp=$FundID;
			return $temp;
		}

	}
?>