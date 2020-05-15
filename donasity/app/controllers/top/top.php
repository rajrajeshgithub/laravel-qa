<?php
	class Top_Controller extends Controller
	{
		function index()
		{
			
		}
		
		function activeLanguage()
		{
			$this->activeLang = request("post","activeLanguage",0);
			setCookie("activeLanguage",$this->activeLang, time() + (60*60*24),'/');	
			redirect($_SERVER['HTTP_REFERER']);	
			/*redirect(URL.'home');*/	
		}
	}
?>