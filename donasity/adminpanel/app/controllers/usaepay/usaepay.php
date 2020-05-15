<?php
	class usaepay_Controller extends Controller
	{
		public $client, $token,$CustNum;
		
		function __construct()
		{
			$this->sourcekey = '_189I7vYe0AZ00N0DATRlAM6KnXfTJry';
  			$this->pin = '4321';			
			$this->setObjClient();
			$this->setToken(); 
		}	
		
		public function test()
		{
			echo getDateTime();
			echo "<br>";
			echo formatDate(getDateTime(),'m-d-Y H:i:s');
			$messageParams = array(
				"msgCode"=>$Code,
				"msg"			=> $Msg,
				"msgLog"		=> 0,									
				"msgDisplay"	=> 1,
				"msgType"		=> 2);
			EnPException::setConfirmation($messageParams);

			echo "<br>";
			echo getDateTime();
			exit;
		}
		
		private function setObjClient()
		{
			$wsdl='https://sandbox.usaepay.com/soap/gate/0AE595C1/usaepay.wsdl';
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
		
		public function index($param,$cusNum='')
		{
			if($cusNum!='')
			$this->CustNum = $cusNum;
			switch($param)
			{
				case 'addCustomer':
					$this->addCustomer();
				break; 
				case 'runCustomerTransaction':
					$this->runCustomerTransaction();
				break;
				case 'getCustomerHistory':
					$this->getCustomerHistory();
				break;	
				case 'getTransactionStatus':
					$this->getTransactionStatus();
				break;		
				case 'getCustomer':
					$res=$this->client->getCustomer($this->token,'4785427'); 
					dump($res);
				break; 				
			}
		
		}	
		private function addCustomer()
		{
			try { 
				$CustomerData=array(
									'BillingAddress'=>array(
									'FirstName'=>'Test',
									'LastName'=>'One',
									'Company'=>'Qualdev',
									'Street'=>'1234 main st',
									'Street2'=>'Suite #123',
									'City'=>'Los Angeles',
									'State'=>'CA',
									'Zip'=>'12345',
									'Country'=>'US',
									'Email'=>'qualdev.test@gmail.com',
									'Phone'=>'333-333-3333',
									'Fax'=>'333-333-3334'),
									
									'CustomData'=>base64_encode(serialize(array("mydata"=>"All details correct only scheduel disabled"))),
									'CustomFields'=>array(
															array('Field'=>'Foo', 'Value'=>'Testing'),
															array('Field'=>'Bar', 'Value'=>'Tested')
														),
									'CustomerID'=>1,
									'Description'=>'Daily Bill',
									'Enabled'=>false,
									'Amount'=>'1.30',
									'Tax'=>'0',
									'Next'=>'2016-02-10',
									'Notes'=>'All details correct only scheduel disabled',
									'NumLeft'=>'50',
									'OrderID'=>2,
									'ReceiptNote'=>'addCustomer test Created Charge',
									'Schedule'=>'daily',									
									'SendReceipt'=>true,	
									'User'=>'donasitytestaccount',
									'CustNum'=>12345
									);

				
				$Result=$this->client->addCustomer($this->token,$CustomerData); 
				echo "Customer Number : ";
				dump($Result);
			 
			}
			catch(SoapFault $e) 
			{ 			 
			  echo "SoapFault: " .$e->getMessage(); dump($e,0); 
			  echo "\n\nRequest: " . $this->client->__getLastRequest(); 
			  echo "\n\nResponse: " . $this->client->__getLastResponse(); 
			} 
			exit;		
		}
		public function runCustomerTransaction()
		{
			try{
				$Parameters=array(
								'Command'=>'Check',
								'Details'=>array( 
								'Invoice' => rand(), 
								'PONum' => '', 
								'OrderID' => '', 
								'Description' => 'first payment', 
								'Amount'=>'1' )
				); 
			 
			  $CustNum='4785679'; 
			  $PayMethod=''; 
			
			  $res=$this->client->runCustomerTransaction($this->token, $CustNum, $PayMethod, $Parameters); 
			  dump($res);
			  
			}
			catch (SoapFault $e) 
			{ 
			  echo $this->client->__getLastRequest(); 
			  echo $this->client->__getLastResponse(); 
			  die("runCustomerTransaction failed :" .$e->getMessage());  
			} 
			exit;
		}
		
		public function getTransactionStatus()
		{
			try { 
 
			  $refnum='104438146'; 
			  echo "<pre>";
			  print_r($this->client->getTransactionStatus($this->token,$refnum)); 
			 exit;
			  } 
			 
			catch(SoapFault $e) { 
			 
			  echo $e->getMessage(); exit;
			 
			} 	
		}
		
		public function updateCustomerPaymentMethod()
		{
			try { 
 
				  $this->CustNum; 
				  $this->ArrayPaymentMethod = array('MethodID'=>'ACH',
													'MethodName'=>'Check Payment',
													'SecondarySort'=>1,
													'Account'=>'345654345',
													'AccountType'=>'checking',
													'Routing'=>'592755385',
													'CheckNumber'=>'999999'
													);
				 
				 /*If set to true, an AuthOnly verification of the credit card validity will be run. (See above.)*/
				  $this->Verify=false; 
				 
				  $res=$this->client->updateCustomerPaymentMethod($this->token,$this->ArrayPaymentMethod,$this->Verify);
				 
				  print_r($res); 
				 
				} 
				 
				catch(SoapFault $e) { 
				 
				  echo "\n\nResponse: " . $tran->__getLastResponse(); 
				  die("soap fault: " .$e->getMessage()); 
				  echo "SoapFault: " .$e->getMessage(); 
				 
				  print_r($e); 
				 
				  echo "\n\nRequest: " . $tran->__getLastRequest(); 
				 
				} 	
		}
		
		public function getCustomerHistory()
		{
			 dump($this->client->getCustomerHistory($this->token,$this->CustNum)); 	
		}
		
		public function  getCustomerReport()
		{
			$Fields = array( 
			'Details.Amount', 
			'AccountHolder',  
			'CheckTrace.TrackingNum' 
			);
			
			$res = $this->client->getCustomerReport($this->token, 'All Transactions by Date',$Fields,'html');
			dump($res);	
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
					'MethodID' => array(
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
		
	}

?>