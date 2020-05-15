<?php
	class Bottom_Controller extends Controller
	{
		function __construct()
		{
			$this->load_model("Cms", "objCms");	
		}
		
		public function index()
		{
			$fields = array("cp.CMSPagesID","cp.CMSPageGroupID","cp.internal_url","cp.IsInternalLink","cp.external_url","cp.IsExternalLink","cp.LoginRequired",
							"cp.CMSPagesName","cp.CMSPagesNameINURL","cp.CMSPagesTitle","cpg.Title","cpg.SortingOrder");
			$fieldString = array("cp.Status"=>"'1'","cp.Language"=>"'".LANG_ID."'","cpg.Status"=>"'1'","cpg.Language"=>"'".LANG_ID."'","cp.ShowLink"=>"'1'");
			$cmsPageList = $this->objCms->getCmsPagesList($fields,$fieldString);
           
			$this->tpl = new View;
			$this->tpl->assign("arrBottomInfo",$cmsPageList);
		    $this->tpl->draw("bottom/bottom");
			
		}
	}
?>