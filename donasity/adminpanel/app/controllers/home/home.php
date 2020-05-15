<?php 
class Home_controller extends Controller
{
	public $AdminFname,$AdminLname,$FullName,$LoginUserType;
	//public $arrParentDetail=array();
	function __construct()
	{
		checkLogin();
		$this->load_model("Npos","objnpos");
		$this->load_model('Common','objCom');	
	}
	
	public function index()
	{
		$this->writeModuleJson();
		$arrModule = userRightModuleArray();
		
		$tpl = new view;
		$tpl->assign('arrModule',$arrModule);
		$tpl->draw('home/home');
	}
	
	function writeModuleJson()
	{
		
		$arrField  = array("Module_ID","Module_ParentID","Module_Style","Module_Desc","Module_Caption","Module_Url");
		$filterWhere = " Where Module_Active='1' ";
		$arrModule = $this->objCom->getModuleList($arrField,$filterWhere);
		WriteModuleArray($arrModule);
	}
	
	private function generateuniquecode($arrData)	
	{
		$State			= substr(trim($arrData['NPO_State']),0,2);		
		$EIN			= substr(trim($arrData['NPO_EIN']),0,2);
		$NPOName		= substr(trim($arrData['NPO_Name']),0,1);//echo "State===".$State." and EIN === ".$EIN." and NPOName ==".$NPOName;exit;
		if($State != NULL && $EIN != NULL && $NPOName != NULL){
			$RandomNumber	=  $this->UniqueRandomNumbersWithinRange(1001,9999,2);
			$UniqueCode		= $State.$EIN."-".$RandomNumber.$NPOName;
			
			/*if($this->objnpos->IsDuplicateCode($UniqueCode))
			{
				$this->generateuniquecode($arrData);
			}
			else
			{
				return $UniqueCode;
			}*/
			return $UniqueCode;
		}
		else
		{
			return "";	
		}
	}
	
		
	function showTpl()
	{
		$tpl = new view;
		$tpl->assign('Date',getDateTime());		
		$tpl->draw('home/tpl');	
	}
	function showProgress()
	{
		ini_set('max_execution_time', 300000);
		$arrRes = $this->objCom->checkUpdate();		
		if(count($arrRes)<1)
		{
			redirect(URL."home/");
		}
		foreach($arrRes as $key => $value)
		{
			$uniqueCode = $this->generateuniquecode($value);
			
			$arrFields = array('NPO_UniqueCode'=>$uniqueCode);	
			
			$this->objCom->updateCheck($arrFields,$value['NPO_ID']);		
		}
		$this->showTpl();
		
	}
	private function UniqueRandomNumbersWithinRange($min, $max, $quantity) 
	{
		$numbers = range($min, $max);
		shuffle($numbers);
		return implode("-",array_slice($numbers, 0, $quantity));
	}
	
	
	function getParentID($arrModule,$ModuleID)
	{
		$this->arrParentDetail=array();
		foreach($arrModule	as $key => $value)
		{	
			if($ModuleID==$value['Module_ID'])
			{
				if($value['Module_ParentID']==0)
				{
					$this->arrParentDetail = $value;
					break;
				}
				else
				{
					$this->getParentID($arrModule,$value['Module_ParentID']);
				}
			}
		}
		return $this->arrParentDetail;
	}
	
}
?>
