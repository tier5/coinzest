<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dailytasks extends CI_Controller {

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

	public function manage() {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		print BackEnd_DailyTasks_View::build_manage_html();
	}
	//tier5 llc daily task pending list generation
	public function show_pending_users_task() {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			print BackEnd_DailyTasks_View::build_approve_clients_html();
		}
	}
	public function needs_approval_list() {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		print BackEnd_DailyTasks_View::build_need_approvals_user_daily_tasks_html();
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

	// is past month will be temporary
	public function choose_task($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		$is_path_month = false;
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
			if (isset($obj_request_data->is_previous_month)) {
				$is_past_month = $obj_request_data->is_previous_month;
			}
		}

		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();

		$user_id = LoginAuth::get_session_user_id();
	
		$round_id = 0;
		$arr_criteria = Array();
		$arr_criteria['id'] = $id;
		$db_daily_tasks = new DB_DailyTasks();
		$daily_tasks_data = $db_daily_tasks->search($arr_criteria);
		if (count($daily_tasks_data) > 0) {
			$round_id = $daily_tasks_data[0]['current_round_id'];
		}
		//print_r($daily_tasks_data);

		if ($id > 0 && count($daily_tasks_data) > 0) {

			$db_model = new DB_UserDailyTasks();
			$data = Array();
			$data['user_id'] = $user_id;
			$data['daily_task_id'] = $id;
			if ($is_past_month) {
				$year = gmdate("Y");
				$month = gmdate("n")-1;
				if ($month == 0) {
					$month = 12;
					$year -= 1;
				}
				if ($month <= 9) {
					$month = "0" . $month;
				}
			} else {
				$year = gmdate("Y");
				$month = gmdate("m");
			}
			$data['year_month'] = "$year-$month";


			
			$data['round_id'] = $round_id;
				
			$number_affected = $db_model->save(0, $data);
			//print_r($data);
			//print $db_model->get_last_query();
			if ($number_affected > 0) {
				$obj_result->is_success = true;
				$db_user_daily_tasks = new DB_UserDailyTasks();
				$db_model = new DB_DailyTasks();
				//$arr_active_daily_tasks_data = $db_user_daily_tasks->get_all_user_active_daily_tasks_for_current_round($user_id);
				$arr_active_daily_tasks_data = $db_user_daily_tasks->get_all_user_active_daily_tasks_for_current_round_not_submitted($user_id);
				//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round($user_id);
				$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round_not_submitted($user_id);

				$obj_result->arr_active_daily_tasks_data = $arr_active_daily_tasks_data;
				$obj_result->number_active_daily_tasks = $number_active_daily_tasks;
			}
			$data = Array();
		}

		print json_encode($obj_result);

	}

	// get user_data tasks
	public function get_all_needs_approval() {
		Library_Auth_Common::check_allowed_request();
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
		
		$db_user_daily_tasks = new DB_UserDailyTasks();

		//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round_month_year($user_id);

		//$arr_active_daily_tasks_data = $db_user_daily_tasks->get_all_user_active_daily_tasks_for_current_round($user_id);
		$arr_data = $db_user_daily_tasks->get_all_needs_approval();
		//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round($user_id);
		//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round_not_submitted($user_id);

		//$obj_result->arr_active_daily_tasks_data = $arr_active_daily_tasks_data;
		//$obj_result->number_active_daily_tasks = $number_active_daily_tasks;

		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;

		print json_encode($obj_result);
	}

	// get user_data tasks
	public function get_user_data() {
		Library_Auth_Common::check_allowed_request();
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
		
		$db_user_daily_tasks = new DB_UserDailyTasks();

		//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round_month_year($user_id);

		//$arr_active_daily_tasks_data = $db_user_daily_tasks->get_all_user_active_daily_tasks_for_current_round($user_id);
		$arr_active_daily_tasks_data = $db_user_daily_tasks->get_all_user_active_daily_tasks_for_current_round_not_submitted($user_id);
		//$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round($user_id);
		$number_active_daily_tasks = $db_user_daily_tasks->get_user_number_of_active_tasks_for_current_round_not_submitted($user_id);

		$obj_result->arr_active_daily_tasks_data = $arr_active_daily_tasks_data;
		$obj_result->number_active_daily_tasks = $number_active_daily_tasks;

		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;

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


}
?>
