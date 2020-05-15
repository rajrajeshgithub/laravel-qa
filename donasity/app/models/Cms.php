<?php
	class Cms_Model extends Model
	{	
		public function GetPageDetails($CMSPagesNameINURL,$DataArray=array('CMSPagesID','CMSPageGroupID','internal_url','IsInternalLink','external_url','IsExternalLink','CMSPageHead',
									'CMSPagesName','CMSPagesNameINURL','Content as ContentEN','ContentES','Metatitle','Metadesc','Metakeyword','LoginRequired','Status','GoogleAnalyticCode','CreatedBy','Permission'))
		{
			EnPException::writeProcessLog('CMS_Model :: GetPageDetail Function Call To View All Details for :: ');
			$Fields	= implode(',',$DataArray);
			$sql = "SELECT 	$Fields, CMSPagesTitle"._DBLANG_." as CMSPagesTitle FROM ".TBLPREFIX."cmspages 
					WHERE CMSPagesNameINURL = '".$CMSPagesNameINURL."' AND Status = '1'";
			//echo $sql;exit; 
			$res = db::get_row($sql);
			//dump($res);
			//dump(_DBLANG_);
			if(count($res)>0)
			{
				$PageDetail = array("LoginRequired"=>$res['LoginRequired'],
									"PageTitle"=>$res['CMSPagesTitle'],
									"PageName"=>$res['CMSPagesName'],
									"Content"=>$res["Content"._DBLANG_],
									"CMSPageHead"=>$res['CMSPageHead'],
									"MetaTitle"=>$res['Metatitle'],
									"MetaDesc"=>$res['Metadesc'],
									"MetaKeyword"=>$res['Metakeyword'],
									"IsInternalLink"=>$res['IsInternalLink'],
									"internal_url"=>$res['internal_url'],
									"external_url"=>$res['external_url'],
									"IsExternalLink"=>$res['IsExternalLink']);		
				//dump($PageDetail);					
				return $PageDetail;
			}
			else
			{	
				return array();
			}
		}
		
		function IsPageExist($pageUrlTitle)
		{
			$Sql="SELECT CMSPagesNameINURL FROM ".TBLPREFIX."cmspages WHERE CMSPagesNameINURL='".$pageUrlTitle."'";
			$sql_res	=	db::get_all($Sql);
			return count($sql_res) > 0 ?true:false;	
		}
	
		public function GetPageMetaDetails($DataArray,$PageKeword = '')
		{
			$Fields	= implode(',',$DataArray);
			$sql = "SELECT $Fields FROM ".TBLPREFIX."pagemetavalue AS PMV 
					LEFT JOIN ".TBLPREFIX."pagemetatext AS PMT ON PMV.PMV_id = PMT.PMT_PMVid
					WHERE PMV.PMV_pageKeyword = '".$PageKeword."'";
			 		
			$res = db::get_all($sql);
			
			$arrPageDetail = array();
			if(count($res)>0)
			{
				foreach($res as $key => $arrValue)
				{
					$Serial	= $key+1;
					$arrPageDetail['MetaTitle']			= $arrValue["PMV_metaTitle"._DBLANG_];
					$arrPageDetail['MetaDesc']			= $arrValue["PMV_metaDesc"._DBLANG_];
					$arrPageDetail['MetaKeyword']		= $arrValue["PMV_metaKeyword"._DBLANG_];
					$arrPageDetail['PageKeyword']		= $arrValue["PMV_pageKeyword"];
					$arrPageDetail['PageName'] 			= $arrValue["PMV_pagename"._DBLANG_];
					$arrPageDetail['pageDescription']	= $arrValue["PMV_desc"._DBLANG_];
					$arrPageDetail['content'.$Serial] 	= $arrValue["PMT_content"._DBLANG_];
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
			$orderBy = " order by SortBy";
			$Sql="Select $fieldString from ".TBLPREFIX."cmspages cp LEFT JOIN ".TBLPREFIX."cmspagegroup cpg ON(cpg.CMSPageGroupID = cp.CMSPageGroupID)";			
			//echo $Sql.$filterString.$orderBy;exit;
			$sql_res	= db::get_all($Sql.$filterString.$orderBy);
			if(!count($sql_res))
			{
				$sql_res=array();
			}
			else
			{
				return $sql_res;
				//dump($sql_res);
				//$sql_res = $this->createPageArray($sql_res);
				
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