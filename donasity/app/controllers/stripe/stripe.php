<?php
	class Stripe_Controller extends Controller
	{
		function __construct()
		{
			
		}
	
		function index()
		{
			$this->load_model('Stripe','ObjS');
			$this->ObjS->amount=1000;
			$this->ObjS->transactionFee=49.5;
			$this->ObjS->cc_number="4242424242424242";
			$this->ObjS->cc_cvv="001";
			$this->ObjS->cc_exp_month="08";
			$this->ObjS->cc_exp_year="2016";
			$this->ObjS->cc_name="Deepak Sharma";
			$this->ObjS->invoice="D100001";
			$this->ObjS->receipt_email="qualdev.deepak@gmail.com";
			$this->ObjS->StripeConnectedAccountID="acct_16anboLBcDt59kyc";
			$this->ObjS->txnDescription="[NPO][EIN]";

			if($this->ObjS->chargeCredit())
			print_r($this->ObjS->stripe_response_filtered);
			else
			print_r($this->ObjS->stripe_response_err);
			

			exit;
		}
	}


?>