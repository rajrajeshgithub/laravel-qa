<?php
	class Campaignimages_Controller extends Controller
	{
		public 	$CI_ID,$CI_CID,$filterParam,$Image,$LargePhysPath,$sortParam,$MedPhysPath,$ThumbPhysPath,$Action,$OldImageName,$ImageTitle,$ImageType,$Sorting,$Status,$FieldArr;
		public $loginDetails;
		public  $pageSelectedPage,$totalRecord,$pageLimit;
		public  $P_ErrorCode,$P_status,$P_ErrorMessage,$P_ConfirmCode,$P_ConfirmMsg,$MsgType;
		
		function __construct()
		{
			$this->load_model('CompaignImages','objCIDetails');
			$this->LastUpdateBy = getsession("DonasityAdminLoginDetail","admin_fullname");
			$this->P_status=1;
			$this->loginDetails = getsession("DonasityAdminLoginDetail");	
		}
		
		public function index($type='list-images',$CI_CID=NULL,$CI_ID=NULL)
		{
			if(isset($CI_ID) && $CI_ID<>NULL)
			{
				$this->CI_ID	= keyDecrypt($CI_ID);
			}
			
			if(isset($CI_CID) && $CI_CID<>NULL)
			{
				$this->CI_CID	= keyDecrypt($CI_CID);
			}
			
			$this->tpl 			= new view;
			switch(strtolower($type))
			{
				case 'add-image':
					$this->Insert();				
					break;	
				case 'edit-image':
					$this->Listing();
					$this->Edit();
					$this->tpl->draw('campaignimages/listcampaignimages');
					break;
				case 'update-image':
					$this->Update();
					break;
				case 'list-images':
					$this->Listing();
					$this->tpl->draw('campaignimages/listcampaignimages');
				break;
				case 'deleteimage':
				    $this->DeleteImageFunction();
					break;
				case 'delete-detail':
				    $this->DeleteDetail();
					break;			
			}	
		}
		
		
		private function DeleteDetail()
		{
			/*----update process log------*/
			$userType 	= 'ADMIN';					
			$userID		= $this->loginDetails['admin_id'];
			$userName	= $this->loginDetails['admin_fullname'];
			$sMessage = "Error in image deletion";
			$lMessage = "Error in image deletion";
			if($this->objCIDetails->DeleteDetail_DB($this->CI_ID))
			{
				$this->SetStatus(true,'C16004');
				$sMessage = "Fundraiser image deleted";
				$lMessage = "Fundraiser image deleted with Id - ".$this->CI_ID;
			}
			else
			{
				$this->SetStatus(false,'E16011');
			}			
			
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$this->CI_ID,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objCIDetails));	
				$this->objCIDetails->updateProcessLog($DataArray);	
			/*-----------------------------*/	
			redirect(URL."campaignimages/index/list-images/".keyEncrypt($this->CI_CID));
		}
		
		private function DeleteImageFunction()
		{
			EnPException::writeProcessLog('Campaignimages_Controller :: DeleteImageFunction action to delete profile image');	
			if(!is_numeric($this->CI_ID) && !is_numeric($this->CI_CID))
		  	{
				$this->SetStatus(false,'E2001');
				redirect(URL."home/");
		  	}
			$this->DeleteMainImage();
			if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."campaignimages/index/edit-image/".keyEncrypt($this->CI_CID)."/".keyEncrypt($this->CI_ID));}
			if($this->P_status)
			{
				$this->SetStatus(true,'C16003');
			}
			redirect(URL."campaignimages/index/edit-image/".keyEncrypt($this->CI_CID)."/".keyEncrypt($this->CI_ID));
		}
		
		private function DeleteMainImage()
		{
			$field = array('Camp_Image_ID','Camp_Image_Name','Camp_Image_CampID');
			$where = array('Camp_Image_ID'=>$this->CI_ID);
			$checkUser = $this->objCIDetails->GetCompaignImagesListing($field,$where);
			if(count($checkUser)>0)
			{
				if($checkUser[0]['Camp_Image_Name']!='')
				{
					if(chkFile(CAMPAIGN_LARGE_IMAGE_DIR,$checkUser[0]['Camp_Image_Name']))
					unlink(CAMPAIGN_LARGE_IMAGE_DIR.$checkUser[0]['Camp_Image_Name']);
					if(chkFile(CAMPAIGN_MEDIUM_IMAGE_DIR,$checkUser[0]['Camp_Image_Name']))
					unlink(CAMPAIGN_MEDIUM_IMAGE_DIR.$checkUser[0]['Camp_Image_Name']);	
					if(chkFile(CAMPAIGN_THUMB_IMAGE_DIR,$checkUser[0]['Camp_Image_Name']))
					unlink(CAMPAIGN_THUMB_IMAGE_DIR.$checkUser[0]['Camp_Image_Name']);
					$ImgArr['Camp_Image_Name']='';
					$this->objCIDetails->UpdateCImage_DB($ImgArr,$this->CI_ID);
				}
			}
			else
			{
				$this->setErrorMsg('E16010');
			}
		}
		
		private function Listing()
		{
			
			EnPException::writeProcessLog('Campaignimages_Controller :: Listing action to view all Image Details');
			
			if($this->CI_CID!=NULL)
			{
				if(!is_numeric($this->CI_CID))
				{
					$this->SetStatus(false,'E2001');
					redirect(URL."home/");
				}
		
				$DataArray			=	array("SQL_CACHE Camp_Image_ID as ID","Camp_Image_CampID","Camp_Image_Name","Camp_Image_Title","Camp_Image_Type","Camp_Image_SortOrder","Camp_Image_ShowOnWebsite");
				$this->filterParam	=	array("Camp_Image_CampID"=>$this->CI_CID);
				
				$CIDList 			= 	$this->objCIDetails->GetCompaignImagesListing($DataArray,$this->filterParam,$this->sortParam);
				
				$PagingArr			=	constructPaging($this->objCIDetails->pageSelectedPage,$this->objCIDetails->totalRecord,$this->objCIDetails->pageLimit);		
				$LastPage 			= 	ceil($this->objCIDetails->totalRecord/$this->objCIDetails->pageLimit);
				
				$ImageType     		= 	$GLOBALS['imagetype'];
				//dump($ImageType);
				
				$this->tpl->assign("totalRecords",$this->objCIDetails->totalRecord);
				$this->tpl->assign("CIList",$CIDList);
				$this->tpl->assign("action","add-image");
				$this->tpl->assign("PagingList",$PagingArr['Pages']);
				$this->tpl->assign("PageSelected",$PagingArr['PageSel']);
				$this->tpl->assign("startRecord",$PagingArr['StartPoint']);
				$this->tpl->assign("endRecord",$PagingArr['EndPoint']);
				$this->tpl->assign("lastPage",$LastPage);
				$this->tpl->assign("ImageType",$ImageType);
				$this->tpl->assign("CI_CID",$this->CI_CID);
			}
			else
			{
				redirect(URL."home/");
			}
		}
		
		
		private function Edit()
		{
			if($this->CI_CID>0)
			{
				if($this->CI_ID>0)
				{
					if(!is_numeric($this->CI_ID) || !is_numeric($this->CI_CID))
					{
						$this->SetStatus(false,'E2001');
						redirect(URL."home/");
					}
					$DataArray			=	array("SQL_CACHE Camp_Image_ID as ID","Camp_Image_CampID","Camp_Image_Name","Camp_Image_Title","Camp_Image_Type","Camp_Image_SortOrder","Camp_Image_ShowOnWebsite");
														
					$where      					= 	array("Camp_Image_ID"=>$this->CI_ID);
					$ImageDetail					= 	$this->objCIDetails->GetCompaignImagesListing($DataArray,$where);
					
					
					$this->tpl->assign("action",'update-image');
					$this->tpl->assign("CI_ID",$this->CI_ID);
					$this->tpl->assign("ImageDetail",$ImageDetail[0]);
				}
				else
				{
					redirect(URL."campaignimages/index/list-images/".keyEncrypt($this->CI_CID));
				}
			}
			else
			{
				redirect(URL."campaign");
			}
		}
		
		
		private function Insert()
		{
			$this->getFormData();
			$this->ValidateFormData();
			if($this->P_status == 0) {
				$this->SetStatus(false,$this->P_ErrorCode);
				redirect(URL."compaignimages");
			}
			$InsertImage = $this->objCIDetails->CImagesInsert_DB(TBLPREFIX.'campaignimages',$this->FieldArr);
			
			/*----update process log------*/
			$userType 	= 'ADMIN';					
			$userID		= $this->loginDetails['admin_id'];
			$userName	= $this->loginDetails['admin_fullname'];
			$sMessage = "Error in image uploading";
			$lMessage = "Error in image uploading";
			if($InsertImage > 0)
			{
				$sMessage = "Fundraiser image uploaded";
				$lMessage = "Fundraiser image uploaded with Id - ".$InsertImage;
			}
				$DataArray = array(	"UType"=>$userType,
									"UID"=>$userID,
									"UName"=>$userName,
									"RecordId"=>$InsertImage,
									"SMessage"=>$sMessage,
									"LMessage"=>$lMessage,
									"Date"=>getDateTime(),
									"Controller"=>get_class()."-".__FUNCTION__,
									"Model"=>get_class($this->objCIDetails));	
				$this->objCIDetails->updateProcessLog($DataArray);	
			/*-----------------------------*/	
			if($InsertImage!=NULL && $InsertImage>0)
			{
				if($_FILES['CI_imageName']['name']!='')
				{
					$this->CI_ID = $InsertImage;
					$ImageStatus = $this->Image();
					if($ImageStatus)
					{
						$this->SetStatus(true,'C16001');
					}
					else
					{
						$this->SetStatus(false,'E16005');
					}
				}
				else
				{
					$this->SetStatus(false,'C16001');
					//redirect(URL."reguser/index/add");
				}
				redirect(URL."campaignimages/index/edit-image/".keyEncrypt($this->CI_CID).'/'.keyEncrypt($InsertImage));
			}
			else
			{
				$this->SetStatus(false,'E16006');
				redirect(URL."npocontacts");
			}	
		}
		
		private function Image()
		{
			$this->Image			= count($_FILES['CI_imageName'])>0?$_FILES['CI_imageName']:'';
			$ImageFile  			= $this->Image;
			$Ext					= strtolower(file_ext($ImageFile['name']));
			$CustomName	 			= $ImageFile['name'];
			$Image					= $this->CI_ID.".".$Ext;
			$this->LargePhysPath	= CAMPAIGN_LARGE_IMAGE_DIR.$this->CI_ID.".".$Ext;
			$this->MedPhysPath		= CAMPAIGN_MEDIUM_IMAGE_DIR.$this->CI_ID.".".$Ext;
			$this->ThumbPhysPath	= CAMPAIGN_THUMB_IMAGE_DIR.$this->CI_ID.".".$Ext;
			
			$oldProflieImage		= request('post','CI_oldImage',0);
			if($oldProflieImage!=NULL)
			{
				$oldExt				= explode('.',$oldProflieImage);
				
				
				unlink(CAMPAIGN_LARGE_IMAGE_DIR.$this->CI_ID.".".$oldExt[1]);
				unlink(CAMPAIGN_MEDIUM_IMAGE_DIR.$this->CI_ID.".".$oldExt[1]);
				unlink(CAMPAIGN_THUMB_IMAGE_DIR.$this->CI_ID.".".$oldExt[1]);
			}
			
			move_uploaded_file($ImageFile["tmp_name"],$this->LargePhysPath);
			$ImageField = array("Camp_Image_Name"=>$Image);
			$ImageStatus = $this->objCIDetails->UpdateCImage_DB($ImageField,$this->CI_ID);
			$this->CreateMediumImg();
			$this->CreateThumbImg();
			return $ImageStatus;
		}
		
		 private function CreateMediumImg()
		{
			//image_resize( $this->LargePhysPath, $this->MedPhysPath, 300, 300,false,70)	;
			$objFile=LoadLib('resize_image');
			$objFile= new resize($this->LargePhysPath);
			$objFile -> resizeImage(300, 400, 'auto');
			$objFile -> saveImage($this->MedPhysPath, 100);
		}
	
		private function CreateThumbImg()
		{
			//image_resize( $this->LargePhysPath, $this->ThumbPhysPath, 70, 70,false,70)	;
			$objFile=LoadLib('resize_image');
			$objFile= new resize($this->MedPhysPath);
			$objFile->resizeImage(70, 70, 'crop');
			$objFile->saveImage($this->ThumbPhysPath, 100);
		}
		
		private function Update()
		{
			EnPException::writeProcessLog('Campaignimages_Controller :: Update action to Update compain image details');
			try
			{	
				$this->getFormData();
				$this->ValidateFormData();
				
				if($this->P_status==0){$this->SetStatus(false,$this->P_ErrorCode);redirect(URL."compaignimages/index/edit-images/".keyEncrypt($this->CI_ID));}
			   
				$Status	= $this->objCIDetails->UpdateCImage_DB($this->FieldArr,$this->CI_ID);
				
				/*----update process log------*/
				$userType 	= 'ADMIN';					
				$userID		= $this->loginDetails['admin_id'];
				$userName	= $this->loginDetails['admin_fullname'];
				$sMessage = "Error in image details updation";
				$lMessage = "Error in image details updation";
				if($Status>0)
				{
					$sMessage = "Fundraiser image details updated";
					$lMessage = "Fundraiser image details updated with Id - ".$this->CI_ID;
				}
					$DataArray = array(	"UType"=>$userType,
										"UID"=>$userID,
										"UName"=>$userName,
										"RecordId"=>$this->CI_ID,
										"SMessage"=>$sMessage,
										"LMessage"=>$lMessage,
										"Date"=>getDateTime(),
										"Controller"=>get_class()."-".__FUNCTION__,
										"Model"=>get_class($this->objCIDetails));	
					$this->objCIDetails->updateProcessLog($DataArray);	
				/*-----------------------------*/	
				
				if($Status)
				{
					if($_FILES['CI_imageName']['name']!='')
					{
						if($_FILES['CI_imageName']['error'] == 0)
						{
							$ImageStatus=$this->Image();
							if($ImageStatus)
							{
								$this->SetStatus(true,'C16002');
							}
							else
							{
								$this->SetStatus(false,'E16007');
							}
						}
					}
					else
					{
						$this->SetStatus(true,'C16002');
					}
						
				}
				else
				{
					$this->SetStatus(false,'E16008');
				}
			}
			catch(Exception $e)
			{
				EnPException::exceptionHandler($e);	
			}
			redirect(URL."campaignimages/index/edit-image/".keyEncrypt($this->CI_CID).'/'.keyEncrypt($this->CI_ID));
		}
		
		
		private function getFormData()
		{
			EnPException::writeProcessLog('Campaignimages_Controller :: getFormData action to get all data');
			
			$this->CI_ID		= request('post','CI_ID',1);
			$this->CI_CID		= request('post','CI_CampaignID',1);
			$this->Action		= request('post','action',0);
			$this->OldImageName = request('post','CI_oldImage',0);
			
			$this->ImageTitle 	= request('post','CI_imageTitle',0);
			$this->ImageType 	= request('post','CI_imageType',0);
			$this->Sorting 		= (request('post','CI_sorting',1)==0)?999:request('post','CI_sorting',1);
			$this->Status 		= request('post','CI_status',1);
			$TodayDate			= getdatetime();
			
			$this->FieldArr		= array("Camp_Image_CampID"=>$this->CI_CID,"Camp_Image_Title"=>$this->ImageTitle,"Camp_Image_Type"=>$this->ImageType,
										"Camp_Image_SortOrder"=>$this->Sorting,"Camp_Image_ShowOnWebsite"=>$this->Status);
											
		}
		
		private function ValidateFormData()
		{
			if($this->Action=='add-image')
			{
				
				if($_FILES['CI_imageName']['name']<>NULL)
				{
					if(isset($_FILES['CI_imageName']['name']) && $_FILES['CI_imageName']['name']==NULL){$this->SetStatus(0,'E16001');redirect($_SERVER['HTTP_REFERER']);}
					elseif(isset($_FILES['CI_imageName']['name']) && $_FILES['CI_imageName']['name']!='')
					{
						if(file_ext($_FILES['CI_imageName']['name'])<>'gif' && file_ext($_FILES['CI_imageName']['name'])<>'jpg' && file_ext($_FILES['R_profileImage']['name'])<>'jpeg'  && file_ext($_FILES['R_profileImage']['name'])!='png'){$this->SetStatus(0,'E16001');redirect($_SERVER['HTTP_REFERER']);}
					}
					
				}
			}
			elseif($this->FieldArr['Camp_Image_Title']==NULL){$this->SetStatus(0,'E16003');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->FieldArr['Camp_Image_Type']==NULL){$this->SetStatus(0,'E16004');redirect($_SERVER['HTTP_REFERER']);}
			elseif($this->Sorting < 0 && $this->Sorting != NULL){$this->setErrorMsg('E16009');redirect($_SERVER['HTTP_REFERER']);};
		}
		
		private function setErrorMsg($ErrCode,$MsgType=1)
		{
			EnPException::writeProcessLog('Campaignimages_Controller :: setErrorMsg Function To Set Error Message => '.$ErrCode);
			$this->P_status=0;
			$this->P_ErrorCode.=$ErrCode.",";
			$this->P_ErrorMessage=$ErrCode;
			$this->MsgType=$MsgType;
		}
		
		private function setConfirmationMsg($ConfirmCode,$MsgType=2)
		{
			EnPException::writeProcessLog('Campaignimages_Controller :: setConfirmationMsg Function To Set Confirmation Message => '.$ConfirmCode);
			$this->P_status=1;
			$this->P_ConfirmCode=$ConfirmCode;
			$this->P_ConfirmMsg=$ConfirmCode;
			$this->MsgType=$MsgType;
		}
		
		private function SetStatus($Status,$Code)
		{
			if($Status)
			{
				$messageParams=array("msgCode"=>$Code,
												 "msg"=>"Custom Confirmation message",
												 "msgLog"=>0,									
												 "msgDisplay"=>1,
												 "msgType"=>2);
					EnPException::setConfirmation($messageParams);
			}
			else
			{
				$messageParams=array("errCode"=>$Code,
										 "errMsg"=>"Custom Confirmation message",
										 "errOriginDetails"=>basename(__FILE__),
										 "errSeverity"=>1,
										 "msgDisplay"=>1,
										 "msgType"=>1);
					EnPException::setError($messageParams);
			}
		}
	}
?>