<?php
	class Fundraiserlist_Controller extends Controller
	{		
		public $tpl;
		public $filterInput = array();
		
		function __construct() {
			$this->tpl = new View;
			$this->load_model('FundraisersList', 'objFradraisersList');	
			$this->objFradraisersList = new FundraisersList_Model();
			$this->load_model('Common', 'objCommon');
			$this->objCommon = new Common_Model();
		}
		
		public function index($type='fundeaisersearch',$arrFilters='') {
			switch($type) {
				case 'fundeaisersearch':
					$this->listFundariser();
				break;
				default:
				break;
			}
		}
		
		private function FilterInput() {
			$Filter	= request("get", "filter", 3);
			$this->filterInput = $Filter;
		}
		
		public function getFundariserList() {
			$this->load_model('UserType1', 'objutype1');
			$this->load_model('Common', 'objCommon');
			
			$this->LoginUserDetail = getSession('Users');
			$this->objFradraisersList->npoCondition = " WHERE Camp_RUID=" . keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$this->load_model('Fundraisers', 'objFund');
			$this->objFund = new Fundraisers_Model();
			$Wherecondition = " AND Camp_RUID=" . keyDecrypt($this->LoginUserDetail['UserType1']['user_id']);
			$fundraiserlist = $this->objFund->GetFundraiserDetails(array('Camp_ID', 'Camp_Title', 'camp_thumbImage', 'Camp_RUID', 'Camp_Status', 'Camp_Level_ID', 'Camp_UrlFriendlyName', 'Camp_DonationGoal', 'Camp_DonationReceived', 'concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage'), $Wherecondition);
			
			$this->objutype1->GetUserDetails();			
			$this->objut1report->SortOrder = " PDD.PDD_DateTime DESC ";
			/*==== Meta section ===== */
			$this->tpl->assign('arrBottomInfo', $this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray', $this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo', $this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$arrMetaInfo = $this->objCommon->GetPageCMSDetails('UT1_DASHBOARD');
			$UserName = $this->objutype1->UserDetailsArray['RU_FistName']." ".$this->objutype1->UserDetailsArray['RU_LastName'];
			$Address1 = $this->objutype1->UserDetailsArray['RU_Address1']." , ";
			$Address1 .= ($this->objutype1->UserDetailsArray['RU_Address1'] != "") ? $this->objutype1->UserDetailsArray['RU_Address2'].", " : "";
			$Address2 .= $this->objutype1->UserDetailsArray['RU_City'];
			$Address2 .= ($this->objutype1->UserDetailsArray['RU_ZipCode'] != "") ? " - " . $this->objutype1->UserDetailsArray['RU_ZipCode'] : "";
			$Image = CheckImage(UT1PROFILE_MEDIUM_IMAGE_DIR,UT1PROFILE_MEDIUM_IMAGE_URL,NO_PERSON_IMAGE, $this->objutype1->UserDetailsArray['RU_ProfileImage']);
			$arrMetaInfo["userdetails"] = strtr($arrMetaInfo["userdetails"], array('{{UserName}}' =>$UserName, '{{Address1}}' => $Address1, '{{Address2}}' => $Address2, '{{EmailID}}' => $this->objutype1->UserDetailsArray['RU_EmailID'], '{{Image}}'=>$Image));
			$this->tpl->assign($arrMetaInfo);
			/* ======== Meta Section End ========== */			
			$this->tpl->assign("UserDetail", $this->objutype1->UserDetailsArray);
			$this->tpl->assign("fundraiserlistCount", count($fundraiserlist));
			$this->tpl->assign("fundraiserlist", $fundraiserlist);
			$this->tpl->draw("ut1myaccount/fundraiserlist");
		}
		
		private function listFundariser() {			
			setsession("continue_to_donate_url", "/fundraisers-search?".$_SERVER['QUERY_STRING']);
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$pageSelected = ($pageSelected == 0) ? 1 : $pageSelected;
			$this->objFradraisersList->PageSelected	= $pageSelected;
			$this->FilterInput();
			
			$DataArray = array('C.Camp_ID','C.Camp_Title','C.camp_thumbImage','C.Camp_DescriptionHTML','CC.NPOCat_DisplayName_'._DBLANG_.'','CC.NPOCat_URLFriendlyName','C.Camp_CP_City','C.Camp_CP_State','C.Camp_CP_Country','C.Camp_CP_ZipCode','C.Camp_PaymentMode','C.Camp_NPO_EIN');
			$this->objFradraisersList->GetNPOList($DataArray, $this->filterInput);
			$this->GetNPOCategoryList();
			//================= pagination code start =================
			$Page_totalRecords = $this->objFradraisersList->recordsCount;
			$PagingArr = constructPaging($pageSelected, $Page_totalRecords,$this->objFradraisersList->PageLimit);
			$LastPage = ceil($Page_totalRecords / $this->objFradraisersList->PageLimit);
			
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
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('FUNDRAISER_LISTING'));			
			if(isset($this->filterInput['category']))
				$this->tpl->assign("SearchCategory", $this->filterInput['category']);
			else
				$this->tpl->assign("SearchCategory", '');
					
			$this->tpl->assign("CatArr", array_filter($this->objFradraisersList->SelectedCategoryArray));
			
			if(isset($this->filterInput['location']))
				$this->tpl->assign("SearchLocation",$this->filterInput['location']);
			else
				$this->tpl->assign("SearchLocation",'');
				
			if(isset($this->filterInput['keyword']))
				$this->tpl->assign("SearchTitle",$this->filterInput['keyword']);
			else	
				$this->tpl->assign("SearchTitle",'');
				
			if(isset($this->filterInput['country']))	
				$this->tpl->assign("SearchCountry",$this->filterInput['country']);
			else
				$this->tpl->assign("SearchCountry",'');
			
			$this->tpl->assign('Categoryname','NPOCat_DisplayName_'._DBLANG_.'');
			
			$this->tpl->assign("NPOList",$this->objFradraisersList->npoListArray);
			$this->tpl->draw("fundraiserlist/fundraiserlist");	
		}
		
		private function GetNPOCategoryList() {
			$this->objFradraisersList->GetNPOCategoryListDB();
			$this->tpl->assign("category", $this->objFradraisersList->npoCatetoryArray);
		}
		
		public function GetLocation() {
			$SearchStr = request('get', 'term', 0);
			$this->objFradraisersList->GetLocationDB($SearchStr);
			$Array = array();
			if(count($this->objFradraisersList->autoLocationArray) > 0){
				foreach($this->objFradraisersList->autoLocationArray as $val) {
					$Array[] = $val['result'];
				}
			}
			echo json_encode($Array);
			exit;	
		}
	}
?>