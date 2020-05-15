<?php
	class Usaepay_Controller extends Controller
	{
		function __construct()
		{
			
		}
	
		function index()
		{
			$this->load_model('Usaepay','tran');
			
			$this->tran->amount="9999.99";						// charge amount in dollars
			$this->tran->invoice="12345";   					// invoice number.  must be unique.
			$this->tran->description="Online Order";			// description of charge
			$this->tran->accountnumber="126543456";			// bank account number
			$this->tran->account='345654345';					// bank account number
			$this->tran->routing=592755385;					// bank routing number
			$this->tran->checknum='123';						// Check Number
			$this->tran->accounttype='checking';       		// Checking or Savings
			$this->tran->billfname='Sally';
			$this->tran->billlname='Gura';
		
			


			if($this->tran->Process())
			{
				/*echo '<div class="rcr300"><b>Payment Approved</b><br>';
				echo "<b>Authcode:</b> " . $this->tran->authcode . "<br>";
				echo "<b>RefNum:</b> " . $this->tran->refnum . "<br>";
				echo "<b>Result:</b> " . $this->tran->result . "<br>";
				echo "<b>Result code:</b> " . $this->tran->resultcode . "<br>";
				echo "<b>Conv amt:</b> " . $this->tran->convertedamount . "<br>";
				echo "<b>Auth amt:</b> " . $this->tran->authamount . "<br>";
				echo "<b>Batch:</b> " . $this->tran->batch . "<br>";*/
				echo("<pre>");
				print_r($this->tran->usaepay_response_filtered);
				print_r($this->tran->usaepay_response_complete);
				
			} else {
				echo '<div class="rcr300"><b>Payment Declined</b> (' . $this->tran->result . ')<br>';
				echo "<b>Reason:</b> " . $this->tran->error . "<br>";	
				if(@$this->tran->curlerror) echo "<b>Error 33:</b> " . $this->tran->curlerror . "<br>";	
				echo("<pre>");
				print_r($this->tran->usaepay_response_err);
				print_r($this->tran->usaepay_response_complete);
			}		
			

			exit;
			
		}
	}


?>