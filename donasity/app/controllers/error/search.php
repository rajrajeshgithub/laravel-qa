<?php
	class Search_Controller extends Controller
	{		
		public $tpl;
		public $filterInput;
		
		function __construct()
		{
			$this->tpl	= new View;
			$this->load_model('Search','objNpoList');	
			$this->load_model('Common','objCommon');	
		}
		
		public function index($type='nposearch',$arrFilters)
		{
			switch($type)
			{
				case 'nposearch':
					$this->listNPO();
				break;	
				default:
				break;
			}	
		}	
		
		private function FilterInput()
		{
			$Filter		= request("get","filter",3);
			$this->filterInput	= $Filter;
		}		
		
		private function listNPO()
		{
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$pageSelected	= ($pageSelected==0)?1:$pageSelected;
			$this->objNpoList->PageSelected	= $pageSelected;
			$this->FilterInput();
			$DataArray	= array("N.NPO_ID","N.NPO_EIN","N.NPO_Name","N.NPO_SubSectionName","N.NPO_City","N.NPO_Street","CR.NPO_CatName","N.NPO_Zip","NUR.NPODescription",
								"IF(ISNULL(NUR.ID),N.NPO_SubSectionName,NUR.NPODescription) as Description","IF(ISNULL(NUR.ID),0,1) as IsRegistered","NUR.NPOLogo","NPO_State");
								
			$this->objNpoList->GetNPOList($DataArray,$this->filterInput);
			foreach($this->objNpoList->npoListArray as &$val)
			{
				$CategoryName	= explode('||',$val['NPO_CatName']);
				$val['NPO_State']	= $this->objCommon->GetStateName($val['NPO_State']);
				$val['CategoryName']	=   (LANG_ID == 'en')?$CategoryName[0]:$CategoryName[1];
				$DesLen	= strlen($val['Description']);
				if($DesLen > 175)
				{
					$ReadMore	= "<a href='javascript://' class='read-more' title='Click here to read more'>"._READ_MORE_." >></a>  ";
					$val['LimitedDescription']	= 	substr($val['Description'],0,175).$ReadMore;
					$val['NextDescription']		= 	substr($val['Description'],176);
				}
				else
				{
					$val['LimitedDescription']	= 	substr($val['Description'],0,175);
					$val['NextDescription']		= 	"";
				}
			}
			//dump($this->objNpoList->npoListArray);
			$this->GetCountryList();
			$this->GetNPOCategoryList();
			//================= pagination code start =================
			$Page_totalRecords = $this->objNpoList->recordsCount;
			$PagingArr=constructPaging($pageSelected, $Page_totalRecords,$this->objNpoList->PageLimit);
			$LastPage = ceil($Page_totalRecords/$this->objNpoList->PageLimit);
			
			$this->tpl->assign("pageSelected",$pageSelected);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("totalrecord",$Page_totalRecords);
			$this->tpl->assign("lastPage",$LastPage);
			//================= pagination code end ===================
			
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			
			$this->tpl->assign("SearchCategory",$this->filterInput['category']);
			$this->tpl->assign("CatArr",array_filter($this->objNpoList->npoCatetoryArray));
			$this->tpl->assign("SearchLocation",$this->filterInput['location']);
			$this->tpl->assign("SearchTitle",$this->filterInput['keyword']);
			$this->tpl->assign("SearchCountry",$this->filterInput['country']);
			
			
			$this->tpl->assign("NPOList",$this->objNpoList->npoListArray);
			$this->tpl->assign("tab",'npos');
			$this->tpl->assign("ActionURL",URL."non-profits-search");
			$this->tpl->draw("search/search");	
		}
		
		private function GetCountryList()
		{
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objNpoList->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		private function GetNPOCategoryList()
		{
			$Filter		= request("get","filter",3);
			$DataArray	= array('CR.NPO_CatName as DisplayName','CR.NPOCat_ID','count(CR.NPOCat_ID) cnt');
			$Category	= $this->objNpoList->GetNPOCategoryListDB($DataArray,$Filter);
			$Category	= array_filter($Category);
			foreach($Category as $key => &$cat)
			{
				$CategoryDisplayName	= explode("||",$cat['DisplayName']);
				$cat['DisplayName']	= (LANG_ID == "en")?$CategoryDisplayName[0]:$CategoryDisplayName[1];
				if($cat['DisplayName'] == "")
				{
					unset($Category[$key]);	
				}
			}
			$this->tpl->assign("category",$Category);
		}
		
		public function GetLocation()
		{
			$SearchStr	= request('get','term',0);
			$Res		= $this->objNpoList->GetLocationDB($SearchStr);
			$Array		= array();
			foreach($Res as $val)
			{
				$Array[]	= $val['result'];	
			}
			echo json_encode($Array);
			exit;	
		}
		
		
	}
?>