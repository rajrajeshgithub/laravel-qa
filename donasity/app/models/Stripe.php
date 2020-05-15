<?php
	class Stripe_Model extends Model
	{
		public $amount,$transactionFee,$cc_number,$cc_cvv,$cc_exp_month,$cc_exp_year,$cc_name,$StripeConnectedAccountID,$txnDescription,$sub_planID;
		public $application_fee,$destination,$invoice,$receipt_email;
		
		public $stripe_request,$stripe_response_complete,$stripe_response_filtered,$stripe_response_err;
		public $recurring_request;
		private $currency,$source_object;
		private $P_status;
		public $subscriptionID,$customerID;
		
		
		
		function __construct()
		{
		//require(LIBRARY_DIR.'stripe/c0onf1ig.php');
			require(LIBRARY_DIR.'stripe/init.php');
			$this->currency="usd";
			$this->source_object="card";
		}
		
		
		
		public function GetAccountNumber($AccountNumber)
		{
			
			
			\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
			return \Stripe\Account::retrieve($AccountNumber);
		}
		
		public function chargeCredit()
		{
			try
			{
				\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
				$resp= \Stripe\Charge::create($this->setChargeCreditRequest());
				
				$this->setResponseVariables($resp);
				
			}
			catch(Exception $e)
			{
				
				 $this->setErrorVariables($e);
				
			}
		   return $this->P_status;
		}
		
		public function chargeCreditForPurchase()
		{
			try
			{
				\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
				$resp= \Stripe\Charge::create($this->setChargeCreditForPurchaseRequest());
				
				$this->setResponseVariables($resp);
				
			}
			catch(Exception $e)
			{
				
				 $this->setErrorVariables($e);
				
			}
		   return $this->P_status;
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
		
		
		private function convertDollortoCent($dollorAmount)
		{
			return $dollorAmount*100;
		}
		private function convertCenttoDollor($dollorAmount)
		{
			return $dollorAmount/100;
		}
		
		
		private function setChargeCreditRequest()
		{
			$temp=array(
			 "amount" => $this->convertDollortoCent($this->amount),
			  "currency" => $this->currency,
			  "source" => array(
					"object" => $this->source_object,
			 		"number" => str_replace("-","",$this->cc_number),
			  		"exp_month" => $this->cc_exp_month, 
					"exp_year" => $this->cc_exp_year,
					"cvc" => $this->cc_cvv,
					"name"=>$this->cc_name
					),
			  "description" => $this->txnDescription,
			  "destination"=>$this->StripeConnectedAccountID, //"acct_16anboLBcDt59ky",
			  "application_fee"=>$this->convertDollortoCent($this->transactionFee)
			);
		
		$this->stripe_request=$temp;
	//	dump($this->stripe_request);
		return $temp;
		}
		
		
		
		private function setChargeCreditForPurchaseRequest()
		{
			$temp=array(
			 "amount" => $this->convertDollortoCent($this->amount),
			  "currency" => $this->currency,
			  "source" => array(
					"object" => $this->source_object,
			 		"number" => str_replace("-","",$this->cc_number),
			  		"exp_month" => $this->cc_exp_month, 
					"exp_year" => $this->cc_exp_year,
					"cvc" => $this->cc_cvv,
					"name"=>$this->cc_name
					),
			  "description" => $this->txnDescription
			  
			);
		
		$this->stripe_request=$temp;
	//	dump($this->stripe_request);
		return $temp;
		}
		
		
		
		
		public function setRecurring()
		{
			//dump($this->recurring_request);
			try
			{
				\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
				$resp=\Stripe\Customer::create($this->setRecurringRequest());
				/*array("email"=>"qualdev.deepak@gmail.com","plan"=>"1centmonthly","quantity"=>"1450","description"=>"Deepak Monthly","source" => array("object" => $this->source_object,"number" => "4242424242424242"	,"exp_month" => 10, 	"exp_year" => 2016,	"cvc" => 123,"name"=>"Deepak"))*/
		
				$this->setRecurringResponseVariables($resp);
			}
			catch(Exception $e)
			{
			   $this->setRecurringErrorVariables($e);
			}

		   return $this->P_status;
		}

		public function ChangeCreditCard()	
		{	
		try
			{		
			
			\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
			$cu = \Stripe\Customer::retrieve($this->customerID);
			$subscription = $cu->subscriptions->retrieve($this->subscriptionID);
			$subscription->source= array("object"=>"card","name"=>$this->cc_name,"exp_month"=>$this->cc_exp_month,"exp_year"=>$this->cc_exp_year,"number"=>$this->cc_number,"cvc"=>$this->cc_cvv);
			
			$response = $subscription->save();
			$this->setCCChangeResponseVariables($response);
			}
			catch(Exception $e)
			{
				 $this->setCCChangeErrorVariables($e);	
			}
			return $this->P_status;
		}

		private function setCCChangeResponseVariables($successResponse)
		{			
				$this->stripe_response_filtered = array("status"=>$successResponse->status,"subscriptionID"=>$successResponse->id);
				$this->stripe_response_complete=$successResponse;
				$this->P_status=1; 
				return 1;
		}
		private function setCCChangeErrorVariables($errResponse)
		{
			$body = $errResponse->getJsonBody();
			$err  = $body['error'];
			
			 if(!isset($err['code']) && !isset($err['message']))
			{
				$err['code']="000";
				$err['message']="Temporary Error In Processing Credit Card Details Updation";
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
		
		public function setRecurringRequest()
		{
	
			$temp = array(
						"email"=>$this->receipt_email,
						"plan"=>$this->sub_planID,
						"quantity"=>$this->convertDollortoCent($this->amount),
						"description" => $this->txnDescription,
					  		"source" => array(
							"object" => $this->source_object,
			 				"number" => $this->cc_number,
			  				"exp_month" => $this->cc_exp_month, 
							"exp_year" => $this->cc_exp_year,
							"cvc" => $this->cc_cvv,
							"name"=>$this->cc_name)
							);
		
		$this->stripe_request=$temp;
	//	dump($this->stripe_request);
		return $temp;
		}
		
		public function cancelRecurring()
		{ 
			if($this->customerID!='' && $this->subscriptionID!='')
			{
				// Set your secret key: remember to change this to your live secret key in production
				// See your keys here https://dashboard.stripe.com/account/apikeys
				try
				{
					\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
					
					$customer 		= \Stripe\Customer::retrieve($this->customerID);
					$subscription 	= $customer->subscriptions->retrieve($this->subscriptionID);
					$response 		= $subscription->cancel();
										
					$this->setCanceledResponseVariables($response);					
				}
				catch(Exception $e)
				{				
				   $this->setCancelRecurringErrorVariables($e);
				}
				return $this->P_status;
			}
		}
		
		private function setCanceledResponseVariables($successResponse)
		{
			
				$this->stripe_response_filtered = array("status"=>$successResponse->status,"subscriptionID"=>$successResponse->id);
				$this->stripe_response_complete=$successResponse;
				$this->P_status=1; 
				return 1;
		}
		
		private function setCancelRecurringErrorVariables($errResponse)
		{
			$body = $errResponse->getJsonBody();
			
			$err  = $body['error'];
			
			 if(!isset($err['code']) && !isset($err['message']))
			{
				$err['code']="000";
				$err['message']="Temporary Error In Processing Cancel Recurring";
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
		
		private function setRecurringResponseVariables($successResponse)
		{
			$this->stripe_response_filtered=array("subscriptionID"=>$successResponse->subscriptions["data"][0]["id"],
			"subscriptionStartDate"=>$successResponse->subscriptions["data"][0]["current_period_start"],
			"subscriptionEndDate"=>$successResponse->subscriptions["data"][0]["current_period_end"],
			"customerID"=>$successResponse->subscriptions["data"][0]["customer"],
			"planID"=>$successResponse->subscriptions["data"][0]["plan"]["id"],
			"amount"=>$this->convertCenttoDollor($successResponse->subscriptions["data"][0]["plan"]["amount"]*$successResponse->subscriptions["data"][0]["quantity"]),
			"planName"=>$successResponse->subscriptions["data"][0]["plan"]["name"]);
					
			$this->P_status=1; 
		    $this->stripe_response_complete=$successResponse;

			return 1;
			
		}
		private function setRecurringErrorVariables($errResponse)
		{
			$body = $errResponse->getJsonBody();
			$err  = $body['error'];
			
			 if(!isset($err['code']) && !isset($err['message']))
			{
				$err['code']="000";
				$err['message']="Temporary Error In Processing Recurring Payment";
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
		
		public function getWebhookResponse()
		{
			try{
				
				\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
				// Retrieve the request's body and parse it as JSON
				$input = @file_get_contents("php://input");
				$this->setWebhookResponseVariables($input);
			}
			catch(Exception $e)
			{
				$this->setWebhookErrorVariables($e);
			}
		   	return $this->P_status;
		}
		
		private function setWebhookResponseVariables($successResponse)
		{	
			$successResponse = json_decode($successResponse,true);					
				$this->stripe_response_filtered=array("eventID"=>$successResponse['id'],					
				"typeStatus"=>$successResponse['type'],
				"chargeID"=>$successResponse['data']['object']['charge'],
				"subscriptionID"=>$successResponse['data']['object']['subscription'],
				"subscriptionDate"=>$successResponse['data']['object']['date'],
				"customerID"=>$successResponse['data']['object']["customer"],
				"planID"=>$successResponse["data"]['object']['lines']['data'][0]["plan"]["id"],
				"amount"=>$this->convertCenttoDollor($successResponse["data"]['object']['lines']['data'][0]["plan"]["amount"]*$successResponse["data"]['object']['lines']['data'][0]["quantity"]),
				"planName"=>$successResponse["data"]['object']['lines']['data'][0]["plan"]["name"],
				"periodEnd"=>$successResponse["data"]['object']['period_end'],
				"periodStart"=>$successResponse["data"]['object']['period_start']);
				if($successResponse['type']=="customer.subscription.deleted")
					$this->stripe_response_filtered["subscriptionID"] = $successResponse['data']['object']['id'];
				$this->P_status=1; 
				$this->stripe_response_complete=$successResponse;
			
			return 1;
		}
		
		private function setWebhookErrorVariables($errResponse)
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
		
		public function setTransfer()
		{
			try{
				\Stripe\Stripe::setApiKey(STRIPE_PRIVATE_KEY);
				$response = \Stripe\Transfer::create($this->setTransferDetails());
				
				$this->setTransferResponseVariables($response);		
			}
			catch(Exception $e)
			{
				$this->setTransferErrorVariables($e);
			}
		   	return $this->P_status;
		}
	
		private function setTransferDetails()
		{
			
			$arrayTransfer = array("amount"=>$this->convertDollortoCent($this->amount),
									"currency"=>'usd',
									"destination"=>$this->StripeConnectedAccountID,
									"description"=>$this->txnDescription);
			return 	$arrayTransfer;
		}
		
		private function setTransferResponseVariables($successResponse)
		{		
			$this->stripe_response_filtered=array("transID"=>$successResponse->id,
			"status"=>$successResponse->status,
			"amount"=>$successResponse->amount,
			"destination"=>$successResponse->destination,
			"paidDate"=>$successResponse->date,
			"destinationPayment"=>$successResponse->destination_payment);
			
			$this->P_status=1; 
			$this->stripe_response_complete=$successResponse;	
						
			return 1;				
		}
		
		private function setTransferErrorVariables($errResponse)
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
		
}
?>