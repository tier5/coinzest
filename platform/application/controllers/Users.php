<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

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

		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_Users_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//$this->load->view('welcome_message');
	}

	public function edit($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_Users_View::build_edit_html($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
		//$this->load->view('welcome_message');
	}

	

	public function get_user_manager($id) {
		Library_Auth_Common::check_allowed_request();
		$db_model = new DB_Users();
		$obj_result = new stdclass();
		$obj_resul->arr_data = Array();
		$obj_result->is_success = false;

		if ($id > 0) {
			$arr_data = $db_model->arr_get_user_manager($id);

			$obj_result->arr_data = $arr_data;
			if (count($arr_data) > 0) {
				unset($arr_data['password']);
				unset($arr_data['otp']);
				unset($arr_data['btc_address']);
				unset($arr_data['ph_completed']);
				$obj_result->is_success = true;
			} else {
				$obj_result->is_success = false;
			}
		}
		print json_encode($obj_result);
	}

	public function get_user_data() {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		include_once($_SERVER['DOCUMENT_ROOT']."/application/controllers/Myprofile.php");
		$my_profile = new MyProfile();
		//$obj_result->is_success = true;
		print $my_profile->get_data();
		//print_r($obj_result->data);
		//exit(0);
		//print json_encode($obj_result);
	}

	public function get($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			$db_model = new DB_Users();
			$arr_data = $db_model->get($id);
			print json_encode($arr_data);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
	}

	public function save($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			$data = $_POST['data'];


			if ($data['password'] != "")  {
				$bcrypt = new Bcrypt(5);
				$data['password'] = $bcrypt->hash($data['password']);
			} else {
				unset($data['password']);
			}
			//$isGood = $bcrypt->verify('password', $hash);

			$db_model = new DB_Users();
			$db_model->save($id,$data);
			//$arr_data = $db_model->get($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
	}

	public function delete($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			$db_model = new DB_Users();
			$db_model->delete($id);
			//$arr_data = $db_model->get($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
	}

	public function get_arr_search_results() {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$search = "";
		$limit = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
				$arr_criteria['search'] = $search;
			}
			if (isset($obj_request_data->limit)) {
				$limit = $obj_request_data->limit;
				if (is_numeric($limit)) {
					$arr_criteria['limit'] = $limit;
				}
			}
		}

		if (LoginAuth::is_user_have_access(true)) {
			$db_model = new DB_Users();
			//$arr_criteria = Array();
			//$arr_criteria['segment_id'] = $segment_id;
			
			/*
			if (isset($_POST['search']) && $_POST['search'] != "") {
				$arr_criteria['search'] = $_POST['search'];
			}
			if (isset($_POST['limit']) && $_POST['limit'] != "") {
				if (is_numeric($_POST['limit'])) {
					$arr_criteria['limit'] = $_POST['limit'];
				}
				//$arr_criteria['search'] = $_POST['search'];
			}
			*/
			$arr_criteria['login']['<>'] = "";

			//print_r($arr_criteria);
			//$arr_criteria['limit'] = 200;
			$arr_data = $db_model->search($arr_criteria);
			$obj_result = new stdclass();
			$obj_result->arr_data = $arr_data;
			
			print json_encode($obj_result);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
	}

}
