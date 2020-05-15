<?php
	class Templatepage_Controller extends Controller
	{
		public $tpl;
		
		public function __construct()
		{ 
			$this->load_model("Profile","objRegUser");
			$this->P_status=1;
		}
		
		public function index($type='list')
		{
			$this->tpl 			= new view;
			switch(strtolower($type))
			{
				default:
					$this->tpl->draw("templatepage/index");	
			}
		}
		
		
		
	}
?>