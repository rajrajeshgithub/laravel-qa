<?php
	class Contact_Controller extends Controller
	{
		public $tpl;
		
		public function __construct()
		{ 
			$this->load_model("Common","objCom");
			$this->tpl=new View;
		}
		
		public function index()
		{
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCom->GetPageCMSDetails('contact'));
			$this->tpl->draw('contact/index');
		}
		
		 
	}
?>