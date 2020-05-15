<?php
	class Campaignvideo_Controller extends Controller
	{
		public $CV_ID, $CV_CID, $filterParam, $Action, $Sorting, $Status,$FieldArr, $sortParam, $CV_embedCode, $CV_FileArray, $Hdn_Camp_Video_File;
		public $loginDetails;
		public $pageSelectedPage, $totalRecord, $pageLimit;
		public $P_ErrorCode, $P_status, $P_ErrorMessage, $P_ConfirmCode, $P_ConfirmMsg, $MsgType, $VideoExt;
		
		function __construct() {
			$this->load_model('CompaignVideo', 'objCVDetails');
			$this->LastUpdateBy = getsession("DonasityAdminLoginDetail","admin_fullname");
			$this->P_status = 1;
			$this->CV_File = NULL;
			$this->CV_FileArray = array();
			$this->VideoExt = array('mp4', 'flv', 'f4v', 'ogv', 'avi', 'wmv', 'mpg');
			$this->loginDetails = getsession("DonasityAdminLoginDetail");	
		}
		
		public function index($type='list-videos', $CV_CID=NULL, $CV_ID=NULL) { 
			if(isset($CV_ID) && $CV_ID <> NULL) {
				$this->CV_ID = keyDecrypt($CV_ID);
			}
			if(isset($CV_CID) && $CV_CID<>NULL) {
				$this->CV_CID = keyDecrypt($CV_CID);
			}
			
			$this->tpl = new view;
			switch(strtolower($type)) {
				case 'add-video':
					$this->Insert();
					break;
				case 'edit-video':
					$this->Listing();
					$this->Edit();
					$this->tpl->draw('campaignvideo/listcampaignvideo');
					break;
				case 'update-video':
					$this->Update();
					break;
				case 'list-videos':
					$this->Listing();
					$this->tpl->draw('campaignvideo/listcampaignvideo');
				break;
				case 'delete-video':
					$this->Delete();
					break;
			}
		}
		
		private function Delete() {
			
			if(!is_numeric($this->CV_ID) && $this->CV_ID <= 0) {
				$this->SetStatus(false, 'E18010');
				redirect(URL . "home/");
			}
			
			$DataArray = array("Camp_Video_File");
			$this->filterParam	= array("Camp_Video_ID"=>$this->CV_ID);
				
			$CV_FileName = $this->objCVDetails->GetFileName_DB($DataArray, $this->filterParam);
			$FilePath = CAMPAIGN_VIDEO_DIR.$CV_FileName['Camp_Video_File'];
			/*----update process log------*/
			$userType 	= 'ADMIN';					
			$userID		= $this->loginDetails['admin_id'];
			$userName	= $this->loginDetails['admin_fullname'];
			$sMessage = "Error in video deletion";
			$lMessage = "Error in video deletion";			
				
			if($this->objCVDetails->DeleteVideo_DB($this->CV_ID)) 			{
				if(file_exists($FilePath))
					unlink($FilePath);
					
				$this->SetStatus(true,'C18003');
				$sMessage = "Fundraiser video deleted";
				$lMessage = "Fundraiser video deleted with Id - ".$this->CV_ID;
			}else{ 
				$this->SetStatus(false,'E18006');
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->CV_CID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objCVDetails));	
				$this->objCVDetails->updateProcessLog($DataArray);	
			/*-----------------------------*/	
			redirect(URL . "campaignvideo/index/list-videos/" . keyEncrypt($this->CV_CID));
		}
		
		private function Listing() {	
			EnPException::writeProcessLog('CampaignVideo_Controller :: Listing action to view all Video Details');
			
			if($this->CV_CID != NULL) {
				if(!is_numeric($this->CV_CID)) {
					$this->SetStatus(false, 'E2001');
					redirect(URL . "home/");
				}
				
				$DataArray = array(
					"SQL_CACHE Camp_Video_ID as ID",
					"Camp_Video_CampID",
					"Camp_Video_Title",
					"Camp_Video_EmbedCode",
					"Camp_Video_File",
					"Camp_Video_SortOrder",
					"Camp_Video_ShowOnWebsite");
					
				$this->filterParam = array("Camp_Video_CampID"=>$this->CV_CID);		
				
				$CVDList = $this->objCVDetails->GetCompaignVideoListing($DataArray, $this->filterParam, $this->sortParam);
				
				foreach($CVDList as &$row) {
					if($row['Camp_Video_EmbedCode'] != '') {
						preg_match('/src="([^"]+)"/', $row['Camp_Video_EmbedCode'], $match);
						$matchEmebed = explode('/embed', $match[1]);
						$Url = $matchEmebed[0].$matchEmebed[1];
						$Url = str_replace('www.youtube.com/', 'youtu.be/',$Url);
						$EmbedImg = $this->ImgYoutube($Url);
					} else {
						$EmbedImg = NULL;
						$match[1] = '';
					}
					
					if($EmbedImg != NULL) {
						$row['Camp_Video_EmbedCode'] = $match[1];
						$row['EmbedCodeImg'] = $EmbedImg;
					} else {
						$row['Camp_Video_EmbedCode'] = $row['Camp_Video_EmbedCode'];
						$row['HEmbedCode'] = $match[1];
					}
				}
				
				/*foreach($CVDList as &$row) {
					preg_match('/src="([^"]+)"/', $row['Camp_Video_EmbedCode'], $match);
					$matchEmebed = explode('/embed', $match[1]);
					$Url = $matchEmebed[0].$matchEmebed[1];
					$Url = str_replace('www.youtube.com/', 'youtu.be/',$Url);
					$EmbedImg = $this->ImgYoutube($Url);
					
					if($EmbedImg != NULL) {
						$row['Camp_Video_EmbedCode'] = $match[1];
						$row['EmbedCodeImg'] = $EmbedImg;
					} else {
						$row['Camp_Video_EmbedCode'] = $row['Camp_Video_EmbedCode'];
						$row['HEmbedCode'] = $match[1];
					}
				}*/
				
				$PagingArr = constructPaging($this->objCVDetails->pageSelectedPage, $this->objCVDetails->totalRecord, $this->objCVDetails->pageLimit);		
				$LastPage = ceil($this->objCVDetails->totalRecord / $this->objCVDetails->pageLimit);
				
				$this->tpl->assign("totalRecords", $this->objCVDetails->totalRecord);
				$this->tpl->assign("CVList", $CVDList);
				$this->tpl->assign("action", "add-video");
				$this->tpl->assign("PagingList", $PagingArr['Pages']);
				$this->tpl->assign("PageSelected", $PagingArr['PageSel']);
				$this->tpl->assign("startRecord", $PagingArr['StartPoint']);
				$this->tpl->assign("endRecord", $PagingArr['EndPoint']);
				$this->tpl->assign("lastPage", $LastPage);
				$this->tpl->assign("CV_CID", $this->CV_CID);
			} else 
				redirect(URL . "home/");
		}
		
		
		/*private function video_image($url)
		{
			$image_url = parse_url($url);
			
			if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
				$array = explode("&", $image_url['query']);
				return "http://img.youtube.com/vi/".substr($array[0], 2)."/0.jpg";
			} else if($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
				
				$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($image_url['path'], 1).".php"));
				return $hash[0]["thumbnail_small"];
			}
		}*/
		
		
		private function ImgYoutube($url) {
			if(!filter_var($url, FILTER_VALIDATE_URL)) {
				// URL is Not valid
				return false;
			}
			$domain = parse_url($url, PHP_URL_HOST);
			
			/*if($domain=='www.youtube.com' OR $domain=='youtube.com') // http://www.youtube.com/watch?v=t7rtVX0bcj8&feature=topvideos_film
			{
				
				if($querystring=parse_url($url,PHP_URL_QUERY))
				{
					parse_str($querystring);
					if(!empty($v)) return "http://img.youtube.com/vi/$v/0.jpg";
					else return false;
				}
				else return false;
			 
			}*/
			
			if($domain == 'youtu.be') // something like http://youtu.be/t7rtVX0bcj8
			{
				$v= str_replace('/','', parse_url($url, PHP_URL_PATH));
				return (empty($v)) ? false : "http://img.youtube.com/vi/$v/0.jpg" ;
			}
			elseif($domain == 'www.youtube.com') // something like http://youtu.be/t7rtVX0bcj8
			{
				
				$v= str_replace('/','', parse_url($url, PHP_URL_PATH));
				return (empty($v)) ? false : "http://img.youtube.com/vi/$v/0.jpg" ;
			}
			else if($domain == 'www.vimeo.com' || $domain == 'vimeo.com' || $domain == 'player.vimeo.com')
			{
				$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($domain, 1).".php"));
				return $hash[0]["thumbnail_small"];
			}
			else	 
				return false;
		}
		
		private function Edit() {
			if($this->CV_CID > 0) {
				if($this->CV_ID > 0) {
					if(!is_numeric($this->CV_ID) || !is_numeric($this->CV_CID)) {
						$this->SetStatus(false, 'E2001');
						redirect(URL . "home/");
					}
					
					$DataArray = array(
						"SQL_CACHE Camp_Video_ID as ID",
						"Camp_Video_CampID",
						"Camp_Video_Title",
						"Camp_Video_EmbedCode",
						"Camp_Video_File",
						"Camp_Video_SortOrder",
						"Camp_Video_ShowOnWebsite");
						
					$this->filterParam = array("Camp_Video_CampID"=>$this->CV_CID, "Camp_Video_ID"=>$this->CV_ID);
					
					$VideoDetail = $this->objCVDetails->GetCompaignVideoListing($DataArray, $this->filterParam);
					
					$this->tpl->assign("action", 'update-video');
					$this->tpl->assign("CV_ID", $this->CV_ID);
					$this->tpl->assign("VideoDetail", $VideoDetail[0]);
				} else {
					redirect(URL . "campaignvideo/index/list-videos/" . keyEncrypt($this->CV_CID));
				}
			} else 
				redirect(URL."campaign");
		}
		
		private function Insert() {
			$this->getFormData();
			$this->ValidateFormData();
			if( $this->P_status == 0 ) { 
				$this->SetStatus(false, $this->P_ErrorCode );
				redirect(URL."compaignvideo");
			}
			
			$InsertVideoId = $this->objCVDetails->CVideoInsert_DB($this->FieldArr);
			
			if($InsertVideoId > 0) {
				if($this->CV_FileArray['tmp_name'] != '' && $this->CV_FileArray['error'] == 0) {
					$Ext = pathinfo($this->CV_FileArray['name'], PATHINFO_EXTENSION);
					$FileName = $InsertVideoId . '.' . $Ext;
					move_uploaded_file($this->CV_FileArray['tmp_name'], CAMPAIGN_VIDEO_DIR . $FileName);
					
					$this->FieldArr = array('Camp_Video_File'=>$FileName);
					$UpdateVideoId = $this->objCVDetails->UpdateCVideoInsert($this->FieldArr, $InsertVideoId);
					/*----update process log------*/
					$userType 	= 'ADMIN';					
					$userID		= $this->loginDetails['admin_id'];
					$userName	= $this->loginDetails['admin_fullname'];
					$sMessage = "Error in video uploading";
					$lMessage = "Error in video uploading";
					if($UpdateVideoId)
					{
						$sMessage = "Fundraiser video uploaded";
						$lMessage = "Fundraiser video uploaded with Id - ".$InsertVideoId;
					}
						$DataArray = array(	"UType"=>$userType,
											"UID"=>$userID,
											"UName"=>$userName,
											"RecordId"=>$InsertVideoId,
											"SMessage"=>$sMessage,
											"LMessage"=>$lMessage,
											"Date"=>getDateTime(),
											"Controller"=>get_class()."-".__FUNCTION__,
											"Model"=>get_class($this->objCVDetails));	
						$this->objCVDetails->updateProcessLog($DataArray);	
					/*-----------------------------*/	
					if(!$UpdateVideoId){
						$this->SetStatus(false, 'E18001');
						redirect(URL."campaignvideo/index/list-videos/".keyEncrypt($this->CV_CID));
					}
				}
				
				$this->SetStatus(true, 'C18001');
				redirect(URL."campaignvideo/index/edit-video/".keyEncrypt($this->CV_CID)."/".keyEncrypt($InsertVideoId));
				
			} else {
				$this->SetStatus(false, 'E18001');
				redirect(URL."campaignvideo/index/list-videos/".keyEncrypt($this->CV_CID));	
			}
		}
		
		private function Update() {
			EnPException::writeProcessLog('Campaignvideo_Controller :: Update action to Update compain video details');
			try
			{	
				$this->getFormData();
				$this->ValidateFormData();
				
				if($this->P_status == 0) {
					$this->SetStatus(false, $this->P_ErrorCode);
					redirect(URL . "compaignvideo/index/edit-videos/" . keyEncrypt($this->CV_ID));
				}
				
				// upload video on update video data
				if($this->CV_FileArray['error'] == 0 && $this->CV_ID > 0){
					if($this->CV_FileArray['tmp_name'] != NULL && $this->CV_FileArray['error'] == 0) {
						$Ext = pathinfo($this->CV_FileArray['name'], PATHINFO_EXTENSION);
						$FileName = $this->CV_ID . '.' . $Ext;
						move_uploaded_file($this->CV_FileArray['tmp_name'], CAMPAIGN_VIDEO_DIR . $FileName);
						
						$this->FieldArr['Camp_Video_File'] = $FileName;
						
					} else {
						$this->SetStatus(false, 'E18007');
						redirect(URL."campaignvideo/index/list-videos/".keyEncrypt($this->CV_CID));
					}
				}
			  	
				$Status	= $this->objCVDetails->UpdateCVideo_DB($this->FieldArr, $this->CV_ID);
				/*----update process log------*/
				$userType 	= 'ADMIN';					
				$userID		= $this->loginDetails['admin_id'];
				$userName	= $this->loginDetails['admin_fullname'];
				$sMessage = "Error in video uploading";
				$lMessage = "Error in video uploading";
				if($Status)
				{
					$sMessage = "Fundraiser video details updated";
					$lMessage = "Fundraiser video details updated with Id - ".$this->CV_ID;
				}
					$DataArray = array(	"UType"=>$userType,
										"UID"=>$userID,
										"UName"=>$userName,
										"RecordId"=>$this->CV_ID,
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objCVDetails));	
					$this->objCVDetails->updateProcessLog($DataArray);	
					/*-----------------------------*/	
					
					$Status ? $this->SetStatus(true, 'C18002') : $this->SetStatus(false, 'E18002');
			}
			catch(Exception $e) {
				EnPException::exceptionHandler($e);	
			}
			
			redirect(URL."campaignvideo/index/edit-video/".keyEncrypt($this->CV_CID).'/'.keyEncrypt($this->CV_ID));
		}
		
		
		private function getFormData() {
			EnPException::writeProcessLog('Campaignvideo_Controller :: getFormData action to get all data');
			
			$this->CV_ID		= request('post', 'CV_ID', 1);
			$this->CV_CID		= request('post', 'CV_CampaignID', 1);
			$this->Action		= request('post', 'action', 0);
			
			$this->CV_Title 	= addslashes(request('post', 'CV_Title', 0));
			
			$this->CV_embedCode = addslashes($_POST['CV_embedCode']);
			$this->CV_FileArray	= $_FILES['CV_File'];
			$this->Hdn_Camp_Video_File = request('post', 'Hdn_Camp_Video_File', 0);
			
			$this->Sorting 		= (request('post', 'CV_sorting', 1) == 0) ? 999 : request('post', 'CV_sorting', 1);
			$this->Status 		= request('post', 'CV_status', 1);
			//$TodayDate				= getdatetime();
			
			$this->FieldArr		= array(
				"Camp_Video_CampID"	=> $this->CV_CID,
				"Camp_Video_Title"	=> $this->CV_Title,
				"Camp_Video_EmbedCode" => $this->CV_embedCode,
				"Camp_Video_SortOrder" => $this->Sorting,
				"Camp_Video_ShowOnWebsite" => $this->Status);
				
			//dump($this->FieldArr);			
		}
		
		private function ValidateFormData() {
			if($this->FieldArr['Camp_Video_Title'] == NULL) {
				$this->SetStatus(0, 'E18003');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->FieldArr['Camp_Video_EmbedCode'] == '' && $this->CV_FileArray['name'] == '' && $this->Hdn_Camp_Video_File == '') {
				$this->SetStatus(0, 'E18004');
				redirect($_SERVER['HTTP_REFERER']);
			} elseif($this->CV_FileArray['name'] != '') {
				if($this->CV_FileArray['size'] > 5000000) {
					$this->SetStatus(0,'E18008');
					redirect($_SERVER['HTTP_REFERER']);
				}
					
				$Ext = pathinfo($this->CV_FileArray['name'], PATHINFO_EXTENSION);
				if(!in_array($Ext, $this->VideoExt)) {
					$this->SetStatus(0, 'E18009');
					redirect($_SERVER['HTTP_REFERER']);
				}
				
			} elseif($this->Sorting < 0 && $this->Sorting != NULL) {
				$this->setErrorMsg('18005');
				redirect($_SERVER['HTTP_REFERER']);
			}
		}
	
		private function setErrorMsg($ErrCode, $MsgType = 1) {
			EnPException :: writeProcessLog('Campaignimages_Controller :: setErrorMsg Function To Set Error Message => ' . $ErrCode);
			$this->P_status = 0;
			$this->P_ErrorCode .= $ErrCode . ",";
			$this->P_ErrorMessage = $ErrCode;
			$this->MsgType = $MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode, $MsgType = 2) {
			EnPException::writeProcessLog('Campaignimages_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->P_status = 1;
			$this->P_ConfirmCode = $ConfirmCode;
			$this->P_ConfirmMsg = $ConfirmCode;
			$this->MsgType = $MsgType;
		}
		
		private function SetStatus($Status, $Code) {
			if($Status) {
				$messageParams = array(
					"msgCode"	=> $Code,
					"msg"		=> "Custom Confirmation message",
					"msgLog"	=> 0,									
					"msgDisplay"=> 1,
					"msgType"	=> 2);
					EnPException :: setConfirmation($messageParams);
			} else {
				$messageParams = array(
					"errCode"=> $Code,
					"errMsg"=> "Custom Confirmation message",
					"errOriginDetails"=> basename(__FILE__),
					"errSeverity"=> 1,
					"msgDisplay"=> 1,
					"msgType"=> 1);
					EnPException :: setError($messageParams);
			}
		}
	}
?>