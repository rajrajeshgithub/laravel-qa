<?php
	class Salesubscription_Controller extends Controller
	{
		public $itemId,	$itemCode, $itemName, $itemPrice, $itemQty, $itemAmount, $firstName, $lastName;
		public $emailAddress, $phoneNo, $organizationName, $EIN, $websiteUrl, $streetAddress1;
		public $streetAddress2, $City, $ZipCode, $country, $state, $paymentMode, $interval, $sentMailReceipt, $referenceNote, $internalNote;
		public $loginDetails, $SS_Id, $P_Status,$RU_Id;
		public $arraySaleSubDetails,$CustNum,$ProcessStatus;
		public $bankName,$accountType, $routingNumber, $accountNumber, $checkNumber, $licenceNumber, $licenceState;
		public $PaymentMethod;
		
		function __construct()
		{
			$this->load_model('Common', 'objCMN');
			$this->load_model('SaleSubscription', 'objSaleSub');			
			$this->loginDetails = getsession("DonasityAdminLoginDetail");	
			$this->P_Status = 1;			
		}
		
		public function index($action='list',$SS_Id=NULL,$RU_Id=NULL)
		{	
		
			if($SS_Id!=NULL)
				$this->SS_Id = keyDecrypt($SS_Id);
			if($RU_Id!=NULL)
				$this->RU_Id = keyDecrypt($RU_Id);
						
			switch(strtolower($action))
			{
				case 'step-1':
					$this->showStep1();
				break;
				case 'step-2':
					$this->showStep2();
				break;
				default:
					$this->showList();
			}
			
		}
		
		private function showList()
		{
			$this->tpl = new view;
			$this->tpl->draw('salesubscription/showList');	
		}
		
		private function showStep1()
		{
			$countriesList		= $this->objCMN->getCountriesList();
			$stateList			= $this->objCMN->getStateList('US');	
			$itemList			= $this->objCMN->getProductList();	
			if($this->SS_Id!=NULL && $this->SS_Id!='')
			{	
				$this->getProcessStatus();			
				$this->ProcessStatus = $this->arraySaleSubDetails['SS_Status'];
				switch($this->ProcessStatus)
				{
					case 2:
						redirect(URL.'salesubscription/index/step-2/'.keyEncrypt($this->SS_Id));
					break;
					case 3:
					break;			
				}
			
				$FieldArray = array('*','SS_ID','SS_RefNumber','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
									'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City',
									'SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaymentMode',
									'SS_Schedule','SS_SpecialInstruction','SS_EnableRecipt');
				$this->objSaleSub->SS_Id = $this->SS_Id;			
				$this->arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);		
			}
			$this->tpl = new view;
			$this->tpl->assign('CountryList',$countriesList);
			$this->tpl->assign('StateList', $stateList);
			$this->tpl->assign('ItemList', $itemList);
			$this->tpl->assign('SS_Id',$this->SS_Id);
			$this->tpl->assign('RU_Id' ,isset($this->RU_Id)?keyEncrypt($this->RU_Id):'');
			$this->tpl->assign('ReoccurringInterval',get_setting('ReoccurringInterval'));
			$this->tpl->assign('arraySaleSubDetails',$this->arraySaleSubDetails);
			$this->tpl->draw('salesubscription/formStep1');
		}
		
		private function showStep2()
		{
			if(!$this->SS_Id || $this->SS_Id=='')
				redirect(URL.'salesubscription/index/step-1');
			
			$FieldArray = array('SS_ID','SS_RefNumber','SS_ItemCode','SS_ItemName','SS_ItemQuantitiy','SS_ItemPrice','SS_Amount',
								'SS_OrganizationName','SS_FirstName','SS_LastName','SS_StreetAddress1','SS_StreetAddress2','SS_City',
								'SS_State','SS_Zipcode','SS_Country','SS_Phone','SS_EmailAddress','SS_Website','SS_EIN','SS_PaymentMode',
								'SS_Schedule','SS_SpecialInstruction','SS_EnableRecipt');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);
			$this->tpl = new view;
			$this->tpl->assign('SS_Id',$this->SS_Id);
			$this->tpl->assign('arraySaleSubDetails',$arraySaleSubDetails);
			$this->tpl->draw('salesubscription/formStep2');
		}
		
		public function processStep1()
		{
			$this->getFormDataStep1();			
			if($this->SS_Id!='')
			{
				/*
				$myvar=modelobj->getOrderSDetails("status","where");
				$myvar=$this->getProcessStatus();
				$this->getProcessStatus();
				*/
				$this->getProcessStatus();			
				$this->ProcessStatus = $this->arraySaleSubDetails['SS_Status'];
				switch($this->ProcessStatus)
				{
					case 1:
						$this->updateCustomerDetailsDB();
					break;					
					default:
						redirect(URL.'salesubscription/index/step-2/'.keyEncrypt($this->SS_Id));
					break;					
				}
			}
			else
			{
				$this->addCustomerDetailsDB();
			}
			
			if($this->P_Status)
				$this->addCustomerDetailsUSAePay();				
			if($this->P_Status)
				$this->updateCustomerDetailsAfterProcess1();
			if($this->P_Status)
			{
				$this->SetStatus(1,'C21001');
				redirect(URL.'salesubscription/index/step-2/'.keyEncrypt($this->SS_Id));
			}
			else
			{
				$this->SetStatus(0,'E21001');
				redirect(URL.'salesubscription/index/step-1/'.keyEncrypt($this->SS_Id));
			}										
		}
		
		private function getProcessStatus()
		{
			$FieldArray = array('SS_ID','SS_PaySimpleCustomerID','SS_Status');
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);	
		}
		
		private function addCustomerDetailsDB()
		{
			$FieldArray = array('SS_RefNumber'=>$this->generateRefNumber(),
								'SS_DateTime'=>getDateTime(),
								'SS_ItemId'=>isset($this->itemId)?$this->itemId:NULL,
								'SS_ItemCode'=>isset($this->itemCode)?$this->itemCode:NULL,
								'SS_ItemName'=>isset($this->itemName)?$this->itemName:NULL,
								'SS_ItemQuantitiy'=>isset($this->itemQty)?$this->itemQty:NULL,
								'SS_ItemPrice'=>isset($this->itemPrice)?$this->itemPrice:NULL,
								'SS_Amount'=>isset($this->itemAmount)?$this->itemAmount:NULL,
								'SS_OrganizationName'=>isset($this->organizationName)?$this->organizationName:NULL,
								'SS_FirstName'=>isset($this->firstName)?$this->firstName:NULL,
								'SS_LastName'=>isset($this->lastName)?$this->lastName:NULL,
								'SS_StreetAddress1'=>isset($this->streetAddress1)?$this->streetAddress1:NULL,
								'SS_StreetAddress2'=>isset($this->streetAddress2)?$this->streetAddress2:NULL,
								'SS_City'=>isset($this->City)?$this->City:NULL,
								'SS_State'=>isset($this->state)?$this->state:NULL,
								'SS_Zipcode'=>isset($this->ZipCode)?$this->ZipCode:NULL,
								'SS_Country'=>isset($this->country)?$this->country:NULL,
								'SS_Phone'=>isset($this->phoneNo)?$this->phoneNo:NULL,
								'SS_EmailAddress'=>isset($this->emailAddress)?$this->emailAddress:NULL,
								'SS_Website'=>isset($this->websiteUrl)?$this->websiteUrl:NULL,
								'SS_EIN'=>isset($this->EIN)?$this->EIN:NULL,
								'SS_PaymentMode'=>isset($this->paymentMode)?$this->paymentMode:NULL,
								'SS_Schedule'=>isset($this->interval)?$this->interval:NULL,
								'SS_TotalCycles'=>'999',
								'SS_StartDate'=>getDateTime(0,'Y-m-d'),
								'SS_NextOuccringDate'=>$this->getNextReoccurringDate($this->interval),
								'SS_CheckNumber'=>'999999',
								'SS_PaymentStatus'=>0,
								'SS_SpecialInstruction'=>isset($this->referenceNote)?$this->referenceNote:NULL,
								'SS_AdminNotes'=>isset($this->internalNote)?$this->internalNote:NULL,
								'SS_EnableRecipt'=>isset($this->sentMailReceipt)?$this->sentMailReceipt:NULL,
								'SS_Status'=>1,
								'SS_CreatedByUserId'=>$this->loginDetails['admin_id'],
								'SS_CreatedDate'=>getDateTime(),
								'SS_LastUpdatedDate'=>getDateTime(),
								'SS_Locale'=>GetUserLocale());
			$this->SS_Id = $this->objSaleSub->insertSaleSubscriptionDetails($FieldArray);
			if($this->SS_Id)
			{
				setSession('saleSubId',keyEncrypt($this->SS_Id));
				$this->P_Status = 1;				
			}
			else
			{
				$this->P_Status = 0;
				$this->SetStatus(0,'C21001');
				redirect(URL.'salesubscription/index/step-1');	
			}		
		}
		
		private function updateCustomerDetailsDB()
		{
			$DataArray = array('SS_RefNumber'=>$this->generateRefNumber(),
								'SS_DateTime'=>getDateTime(),
								'SS_ItemId'=>isset($this->itemId)?$this->itemId:NULL,
								'SS_ItemCode'=>isset($this->itemCode)?$this->itemCode:NULL,
								'SS_ItemName'=>isset($this->itemName)?$this->itemName:NULL,
								'SS_ItemQuantitiy'=>isset($this->itemQty)?$this->itemQty:NULL,
								'SS_ItemPrice'=>isset($this->itemPrice)?$this->itemPrice:NULL,
								'SS_Amount'=>isset($this->itemAmount)?$this->itemAmount:NULL,
								'SS_OrganizationName'=>isset($this->organizationName)?$this->organizationName:NULL,
								'SS_FirstName'=>isset($this->firstName)?$this->firstName:NULL,
								'SS_LastName'=>isset($this->lastName)?$this->lastName:NULL,
								'SS_StreetAddress1'=>isset($this->streetAddress1)?$this->streetAddress1:NULL,
								'SS_StreetAddress2'=>isset($this->streetAddress2)?$this->streetAddress2:NULL,
								'SS_City'=>isset($this->City)?$this->City:NULL,
								'SS_State'=>isset($this->state)?$this->state:NULL,
								'SS_Zipcode'=>isset($this->ZipCode)?$this->ZipCode:NULL,
								'SS_Country'=>isset($this->country)?$this->country:NULL,
								'SS_Phone'=>isset($this->phoneNo)?$this->phoneNo:NULL,
								'SS_EmailAddress'=>isset($this->emailAddress)?$this->emailAddress:NULL,
								'SS_Website'=>isset($this->websiteUrl)?$this->websiteUrl:NULL,
								'SS_EIN'=>isset($this->EIN)?$this->EIN:NULL,
								'SS_PaymentMode'=>isset($this->paymentMode)?$this->paymentMode:NULL,
								'SS_Schedule'=>isset($this->interval)?$this->interval:NULL,
								'SS_TotalCycles'=>'999',
								'SS_StartDate'=>getDateTime(0,'Y-m-d'),
								'SS_NextOuccringDate'=>$this->getNextReoccurringDate($this->interval),
								'SS_CheckNumber'=>'999999',
								'SS_PaymentStatus'=>0,
								'SS_SpecialInstruction'=>isset($this->referenceNote)?$this->referenceNote:NULL,
								'SS_AdminNotes'=>isset($this->internalNote)?$this->internalNote:NULL,
								'SS_EnableRecipt'=>isset($this->sentMailReceipt)?$this->sentMailReceipt:NULL,
								'SS_Status'=>1,								
								'SS_LastUpdatedDate'=>getDateTime(),
								'SS_Locale'=>GetUserLocale());	
								
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			if($this->objSalSub->P_Status)
				$this->P_Status=1;	
		}
		
		private function updateCustomerDetailsAfterProcess1()
		{
			if($this->CustNum)
			{
				$this->SS_Id = keyDecrypt(getSession('saleSubId'));
				unset($_SESSION['saleSubId']);
				if($this->SS_Id)
				{
					$this->objSaleSub->SS_Id = $this->SS_Id;
					$DataArray = array('SS_PaySimpleCustomerID'=>$this->CustNum,'SS_Status'=>2,'SS_LastUpdatedDate'=>getDateTime());
					$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
					if($this->objSalSub->P_Status)
						$this->P_Status=1;	
				}
				else
				{
					$this->P_Status=0;
				}
			}
		}
		
		private function addCustomerDetailsUSAePay()
		{
			$this->load_model('Usaepay', 'objUSAePay');
			$this->objUSAePay->ArrayCustomerDetails = array(
										'BillingAddress' => array(
																'FirstName'=>$this->firstName,
																'LastName'=>$this->lastName,
																'Company'=>$this->organizationName,
																'Street'=>$this->streetAddress1,
																'Street2'=>$this->streetAddress2,
																'City'=>$this->City,
																'State'=>$this->state,
																'Zip'=>$this->ZipCode,
																'Country'=>$this->country,
																'Email'=>$this->emailAddress,
																'Phone'=>$this->phoneNo																		
																),										
										'CustomFields' => array(
															array('Field'=>'ItemName', 'Value'=>$this->itemName),
															array('Field'=>'ItemCode', 'Value'=>$this->itemCode),
															array('Field'=>'ItemPrice', 'Value'=>$this->itemPrice)
																),
										'CustomerID'=>($this->RU_Id!='')?'R'.$this->RU_Id:$this->SS_Id+1000,
										'Description'=>$this->interval,										
										'Amount'=>$this->itemAmount,
										'Tax'=>'0',
										'Next'=>$this->getNextReoccurringDate($this->interval),
										'Notes'=>$this->internalNote,
										'NumLeft'=>'999',
										'OrderID'=>$this->SS_Id+1000,
										'ReceiptNote'=>$this->referenceNote,
										'Schedule'=>$this->interval,
										'SendReceipt'=>true,
										'Source'=>'Recurring',
										'Enabled'=>($this->paymentMode=='RC')?true:false,
										'User'=>'donasitytestaccount'
																													
										);	
			$response = $this->objUSAePay->addCustomer();			
			if($this->objUSAePay->P_Status)
			{
				$this->CustNum = $response;				
			}
			else
			{
				$this->SetStatus(0,000,$response);	
			}
		}
		
		private function addCustomerPaymentMethod()
		{
			$this->load_model('Usaepay', 'objUSAePay');			
			$this->objUSAePay->ArrayPaymentMethod = array('MethodName'=>'ACH',
											'BankName'=>$this->bankName,											
											'SecondarySort'=>1,
											'Account'=>$this->accountNumber,
											'AccountType'=>$this->accountType,
											'Routing'=>$this->routingNumber,
											'CheckNumber'=>$this->checkNumber
											);
			$this->objUSAePay->Default	= true;
			$this->objUSAePay->CustNum 	= $this->arraySaleSubDetails['SS_PaySimpleCustomerID'];
			$response = $this->objUSAePay->addCustomerPaymentMethod();
			if($this->objUSAePay->P_Status)
			{				
				$this->PaymentMethod = $response;				
			}
			else
			{
				$this->SetStatus(0,000,$response);	
			}
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
			}	
			return $res_date;
		}
		
		public function processStep2()
		{
			$this->SS_Id			= request('post','SS_Id',0);
			$this->bankName 		= request('post','bankName',0);
			$this->accountType		= request('post','accountType',0);
			$this->routingNumber	= request('post','routingNumber',0);
			$this->accountNumber	= request('post','accountNumber',0);
			$this->checkNumber		= request('post','checkNumber',0);
			$this->licenceNumber	= request('post','licenceNumber',0);
			$this->licenceState		= request('post','licenceState',0);
			
			setSession('saleSubId',keyEncrypt($this->SS_Id));
			$this->addCustomerPaymentMethod();
			if($this->objUSAePay->P_Status)
				$this->updateCustomerDetailsAfterProcess2();
			if($this->P_Status)
			{
				$this->SetStatus(1,'C21002');
				redirect(URL.'salesubscription');
			}
			else
			{
				$this->SetStatus(0,'E21002');
				redirect(URL.'salesubscription/index/step-2/'.$this->SS_Id);
			}
		}
		
		private function updateCustomerDetailsAfterProcess2()
		{
			$this->SS_Id = keyDecrypt(getSession('saleSubId'));	
			$DataArray = array(	'SS_Status'=>3,
								'SS_LastUpdatedDate'=>getDateTime(),
								'SS_Locale'=>GetUserLocale());
								
			$this->objSaleSub->SS_Id = $this->SS_Id;
			$this->objSaleSub->updateSaleSubscriptionDetails($DataArray);
			if($this->objSalSub->P_Status)
				$this->P_Status=1;	
			else
				$this->P_Status;
		}
		
		private function getFormDataStep2()
		{
			$this->SS_Id			= request('post','SS_Id',0);
			$this->bankName 		= request('post','bankName',0);
			$this->accountType		= request('post','accountType',0);
			$this->routingNumber	= request('post','routingNumber',0);
			$this->accountNumber	= request('post','accountNumber',0);
			$this->checkNumber		= request('post','checkNumber',0);
			$this->licenceNumber	= request('post','licenceNumber',0);
			$this->licenceState		= request('post','licenceState',0);
		}
		
		private function getFormDataStep1()
		{	
			$this->RU_Id			= request('post','RU_Id',0);
			if($this->RU_Id!='' || $this->RU_Id!=NULL)
			$this->RU_Id			= keyDecrypt($this->RU_Id);
			$SS_Id					= request('post','SS_Id',0);
			$this->SS_Id			= keyDecrypt($SS_Id);
			$this->itemId			= request('post','itemId',1);
			$this->itemCode			= request('post','itemCode',0);
			$this->itemName			= request('post','itemName',0);
			$this->itemPrice		= request('post','itemPrice',0);
			$this->itemQty			= request('post','itemQty',1);			
			$this->itemAmount		= request('post','itemAmount',0);
			$this->firstName		= request('post','firstName',0);
			$this->lastName			= request('post','lastName',0);
			$this->emailAddress		= request('post','emailAddress',0);
			$this->phoneNo			= request('post','phoneNo',0);
			$this->organizationName	= request('post','organizationName',0);
			$this->EIN				= request('post','EIN',0);
			$this->websiteUrl		= request('post','websiteUrl',0);
			$this->streetAddress1	= request('post','streetAddress1',0);
			$this->streetAddress2	= request('post','streetAddress2',0);
			$this->City				= request('post','city',0);
			$this->ZipCode			= request('post','zipCode',0);
			$this->country			= request('post','country',0);
			$this->state			= request('post','state',0);
			$this->paymentMode		= request('post','paymentMode',0);
			$this->interval			= request('post','interval',0);
			$this->sentMailReceipt	= request('post','sendReceipt',1);
			$this->referenceNote	= request('post','referenceNote',0);
			$this->internalNote		= request('post','internalNote',0);	
		}
	
		private function generateRefNumber()
		{
			return $this->UniqueRandomNumbersWithinRange(1001,9999,2);
		}
	
			
		private function UniqueRandomNumbersWithinRange($min, $max, $quantity) 
		{
			$numbers = range($min, $max);
			shuffle($numbers);
			return implode("",array_slice($numbers, 0, $quantity));
		}
	
		public function getStateList($countryAbbr, $stateAbbr) 
		{
			$html = '<option value="">Select State</option>';
			$stateList = $this->objCMN->getStateList($countryAbbr);		
			if(count($stateList) > 0) 
			{
				for($s = 0; $s < count($stateList); $s++) {
					if(trim($stateAbbr) != '') {
						if($stateList[$s]['State_Value'] == $stateAbbr)
							$sel='selected';
						else
							$sel = '';
					} else 
						$sel = '';
						
					$html .= '<option value="' . $stateList[$s]['State_Value'] . '" ' . $sel . '>' . $stateList[$s]['State_Name'] . '</option>';   
				}
			}
			echo $html;
			exit;
		}
		
		private function SetStatus($Status, $Code, $custom=NULL) 
		{
			$this->P_Status = $Status;
			$Msg = "Custom Confirmation message";
			if($custom!=NULL){
				$Msg = $custom;
				$Code = '000';
			}
			
			if($Status) {							
				$messageParams = array(
					"msgCode"=>$Code,
					"msg"			=> $Msg,
					"msgLog"		=> 0,									
					"msgDisplay"	=> 1,
					"msgType"		=> 2);
				EnPException::setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode" 			=> $Code,
					"errMsg"			=> $Msg,
					"errOriginDetails"	=> basename(__FILE__),
					"errSeverity"		=> 1,
					"msgDisplay"		=> 1,
					"msgType"			=> 1);
				EnPException::setError($messageParams);
			}
		}
		
		private function checkProcessStatus($ss_id)
		{
			$ss_id = keyDecrypt($ss_id);
			if($ss_id!='')
			{
				$FieldArray = array('SS_ID','SS_Status','SS_PaySimpleCustomerID','SS_PaySimplePaymentMethodID');
				$this->objSaleSub->SS_Id = $ss_id;
				$arraySaleSubDetails = $this->objSaleSub->getSaleSubscriptionDetails($FieldArray);		
				$status = $arraySaleSubDetails['SS_Status'];
				if($status!=$status1)
				switch(strtolower($status))
				{
					case 1:
						$this->ProcessStatus = 'Order Added';
						$redirect = URL."salesubscription/process/add-order/".keyEncrypt($this->SS_Id);
					break;
					case 2:
						$this->ProcessStatus = 'Order Added to USAePay';
						$redirect = URL."salesubscription/process/".keyEncrypt($this->SS_Id);
					break;
					case 3:
						$this->ProcessStatus = 'Payment Details Added to USAePay';
						$redirect = URL."salesubscription/process/".keyEncrypt($this->SS_Id);
					break;
					default:
						$redirect = URL."salesubscription/process";
				}
			}
		}
	}
	
	/*
	
	Step#1
	--Descrive Flow
	ProcessStep1
	-Get Input
	-Validate Input
	-Add to Database
	-Add to Usapaye
	-Get Order Details
	-Update Details
	
	Step#2
	--Make Voids
	Input1
	Input1
	Input1
	Input1
	Input1
	
	CheckInput(Input1);
	AddUSAPAye(Array);
	GetOrderDetails(INPUT);
	
	AddOrderDetails(Array);
	SetOrderDetails(Array);
	
	Step#3
	Write Business Locaic of function 
	
	*/
	
?>

