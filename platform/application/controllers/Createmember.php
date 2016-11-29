<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Createmember extends CI_Controller {

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
		
		print BackEnd_CreateMember_View::build_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	// must get exact match for sponsor
	public function get_sponsor_details() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->sponsor_login)) {
				$sponsor_login = $obj_request_data->data->sponsor_login;
			}
		}
		
		//print_r($_POST);
		//print_r($obj_request_data);

		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if ($sponsor_login != "") {
			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['exact_search'] = $sponsor_login;
			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			if (count($arr_data) > 0 ) {
				$obj_result->is_success = true;
				$obj_result->sponsor_id = $arr_data[0]['id'];
				$obj_result->sponsor_fullname = $arr_data[0]['fullname'];
			}
		}
		print json_encode($obj_result);
		
	}

	// must get exact match for sponsor
	public function check_if_login_already_exists() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->sponsor_login)) {
				$sponsor_login = $obj_request_data->data->sponsor_login;
			}
		}
		
		//print_r($_POST);
		//print_r($obj_request_data);

		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if ($sponsor_login != "") {
			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['exact_search'] = $sponsor_login;
			$arr_criteria['is_activated'] = "Y";
			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			if (count($arr_data) > 0 ) {
				$obj_result->is_success = true;
				//$obj_result->sponsor_id = $arr_data[0]['id'];
				//$obj_result->sponsor_fullname = $arr_data[0]['fullname'];
			}
		}
		print json_encode($obj_result);
		
	}


	public function create_member() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		//if (LoginAuth::is_user_have_admin_access()) {
		$is_do = true;
		if ($is_do) {
			$login = "";
			$country = "";
			$password = "";
			$fullname = "";
			$sponsor_id = 0;
			$sponsor_login = "";
			$obj_request_data = Common::load_request_data();
			if (is_object($obj_request_data) && isset($obj_request_data->data)) {
				if (isset($obj_request_data->data->login)) {
					$login = $obj_request_data->data->login;
				}
				if (isset($obj_request_data->data->country)) {
					$country = $obj_request_data->data->country;
				}
				if (isset($obj_request_data->data->fullname)) {
					$fullname = $obj_request_data->data->fullname;
				}
				if (isset($obj_request_data->data->sponsor_id)) {
					$sponsor_id = $obj_request_data->data->sponsor_id;
				}
				if (isset($obj_request_data->data->sponsor_login)) {
					$sponsor_login = $obj_request_data->data->sponsor_login;
				}
			}
			$data = Array();
			$db_model = new DB_Users();
			

			//print_r($obj_request_data);
			if ($sponsor_id > 0 && $sponsor_id != "") {
				// if we have a sponsor id we dont trust it just grab from database
				if ($sponsor_login != "") {
					$arr_criteria = Array();
					$arr_criteria['exact_search'] = $sponsor_login;
					$arr_criteria['status_id'] = DB_Users::$ACTIVE_STATUS_ID;
					$arr_temp_data = $db_model->search($arr_criteria);

					if (count($arr_temp_data) > 0) {
						$referral_id = $arr_temp_data[0]['id'];
					} else {
						$referral_id = LoginAuth::get_session_user_id();
					}
				} else {
					$referral_id = LoginAuth::get_session_user_id();
				}
			} else {
				$referral_id = LoginAuth::get_session_user_id();
			}
			/*
			print_r($obj_request_data);
			print "full name is $fullname\n";
			print "login $login\n";
			print "country $country\n";
			*/


			if ($login != "" && $country != "" && $fullname != "") {
				$password = rand(100000, 999999);
				$data['password'] = $password;
				$bcrypt = new Bcrypt(5);
				$data['password'] = $bcrypt->hash($data['password']);
				$data['login'] = $login;
				$data['otp'] = rand(1000,9999);
				$data['country'] = $country;
				$data['is_activated'] = "Y";
				$data['referral_id'] = $referral_id;
				$data['fullname'] = $fullname;
				$data['is_activated'] = "Y";
				//print_r($data);
				$db_model = new DB_Users();

				$arr_criteria = Array();
				$arr_criteria['exact_search'] = "$login";
				$user_data = $db_model->search($arr_criteria);
				$id = 0;
				if (count($user_data) > 0) {
					$id = $user_data[0]['id'];
				}

				$number_affected = 0;
				if ($id > 0) {
					// if already exists check if activated
					$temp_user_data = $db_model->get($id);
					if (count($temp_user_data) > 0) {
						if ($temp_user_data['is_activated'] == "N") {
							// if not activated yet then make it activated and sign the person up
							$numb_affected = $db_model->save($id,$data);
						}
					}
					
				} else {
					$numb_affected = $db_model->save(0,$data);
				}

				// check if user is is_activated
				// need to create logic that if it already exists and not activated to set to activate and update data with this account detils

				//print_r($data);
				//print "Num affected is $numb_affected\n";
				if ($numb_affected == 1) {

					$user_id = $db_model->get_last_insert_id();
					$site_url = Registry::get("site_url");
					$obj_result->is_success = true;
					$is_dev_server = Registry::get("is_dev_server");

					$site_url = Registry::get("site_url");
					$from = "no-replay@$site_url";
					/*


					if ($is_dev_server) {
						//$to = "dino.bartolome@gmail.com, sdoron713@gmail.com, $login";
						$to = "$login";
					} else {
						$to = $login;
					}
					$subject = "WELCOME | BIT MUTUAL HELP";
					 
					// To send HTML mail, the Content-type header must be set
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					 
					// Create email headers
					$headers .= 'From: '.$from."\r\n".
					    'Reply-To: '.$from."\r\n" .
					    'X-Mailer: PHP/' . phpversion();
					 
					$data_object = new stdclass();
					$data_object->password = $password;
					$data_object->login = $data['login'];
					$data_object->country = $data['country'];
					$data_object->otp = $data['otp'];
					$data_object->fullname = $data['fullname'];
					$data_object->sponsor_email = "";
					if ($referral_id > 0) {
						$db_model = new DB_Users();
						$arr_temp_data = $db_model->get($referral_id);
						if (count($arr_temp_data) > 0) {
							$data_object->sponsor_email = $arr_temp_data['login'];
						}
					}

					$data_object->site_url = $site_url;
					$message = BackEnd_CreateMember_View::build_signup_html($data_object);
					 
					// Sending email
					if (mail($to, $subject, $message, $headers, "-f$from")) {
					    //echo 'Your mail has been sent successfully.';
					} else {
					    //echo 'Unable to send email. Please try again.';
					}
					*/

					Emailer::send_signup_email($user_id, $password);
					Emailer::send_signup_to_sponsor_email($user_id);
					

				}
			}
		}
		print json_encode($obj_result);

	}
}
?>
