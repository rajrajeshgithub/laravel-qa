<?php
	class Cms_Controller extends Controller
	{
		public $tpl;
		
		public function __construct()
		{ 
			$this->load_model("Cms","objCMS");
			$this->tpl=new View;
		}
		
		public function index($PageNameURL)
		{
			$PageNameURL	= str_replace('.html','',$PageNameURL);
			if(!$this->objCMS->IsPageExist($PageNameURL))
			{
				redirect(URL."home");	
			}
			$DataArray		= array('CMSPagesID','CMSPageGroupID','internal_url','IsInternalLink','external_url','IsExternalLink','CMSPageHead',
									'CMSPagesName','CMSPagesNameINURL','CMSPagesTitle',_CMSPages_Content_,'Metatitle','Metadesc','Metakeyword',
									'LoginRequired','Status','GoogleAnalyticCode','CreatedBy','Permission');
									
			$StaticPage		= $this->objCMS->GetPageDetails($DataArray,$PageNameURL);
			//dump($StaticPage);
			$this->checkUrl($StaticPage);
			//dump($StaticPage['Content']);
			$getCms			=	EvalToken($StaticPage['Content']);
			
			foreach($getCms as $row)
			{
				
				if($row!='')
				{
					$this->load_model("Common","objCom");				
					$GetCmsLink		=	$this->objCom->GetCMSPageList($row);
					
				}
				//dump($GetCmsLink);
				$str='';
				$str.='<div class="col-md-3 col-sm-3 col-xs-12">';
				foreach($GetCmsLink as $value)
				{
					
					$str.='<a href="'.$value["CMSPagesNameINURL"].'.html" class="link-1" title="'.$value["CMSPagesName"].'">'.$value["CMSPagesName"].'</a>'; 
				}
				$str.="</div>";
				
				$StaticPage['Content']=str_replace("{{".$row."}}",$str,$StaticPage['Content']);	
			}
			//dump($StaticPage['Content']);
			InitMetadetail($this->tpl,'cms');
			$this->tpl->assign("GetCmsLink",$GetCmsLink);
			$this->tpl->assign($StaticPage);
			$this->tpl->draw("cms/index");
		}
		
		private function checkUrl($arrDetail)
		{
			if(count($arrDetail)>0)
			{
				if($arrDetail['IsInternalLink']==1 && $arrDetail['internal_url']!='')
				{
					redirect(URL.$arrDetail['internal_url']);
				}
				elseif($arrDetail['IsExternalLink']==1 && $arrDetail['external_url']!='')
				{
					redirect($arrDetail['external_url']);
				}				
			}
			return true;
		} 
	}
?>