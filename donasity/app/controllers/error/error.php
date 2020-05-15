<?php 
	class Error_Controller extends Controller
    {
    	function __construct()
		{
			$this->tpl = new view;
			$this->load_model("Common","objCommon");	
			
		}
		
		public function index()
		{
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('error'));
			$this->tpl->draw("error/error");
		}
		
		public function restricted()
		{
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('restricted'));
			$this->tpl->draw("error/restricted");	
		}		
   	}
?>