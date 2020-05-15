<?php
	class Usaepayment_Model extends Model
	{
		public $token, $client, $CustNum, $PayMethod, $ArrayTransactionDetails, $ArrayPaymentMethod,$Verify,$Default,$MethodID,$ErrorMessage;		
		
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
	}
	
?>