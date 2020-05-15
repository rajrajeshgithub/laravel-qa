<?php
	class Campaigncomment_Controller extends Controller
	{
		public $tpl,$ComID,$CampID,$InputDataArray;
		public $LoginUserName;
		
		function __construct()
		{
			//checkLogin(12);
			$this->load_model('CampaignComment','objCampComment');
			$this->tpl 			= new view;
			$this->showmsg();
			$this->LoginUserName	= getSession('DonasityAdminLoginDetail','admin_fullname');
		}
		
		public function index($type='list',$CampID=NULL,$ComID=NULL)
		{
			$this->ComID	= ($ComID == NULL)?NULL:keyDecrypt($ComID);
			$this->CampID	= ($CampID == NULL)?NULL:keyDecrypt($CampID);
			switch(strtolower($type))
			{				
				case 'add':
					$this->Add();
					break;
				case 'insert':
					$this->Insert();
					break;
				case 'edit':
					$this->Edit();
					break;	
				case 'update':
				    $this->Update();
					break;	
				case 'delete':
					$this->Delete();
					break;		
				default:
				   	$this->Listing();
				   	break;  
			}
		}
		
		private function Delete()
		{
				if($this->objCampComment->DeleteDB($this->ComID))
				{
					$this->setConfirmationMsg('C17003');
				}
				else
				{
					$this->setErrorMsg('E17004');
				}
				redirect(URL."campaigncomment/index/list/".keyEncrypt($this->CampID));
		}
		
		private function Add()
		{
			$this->CampaignCommentListing();
			//$this->CampaignCommentDetail();
			$this->CampaignDetail();
			$this->tpl->assign('action','insert');
			$this->tpl->assign('campID',$this->CampID);
			$this->tpl->draw("campaigncomment/campaigncomment");	
		}
		
		private function Edit()
		{
			$this->CampaignCommentListing();
			$this->CampaignCommentDetail();
			$this->CampaignDetail();
			$this->tpl->assign('action','update');
			$this->tpl->assign('campID',$this->CampID);
			$this->tpl->draw("campaigncomment/campaigncomment");	
		}
		
		private function Insert()
		{
			$this->InputData();
			$this->ValidateInputData();
			$this->ComID	= $this->objCampComment->InsertDB($this->InputDataArray);
			if($this->ComID > 0)
			{
				$this->setConfirmationMsg('C17001');
				redirect(URL."campaigncomment/index/list/".keyEncrypt($this->CampID));
			}
			else
			{
				$this->setErrorMsg('E17002');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
		
		private function Update()
		{
			$this->InputData();
			if($this->objCampComment->UpdateDB($this->InputDataArray,$this->ComID))
			{
				$this->setConfirmationMsg('C17002');
			}
			else
			{
				$this->setErrorMsg('E17003');	
			}
			redirect($_SERVER['HTTP_REFERER']."#formsectiondiv");
		}
		
		
		private function InputData()
		{
			$this->CampID	= request('post','CampID',1);
			$this->ComID	= request('post','CommentID',1);
			$Comment		= request('post','comment',0);
			$ShowOnWEb		= request('post','showonwebsite',1);	
			$this->InputDataArray	= array(/*'Camp_Cmt_UserName'=>$this->LoginUserName,*/'Camp_Cmt_CampID'=>$this->CampID,'Camp_Cmt_Comment'=>addslashes($Comment),
											'Camp_Cmt_CreatedDate'=>getDateTime(),'Camp_Cmt_LastUpdatedDate'=>getDateTime(),'Camp_Cmt_ShowOnWebsite'=>$ShowOnWEb);
											
			if($this->ComID > 0)
			{
				unset($this->InputDataArray['Camp_Cmt_CreatedDate']);	
			}		
		}
		
		private function ValidateInputData()
		{
			if(trim($this->InputDataArray['Camp_Cmt_Comment']) == "")
			{
				$this->setErrorMsg('E17001');
				redirect($_SERVER['HTTP_REFERER']);	
			}	
		}
		
		private function Listing()
		{
			$this->CampaignCommentListing();
			//$this->CampaignCommentDetail();
			$this->CampaignDetail();
			$this->tpl->assign('action','list');
			$this->tpl->assign('campID',$this->CampID);
			$this->tpl->draw("campaigncomment/campaigncomment");	
		}
		
		private function CampaignCommentListing()
		{
			$DataArray	= array('Camp_Cmt_ID','Camp_Cmt_RUID','Camp_Cmt_UserName','Camp_Cmt_CampID','Camp_Cmt_Comment','Camp_Cmt_CreatedDate','Camp_Cmt_LastUpdatedDate',
								'Camp_Cmt_ShowOnWebsite');
			$Condition	= " WHERE Camp_Cmt_CampID=".$this->CampID;					
			$CommentList	= $this->objCampComment->CampaignCommentListingDB($DataArray,$Condition);
			
			foreach($CommentList as &$val)
			{
				$strlen	= strlen($val['Camp_Cmt_Comment']);
				$val['limitedcomment']	= trim(substr($val['Camp_Cmt_Comment'],0,90));	
				if($strlen > 90)
				{
					$val['limitedcomment']	.= "....&nbsp;<span class='more text-bold link-text cursor-pointer'>Read more</span>";
					$val['Camp_Cmt_Comment']	.= "&nbsp;<span class='less link-text text-bold cursor-pointer'>Less</span>";
				}
			}
			
			$this->tpl->assign('CommentList',$CommentList);
		}
		
		private function CampaignDetail()
		{
			$this->load_model('Campaign','objCamp');
			$DataArray	= array('Camp_ID','Camp_Title');
			//$CampaingDetail	= $this->objCamp->CampaignDetailDB($DataArray,$this->CampID);
			$CampaingDetail='';
			//dump($CampaingDetail);
			$this->tpl->assign('CampaignDetail',$CampaingDetail);
		}
		
		private function CampaignCommentDetail()
		{
			$DataArray	= array('Camp_Cmt_ID','Camp_Cmt_RUID','Camp_Cmt_UserName','Camp_Cmt_CampID','Camp_Cmt_Comment','Camp_Cmt_CreatedDate','Camp_Cmt_LastUpdatedDate',
								'Camp_Cmt_ShowOnWebsite');
			$CommentDetail	= $this->objCampComment->CampaignCommentDetailDB($DataArray,$this->ComID);
			//dump($CommentDetail);
			$this->tpl->assign('CommentDetail',$CommentDetail);
		}
		
		private function setErrorMsg($ErrCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$errParams=array("errCode"=>$ErrCode,
							 "errMsg"=>"Custom Exception message",
							 "errOriginDetails"=>basename(__FILE__),
							 "errSeverity"=>1,
							 "msgDisplay"=>1,
							 "msgType"=>1);
			EnPException::setError($errParams);
		}
		
		private function setConfirmationMsg($ConfirmCode)
		{
			EnPException::writeProcessLog('Events_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$confirmationParams=array("msgCode"=>$ConfirmCode,
									  "msgLog"=>1,									
									  "msgDisplay"=>1,
									  "msgType"=>2);
			$placeholderValues=array("placeValue1");
			EnPException::setConfirmation($confirmationParams, $placeholderValues);
		}
		
		private function showmsg()
		{
			$msgValues=EnPException::getConfirmation(false);			
			$this->tpl->assign('msgValues',$msgValues);
		}
	

	}
?>	