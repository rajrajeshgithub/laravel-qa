<?php
	class Search_Controller extends Controller
	{
		public $tpl;
		public $CampaignCondition="";
		function __construct()
		{
			$this->load_model('Search','objSearch');
			$this->tpl = new view();
		}
		
		public function index($type='nposearch')
		{
			switch(strtolower($type))
			{
				case "nposearch":
					InitMetaDetail_temp($this->tpl,'');
					$this->GetNPOs();
					break;
				case "campaignsearch":
					InitMetaDetail_temp($this->tpl,'');
					$this->GetCampaigns();
					break;
				default:
					InitMetaDetail_temp($this->tpl,'');
					$this->GetNPOs();
					break;		
			}
		}
		
		private function GetNPOs()
		{
			//dump($_REQUEST);
			$Location	= request("get","filter_location",0);
			$Category	= request("get","filter_category",0);
			$Title		= request("get","filter_title",0);
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$pageSelected	= ($pageSelected==0)?1:$pageSelected;
			
			$DataArray	= array("N.NPO_ID","N.NPO_EIN","N.NPO_Name","N.NPO_SubSectionName","N.NPO_City","N.NPO_Street","N.NPO_SubSectionName","N.NPO_Zip");
			$NPOsList	= $this->objSearch->GetNPOsDB($DataArray,$Location,$Category,$Title,$pageSelected);
			foreach($NPOsList as &$val)
			{
				$val['IsRegistered']	= 	($this->IsRegisteredNPO($val['NPO_ID']))?1:0;
			}
			$this->GetCountryList();
			$this->GetNPOCategoryList($Location,$Title);
			
			//================= pagination code start =================
			$Page_totalRecords = $this->objSearch->TotalCountNPO;
			$PagingArr=constructPaging($pageSelected, $Page_totalRecords,$this->objSearch->PageLimit);
			$LastPage = ceil($Page_totalRecords/$this->objSearch->PageLimit);
			
			$this->tpl->assign("pageSelected",$pageSelected);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("totalrecord",$Page_totalRecords);
			$this->tpl->assign("lastPage",$LastPage);
			//================= pagination code end ===================
			
			$this->tpl->assign("SearchCategory",$this->objSearch->SearchCategory);
			$this->tpl->assign("CatArr",array_filter($this->objSearch->CategoryArr));
			$this->tpl->assign("SearchLocation",$Location);
			$this->tpl->assign("SearchTitle",$Title);
			
			$this->tpl->assign("NPOList",$NPOsList);
			$this->tpl->assign("tab",'npos');
			$this->tpl->assign("ActionURL",URL."non-profits-search");
			$this->tpl->draw("search/search");
		}
		
		private function GetCampaigns()
		{
			$Location	= request("get","filter_location",0);
			$Category	= request("get","filter_category",0);
			$Country	= request("get","filter_country",0);
			$Title		= request("get","filter_title",0);
			$pageSelected = (int)request('get', 'pageNumber', 1);
			$pageSelected	= ($pageSelected==0)?1:$pageSelected;
			$this->GetCountryList();
			$this->GetCategoryList();
			
			$DataArray	= array("C.Camp_Title","C.Camp_ShortDescription");
			$CampaignList	= $this->objSearch->GetCampaignsDB($DataArray,$Location,$Category,$Title,$Country,$pageSelected);
			
			//================= pagination code start =================
			$Page_totalRecords = $this->objSearch->TotalCountCampaign;
			$PagingArr=constructPaging($pageSelected, $Page_totalRecords,$this->objSearch->PageLimit);
			$LastPage = ceil($Page_totalRecords/$this->objSearch->PageLimit);
			
			$this->tpl->assign("pageSelected",$pageSelected);
			$this->tpl->assign("PagingList",$PagingArr['Pages']);
			$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
			$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
			$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
			$this->tpl->assign("totalrecord",$Page_totalRecords);
			$this->tpl->assign("lastPage",$LastPage);
			//================= pagination code end ===================
			
			$this->tpl->assign("SearchCategory",$this->objSearch->SearchCategory);
			$this->tpl->assign("CatArr",array_filter($this->objSearch->CategoryArr));
			$this->tpl->assign("SearchLocation",$Location);
			$this->tpl->assign("SearchTitle",$Title);
			$this->tpl->assign("SearchCountry",$Country);
			
			$this->tpl->assign("CampaignList",$CampaignList);
			$this->tpl->assign("tab",'campaign');
			$this->tpl->assign("ActionURL",URL."fundraiser-search");
			$this->tpl->draw("search/search");
		}
		
		private function GetNPOCategoryList($Location,$Title)
		{
			$DataArray	= array('C.'._NPOCat_DisplayName_.' as DisplayName','C.NPOCat_ID','count(N.NPO_ID) AS cnt','NPOCat_URLFriendlyName');
			$Category	= $this->objSearch->GetNPOCategoryListDB($DataArray,$Location,$Title);	
			$this->tpl->assign("category",$Category);
		}
		
		private function GetCategoryList()
		{
			$Condition	= " AND NPOCat_ShowOnWebsite='1' ";
			$Order		= " ORDER BY NPOCat_SortOrder ";
			$DataArray	= array(_NPOCat_DisplayName_.' as DisplayName','NPOCat_CodeName','NPOCat_URLFriendlyName','NPOCat_ID');
			$Category	= $this->objSearch->GetCategoryListDB($DataArray,$Condition,$Order);	
			$this->tpl->assign("category",$Category);
		}
		
		private function GetCountryList()
		{
			$DataArray	= array("Country_Title","Country_Abbrivation");	
			$Condition	= "";
			$Order		= " ORDER BY Country_Title";
			$CountryList	= $this->objSearch->GetCountryListDB($DataArray,$Condition,$Order);
			$this->tpl->assign("CountryList",$CountryList);
		}
		
		public function GetLocation()
		{
			$SearchStr	= request('get','term',0);
			$Res		= $this->objSearch->GetLocationDB($SearchStr);
			$Array		= array();
			foreach($Res as $val)
			{
				$Array[]	= $val['result'];	
			}
			echo json_encode($Array);
			exit;	
		}
		
		public function GetCampaignLocation()
		{
			$SearchStr	= request('get','term',0);
			$Res		= $this->objSearch->GetCampaignLocationDB($SearchStr);
			$Array		= array();
			foreach($Res as $val)
			{
				$Array[]	= $val['result'];	
			}
			echo json_encode($Array);
			exit;	
		}
		
		private function IsRegisteredNPO($NPOID)
		{
			return $this->objSearch->IsRegisteredNPO($NPOID);
		}
		
		
	}
?>