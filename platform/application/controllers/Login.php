<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

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
		
		print BackEnd_Login_View::build_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	public function logout() {
		session_destroy();
	}

	public function reset_otp_code() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);


		$login = "";
		$password = "";
		$captcha = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->login)) {
				$login = $obj_request_data->data->login;
			}
		}
		//print_r($obj_request_data);
		// authenticates return true or false
		$obj_result = new Stdclass();
		$obj_result->otp_code = "";

		$db_model = new DB_Users();
		if ($login != "") {
			$arr_criteria = Array();
			$arr_criteria['login'] = $login;
			$arr_criteria['status_id'][] = DB_Users::$ACTIVE_STATUS_ID;
			$arr_criteria['status_id'][] = DB_Users::$SUSPENDED_STATUS_ID;

			$arr_data = $db_model->search($arr_criteria);
			if (count($arr_data) >0) {
				$user_id = $arr_data[0]['id'];
				$data = array();
				$data['otp'] = rand(1000, 9999);
				$db_model->save($user_id, $data);

				//$obj_result->otp_code = $data['otp'];

				$email = $arr_data[0]['login'];
				$data_object = new stdclass();
				$data_object->otp_code = $data['otp'];
				$data_object->fullname = $arr_data[0]['fullname'];
				Emailer::send_reset_otp_code_email($email, $data_object);
			}
		}
		print json_encode($obj_result);
	}

	// check btc address again and submit if not in database and valid send out OTP Code via SMS
	public function first_time_signin_submit() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);


		$btc_address = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->btc_address)) {
				$btc_address = $obj_request_data->data->btc_address;
			}
		}

		$obj_result = new Stdclass();
		$obj_result->is_valid = false;
		$obj_result->is_already_in_database = false;


		if ($btc_address != "") {
			$is_valid_btc_address = BTCAddress::is_valid($btc_address);
			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['btc_address'] = $btc_address;
			$arr_data = $db_model->search($arr_criteria);

			if (count($arr_data) >0) {
				//$obj_result->is_already_in_database = true;
			}
		}
		print json_encode($obj_result);
	}

	/* from second page */
	public function check_first_time_signin_form() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);


		$phone_number = "";
		$btc_address = "";
		$login = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->phone_number)) {
				$phone_number = $obj_request_data->data->phone_number;
			}
			if (isset($obj_request_data->data->btc_address)) {
				$btc_address = $obj_request_data->data->btc_address;
			}
			if (isset($obj_request_data->data->login)) {
				$login = $obj_request_data->data->login;
			}
		}

		$is_valid_btc_address = false;
		$is_duplicate_phone_number = false;

		if ($btc_address != "") {
			$is_valid_btc_address = BTCAddress::is_valid($btc_address);
		}

		if ($phone_number != "") {
			$is_duplicate_phone_number = User::check_phone_number_in_db("", $phone_number, $login);
		}

		
		$is_dev_server = Registry::get("is_dev_server");
		if ($is_dev_server) {
			$is_duplicate_phone_number = false;
		} else {
		}

		$obj_result = new Stdclass();
		$obj_result->is_valid_btc_address = $is_valid_btc_address;
		$obj_result->is_duplicate_phone_number = $is_duplicate_phone_number;
		$obj_result->is_duplicate_info_found = false;
		$obj_result->is_success = false;

		//print "login is $login\n";
		if ($login != "" && $is_valid_btc_address) {
			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['login'] = $login;
			$arr_data = $db_model->search($arr_criteria);
			if (count($arr_data) > 0) {

				$user_id = $arr_data[0]['id'];
				// check if valid login
				//print_r($arr_data[0]);
				if ($arr_data[0]['is_verified'] == "N") {
				/*if (is_null($arr_data[0]['btc_address']) && is_null($arr_data[0]['phone'])) {*/

					$data = Array();
					$data['btc_address'] = $btc_address;
					$data['phone'] = $phone_number;
					//print "user id is $user_id\n";
					$number_affected = $db_model->save($user_id, $data);
					if ($number_affected == -1) {
						// means either duplicate btc address or phone
					}
					//print "number affected is $number_affected\n";
					//$number_affected = 1;

					if ($number_affected == 1) {
						// send sms 
						$current_time = time()+90;
						$date_time = Library_DB_Util::time_to_db_time("",strtotime(gmdate('Y-m-d H:i:s', $current_time)));
						$data = Array();
						$data['otp_expire_gm_date_time'] = $date_time;
						$data['otp'] = rand(1000, 9999);
						$number_affected = $db_model->save($user_id, $data);
						
						//SMS::send_otp_code($data['otp'], $phone_number);

						$is_dev_server = Registry::get("is_dev_server");
						$user_data = $db_model->get($user_id);
						if (count($user_data) > 0) {
							$to = $user_data['login'];
							$data_object = new stdclass();
							$data_object->otp_code = $data['otp'];
							$data_object->fullname = $arr_data[0]['fullname'];
							Emailer::send_otp_code_email($to, $data_object);
							$obj_result->is_success = true;
						}
					} else if ($number_affected == -1) {
						$obj_result->is_duplicate_info_found = true;
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	public function check_phone_number() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);


		$phone_number = "";
		$login = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->phone_number)) {
				$phone_number = $obj_request_data->data->phone_number;
			}
			if (isset($obj_request_data->data->login)) {
				$login = $obj_request_data->data->login;
			}
		}

		$obj_result = new Stdclass();
		$obj_result->is_duplicate_phone_number = false;
		$obj_result->is_valid = false;


		$is_dev_server = Registry::get("is_dev_server");

		if ($phone_number != "") {
			$obj_result->is_valid = true;
			$is_duplicate_number = User::check_phone_number_in_db("", $phone_number, $login);
			if ($is_dev_server) {
				//$is_duplicate_number = false;
			} else {
			}
			$obj_result->is_duplicate_phone_number = $is_duplicate_number;
		} else {
			$obj_result->is_valid = false;
		}
		print json_encode($obj_result);
	}

	public function check_btc_address() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);


		$btc_address = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->btc_address)) {
				$btc_address = $obj_request_data->data->btc_address;
			}
		}
		//print "btc_address is $btc_address\n";
		//print_r($obj_request_data);
		// authenticates return true or false
		$obj_result = new Stdclass();
		$obj_result->is_valid = false;
		$obj_result->is_already_in_database = false;



		if ($btc_address != "") {
			$obj_result->is_valid = BTCAddress::is_valid($btc_address);
			if ($obj_result->is_valid) {
				//SMS::send_otp_code("5555", "3109234078");
				//print "end trying to send otp code\n";
			}

			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['btc_address'] = $btc_address;
			$arr_data = $db_model->search($arr_criteria);


			if (count($arr_data) >0) {
				$obj_result->is_already_in_database = true;
			}

			// do also a search in new table btc_addresses
		}
		print json_encode($obj_result);
	}

	public function authenticate() {
		$obj_request_data = Common::load_request_data();
		//print_r($obj_request_data);

		$is_captch_success = false;

		$login = "";
		$password = "";
		$captcha = "";
		if (isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->login)) {
				$login = $obj_request_data->data->login;
			}
			if (isset($obj_request_data->data->password)) {
				$password = $obj_request_data->data->password;
			}
			if (isset($obj_request_data->data->captcha)) {
				$captcha = $obj_request_data->data->captcha;
			}
		}


		// check if login is not verified
		$is_verified = true;
		$is_in_process_of_verifying = false;
		$is_verify_opt_timeout = false;
		if ($login != "") {
			$db_model = new DB_Users();
			$arr_criteria = Array();
			$arr_criteria['login'] = $login;
			$arr_data = $db_model->search($arr_criteria);

			if (count($arr_data) >0) {
				if ($arr_data[0]['is_verified'] == "N") {
					$is_verified = false;
				}
				if (!is_null($arr_data[0]['otp_expire_gm_date_time']) && $arr_data[0]['otp'] != "") {
					$date_time_in_utc = $arr_data[0]['otp_expire_gm_date_time'];
					
					$time = strtotime($date_time_in_utc.' UTC');
					//print "time now is ".time()." and time is $time\n";
					// means we are still in time allotment of verification
					$is_in_process_of_verifying = true;
					if ($time >= time()) {
						//print "we are in process of verifying\n";
					} else {
						$is_verify_opt_timeout = true;
					}
				}
			}
		}

		// bypass captcha if in process of verifying
		if ($captcha == "" && $is_in_process_of_verifying) {
			$is_captcha_success = true;
		}

		// remove line below
		//$is_in_process_of_verifying = true;

		$obj_result = new Stdclass();
		$obj_result->is_auth_success = false;
		$obj_result->is_first_time_login = false;
		$obj_result->is_captcha_success = false;
		$obj_result->is_in_process_of_verifying = $is_in_process_of_verifying;
		$obj_result->is_valid_otp_code = false;
		$obj_result->is_verify_opt_timeout = $is_verify_opt_timeout;

		// do login magic in database
		$obj_login = new LoginAuth();

		if (!$is_captcha_success) {
			//print_r($obj_request_data);
			// authenticates return true or false


			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$fields = array(
				'secret' => urlencode("6LerrSMTAAAAALWxwvUNP2qNoV6a6VB4JuhffOoo"),
				'response' => urlencode($captcha)
			);

			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');

			//open connection
			$ch = curl_init();
			//print_r($fields_string);

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			//execute post
			$json_result = &curl_exec($ch);
			//print_r($json_result);
			$result = json_decode($json_result);
			//print "blahblah\n";
			//print "result is\n";
			//print_r($result);
			//close connection
			curl_close($ch);

			if (is_object($result) && isset($result->success) && $result->success) {
				//print "success\n";
				$is_captcha_success = true;
			} else {
			}
		}

		$db_model = new DB_Users();
		if ($is_verify_opt_timeout) {
			$arr_criteria = Array();
			$arr_criteria['login'] = $login;
			$arr_data = $db_model->search($arr_criteria);

			if (count($arr_data) >0) {
				$data = array();
				$data['otp_expire_gm_date_time'] = NULL;
				$arr_data = $db_model->save($arr_data[0]['id'],$data);
			}
		}

		// take this out later
		$is_captcha_success = true;

		
		// if we timed out then dont try to authenticate
		if ($is_captcha_success && !$is_verify_opt_timeout) {
			$obj_result->is_captcha_success = true;
			if ($obj_login->authenticate($login, $password)) {
				$arr_criteria = Array();
				$arr_criteria['login'] = $login;
				$arr_data = $db_model->search($arr_criteria);

				if (count($arr_data) >0) {
					//print_r($arr_data);
					if ($arr_data[0]['is_verified'] == "N" && !$is_in_process_of_verifying) {
						$obj_result->is_first_time_login = true;
					}

					$is_do_checks = true;
					if ($is_do_checks) {
						$country = "";
						$otp = "";
						if (isset($obj_request_data->data->country)) {
							$country = $obj_request_data->data->country;
						}
						if (isset($obj_request_data->data->otp)) {
							$otp = $obj_request_data->data->otp;
						}
						//print "otp is $otp\n";
						//print "country is $country\n";
						//print_r($arr_data);

						if ($obj_result->is_first_time_login) {
							// dont count otp
							if ($country != "" && trim(strtolower($arr_data[0]['country'])) == strtolower($country)) {
								$obj_result->is_auth_success = true;
							}
						} else {
							if ($country != "" && $otp != "" && trim(strtolower($arr_data[0]['country'])) == strtolower($country) && $arr_data[0]['otp'] == $otp) {
								$obj_result->is_auth_success = true;
							}
							if ($otp != "" && $arr_data[0]['otp'] == $otp) {
								$obj_result->is_valid_otp_code = true;
							} else {
								$obj_result->is_valid_otp_code = false;
							}
						}
					}
					//print_r($arr_data);
					//print "authenticated\n";

					if ($obj_result->is_auth_success && !$obj_result->is_first_time_login) {
						session_start();
						$_SESSION['is_authenticated'] = true;

						$db_model = new DB_Users();

						$_SESSION['session_start_gm_date_time'] = Library_DB_Util::time_to_gm_db_time();

						$arr_criteria = Array();
						$arr_criteria['login'] = $login;
						$arr_criteria['status_id'] = DB_Users::$ACTIVE_STATUS_ID;
						$arr_data = $db_model->search($arr_criteria);
						if (count($arr_data) > 0) {
							$_SESSION['user_id'] = $arr_data[0]['id'];
						} else {
							
						}
						session_write_close();

						if ($is_in_process_of_verifying) {
							$data = array();
							$data['is_verified'] = "Y";
							$data['otp_expire_gm_date_time'] = NULL;
							$arr_data = $db_model->save($arr_data[0]['id'],$data);
						}
					}
				}
			}
		}
		print json_encode($obj_result);
	}
}
?>
