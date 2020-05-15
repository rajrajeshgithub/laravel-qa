<?php
	class Left_Controller extends Controller
	{   
		public $tpl;
		
		public function index()
		{
			//checkLogin();			
			$arrModule = userRightModuleArray();
		   	$this->tpl = new View;		 
		   	$this->tpl->assign("title","");
			$this->tpl->assign('arrModule',$arrModule);
		   	$this->tpl->draw("left/left");
		}
		
				
	}
?>