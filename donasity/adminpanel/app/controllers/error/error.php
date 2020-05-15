<?php 
	class Error_Controller extends Controller
    {
    	function __construct()
		{
			$this->load_model("Common","objCommon");	
			$msgValues=EnPException::getConfirmation();
		}
		
		public function index()
		{
			$tpl = new view;
			$tpl->draw("error/error");
		}
		
		public function restricted()
		{
			$tpl = new view;
			$tpl->draw("error/restricted");	
		}		
   	}
?>