<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Send_email extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
		Library_Auth_Common::check_allowed_request();

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			print BackEnd_SendEmail_View::build_html();
		}

		/*
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}


	// get data for user
	public function get_email_template_html() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		if (LoginAuth::is_user_have_admin_access()) {
			$db_model = new DB_Users();
			$obj_result->is_success = true;
			//$result = DownlineModel::get_arr_user_downline_data($user_id);
			$obj_result->html_template = BackEnd_SendEmail_View::build_general_email_template_html();
		}
		print json_encode($obj_result);
	}


	public function send_test_email() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		$subject = "";
		$message = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->email_contents_html)) {
				$message = $obj_request_data->email_contents_html;
			}
			if (isset($obj_request_data->subject)) {
				$subject = $obj_request_data->subject;
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		if (LoginAuth::is_user_have_admin_access()) {
			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-replay@$short_site_url";

			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			} else {
				$to = "dino.bartolome@gmail.com, sdoron713@gmail.com";
			}

			$referral_id = $data['referral_id'];

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			 
			$subject = "Test: ".$subject;
					 
			/*
			print "in here<br>\n";
			print "to is $to\n";
			print "headers is $headers\n";
			print "message is $message\n";
			*/
			//print "message is $message\n";
			// Sending email
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			    //echo 'Your mail has been sent successfully.';
			} else {
			    //echo 'Unable to send email. Please try again.';
			}

		}
		print json_encode($obj_result);
	}


	public function send_emails() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		$subject = "";
		$message = "";
		$arr_to = Array();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->email_contents_html)) {
				$message = $obj_request_data->email_contents_html;
			}
			if (isset($obj_request_data->subject)) {
				$subject = $obj_request_data->subject;
			}
			if (isset($obj_request_data->arr_to)) {
				$arr_to = (Array)$obj_request_data->arr_to;
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$number_emails_sent = 0;


		if (LoginAuth::is_user_have_admin_access()) {
			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-replay@$short_site_url";

			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			} else {
				$to = "dino.bartolome@gmail.com";
			}

			for($n=0;$n<count($arr_to);$n++) {
				$to = $arr_to[$n]->login;

				$referral_id = $data['referral_id'];

				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				 
				// Create email headers
				$headers .= 'From: '.$from."\r\n".
				    'Reply-To: '.$from."\r\n" .
				    'X-Mailer: PHP/' . phpversion();
				 
						 
				/*
				print "in here<br>\n";
				print "to is $to\n";
				print "headers is $headers\n";
				print "message is $message\n";
				*/
				//print "message is $message\n";
				// Sending email
				if (mail($to, $subject, $message, $headers, "-f$from")) {
					$number_emails_sent += 1;
				    //echo 'Your mail has been sent successfully.';
				} else {
				    //echo 'Unable to send email. Please try again.';
				}
			}

		}
		$obj_result->number_emails_sent = $number_emails_sent;
		print json_encode($obj_result);
	}
}
?>
