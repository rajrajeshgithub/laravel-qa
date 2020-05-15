<?php
	class Cms_Model extends Model
	{
		public	$CMSM_CMSPagesNameINURL,
				$CMSM_PageDetail,
				$CMSM_PageList;
		
		
		public function GetPageDetails()
		{
			EnPException::writeProcessLog('CMS_Model :: GetPageDetail Function Call To View All Details for :: ');
			$sql = "SELECT 	CMSPagesID,CMSPageGroupID,internal_url,IsInternalLink,external_url,IsExternalLink,
							CMSPagesName,CMSPagesNameINURL,CMSPagesTitle,Content,Metatitle,Metadesc,
						   	Metakeyword,LoginRequired,Status,GoogleAnalyticCode,CreatedBy,Permission
					FROM ".TBLPREFIX."cmspages WHERE CMSPagesNameINURL = '".$this->CMSM_CMSPagesNameINURL."' AND Status = '1'";
			
			$res = db::get_all($sql);
			if(count($res)>0)
			{
				$this->CMSM_PageDetail = $res;
				$PageDetail = array("LoginRequired"=>$this->CMSM_PageDetail[0]['LoginRequired'],
									"PageTitle"=>$this->CMSM_PageDetail[0]['CMSPagesTitle'],
									"Content"=>$this->CMSM_PageDetail[0]['Content'],
									"MetaTitle"=>$this->CMSM_PageDetail[0]['Metatitle'],
									"MetaDesc"=>$this->CMSM_PageDetail[0]['Metadesc'],
									"MetaKeyword"=>$this->CMSM_PageDetail[0]['Metakeyword']);		
				return $PageDetail;
			}
			else
			{	
				return array();
			}
		}
		
		public function GetCMSPageList()
		{
			
			$sql = "SELECT CP.CMSPagesID,CP.CMSPageGroupID,CP.internal_url,CP.IsInternalLink,CP.external_url,CP.IsExternalLink,
						   CP.CMSPagesName, CP.CMSPagesNameINURL,CP.CMSPagesTitle,CP.Content,CP.Metatitle,CP.Metadesc,
						   CP.Metakeyword,CP.LoginRequired,CP.Status,CP.GoogleAnalyticCode,CP.CreatedBy,CP.Permission,
						   CP.DevelopersNote,CPG.Title
					FROM ".TBLPREFIX."cmspages AS CP INNER JOIN ".TBLPREFIX."cmspagegroup AS CPG ON CP.CMSPageGroupID = CPG.CMSPageGroupID WHERE CPG.Status = '1' AND CP.Status = '1' 
					AND CP.ShowLink = '1' ORDER BY SortBy";
					
			EnPException::writeProcessLog($sql);
			$res = db::get_all($sql);
			//echo '<pre>';print_r($res);exit;
			if(count($res)>0)
			{
				return $res;
			}
			else
			{	return array();	}
		}
		
		
		public function GetPageMetaDetails($PageKeword = '')
		{
			
			$sql = "SELECT PMV.".PMV_id.", PMV.".PMV_metaTitle.", PMV.".PMV_metaDesc.", PMV.".PMV_metaKeyword.", PMV.".PMV_pageKeyword.",PMV.".PMV_pagename.", 
				PMV.".PMV_desc.",PMV.".PMV_desc.",PMT.".PMT_desc.",PMT.".PMT_content." FROM ".TBLPREFIX."pagemetavalue AS PMV 
				LEFT JOIN ".TBLPREFIX."pagemetatext AS PMT ON PMV.".PMV_id." = PMT.".PMT_PMVid."
				WHERE PMV.".PMV_pageKeyword." = '".$PageKeword."'";
			$res = db::get_all($sql);
			$arrPageDetail = array();
			$Language	= currentLanguage();
			if(count($res)>0)
			{
				foreach($res as $key => $arrValue)
				{
					$Serial	= $key+1;
					if($Language == 'es')
					{
						$arrPageDetail['ID'] = $arrValue['PMV_id'];
						$arrPageDetail['MetaTitle'] = $arrValue['PMV_metaTitleES'];
						$arrPageDetail['MetaDesc'] = $arrValue['PMV_metaDescES'];
						$arrPageDetail['MetaKeyword'] = $arrValue['PMV_metaKeywordES'];
						$arrPageDetail['PageKeyword'] = $arrValue['PMV_pageKeyword'];
						$arrPageDetail['PageName'] = $arrValue['PMV_pagenameES'];
						$arrPageDetail['pageDescription'] = $arrValue['PMV_descES'];
						$arrPageDetail['content'.$Serial] = $arrValue['PMT_contentES'];
					}
					else
					{
						$arrPageDetail['ID'] = $arrValue['PMV_id'];
						$arrPageDetail['MetaTitle'] = $arrValue['PMV_metaTitleEN'];
						$arrPageDetail['MetaDesc'] = $arrValue['PMV_metaDescEN'];
						$arrPageDetail['MetaKeyword'] = $arrValue['PMV_metaKeywordEN'];
						$arrPageDetail['PageKeyword'] = $arrValue['PMV_pageKeyword'];
						$arrPageDetail['PageName'] = $arrValue['PMV_pagenameEN'];
						$arrPageDetail['pageDescription'] = $arrValue['PMV_descEN'];
						$arrPageDetail['content'.$Serial] = $arrValue['PMT_contentEN'];
					}
				}
				return $arrPageDetail;
			}
			else
			{	
				return array();
			}
		}		
		
		public function getCmsPagesList($field=array(),$filterparam=array(),$arraySortParam=array())
		{
			EnPException::writeProcessLog(' CMS_Model :: getCmsPagesList Function Call To View List of CMS pages ');					
			$fieldString=implode(' , ',$field);
			$filterString=" Where 1=1 ";
			if(count($filterparam)>0)
			{
				foreach($filterparam as $key => $row)
				{
					$cond="";
					switch($key)
					{
						default:
						$cond="$key=$row";
					}
					$filterString.=" AND ( $cond )  ";
				}
			}
			$Sql="Select $fieldString from ".TBLPREFIX."cmspages cp LEFT JOIN ".TBLPREFIX."cmspagegroup cpg ON(cpg.CMSPageGroupID = cp.CMSPageGroupID)";			
			//echo $Sql.$filterString;exit;
			$sql_res	= db::get_all($Sql.$filterString);
			if(!count($sql_res))
			{
				$sql_res=array();
			}
			else
			{
				//dump($sql_res);
				$sql_res = $this->createPageArray($sql_res);
			}
			return $sql_res;			
		}
		
		private function createPageArray($arrPages)
		{
			foreach($arrPages as $key =>$value)
			{
				$arrPagesList[$value['CMSPageGroupID']][] = $value; 	
			}
			return $arrPagesList;
		}
		
		
		
	}
?>