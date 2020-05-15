<?php
	class Ut1myaccount_Ajax_Controller extends Controller
	{		
		
		public $tpl,$LoginUserDetail;
		public $LoginUserId,$CurrentDate;

		function __construct()
		{

			$this->tpl	= new View;
			$this->load_model('UserType1','objutype1');
			$this->objutype1 = new UserType1_Model();
			$this->load_model('Fundraisers','objFund');
			$this->load_model('Ut1_Reporting','objut1report');
			$this->objut1report = new Ut1_Reporting_Model();
			$this->LoginUserDetail	= getSession('Users');
			$this->LoginUserId	= keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$this->CurrentDate	= getDateTime();
		
			$this->FC_PageLimit=3;
		}
		
	
		
		
		
		
		private function SetStatus($Status,$Code)
		{
			if($Status)
			{
				$messageParams=array("msgCode"=>$Code,
												 "msg"=>"Custom Confirmation message",
												 "msgLog"=>0,									
												 "msgDisplay"=>1,
												 "msgType"=>2);
					EnPException::setConfirmation($messageParams);
			}
			else
			{
				$messageParams=array("errCode"=>$Code,
										 "errMsg"=>"Custom Confirmation message",
										 "errOriginDetails"=>basename(__FILE__),
										 "errSeverity"=>1,
										 "msgDisplay"=>1,
										 "msgType"=>1);
					EnPException::setError($messageParams);
			}
		}
	}
?>