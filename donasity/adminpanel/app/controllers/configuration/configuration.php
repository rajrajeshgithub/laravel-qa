<?php
	class Configuration_Controller extends Controller
	{
		public $tpl,$ConfigID; 	
		
		function __construct()
		{
			checkLogin(6);
			$this->load_model('Configuration','objConfig');
		}
		
		public function index($type='list',$ConfigID=NULL)
		{
			$this->ConfigID	= keyDecrypt($ConfigID);
			$this->tpl = new View;
			switch(strtolower($type))
			{
				case 'edit':
					$this->edit();
					$this->tpl->draw('configuration/showEditForm');
				break;
				case 'update':
					$this->update();
				break;
				default:
					$this->showList();
					$this->tpl->draw('configuration/showList');
				break;
			}			
		}
		
		private function processFilters()
		{
			//dump($_REQUEST);	
			$keyword 		= request('post','searchKeyword',0);
			$searchField	= request('post','searchFields',0);
			$condition = "";
			if($keyword!='')
			{
				if($searchField=='code')
				{
					$condition .= "AND ConfigCode LIKE '%".$keyword."%'";			
				}
				else
				{
					$condition .= "AND ConfigValue LIKE '%".$keyword."%'";	
				}
			}
			$this->tpl->assign("keyword",$keyword);
			$this->tpl->assign("searchFields",$searchField);
			return $condition;
		}
		
		public function showList()
		{
			$Condition  = $this->processFilters();
			$Array	= array('ConfigID','ConfigCode','ConfigValue');
			$Condition	.= " AND IsEditable='1'";
			$arrConfigList = $this->objConfig->getConfigDetailDB($Array,$Condition);			
			//dump($arrConfigList);
			$recordCount = $this->objConfig->totalRowCount;
			$this->tpl->assign('arrConfigList',$arrConfigList);			
			$this->tpl->assign('totalRecords',$recordCount);
		}
		
		public function edit()
		{
			//dump($msgValues);
			$Array	= array('ConfigID','ConfigCode','ConfigValue');
			$Condition	= " AND ConfigID=".$this->ConfigID;
			$arrConfigDetail = $this->objConfig->getConfigDetailDB($Array,$Condition);
			$this->tpl->assign('arrConfigDetail',$arrConfigDetail);
			
		}
		
		public function update()
		{	
			$DataArray	= $this->InputData();
			$this->objConfig->ConfigID	= $this->ConfigID;
			try{
				$this->objConfig->updateDB($DataArray);
			}catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}	
			$pStat=EnPException::$EnP_processStatus;
			if($this->objConfig->P_Status=='1' && $pStat=='1') 
			{
				$this->setConfirmationMsg($this->objConfig->ConfirmCode);				
			} else {
				$this->setErrorMsg($this->objConfig->ErrorCode);				
			}	
			
			redirect(URL."configuration/index/edit/".keyEncrypt($this->ConfigID));	
		}
		
		public function InputData()
		{
			$this->ConfigID		= request('post','configid',1);
			$this->ConfigCode	= request('post','configCode',0);
			$this->ConfigValue	= request('post','configValue',0);							
			
			$DataArray	= array('ConfigCode'=>$this->ConfigCode,
							'ConfigValue'=>$this->ConfigValue);
			return 	$DataArray;
		}
		
		private function setErrorMsg($ErrCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$errParams=array("errCode"=>$ErrCode,
							 "errMsg"=>"Custom Exception message",
							 "errOriginDetails"=>basename(__FILE__),
							 "errSeverity"=>1,
							 "msgDisplay"=>1,
							 "msgType"=>$this->objConfig->MsgType);
			EnPException::setError($errParams);
		}
		
		private function setConfirmationMsg($ConfirmCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$confirmationParams=array("msgCode"=>$ConfirmCode,
										 "msgLog"=>1,									
										 "msgDisplay"=>1,
										 "msgType"=>$this->objConfig->MsgType);
			$placeholderValues=array("placeValue1");
			EnPException::setConfirmation($confirmationParams, $placeholderValues);
		}
	}
?>