<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_reset extends CI_Controller {

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
		//PHRequest::update_daily_growth_for_all();
		//exit(0);
		//print BackEnd_PasswordResets_View::build_html();


		//Library_Auth_Common::check_allowed_request();

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

	public function password_reset_validate($user_id) {
		print BackEnd_PasswordResets_View::build_password_reset_form_html();
	}

	public function reset_password_for_login() {
		//Library_Auth_Common::set_post_allowed_request_callback(NULL);
		//Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();

		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		$data = null;
		$login = "";
		if (is_object($obj_request_data) && isset($obj_request_data->login)) {
			if (isset($obj_request_data->login)) {
				$login = $obj_request_data->login;

			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		//print "login is $login\n";
		//print_r($data);
		//$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		//print_r($data);


		if ($login != "") {
			$arr_criteria = Array();
			$arr_criteria['login'] = $login;
			$arr_criteria['status_id'][] = DB_Users::$ACTIVE_STATUS_ID;
			$arr_criteria['status_id'][] = DB_Users::$SUSPENDED_STATUS_ID;

			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			if (count($arr_data) >0) {
				/*
				$hash = $temp_data['reset_password'];
				$password = $data->old_password;
				$bcrypt = new Bcrypt(5);
				$is_good = $bcrypt->verify($password, $hash);
				*/
				$data = Array();
				$expiration_timestamp = strtotime("+1 Day");
				$data['reset_password'] = rand(1000000,99999999);
				$data['is_password_reset_enabled'] = "Y";
				$data['password_reset_expiration_gm_date_time'] = Library_DB_Util::time_to_gm_db_time($db_model->db, $expiration_timestamp);
				$db_model->save($arr_data[0]['id'], $data);
				PasswordResetHelper::send_password_reset_email($arr_data[0]['id'], $data['reset_password']);
				$obj_result->is_success = true;
			}
		}

		// maybe add some sort of sleep
		print json_encode($obj_result);
	}

	// will disable this in the future
	public function reset_password($user_id) {
		//Library_Auth_Common::set_post_allowed_request_callback(NULL);
		//Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();

		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		$data = null;
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		//print_r($data);
		//$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		//print_r($data);

		$temp_data = $db_model->get($user_id);
		if (count($temp_data) > 0) {
			/*
			$hash = $temp_data['reset_password'];
			$password = $data->old_password;
			$bcrypt = new Bcrypt(5);
			$is_good = $bcrypt->verify($password, $hash);
			*/
			$expiration_timestamp = strtotime("+1 Day");
			$data['reset_password'] = rand(1000000,99999999);
			$data['is_password_reset_enabled'] = "Y";
			$data['password_reset_expiration_gm_date_time'] = Library_DB_Util::time_to_gm_db_time($db_model->db, $expiration_timestamp);
			$db_model->save($user_id, $data);
			PasswordResetHelper::send_password_reset_email($user_id, $data['reset_password']);
			$obj_result->is_success = true;
		}

		// maybe add some sort of sleep
		print json_encode($obj_result);
	}

	public function update_password($user_id) {
		//Library_Auth_Common::set_post_allowed_request_callback(NULL);
		//Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();

		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		$data = null;
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data)) {
				$data = $obj_request_data->data;
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		//print_r($data);
		//$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		//print_r($data);

		$temp_data = $db_model->get($user_id);
		if (count($temp_data) > 0) {
			/*
			$hash = $temp_data['reset_password'];
			$password = $data->old_password;
			$bcrypt = new Bcrypt(5);
			$is_good = $bcrypt->verify($password, $hash);
			*/
			$expiration_timestamp = strtotime($temp_data['password_reset_expiration_gm_date_time']." utc");
			$time_now = time();
			//print_r($temp_data);
			//print_r($data);

			if ($temp_data['reset_password'] == $data->old_password && $temp_data['is_password_reset_enabled'] == "Y" && $time_now <= $expiration_timestamp) {
				$is_good = true;
			}

			if ($is_good) {
				//print "is good\n";
				//print "trying to change password\n";
				if ($data->change_password == $data->change_password2) {
					//print "updating\n";
					$bcrypt = new Bcrypt(5);
					$new_user_data = Array();
					$new_user_data['password'] = $bcrypt->hash($data->change_password);
					$new_user_data['is_password_reset_enabled'] = "N";
					$new_user_data['reset_password'] = "";
					$new_user_data['password_reset_expiration_gm_date_time'] = null;
					$numb_affected = $db_model->save($user_id,$new_user_data);
					if ($numb_affected == 1) {
						$obj_result->is_success = true;
					}
				}
			}
		}
		print json_encode($obj_result);
	}




}
?>
