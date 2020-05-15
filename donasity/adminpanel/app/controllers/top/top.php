<?php
	class Top_Controller extends Controller
	{   
		public $topArr=array(),$topSubArr=array(),$controllerName,$actionName,$LoginUserType;
		
		public $loginDetail = array();
		
		public function index() {
			$this->loginDetail = getsession("DonasityAdminLoginDetail");
			$this->tpl = new View;		 
			$this->getMenu();
		   	$date = getDateTime();
		   	$this->tpl->assign('loginDetail', $this->loginDetail);
			$this->tpl->assign('Date', $date);	   
		   	$this->tpl->draw("top/top");
		}
		
		private function getMenu() {
			$arrModule = userRightModuleArray();
		   	$this->tpl->assign("title", "");
			$this->tpl->assign('arrModule', $arrModule);
		}
	}
?>