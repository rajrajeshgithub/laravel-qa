<?php
	class Cms_Controller extends Controller
	{
		public $tpl;
		
		public function __construct()
		{ 
			$this->load_model("Cms","objCMS");
			$this->load_model("Common","objCom");
			$this->tpl=new View;
		}
		
		public function index($PageNameURL)
		{
			$PageNameURL	= str_replace('.html','',$PageNameURL);
			if(!$this->objCMS->IsPageExist($PageNameURL))
			{
				redirect(URL."home");	
			}
			$StaticPage		= $this->objCMS->GetPageDetails($PageNameURL);
			
			
			$this->checkUrl($StaticPage);
			
			$getCms			=	EvalToken($StaticPage['Content']);
			
			if($getCms!='')
			{
				foreach($getCms as $row)
				{
					
					if($row!='')
					{
						$GetCmsLink		=	$this->objCom->GetCMSPageList($row);
						
					}
					$str='';
					$str.='<div class="col-md-3 col-sm-3 col-xs-12">';
					foreach($GetCmsLink as $value)
					{
						$str.='<a href="'.$value["CMSPagesNameINURL"].'.html" class="link-1" title="'.$value["CMSPagesName"].'">'.$value["CMSPagesName"].'</a>'; 
					}
					$str.="</div>";
					
					$StaticPage['Content']=str_replace("{{".$row."}}",$str,$StaticPage['Content']);	
				}
				$this->tpl->assign("GetCmsLink",$GetCmsLink);
			}
			
			
			$StaticPage["Content"] = strtr($StaticPage["Content"], array('{$Resources}' =>URL));
			$StaticPage["CMSPageHead"] = strtr($StaticPage["CMSPageHead"], array('{$Resources}' =>URL));
			
			$this->tpl->assign('arrBottomInfo',$this->objCom->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCom->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCom->GetPageCMSDetails(BOTTOM_META));
			
			
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