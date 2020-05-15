<?php
	class Npolist_Controller extends Controller {
		public $tpl;
		public $filterInput = array();
		
		function __construct() {
			$this->tpl	= new View;
			$this->load_model('NpoList', 'objNpoList');	
			$this->load_model('Common', 'objCommon');	
		}
		
		public function index($type='nposearch',$arrFilters='') {
			switch($type) {
				case 'nposearch':
					$this->listNPO();
				break;
				default:
				break;
			}	
		}	
		
		private function FilterInput() {
			// added htmlpurifier() on 9-02-16 by IGS100
			$Filter	= request("get", "filter", 3);
			$value = trim(htmlpurifier(isset($Filter['keyword']) ? $Filter['keyword'] : ''));
			$value = htmlentities($value, ENT_QUOTES);
			$Filter = array('keyword'=>$value);
			$this->filterInput = $Filter;
		}
		
		private function listNPO() {
			setsession("continue_to_donate_url", $_SERVER['QUERY_STRING'] <> "" ? "/non-profits-search?" . $_SERVER['QUERY_STRING'] : "/campaign/index/campaigncategorylist");
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$pageSelected = ($pageSelected == 0) ? 1 : $pageSelected;
			$this->objNpoList->PageSelected	= $pageSelected;
			$this->FilterInput();
			$DataArray = array("N.NPO_ID", "N.NPO_EIN", "N.NPO_Name", "N.NPO_SubSectionName", "N.NPO_City", "N.NPO_Street", "CR.NPO_CatName", "N.NPO_Zip", "NUR.NPODescription","IF(ISNULL(NUR.ID),N.NPO_SubSectionName,NUR.NPODescription) as Description", "NUR.NPOLogo", "NPO_State", "IF(ISNULL(NUR.ID),0,1) as IsRegistered");
								
			$this->objNpoList->GetNPOList($DataArray, $this->filterInput);
			
			$this->GetNPOCategoryList();
			//================= pagination code start =================
			$Page_totalRecords = $this->objNpoList->recordsCount;
			$PagingArr = constructPaging($pageSelected, $Page_totalRecords, $this->objNpoList->PageLimit);
			$LastPage = ceil($Page_totalRecords / $this->objNpoList->PageLimit);
			
			$this->tpl->assign("pageSelected", $pageSelected);
			$this->tpl->assign("PagingList", $PagingArr['Pages']);
			$this->tpl->assign("PageSelected", $PagingArr['PageSel']);
			$this->tpl->assign("startRecord", $PagingArr['StartPoint']);
			$this->tpl->assign("endRecord", $PagingArr['EndPoint']);
			$this->tpl->assign("totalrecord", $Page_totalRecords);
			$this->tpl->assign("lastPage", $LastPage);
			//================= pagination code end ===================
			
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('NPO_LISTING'));
			
			if(isset($this->filterInput['category']))
				$this->tpl->assign("SearchCategory", $this->filterInput['category']);
			else
				$this->tpl->assign("SearchCategory", '');
					
			$this->tpl->assign("CatArr", array_filter($this->objNpoList->SelectedCategoryArray));
			
			if(isset($this->filterInput['location']))
				$this->tpl->assign("SearchLocation", $this->filterInput['location']);
			else
				$this->tpl->assign("SearchLocation", '');
				
			if(isset($this->filterInput['keyword']))
				$this->tpl->assign("SearchTitle", $this->filterInput['keyword']);
			else	
				$this->tpl->assign("SearchTitle", '');
				
			if(isset($this->filterInput['country']))	
				$this->tpl->assign("SearchCountry", $this->filterInput['country']);
			else
				$this->tpl->assign("SearchCountry", '');
			
			$this->tpl->assign("NPOList", $this->objNpoList->npoListArray);
			$this->tpl->draw("npolist/npolist");	
		}
		
		private function GetNPOCategoryList() {
			$DataArray = array('CR.NPO_CatName as DisplayName', 'CR.NPOCat_ID', 'count(CR.NPOCat_ID) cnt');
			$this->objNpoList->GetNPOCategoryListDB($DataArray);
			$this->tpl->assign("category", $this->objNpoList->npoCatetoryArray);
		}
		
		public function GetLocation() {
			$SearchStr = request('get', 'term', 0);
			$this->objNpoList->GetLocationDB($SearchStr);
			$Array = array();
			if(count($this->objNpoList->autoLocationArray) > 0){
				foreach($this->objNpoList->autoLocationArray as $val) {
					$Array[] = $val['result'];
				}
			}
			echo json_encode($Array);
			exit;	
		}
		
	}
?>