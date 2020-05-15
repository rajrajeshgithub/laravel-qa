<?php
	class Payment_Controller extends Controller
	{
		function __construct()
		{
			
		}	
		
		public function index()
		{
			LoadLib('Config');
			$sale = new AuthorizeNetAIM;
			$sale->amount = rand(1, 10000);
			$sale->card_num = '6011000000000012';
			$sale->exp_date = '04/15';
			$response = $sale->authorizeAndCapture();
        	dump($response);			
			echo $response->response_reason_text;
			
			
			
		}
	}

?>