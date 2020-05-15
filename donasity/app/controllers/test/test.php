<?php
	class Test_Controller extends Controller
	{		
		function __construct()
		{
		}
		
		public function index()
		{	
			/*$host=MAIL_HOST;
			$type=MAIL_TYPE;		   
			$username=MAIL_USERNAME;
			$password=MAIL_PASSWORD;
			*/
			$type="smtp";
			$host="smtp.office365.com";
			$username="noreplydev@donasity.com";
			$password="Donasity@!5";
			
			$email_class_dir = LIBRARY_DIR."Email/";
			$email_class = "PHPMailer_Email";
			require_once $email_class_dir .$email_class . ".php";
			$this->emailObj = new $email_class( );
			try{
			$this->emailObj->configure( $type, $host, $username, $password,true,true,587);
			$this->emailObj->add_address("qualdev.test@gmail.com");
			$this->emailObj->add_replyto("qualdev.test@gmail.com");				 
			$this->emailObj->add_sender("noreplydev@donasity.com");
			$this->emailObj->set_subject("test mail");
			$this->emailObj->set_alt_body('To view the message, please use an HTML compatible email viewer!'); 
			$this->emailObj->set_body('Mail body comes here');
			$this->emailObj->SMTPSecure="tls";
			$this->emailObj->send();
			return true;
			
			}catch(PHPMailerException $pe){
				dump($pe);
				return false;
			}
		}
		
		
		public function checkrecurring()
		{
			$this->load_model('Stripe','ObjStripe');	
			dump($this->ObjStripe->setRecurring());
		
		
		}
		
	}
?>