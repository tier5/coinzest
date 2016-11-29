<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userdailytasks extends CI_Controller {

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
		print BackEnd_DailyTasks_View::build_html();
	}

	public function submit_link_proof($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
		}

		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();

		$user_id = LoginAuth::get_session_user_id();
		$db_user_daily_tasks = new DB_UserDailyTasks();
		$db_model = new DB_DailyTasks();

		
		$user_daily_task_data = $db_user_daily_tasks->get($id);
		if (count($data) > 0 && $data['user_id'] = $user_id && $id > 0) {
	
				
			$user_daily_task_data = Array();
			$user_daily_task_data['proof_link'] = $data['submit_link'];
			$user_daily_task_data['is_have_user_proof_link'] = "Y";
			$arr_criteria = Array();
			$arr_criteria['id'] = $id;
			$arr_criteria['is_have_user_proof_link'] = "N";
			//print_r($data);
			$number_affected = $db_user_daily_tasks->update($user_daily_task_data, $arr_criteria);
			//print $db_model->get_last_query();
			if ($number_affected > 0) {
				$obj_result->is_success = true;
			}
			$data = Array();
			
		}

		print json_encode($obj_result);

	}

	/*
	public function manage() {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		print BackEnd_DailyTasks_View::build_manage_html();
	}

	public function edit() {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		print BackEnd_DailyTasks_View::build_edit_html();
	}

	public function get($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$db_model = new DB_DailyTasks();
		if ($id > 0) {
			$data = $db_model->get($id);
			
			$obj_result->is_success = true;
			$obj_result->data = $data;
		}

		print json_encode($obj_result);
	}

	public function save($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
		}

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();

		$user_id = LoginAuth::get_session_user_id();
	
		if (count($data) > 0 && $id >= 0) {
			unset($data['gm_created']);
			unset($data['gm_password']);
			foreach($data as $key => $value) {
				if ($key == "description") {
				} else if ($key == "title") {
				} else if ($key == "long_description") {
				} else if ($key == "external_url") {
				} else {
					unset($data[$key]);
				}
			}

			$db_model = new DB_DailyTasks();
				
			$number_affected = $db_model->save($id, $data);
			//print $db_model->get_last_query();
			if ($number_affected > 0) {
				$obj_result->is_success = true;
			}
			$data = Array();
			
		}

		print json_encode($obj_result);

	}



	// get daily tasks
	public function get_data() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$db_model = new DB_DailyTasks();
		$arr_criteria = Array();
		$arr_criteria['status_id'] = DB_DailyTasks::$ACTIVE_STATUS_ID;
		$arr_data = $db_model->search($arr_criteria);
		
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;

		print json_encode($obj_result);
	}
	*/


}
?>
