<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ChangePassword extends CI_Controller {

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
		print BackEnd_PasswordResets_View::build_html();


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

	public function update_password() {
		Library_Auth_Common::check_allowed_request();
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
		$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		//print_r($data);

		$temp_data = $db_model->get($user_id);
		if (count($temp_data) > 0) {
			$hash = $temp_data['password'];
			$password = $data->old_password;
			$bcrypt = new Bcrypt(5);
			$is_good = $bcrypt->verify($password, $hash);
			if ($is_good) {
				//print "is good\n";
				if ($data->change_password == $data->change_password2) {
					//print "updating\n";
					$bcrypt = new Bcrypt(5);
					$new_user_data = Array();
					$new_user_data['password'] = $bcrypt->hash($data->change_password);
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
