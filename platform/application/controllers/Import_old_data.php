<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import_old_data extends CI_Controller {

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
		print BackEnd_ImportOldData_View::build_html();

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


	public function show_old_data($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		print BackEnd_ImportOldData_View::build_html();

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

	public function show_all_old_data_temp() {
		Library_Auth_Common::check_allowed_request();

		if (LoginAuth::is_user_have_admin_access())  {
			print BackEnd_ImportOldData_View::build_get_all_old_data_temp_html();
			
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
	}

	public function get_all_old_data_temp() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if (!LoginAuth::is_user_have_admin_access())  {
			//print BackEnd_Login_View::build_no_access_html();

		} else {
			$obj_request_data = Common::load_request_data();
			if (is_object($obj_request_data)) {
				if (isset($obj_request_data->search)) {
					$search = $obj_request_data->search;
				}
			}

			$data = Array();

			$db_model = new DB_Users();
			$db_ut_model = new DB_UserTemp();

			$field_names = DB_UserTemp::get_field_names();
			$this->db->select($field_names . ", ".DB_Users::$TABLE_NAME.".ID as user_ID, ".DB_Users::$TABLE_NAME.".login")->from(DB_UserTemp::$TABLE_NAME);
			$this->db->join(DB_Users::$TABLE_NAME, "".DB_UserTemp::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".ID");
			$this->db->order_by(DB_UserTemp::$TABLE_NAME.".id", "asc");
			$query = $this->db->get();
			//print $this->get_last_query();
			//print_r($this->db->error());
			//exit(0);
			$arr_data = $query->result_array();
			//print_r($arr_data);

			$obj_result->is_success = true;
			$obj_result->arr_data = $arr_data;
		}
		print json_encode($obj_result);
	}

	function save() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data->stored_data)) {
				$stored_data = $obj_request_data->data->stored_data;
			}
		}
		//print_r($obj_request_data);

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();

		$db_model = new DB_Users();
		$db_ut_model = new DB_UserTemp();
		
		//print "user id is $user_id\n";
		//$data = $db_model->get($user_id);
		//print "stored data is $stored_data\n";

		$data['user_id'] = $user_id;
		$data['stored_data'] = $stored_data;
		$data['is_fields_locked'] = "Y";
		$number_affected = $db_ut_model->save(0,$data);
		//print "number affected is $number_affected<br>\n";

		if ($number_affected == -1) {
			$arr_criteria = Array();
			$arr_criteria['user_id'] = $user_id;
			//print "i nhere\n";
			$temp_data = $db_ut_model->search($arr_criteria);
			//print "i nhere\n";
			//print_r($temp_data);
			if (count($temp_data) == 1) {
				
				$data = Array();
				$data['user_id'] = $user_id;
				$data['stored_data'] = $stored_data;
				$data['is_fields_locked'] = "Y";
			

	
				$arr_criteria = Array();
				$is_dev_server = Registry::get("is_dev_server");
				if (!$is_dev_server) {
					$arr_criteria['is_fields_locked'] = "N";
				}
				$arr_criteria['user_id'] = $user_id;
				

				$number_affected = $db_ut_model->update($data,$arr_criteria);
				//print "number affected is $number_affected<br>\n";
				if ($number_affected == 1) {
					// race condition
					$user_data = Array();
					$user_data['is_have_old_data'] = "Y";
					$db_model->save($user_id, $user_data);
					$obj_result->is_success = true;
				}
			}
			
		} else if ($number_affected == 1) {
			// race condition
			$user_data = Array();
			$user_data['is_have_old_data'] = "Y";
			$db_model->save($user_id, $user_data);
			$obj_result->is_success = true;
		}

		print json_encode($obj_result);
	}

	function get_stored_data($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}

		if (LoginAuth::is_user_have_admin_access() && $user_id > 0)  {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}
		


		$data = Array();

		$db_model = new DB_Users();
		$db_ut_model = new DB_UserTemp();
		
		//print "user id is $user_id\n";
		$data = $db_model->get($user_id);

		if (count($data) > 0) {
			$user_temp_data = $db_ut_model->get_by_user_id($user_id);
			if (count($user_temp_data) > 0) {
				if ($user_temp_data['stored_data'] != "") {
					print $user_temp_data['stored_data'];
					exit(0);
				}
			}
		}
		
			
		$obj_result->is_success = true;
		$obj_result->data = $data;
		print json_encode($obj_result);
	}
	
	function get_data($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}

		if (LoginAuth::is_user_have_admin_access() && $user_id > 0)  {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}
		


		$data = Array();

		$db_model = new DB_Users();
		$db_ut_model = new DB_UserTemp();
		
		//print "user id is $user_id\n";
		$data = $db_model->get($user_id);

		if (count($data) > 0) {
			$data = DB_Users::filter_sensitive_data($data);
			$referral_id = $data['referral_id'];
			if ($referral_id > 0) {
				$sponsor_data = $db_model->get($referral_id);
				
				$sponsor_data = DB_Users::filter_sensitive_data($sponsor_data);
				$obj_result->sponsor_data = $sponsor_data;
			}
			$user_temp_data = $db_ut_model->get_by_user_id($user_id);
			if (count($user_temp_data) == 0) {
				$user_temp_data = Array();
				$user_temp_data["is_have_income_level_gh_status_proof_image"] = "N";
				$user_temp_data["is_have_daily_growth_status_proof_image"] = "N";
				$user_temp_data["is_have_gh_request_available_balance_proof_image"] = "N";
				$user_temp_data["is_have_gh_history_proof_image"] = "N";
				$user_temp_data["is_have_ph_history_proof_image"] = "N";
				$user_temp_data['is_fields_locked'] = "N";
			}
			$obj_result->user_temp_data = $user_temp_data;
		}
		
			
		$obj_result->is_success = true;
		$obj_result->data = $data;
		print json_encode($obj_result);
	}






}
?>
