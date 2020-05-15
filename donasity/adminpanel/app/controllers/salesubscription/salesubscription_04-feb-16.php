<?php
	/*
	//get todays pending order for charge
	--Add Transaction in saleTransaction table
	--get sss_id and send request to USAepay
	--update response in saletrasation \
	--update salesubscrition table for last occuring and status 
	
	*/
	class Recurringtransaction_Controller extends Controller
	{
		public $SSPT_Id,$P_Status;
		function __construct()
		{
			$this->load_model('Common', 'objCMN');
			$this->load_model('SaleSubscription', 'objSaleSub');
			$this->P_Status=1;
		}	
		
		public function runTransaction()
		{
			$FieldArray = array('*','SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_PaySimplePaymentMethodID','SS_EnableRecipt','SS_TotalCyclesPaid',
								'SS_Schedule','SS_PaySimpleCustomerID','SS_Status','SS_PaymentStatus','SS_PaymentMode','SS_Amount','SS_CheckNumber');
			$Condition = " AND SS_Status=15 AND SS_PaySimplePaymentMethodID IS NOT NULL AND SS_PaySimpleCustomerID IS NOT NULL and SS_NextOuccringDate='".getDateTime(0,'Y-m-d')."'";
			$ArraySaleSubscription = $this->objSaleSub->getSaleSubscriptionList($FieldArray,$Condition);
			//dump($ArraySaleSubscription);
			if(count($ArraySaleSubscription)>0)
			{
				$this->load_model('Usaepay', 'objUSAePay');
				$arrayTransStatus = get_setting('TransactionStatus');
				$arrDetailsSummary = array();
				foreach($ArraySaleSubscription as $key => $arrDetails)
				{
					$logText = "Added Transaction details - Added by : System , Added on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
					$DataArray = array('SSPT_SSID'=>$arrDetails['SS_ID'],
										'SSPT_PaymentType'=>$arrDetails['SS_PaymentMode'],
										'SSPT_PaymentAmount'=>$arrDetails['SS_Amount'],
										'SSPT_Status'=>0,
										'SSPT_CreatedDate'=>getDateTime(),
										'SSPT_LastUpdatedDate'=>getDateTime(),
										'SSPT_Locale'=>GetUserLocale(),
										'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
										);
					
					$this->SSPT_Id = $this->objSaleSub->insertSaleSubscriptionTransactionDetails($DataArray);
					if($this->SSPT_Id)
					{
						$FieldArray = array('*');
						$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
						$arraySaleSubTransactionDetails = $this->objSaleSub->getSalSubscriptionTransactionDetails($FieldArray);	
						
						$this->objUSAePay->ArrayTransactionDetails = array(																		
																		'Command'=>'Check',
																		'IgnoreDuplicate'=>false,
																		'CheckData' => array(
      																	'CheckNumber' =>$arrDetails['SS_CheckNumber']),
																		'Details'=>array( 
																		'ClientIP'=>get_ip(),
																		'CustReceipt'=>true,																		
																		'PONum' => '', /*Purchase Order Number for commercial card transactions - 25 characters. discussion*/
																		'OrderID' =>1000+$this->SSPT_Id, /*Transaction order ID. This field should be used to assign a unique order id to the transaction. The order ID can support 64 characters.*/
																		'Description' => ($arraySaleSubTransactionDetails['SSPT_PaymentType']=='RC')?'Recurring Payment':'One Time Payment', 
																		'Amount'=>$arraySaleSubTransactionDetails['SSPT_PaymentAmount'])
																		);
						$this->objUSAePay->CustNum = $arrDetails['SS_PaySimpleCustomerID'];
						$this->objUSAePay->MethodID = $arrDetails['SS_PaySimplePaymentMethodID'];
						if($this->objUSAePay->runCustomerTransaction())
						{	
							$intStatus = 0;	
							if(strtolower($this->objUSAePay->PaySimpleResponseArray['Result'])=='approved')
								$intStatus = 1;					
							$logText = "Updated Transaction Details After Create Transaction on USAePay for Status :".$this->objUSAePay->PaySimpleResponseArray['Status']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SSPT_PaidAmount'=>$this->objUSAePay->PaySimpleResponseArray['AuthAmount'],										
												'SSPT_Status'=>$intStatus,
												'SSPT_PaySimpleStatus'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SSPT_PaymentGatewayName'=>'USAePay',
												'SSPT_PaymentGatewayRequest'=>serialize($this->objUSAePay->ArrayTransactionDetails),
												'SSPT_PaymentGatewayResponse'=>serialize($this->objUSAePay->PaySimpleResponse),
												'SSPT_PaymentGatewayTransactionID'=>$this->objUSAePay->PaySimpleResponseArray['RefNum'],
												'SSPT_PaymentStatus_Notes'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SSPT_LastUpdatedDate'=>getDateTime(),
												'SSPT_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
												);
							$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
							$this->objSaleSub->updateSaleSubscriptionTransactionDetails($DataArray);
							
							/**/
							$totalCyclesPaid = $arrDetails['SS_TotalCyclesPaid'];
							if($intStatus==1)
								$totalCyclesPaid = $arrDetails['SS_TotalCyclesPaid']+1;
								
							$nextOccuringDate = $this->getNextReoccurringDate($arrDetails['SS_Schedule']);
							$logText = "Updated Details After Create Transaction on USAePay for Status :".$this->objUSAePay->PaySimpleResponseArray['Status']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SS_TotalCyclesPaid'=>$totalCyclesPaid,
												'SS_LastOuccringStatus'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SS_PaymentStatus'=>1,/*Recurring Running*/
												'SS_LastOuccringDate'=>getDateTime(0,'Y-m-d'),
												'SS_NextOuccringDate'=>$nextOccuringDate=='0000-00-00'?NULL:$nextOccuringDate,
												'SS_LastUpdatedDate'=>getDateTime(),
												'SS_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
												);
							$this->objSaleSub->SS_Id = $arrDetails['SS_ID'];
							$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
							if($this->objSaleSub->P_Status)
							{
								$arrDetails['SSPT_ID'] = $this->SSPT_Id;
								$arrDetails['TransactionId'] = $this->objUSAePay->PaySimpleResponseArray['RefNum'];
								$arrDetails['PaymentStatus'] = $this->objUSAePay->PaySimpleResponseArray['Result'];
								$arrDetails['PaymentType'] = $arraySaleSubTransactionDetails['SSPT_PaymentType'];
								$arrDetails['PaymentDate'] = $arraySaleSubTransactionDetails['SSPT_CreatedDate'];
								$arrDetails['PaidAmount'] = $this->objUSAePay->PaySimpleResponseArray['AuthAmount'];
								/*if($intStatus)
								{
									$this->sendPaymentConfirmationMailToCustomer($arrDetails);
								}
								else
								{
									$this->sendPaymentDeclinedMailToCustomer($arrDetails);
								}*/
								$this->sentTransactionReceiptToCustomer($arrDetails);
								$arrDetailsSummary[] = $arrDetails;
								echo "Successfully Transaction Transaction Id - ".$this->objUSAePay->PaySimpleResponseArray['RefNum']." - Test Mode";
							}
							else
							{
								echo "Successfully Transaction on PaySimple But Error in local Code - Test Mode";
							}
						}
						else
						{
							echo "PaySimple Error - ".$this->objUSAePay->ErrorMessage;
						}
					}					
				}
				if(count($arrDetailsSummary)>0)
					$this->sendPaymentConfirmationMailToAdmin($arrDetailsSummary);		
				/*end loop*/
			}
			else
			{
				echo "Not data found to make recurring transaction ";	
			}
			//$this->sendPaymentConfirmationMailToAdmin($arrDetails);
			
		}
		
		
		public function updateTransactionResponse()
		{
			$FieldArray = array('SSPT_ID','SSPT_SSID','SSPT_PaymentAmount','SSPT_PaymentGatewayName','SSPT_PaymentType','SSPT_PaymentGatewayTransactionID',
								'SSPT_Status','SSPT_PaySimpleStatus','SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_ItemName','SS_Amount');
			$Condition = " AND SSPT_Status<>5 AND SSPT_PaymentGatewayTransactionID IS NOT NULL";
			$ArraySaleSubTransaction = $this->objSaleSub->getSaleSubscriptionTransactionListSimple($FieldArray,$Condition);			
			//dump($ArraySaleSubTransaction);
			if(count($ArraySaleSubTransaction)>0)
			{
				$this->load_model('Usaepay', 'objUSAePay');
				foreach($ArraySaleSubTransaction as $key => $arrDetails)
				{				
					$this->objUSAePay->RefNum = $arrDetails['SSPT_PaymentGatewayTransactionID'];
					if($this->objUSAePay->getTransactionStatus())
					{
						if(strtolower($this->objUSAePay->PaySimpleResponseArray['Status'])!='pending')
						{
							$intStatus = 0;
							foreach($arrayTransStatus as $Key => $statusValue)
							{
								if($this->objUSAePay->PaySimpleResponseArray['Status']==$statusValue)
									$intStatus = $Key;
							}
							$logText = "Updated Details After Getting Transaction Status From USAePay, Status : ".$this->objUSAePay->PaySimpleResponseArray['Status']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SSPT_PaidAmount'=>$this->objUSAePay->PaySimpleResponseArray['AuthAmount'],										
												'SSPT_Status'=>$intStatus,
												'SSPT_PaySimpleStatus'=>$this->objUSAePay->PaySimpleResponseArray['Status'],
												'SSPT_PaymentStatus_Notes'=>$this->objUSAePay->PaySimpleResponseArray['Result'],
												'SSPT_LastUpdatedDate'=>getDateTime(),
												'SSPT_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
												);
							$this->objSaleSub->SSPT_Id = $arrDetails['SSPT_ID'];
							$this->objSaleSub->updateSaleSubscriptionTransactionDetails($DataArray);
							
							/**/
							$totalCyclesPaid = $arrDetails['SS_TotalCyclesPaid'];
							if($intStatus==5)
								$totalCyclesPaid = $arrDetails['SS_TotalCyclesPaid']+1;
								
							$nextOccuringDate = $this->getNextReoccurringDate($arrDetails['SS_Schedule']);
							$logText = "Updated Details After Getting Transaction Status From USAePay, Status : ".$this->objUSAePay->PaySimpleResponseArray['Status']."  - Updated by : System , Updated on : ".formatDate(getDateTime(),'n-j-y h:ia')." <br/>"; /*log*/
							$DataArray = array('SS_TotalCyclesPaid'=>$totalCyclesPaid,
												'SS_LastOuccringStatus'=>$this->objUSAePay->PaySimpleResponseArray['Status'],
												'SS_PaymentStatus'=>11,/*Recurring Running*/
												'SS_LastOuccringDate'=>getDateTime(0,'Y-m-d'),
												'SS_NextOuccringDate'=>$nextOccuringDate=='0000-00-00'?NULL:$nextOccuringDate,
												'SS_LastUpdatedDate'=>getDateTime(),
												'SS_Locale'=>GetUserLocale(),
												'UpdateLog'=>"CONCAT(UpdateLog,'".$logText."')"
												);
							$this->objSaleSub->SS_Id = $arrDetails['SSPT_SSID'];
							$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
							if($this->objSaleSub->P_Status)
							{
								$arrayMailContent = array('SS_FirstName'=>$ArraySaleSubTransaction['SS_FirstName'],
															'SS_LastName'=>$ArraySaleSubTransaction['SS_LastName'],
															'SS_EmailAddress'=>$ArraySaleSubTransaction['SS_EmailAddress'],
															'SSPT_PaymentAmount'=>$ArraySaleSubTransaction['SSPT_PaymentAmount'],
															'SSPT_PaidAmount'=>$this->objUSAePay->PaySimpleResponseArray['AuthAmount'],
															'SSPT_PaymentGatewayName'=>$ArraySaleSubTransaction['SSPT_PaymentGatewayName'],
															'SS_RefNumber'=>$ArraySaleSubTransaction['SS_RefNumber'],
															'SS_ItemName'=>$ArraySaleSubTransaction['SS_ItemName'],
															'SS_Amount'=>$ArraySaleSubTransaction['SS_Amount'],
															'SSPT_Status'=>$intStatus);
								
								
								$ProcessStatusSucces.="Successfully Transaction Id - ".$this->objUSAePay->PaySimpleResponseArray['RefNum']."--".$this->objUSAePay->PaySimpleResponseArray['Result']." - Test Mode </br>";
							}
							else
							{
								$ProcessStatusSucces.="Successfully Transaction on PaySimple But Error in local Code - Test Mode </br>";
							}
						}
						else
						{
							$ProcessStatusSucces.="No change perform</br>";	
						}
					}
					else
					{
						$ProcessStatusFail .="PaySimple Error - ".$this->objUSAePay->ErrorMessage."</br>";
					}
				}
			}
			else
			{
				echo "Not data found to make recurring transaction";	
			}
			echo $ProcessStatusSucces;
			echo $ProcessStatusFail;
			exit;
		}
		
		private function sendPaymentConfirmationMailToCustomer($saleSubDetails)
		{
			//dump($saleSubDetails);
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];			
			
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionSuccessToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('saleSubDetails',$saleSubDetails);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$saleSubDetails['SS_EmailAddress'],
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;	
		}
		
		public function sentTransactionReceiptToCustomer($saleSubDetails)
		{
			
			//dump($saleSubDetails);
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];
			$service_name = $saleSubDetails['SS_ItemName'];
			$charge_amount = $saleSubDetails['SS_Amount'];
			$schedule = $saleSubDetails['SS_Schedule'];
			$order_id = $saleSubDetails['SS_RefNumber'];
			
			$link = "<a href='".URL."recurringtransaction/showTransactionReceipt/".keyEncryptFront($saleSubDetails['SSPT_ID'])."'/>Show Transaction Receipt</a>";
			$link_url = URL."recurringtransaction/showTransactionReceipt/".keyEncryptFront($saleSubDetails['SSPT_ID']);
			$emailId = $saleSubDetails['SS_EmailAddress'];
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionReceiptToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl = new View;
			$tpl->assign('service_name',$service_name);
			$tpl->assign('schedule',$schedule);
			$tpl->assign('charge_amount',$charge_amount);
			$tpl->assign('link',$link);
			$tpl->assign('link_url',$link_url);
			$tpl->assign('order_id',$order_id);
			$tpl->assign('uname',$uname);
	
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$emailId,
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			//dump($InsertDataArray);
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;
			}
			unset($Eobj);
			return $this->P_Status;
		}
		
		
		
		private function sendPaymentDeclinedMailToCustomer($saleSubDetails)
		{
			//dump($saleSubDetails);
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];			
			
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionFailedToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('saleSubDetails',$saleSubDetails);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$saleSubDetails['SS_EmailAddress'],
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;	
		}
		
		private function sendPaymentConfirmationMailToAdmin($saleSubDetails)
		{
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionSummaryToAdmin';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('saleSubDetails',$saleSubDetails);						
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>'',
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;	
		}
		
		private function sendPaymentDeclinedMailToAdmin($sendPaymentDeclinedMailToAdmin)
		{			
			$this->load_model('Email','objemail');
			$Keyword='paySimpleTransactionFailedToAdmin';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('saleSubDetails',$saleSubDetails);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>'',
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;	
		}
		
		public function SchedulerPaymentNotification()
		{
			$FieldArray = array('SS_ID','SS_FirstName','SS_LastName','SS_EmailAddress','SS_NextOuccringDate','SS_Amount','SS_Schedule','SS_ItemName');				
			$Condition = " AND SS_NextOuccringDate='".getNextDate(3)."'";		
			//$Condition = " AND SS_NextOuccringDate='".getDateTime(0,'Y-m-d')."'";		
			$SaleSubscriptionArray = $this->objSaleSub->getSaleSubscriptionListSimple($FieldArray,$Condition);
			//dump($SaleSubscriptionArray);
			if(count($SaleSubscriptionArray)>0)
			{
				foreach($SaleSubscriptionArray as $key => $arrDetails)
				{
					if($this->sendMail($arrDetails))
						echo "ok";
					else
						echo "not ok";
				}
			}
			else
			echo "No record found to send mail";
			exit;
		}
		
		private function sendMail($saleSubDetails)
		{
			//dump($saleSubDetails);
			$uname = $saleSubDetails['SS_FirstName']." ".$saleSubDetails['SS_LastName'];
			$charge_date = $saleSubDetails['SS_NextOuccringDate'];
			$service_name = $saleSubDetails['SS_ItemName'];
			$schedule = $saleSubDetails['SS_Schedule'];
			$charge_amount = $saleSubDetails['SS_Amount'];
			$this->load_model('Email','objemail');
			$Keyword='paySimpleDailySchedulerToCustomer';
			$where=" Where Keyword='".$Keyword."'";
			$DataArray=array('TemplateID','TemplateName','EmailTo','EmailToCc','EmailToBcc','EmailFrom','Subject_EN');
			$GetTemplate=$this->objemail->GetTemplateDetail($DataArray,$where);
			//dump($GetTemplate);
			$tpl=new View;
			$tpl->assign('charge_date',formatDate($charge_date,'n/j/y'));
			$tpl->assign('service_name',$service_name);
			$tpl->assign('schedule',$schedule);
			$tpl->assign('charge_amount',$charge_amount);
			$tpl->assign('uname',$uname);			
			$HTML=$tpl->draw('email/'.$GetTemplate['TemplateName'],true);				
			//echo $HTML;exit;
			$InsertDataArray=array('FromID'=>$this->arrCampainDetails['Camp_ID'],
			'CC'=>$GetTemplate['EmailToCc'],'BCC'=>$GetTemplate['EmailToBcc'],
			'FromAddress'=>$GetTemplate['EmailFrom'],'ToAddress'=>$saleSubDetails['SS_EmailAddress'],
			'Subject'=>$GetTemplate['Subject_EN'],'Body'=>$HTML,'Status'=>'0','SendMode'=>'1','AddedOn'=>getDateTime());
			$id=$this->objemail->InsertEmailDetail($InsertDataArray);
			$Eobj	= LoadLib('BulkEmail');
			
			$Status=$Eobj->sendEmail($id);
			if($Status)
			{
				$this->P_Status=1;
			}
			else
			{
				$this->P_Status=0;			
			}
			unset($Eobj);	
			return $this->P_Status;
		}
		
		private function getNextReoccurringDate($Interval,$Date=NULL)
		{
			/*YYYY-MM-DD*/
			if($Date==NULL)
				$Date = getDateTime(0,'Y-m-d');
			
			switch(strtolower($Interval))
			{
				case 'daily':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 days'));
				break;	
				case 'weekly':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 week'));
				break;
				case 'monthly':				
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 month'));
				break;
				case 'quarterly':
					$res_date = date('Y-m-d', strtotime($Date. ' + 3 month'));
				break;
				case 'bi-annually':
					$res_date = date('Y-m-d', strtotime($Date. ' + 6 month'));
				break;
				case 'annually':
					$res_date = date('Y-m-d', strtotime($Date. ' + 1 year'));
				break;
				default:
					$res_date = $Date;
			}	
			return $res_date;
		}
		
		public function showTransactionReceipt($sspt_Id)
		{
			$this->SSPT_Id = keyDecryptFront($sspt_Id);
			$FieldsArray = array('SSPT_ID','SSPT_PaymentType','SSPT_PaymentAmount','SSPT_PaidAmount','SSPT_PaymentGatewayName','SSPT_Status','SSPT_PaymentGatewayTransactionID',					
								'SSPT_CreatedDate','SS_RefNumber','SS_DateTime','SS_ItemId','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
								'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City','SS_State','SS_Zipcode','SS_Country',
								'SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaySimpleCustomerID','SS_PaymentMode','SS_Schedule','SS_TotalCyclesPaid','SS_StartDate',
								'SS_LastOuccringDate','SS_LastOuccringStatus','SS_NextOuccringDate','SS_PaymentStatus','SS_SpecialInstruction','SS_EnableRecipt','SS_Status'
								);
			$this->objSaleSub->SSPT_Id = $this->SSPT_Id;
			$arrSaleTransDetails = $this->objSaleSub->GetSaleTransactionDetails($FieldsArray);
			//dump($arrSaleTransDetails);
			$this->tpl = new View;
			$this->tpl->assign('arrSaleTransDetails',$arrSaleTransDetails);
			$HTML = $this->tpl->draw('salesubscription/orderReceipt',true);
			//echo $HTML;exit; 
			$DP_Obj=LoadLib('DomPdfGen');
			//dump($DP_Obj);
			$DP_Obj->DP_HTML = $HTML;
			$DP_Obj->ProcessPDF();
			exit;
		}	
		
	}

?>