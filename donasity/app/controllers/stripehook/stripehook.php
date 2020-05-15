<?php
	class Stripehook_Controller extends Controller
	{	
		public $subscriptionID,$customerID,$UserDetailArray;	
		function __construct()
		{
			$this->load_model('Donation','objDonation');
			$this->load_model('UserType1','objutype1');
			$this->load_model('Stripe','objStripe');
		}
		public function index()
		{
			if($this->objStripe->getWebhookResponse())
			{				
				$DataArray = array('SWL_ID','SWL_ProcessStatus');
				$Condition = " AND SWL_EventID='".$this->objStripe->stripe_response_filtered['eventID']."'";
				$arrWebhookDetails = $this->objDonation->getWebhookLogDetails($DataArray,$Condition);
				
				if($this->objDonation->WebhookLogRecords==0)
				{
					$webhookLogID 		= $this->objDonation->addWebhookLogDetails(array("SWL_EventID"=>$this->objStripe->stripe_response_filtered['eventID'],"SWL_Response"=>keyEncrypt(serialize($this->objStripe->stripe_response_complete)),"SWL_ProcessStatus"=>0,"SWL_Date"=>getDateTime()));
					$logProcessStatus 	= 0;
				}
				else
				{
					$logProcessStatus 	= $arrWebhookDetails['SWL_ProcessStatus'];
					$webhookLogID		= $arrWebhookDetails['SWL_ID'];
				}
				
				if($this->objStripe->stripe_response_filtered['typeStatus']=="invoice.payment_succeeded")
				{
					if($logProcessStatus!=1)
					{
						/*order detail*/
						$DataArray = array("PDD_ID","PDD_PD_ID","PDD_DateTime","PDD_RUID","PDD_DonationReciptentType","PDD_StripeConnectedID","PDD_ItemCode","PDD_NPOEIN","PDD_CampID","PDD_PIItemType","PDD_PIItemName",
											"PDD_PIItemDescription","PDD_ItemAttributes","PDD_CategoryCode","PDD_Cost","PDD_TransactionFee","PDD_TransactionFeePaidByUser","PDD_TaxExempt",
											"PDD_SubTotal","PDD_PaymentType","PDD_ReoccuringProfileID","PDD_PaymentTransactionID","PDD_Comments","PDD_Deleted","PDD_eCheckStatus","PDD_eCheckDate",
											"PDD_eCheckComment","PDD_Status","PDD_Status_Notes","PDD_DonorComment");
						$Condition = " AND PDD_ReoccuringProfileID='".$this->objStripe->stripe_response_filtered['subscriptionID']."' AND PDD_PaymentType='FRP'";
						$DonationOrderDetails = $this->objDonation->GetDonationOrderDetails($DataArray,$Condition);
						$orderDetails = $DonationOrderDetails[0];
						if($this->objDonation->TotalRecords>0)
							$this->UserDetailArray = $this->objutype1->GetUserDetails(array('RU.RU_ID','RU.RU_FistName','RU.RU_LastName','RU.RU_EmailID','RU.RU_ProfileImage','RU.RU_City','RU.RU_ZipCode','RU.RU_Address1','RU.RU_Address2','RU.RU_EmailID')," AND RU.RU_ID=".$orderDetails['PDD_RUID']);
						
						
						if($this->objDonation->TotalRecords==1 && $orderDetails['PDD_Status']==12)
						{							
							/*Add new entry in Payment transaction table*/
							$PaymentTransactionID=$this->objDonation->SetPaymentTransaction(array("PT_PDID"=>$orderDetails["PDD_PD_ID"],"PT_PDDID"=>$orderDetails["PDD_ID"],"PT_RUID"=>$orderDetails["PDD_RUID"],"PT_PaymentType"=>"CC",	
							"PT_PaymentAmount"=>$orderDetails["PDD_Cost"],"PT_PaidAmount"=>$orderDetails["PDD_Cost"],"PT_TransactionFee"=>$orderDetails["PDD_TransactionFee"],"PT_PaymentGatewayName"=>"STRIPE","PT_PaymentStatus"=>1,"PT_PaymentStatus_Notes"=>"New",
							"PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),"PT_LastUpdatedDate"=>getDateTime()));
							
							/*Update Order Detail table*/
							$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>11,"PDD_Status_Notes"=>"Processed Successfully"),$orderDetails["PDD_ID"]);
							$Condition = " RP_RecurringProfileID='".$this->objStripe->stripe_response_filtered['subscriptionID']."'";
							$this->objDonation->updateRecurringStatus(array("RP_EndDate"=>date('Y-m-d',strtotime('+1 day',$this->objStripe->stripe_response_filtered['periodEnd'])),"RP_LastUpdatedDate"=>getDateTime()),$Condition);
							$this->objDonation->UpdateWebhookLogDetails(array("SWL_ProcessStatus"=>1),$webhookLogID);
							echo $this->sendConfirmmationToUser($orderDetails);
							$this->objStripe->amount 					= round($orderDetails['PDD_Cost']);
							$this->objStripe->StripeConnectedAccountID 	= $orderDetails['PDD_StripeConnectedID'];
							$this->objStripe->txnDescription 			= $orderDetails["PDD_PIItemName"]." [".$orderDetails["PDD_PIItemType"]."]";
							
															
							if($this->objStripe->setTransfer())
							{
								$this->sendMailOnSuccessTransfer($orderDetails);
							}
							else
							{
								$this->sendMailOnFailedTransfer($orderDetails);
							}
							
						}
						elseif($this->objDonation->TotalRecords==1 && $orderDetails['PDD_Status']==11)
						{
							$DataArray = $this->createOrderDetailsArray($DonationOrderDetails[0]);
							$orderDetailID = $this->objDonation->addOrderDetails($DataArray);
							
							/*Add new entry in Payment transaction table*/
							$PaymentTransactionID=$this->objDonation->SetPaymentTransaction(array("PT_PDID"=>$orderDetailID,"PT_PDDID"=>$orderDetails["PDD_ID"],"PT_RUID"=>$orderDetails["PDD_RUID"],"PT_PaymentType"=>"RCC",	
							"PT_PaymentAmount"=>$orderDetails["PDD_Cost"],"PT_PaidAmount"=>$orderDetails["PDD_Cost"],"PT_TransactionFee"=>$orderDetails["PDD_TransactionFee"],"PT_PaymentGatewayName"=>"STRIPE","PT_PaymentStatus"=>1,"PT_PaymentStatus_Notes"=>"New",
							"PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),"PT_LastUpdatedDate"=>getDateTime()));
							/*Update Order Detail table*/
							$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>11,"PDD_Status_Notes"=>"Processed Successfully"),$orderDetails["PDD_ID"]);
							$Condition = " RP_RecurringProfileID='".$this->objStripe->stripe_response_filtered['subscriptionID']."'";
							$this->objDonation->updateRecurringStatus(array("RP_EndDate"=>date('Y-m-d',strtotime('+1 day',$this->objStripe->stripe_response_filtered['periodEnd'])),"RP_LastUpdatedDate"=>getDateTime()),$Condition);
							$this->objDonation->UpdateWebhookLogDetails(array("SWL_ProcessStatus"=>1),$webhookLogID);
							echo $this->sendConfirmmationToUser($orderDetails);
							$this->objStripe->amount 					= round($orderDetails['PDD_Cost']);
							$this->objStripe->StripeConnectedAccountID 	= $orderDetails['PDD_StripeConnectedID'];
							$this->objStripe->txnDescription 			= $orderDetails["PDD_PIItemName"]." [".$orderDetails["PDD_PIItemType"]."]";
							
							if($this->objStripe->setTransfer())
							{
								$this->sendMailOnSuccessTransfer($orderDetails);
							}
							else
							{
								$this->sendMailOnFailedTransfer($orderDetails);
							}
						}
						elseif($this->objDonation->TotalRecords>1)
						{
							
							$DataArray = $this->createOrderDetailsArray($DonationOrderDetails[0]);
							$orderDetailID = $this->objDonation->addOrderDetails($DataArray);
							
							/*Add new entry in Payment transaction table*/
							$PaymentTransactionID=$this->objDonation->SetPaymentTransaction(array("PT_PDID"=>$orderDetailID,"PT_PDDID"=>$orderDetails["PDD_ID"],"PT_RUID"=>$orderDetails["PDD_RUID"],"PT_PaymentType"=>"RCC",	
							"PT_PaymentAmount"=>$orderDetails["PDD_Cost"],"PT_PaidAmount"=>$orderDetails["PDD_Cost"],"PT_TransactionFee"=>$orderDetails["PDD_TransactionFee"],"PT_PaymentGatewayName"=>"STRIPE","PT_PaymentStatus"=>1,"PT_PaymentStatus_Notes"=>"New",
							"PT_IP"=>GetUserLocale(),"PT_CreatedDate"=>getDateTime(),"PT_LastUpdatedDate"=>getDateTime()));
							/*Update Order Detail table*/
							$this->objDonation->SetDonationOrderDetails(array("PDD_PaymentTransactionID"=>$PaymentTransactionID,"PDD_Status"=>11,"PDD_Status_Notes"=>"Processed Successfully"),$orderDetails["PDD_ID"]);
							$Condition = " RP_RecurringProfileID='".$this->objStripe->stripe_response_filtered['subscriptionID']."'";
							$this->objDonation->updateRecurringStatus(array("RP_EndDate"=>date('Y-m-d',strtotime('+1 day',$this->objStripe->stripe_response_filtered['periodEnd'])),"RP_LastUpdatedDate"=>getDateTime()),$Condition);
							$this->objDonation->UpdateWebhookLogDetails(array("SWL_ProcessStatus"=>1),$webhookLogID);
							echo $this->sendConfirmmationToUser($orderDetails);
							$this->objStripe->amount 					= round($orderDetails['PDD_Cost']);
							$this->objStripe->StripeConnectedAccountID 	= $orderDetails['PDD_StripeConnectedID'];
							$this->objStripe->txnDescription 			= $orderDetails["PDD_PIItemName"]." [".$orderDetails["PDD_PIItemType"]."]";
							
							if($this->objStripe->setTransfer())
							{
								$this->sendMailOnSuccessTransfer($orderDetails);
							}
							else
							{
								$this->sendMailOnFailedTransfer($orderDetails);
							}
						}
					}
				}
				/*subscription deleted*/
				if($this->objStripe->stripe_response_filtered['typeStatus']=="customer.subscription.deleted")
				{
					if($logProcessStatus!=1)
					{
						$DataArray = array("RP_Status"=>11,"RP_LastUpdatedDate"=>getDateTime());
						$Condition = "RP_RecurringProfileID='".$this->objStripe->stripe_response_filtered['subscriptionID']."'";
						if($this->objDonation->updateRecurringStatus($DataArray,$Condition))
						{							
							$this->objDonation->UpdateWebhookLogDetails(array("SWL_ProcessStatus"=>1),$webhookLogID);
						}						
					}					
				}
			}
			else
			{				
				//$this->sendFailedNotificationToUser();				
			}
			//$this->sendemail($input);
			//file_put_contents(APP_LOG_DIR."testwebhook.log",$event_json);
			
			http_response_code(200); // PHP 5.4 or greater			
		}
		
		private function createOrderDetailsArray($orderDetails)
		{
			$DataArray=array("PDD_DateTime"=>getDateTime()
							,"PDD_RUID"=>$orderDetails['PDD_RUID']
							,"PDD_DonationReciptentType"=>$orderDetails['PDD_DonationReciptentType']
							,"PDD_StripeConnectedID"=>$orderDetails['PDD_StripeConnectedID']
							,"PDD_ItemCode"=>$orderDetails['PDD_ItemCode']
							,"PDD_NPOEIN"=>$orderDetails['PDD_NPOEIN']
							,"PDD_CampID"=>$orderDetails['PDD_CampID']
							,"PDD_PIItemType"=>$orderDetails['PDD_PIItemType']
							,"PDD_PIItemName"=>$orderDetails['PDD_PIItemName']
							,"PDD_PIItemDescription"=>$orderDetails['PDD_PIItemDescription']
							,"PDD_ItemAttributes"=>$orderDetails['PDD_ItemAttributes']
							,"PDD_CategoryCode"=>$orderDetails['PDD_CategoryCode']
							,"PDD_Cost"=>$orderDetails['PDD_Cost']
							,"PDD_TransactionFee"=>$orderDetails['PDD_TransactionFee']
							,"PDD_TransactionFeePaidByUser"=>$orderDetails['PDD_TransactionFeePaidByUser']
							,"PDD_TaxExempt"=>$orderDetails['PDD_TaxExempt']
							,"PDD_SubTotal"=>$orderDetails['PDD_SubTotal']
							,"PDD_PaymentType"=>'NRP'
							,"PDD_ReoccuringProfileID"=>$orderDetails['PDD_ReoccuringProfileID']
							,"PDD_PaymentTransactionID"=>$orderDetails['PDD_PaymentTransactionID']
							,"PDD_Comments"=>$orderDetails['PDD_Comments']
							,"PDD_Deleted"=>$orderDetails['PDD_Deleted']
							,"PDD_eCheckStatus"=>$orderDetails['PDD_eCheckStatus']
							,"PDD_eCheckDate"=>$orderDetails['PDD_eCheckDate']
							,"PDD_eCheckComment"=>$orderDetails['PDD_eCheckComment']
							,"PDD_Status"=>'11'
							,"PDD_Status_Notes"=>$orderDetails['PDD_Status_Notes']
							,"PDD_DonorComment"=>$orderDetails['PDD_DonorComment']							
							);			
			return $DataArray;
		}

		private function sendemail($HTML)
		{
			$this->load_model('Email','objemail');
			
			$InsertDataArray=array('FromID'=>0,
			'CC'=>"qualdev.deepak@gmail.com",'FromAddress'=>"noreply@donasity.com",'ToAddress'=>"qualdev.test@gmail.com",'Subject'=>"stripe hook response",'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			$Status=$Eobj->sendEmail($id);
		}
		
		private function sendMailOnSuccessTransfer($orderDetails)
		{
			$this->load_model('Email','objemail');
			$Keyword	= 'paymenttransfersucess';
			$where		= " Where Keyword='".$Keyword."'";
			$DataArray	= array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate= $this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName	= $this->UserDetailArray['RU_FistName'].' '.$this->UserDetailArray['RU_LastName'];
			$UserEmail	= $this->UserDetailArray['RU_EmailID'];
			$tpl		= new view;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('ItemDetails',$orderDetails);
			$HTML		= $tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$this->UserDetailArray['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$GetTemplate['EmailTo'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id			= $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj		= LoadLib('BulkEmail');
			$Status		= $Eobj->sendEmail($id);
			return $Status;				
		}
		private function sendConfirmmationToUser($orderDetails)
		{
			///$this->UserDetailArray;`
			$this->load_model('Email','objemail');
			$Keyword	= 'donationpaymentsucess';
			$where		= " Where Keyword='".$Keyword."'";
			$DataArray	= array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate= $this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName	= $this->UserDetailArray['RU_FistName'].' '.$this->UserDetailArray['RU_LastName'];
			$UserEmail	= $this->UserDetailArray['RU_EmailID'];
			$tpl		= new view;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('ItemDetails',$orderDetails);
			$HTML		= $tpl->draw('email/'.$GetTemplate['TemplateName'],true);
						
			$InsertDataArray=array('FromID'=>$this->UserDetailArray['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id			= $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj		= LoadLib('BulkEmail');
			$Status		= $Eobj->sendEmail($id);
			return $Status;			
		}
		
		private function sendFailedNotificationToUser($orderDetails)
		{
			$this->UserDetailArray;
			$this->load_model('Email','objemail');
			$Keyword	= 'donationpaymentfail';
			$where		= " Where Keyword='".$Keyword."'";
			$DataArray	= array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate= $this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName	= $this->UserDetailArray['RU_FistName'].' '.$this->UserDetailArray['RU_LastName'];
			$UserEmail	= $this->UserDetailArray['RU_EmailID'];
			$tpl		= new view;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('ItemDetails',$orderDetails);
			$HTML		= $tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			$InsertDataArray=array('FromID'=>$this->UserDetailArray['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$UserEmail,
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id			= $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj		= LoadLib('BulkEmail');
			$Status		= $Eobj->sendEmail($id);
			return $Status;					
		}
		
		private function sendMailOnFailedTransfer($orderDetails)
		{
			$this->UserDetailArray;
			$this->load_model('Email','objemail');
			$Keyword	= 'donationpaymentfail';
			$where		= " Where Keyword='".$Keyword."'";
			$DataArray	= array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_'._DBLANG_);
			$GetTemplate= $this->objemail->GetTemplateDetail($DataArray,$where);
			$UserName	= $this->UserDetailArray['RU_FistName'].' '.$this->UserDetailArray['RU_LastName'];
			$UserEmail	= $this->UserDetailArray['RU_EmailID'];
			$tpl		= new view;
			$tpl->assign('UserName',$UserName);
			$tpl->assign('ItemDetails',$orderDetails);
			$HTML		= $tpl->draw('email/'.$GetTemplate['TemplateName'],true);
		
			$InsertDataArray=array('FromID'=>$this->UserDetailArray['RU_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$GetTemplate['EmailTo'],
			'Subject'=>$GetTemplate['Subject_'._DBLANG_],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());

			$id			= $this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj		= LoadLib('BulkEmail');
			$Status		= $Eobj->sendEmail($id);
			return $Status;					
		}
		private function setResponseVariables($successResponse)
		{			
			$this->stripe_response_filtered=array("TransactionID"=>$successResponse["id"],
			"paidStatus"=>$successResponse["paid"],
			"PaidAmount"=>$this->convertCenttoDollor($successResponse["amount"]),
			"PayNote"=>"By Credit Card xxxx-xxxx");
			$this->P_status=1; 
		    $this->stripe_response_complete=$successResponse;

			return 1;
			
		}
		private function setErrorVariables($errResponse)
		{
			$body = $errResponse->getJsonBody();
			$err  = $body['error'];
			
			 if(!isset($err['code']) && !isset($err['message']))
			{
				$err['code']="000";
				$err['message']="Temporary Error In Processing Payment";
				$this->stripe_response_complete=$errResponse;
			}
			else
			{
				$this->stripe_response_complete=$err;
				
			}
		   	$this->stripe_response_err=array("code"=>$err['code'],"type"=>$err['type'],"param"=>$err['param'],"message"=>$err['message']);

			$this->P_status=0; //$e->getHttpStatus();
			
			
			return 1;
		}
		private function strip_quote($text)
		{
		$text=str_replace("'","",$text);
		return $text;
		}

	}
?>