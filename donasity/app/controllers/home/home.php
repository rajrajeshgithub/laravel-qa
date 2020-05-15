<?php
//require_once(LIBRARY_DIR.'facebook/autoload.php');
	class Home_Controller extends Controller
	{	
		public $tpl, $arr_pageMetaInfo;
		public $TagsCondition = '';
		
		function __construct()
		{	
			$this->tpl = new View;
			//$Meta	= InitMetaDetail($this->tpl,'English_Page_Name');
		}
		
		function index($name='Home Page')
		{
			EnPException::writeProcessLog('Home_Controller :: Index Function Call To View All Records');
			$this->load_model('Fundraisers', 'objFund');
			$this->objFund = new Fundraisers_Model();			
			
			$Wherecondition = "Popular";
			$PopularFundsRows=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage')," AND Camp_Tags LIKE '%".$Wherecondition."%' AND Camp_Status='15' AND Camp_IsPrivate!='1' AND Camp_StartDate<='".getDateTime()."' AND Camp_EndDate>='".getDateTime()."' ORDER BY Camp_WeightPOP DESC");
			
			$NewFundsRows=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage')," AND Camp_Tags='' AND Camp_Status='15' AND Camp_IsPrivate!='1' AND Camp_StartDate<='".getDateTime()."' AND Camp_EndDate>='".getDateTime()."' ORDER BY Camp_StartDate DESC LIMIT 7");			
					
            $Wherecondition = "Spotlight";
			$EndingFundsRows=$this->objFund->GetFundraiserDetails(array('Camp_ID','Camp_Title','camp_thumbImage','Camp_RUID','Camp_Status','Camp_Level_ID','Camp_UrlFriendlyName','Camp_DonationGoal','Camp_DonationReceived','concat(round(( Camp_DonationReceived/Camp_DonationGoal * 100 ),0),"%") AS Donationpercentage')," AND Camp_Tags LIKE '%".$Wherecondition."%' AND Camp_Status='15' AND Camp_IsPrivate!='1' AND Camp_StartDate<='".getDateTime()."' AND Camp_EndDate>='".getDateTime()."' ORDER BY Camp_WeightSPOT DESC");
			
			$this->GetCategory();
			$this->load_model('Common','objCommon');
			$this->tpl->assign('arrBottomInfo',$this->objCommon->GetCMSPageList(BOTTOM_URL_NAVIGATION));
			$this->tpl->assign('MenuArray',$this->objCommon->getTopNavigationArray(LANG_ID));
			$this->tpl->assign('arrBottomMetaInfo',$this->objCommon->GetPageCMSDetails(BOTTOM_META));
			$this->tpl->assign('PopularFundsRows', $PopularFundsRows);
			$this->tpl->assign('NewFundsRows', $NewFundsRows);
			$this->tpl->assign('EndingFundsRows', $EndingFundsRows);
			$this->tpl->assign($this->objCommon->GetPageCMSDetails('home'));
			$this->tpl->draw('home/index');
		}
		
		private function GetCategory() {
			$this->load_model('Campaign', 'objCamp');
			$DataArray = array(
				"NPOCat_ID",
				"NPOCat_ParentID",
				"NPOCat_DisplayName_EN",
				"NPOCat_DisplayName_ES",
				"NPOCat_Image_Name",
				"NPOCat_CodeName",
				"NPOCat_URLFriendlyName",
				"NPOCat_SortOrder",
				"NPOCat_ShowOnWebsite");
			$Condition = " AND NPOCat_ShowOnWebsite='1'";
			$Category = $this->objCamp->GetCampaignCategory($DataArray, $Condition);
			
			foreach($Category as &$val)
			{
				$val['CampCat_DisplayName']	= $val["NPOCat_DisplayName_"._DBLANG_];	
				unset($val["NPOCat_DisplayName_" . _DBLANG_]);
			}
			$this->tpl->assign("Category", $Category);
		}
		
		function facebookLogin()
		{
			//$objFb = new FacebookRedirectLoginHelper();
			//echo $objFb;exit;	
		}
		
		function generatealphanumeric()
		{
			//================start=================================================
			ini_set('max_execution_time', 3000);
			$TempArr	= array();
			for($i=1;$i<=500000; $i++)
			{
				$TempArr[]	= GenerateUniqueAlphaNumeric(15);
			}
			echo "Total count".count($TempArr);echo "<br/>";
			echo "Total unique count".count(array_unique($TempArr));echo "<br/>";
			//================end=================================================
		}
	}
?>