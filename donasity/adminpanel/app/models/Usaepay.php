<?php
	/*
		To Create customer addCustomer()
		$this->ArrayCustomerDetails = array(
										'BillingAddress' => array(
																'FirstName'=>$this->FirstName,
																'LastName'=>$this->LastName,
																'Company'=>$this->OrganizationName,
																'Street'=>$this->Address1,
																'Street2'=>$this->Address2,
																'City'=>$this->City,
																'State'=>$this->State,
																'Zip'=>$this->Zip,
																'Country'=>$this->Country,
																'Email'=>$this->EmailAddress,
																'Phone'=>$this->Phone,
																'Fax'=>$this->Fax			
																),
										'PaymentMethods' => array(																		 
																'MethodName'=>'ACH',
																'MethodName'=>'Check Payment',
																'SecondarySort'=>1,
																'Account'=>$this->AccountNumber,
																'AccountType'=>isset($this->AccountType)?$this->AccountType:'checking',
																'Routing'=>$this->Rounting
																),
										'CustomFields' => array(
															array('Field'=>'Product', 'Value'=>''),
															array('Field'=>'ProductCode', 'Value'=>'')
																),
										'CustomerID'=>123123 + rand(),
										'Description'=>'Weekly Bill',
										'Enabled'=>false,
										'Amount'=>'10',
										'Tax'=>'0',
										'Next'=>'2016-10-09',
										'Notes'=>'Testing the soap addCustomer Function',
										'NumLeft'=>'50',
										'OrderID'=>rand(),
										'ReceiptNote'=>'addCustomer test Created Charge',
										'Schedule'=>'weekly',
										'SendReceipt'=>true,
										'Source'=>'Recurring',
										'Enabled'=>true,
										'User'=>'donasitytestaccount',
										'CustNum'=>'C'.rand()																				
										);	
		$this->addCustomer();
		--------------------------------------------------
		To Run Customer Transaction 
		$this->ArrayTransactionDetails = array(
												'Command'=>'Check ',
												'Details'=>array( 
												'Invoice' => rand(), 
												'PONum' => '', 
												'OrderID' => '', 
												'Description' => 'first payment', 
												'Amount'=>'1.50' )
												);
			$this->PayMethod='0'; 
			$this->CustNum;
			$this->runCustomerTransaction()
	    --------------------------------------------------
		To update customer payment method
			$this->updateCustomerPaymentMethod()
			$this->CustNum; 
			$this->Verify=false; 
		  	$this->ArrayPaymentMethod = array('MethodName'=>'ACH',
											'MethodName'=>'Check Payment',
											'SecondarySort'=>1,
											'Account'=>'345654345',
											'AccountType'=>'checking',
											'Routing'=>'592755385',
											'CheckNumber'=>'999999'
											);
		 
	
		  	
		---------------------------------------------------
		To delete customer 
		
		$this->CustNum	
		$this->deleteCustomer()
		--------------------------------------------------
		To disable customer reocurring process
		
		$this->CustNum
		$this->disableCustomer()
		---------------------------------------------------
		To enable customer reoccurring process
		
		$this->CustNum;
		$this->enableCustomer();
	
	*/
	
	class Usaepay_Model extends Model
	{
		public $token, $client,$ArrayCustomerDetails, $CustNum, $PayMethod, $ArrayTransactionDetails, $ArrayPaymentMethod,$Verify,$Default,$MethodID,$ErrorMessage;
		public $PaySimpleResponseArray, $RefNum;
		
		function __construct()
		{
			$this->sourcekey 	= PAYSIMPLE_SOURCE_KEY;
			$this->pin			= PAYSIMPLE_PIN;
			$this->setToken();
			$this->setObjClient();
			$this->P_Status=1;
		}
		
		private function setObjClient()
		{
			$wsdl=PAYSIMPLE_WSDL;
			$this->client = new SoapClient($wsdl);	
			return $this->client;
		}
		
		private function setToken()
		{
			  $seed=time() . rand();			  
			  $clear= $this->sourcekey . $seed . $this->pin;
			  $hash=sha1($clear);			 			
			  $this->token=array(
				'SourceKey'=>$this->sourcekey,
				'PinHash'=>array(
				   'Type'=>'sha1',
				   'Seed'=>$seed,
				   'HashValue'=>$hash
				 ),
				 'ClientIP'=>$_SERVER['REMOTE_ADDR'],
			  );
			  return $this->token;			  
		}
		
		public function addCustomer()
		{
			try{
				$Result = $this->client->addCustomer($this->token,$this->ArrayCustomerDetails);
				$this->P_Status = 1; 
				$this->CustNum = $Result;
				$this->PaySimpleResponse = $Result;
				return true;
			}
			catch(SoapFault $e) 
			{
				$this->P_Status = 0;
				$this->ErrorMessage = $e->getMessage();
				$this->PaySimpleResponse = $e->getMessage();				
				return false;
				
			} 	
		}
		
		public function updateCustomer()
		{
			try{
				$Result = $this->client->updateCustomer($this->token,$this->CustNum,$this->ArrayCustomerDetails);
				$this->P_Status = 1;				
				$this->PaySimpleResponse = $Result;
				return true;
			}
			catch(SoapFault $e) 
			{
				$this->P_Status = 0;
				$this->ErrorMessage = $e->getMessage();
				$this->PaySimpleResponse = $e->getMessage();				
				return false;
			} 	
		}
		
		public function quickUpdateCustomer()
		{
			try { 
 			 
				 $Result = $this->client->quickUpdateCustomer($this->token,$this->CustNum,$this->ArrayCustomerDetails);
				 $this->P_Status = 1;				
					$this->PaySimpleResponse = $Result;
					return true;
				} 								 
				catch(SoapFault $e) { 
				 
				 // echo "Error: " . $e->faultstring;
				  	$this->P_Status = 0;
					$this->ErrorMessage = $e->getMessage();
					$this->PaySimpleResponse = $e->getMessage();				
					return false; 
				}
			
		}
		
		public function runCustomerTransaction()
		{
			try{				
				$res=$this->client->runCustomerTransaction($this->token, $this->CustNum, $this->MethodID, $this->ArrayTransactionDetails); 
				$res = json_decode(json_encode($res), true);
				
				$this->PaySimpleResponseArray['RefNum']=$res['RefNum'];
				$this->PaySimpleResponseArray['Status']=$res['Status'];/* Queued, Pending, Submitted, Funded, Settled, Error, Voided, Returned, Timed out, Manager Approval Req. */
				$this->PaySimpleResponseArray['StatusCode']=$res['StatusCode'];/* N, P, B, F, S, E, V, R, T, M */
				
				$this->PaySimpleResponseArray['Result']=$res['Result'];/* Transaction Result (Approved, Declined, Error, etc) */
				$this->PaySimpleResponseArray['ResultCode']=$res['ResultCode'];
				$this->PaySimpleResponseArray['Error']=$res['Error'];
				$this->PaySimpleResponseArray['CustNum']=$res['CustNum'];
				$this->PaySimpleResponseArray['AuthCode']=$res['AuthCode'];
				$this->PaySimpleResponseArray['AuthAmount']=$res['AuthAmount'];				
				$this->PaySimpleResponse = $res;
				return true;
			}
			catch (SoapFault $e) 
			{
				$this->ErrorMessage = $e->getMessage();
				return false; 
			} 
		}
		
		public function runCheckSale()
		{
			try { 
				  $Request=array(
					'AccountHolder' => 'Tester Jones',
					'Details' => array(
					  'Description' => 'Example Transaction',
					  'Amount' => '4.99',
					  'Invoice' => '44539'
					  ),
					'CheckData' => array(
					  'CheckNumber' => '1234',
					  'Routing' => '123456789',
					  'Account' => '11111111',
					  'AccountType' => 'Savings',
					  'DriversLicense' => '34521343',
					  'DriversLicenseState' => 'CA',
					  'RecordType' => 'PPD'
					  )
					);
				 
				  $res=$client->runCheckSale($token, $Request);
				 
				}				 
				catch (SoapFault $e)  {
				  echo $client->__getLastRequest();
				  echo $client->__getLastResponse();
				  die("runCheckSale failed :" .$e->getMessage());
				  }	
		}
		
		public function getTransactionStatus()
		{
			try { 				 
				  	$res = $this->client->getTransactionStatus($this->token,$this->RefNum); 
				  	$res = json_decode(json_encode($res), true);
				
					$this->PaySimpleResponseArray['RefNum']=$res['RefNum'];
					$this->PaySimpleResponseArray['Status']=$res['Status'];/* Queued, Pending, Submitted, Funded, Settled, Error, Voided, Returned, Timed out, Manager Approval Req. */
					$this->PaySimpleResponseArray['StatusCode']=$res['StatusCode'];/* N, P, B, F, S, E, V, R, T, M */
					
					$this->PaySimpleResponseArray['Result']=$res['Result'];/* Transaction Result (Approved, Declined, Error, etc) */
					$this->PaySimpleResponseArray['ResultCode']=$res['ResultCode'];
					$this->PaySimpleResponseArray['Error']=$res['Error'];
					$this->PaySimpleResponseArray['CustNum']=$res['CustNum'];
					$this->PaySimpleResponseArray['AuthCode']=$res['AuthCode'];
					$this->PaySimpleResponseArray['AuthAmount']=$res['AuthAmount'];				
					$this->PaySimpleResponse = $res;					
					return true;				 
				  }				 
				catch(SoapFault $e) { 				 
				 	$this->ErrorMessage = $e->getMessage();					
					return false; 
				} 
					
		}
		
		public function addCustomerPaymentMethod()
		{
			try { 			 
				 	if(!isset($this->Default))
				  		$this->Default=true; 
				  	$this->Verify=false;
				  	$MethodID = $this->client->addCustomerPaymentMethod($this->token, $this->CustNum, $this->ArrayPaymentMethod,$this->Default, $this->Verify);
					$this->P_Status = 1; 
					$this->MethodID = $MethodID;
					$this->PaySimpleResponse = $MethodID; 
					return true;
				} 
				catch(SoapFault $e) 
				{
					$this->P_Status = 0;
					$this->ErrorMessage = $e->getMessage();
					$this->PaySimpleResponse = $e->getMessage();				
					return false;
				}
		}
		
		public function updateCustomerPaymentMethod()
		{
			try {
				 /*If set to true, an AuthOnly verification of the credit card validity will be run. (See above.)*/
				 	$this->Verify=false;
				 	$Result=$this->client->updateCustomerPaymentMethod($this->token,$this->ArrayPaymentMethod,$this->Verify);					
					/*boolean - Returns true if payment method is updated successfully.*/
				 	$this->P_Status = 1;				
					$this->PaySimpleResponse = $Result;
					return true;
				}				 
				catch(SoapFault $e)
				{
					$this->P_Status = 0;
					$this->ErrorMessage = $e->getMessage();
					$this->PaySimpleResponse = $e->getMessage();				
					return false;
				} 	
		}
		
		public function deleteCustomer()
		{
			try {
				  $res = $this->client->deleteCustomer($this->token, $this->CustNum);     
				  /* boolean - Returns confirmation of request only if successful. If request fails, an exception will be thrown.*/				 
				  return $res; 
				} 
				catch(SoapFault $e) 
				{
				  die("soap fault: " .$e->getMessage());  
				  return false;
				} 
		}
		
		public function disableCustomer()
		{
			try {
				 	$res = $this->client->disableCustomer($this->token,$this->CustNum); 
				 	$this->P_Status = 1;				
					$this->PaySimpleResponse = $res;
					return true;
				}
				catch(SoapFault $e) 
				{
				 $this->P_Status = 0;
				 $this->ErrorMessage = $e->getMessage();
				 $this->PaySimpleResponse = $e->getMessage();	
				 } 		
		}
		
		public function enableCustomer()
		{
			try {
			  		$res=$this->client->enableCustomer($this->token, $this->CustNum);     			 
					$this->P_Status = 1;				
					$this->PaySimpleResponse = $res;
					return true;
			  	}
			catch(SoapFault $e) 
				{
			  		$this->P_Status = 0;
				 $this->ErrorMessage = $e->getMessage();
				 $this->PaySimpleResponse = $e->getMessage();	
			  	}
		}
	}
	
?>