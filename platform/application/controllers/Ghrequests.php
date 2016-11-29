<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GHRequests extends CI_Controller {

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
		/*
		$db_model = new DB_GHRequests();
		$db_model->create_tmp_gh();
		$db_model = new DB_GHRequests();
		$db_model->match_active_gh_requests();
		exit(0);
		*/

		print BackEnd_GHRequests_View::build_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	public function get_user_info() {
		Library_Auth_Common::check_allowed_request();
		$user_id = $_SESSION['user_id'];
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		
		$db_model = new DB_Users();
		$data = $db_model->get($user_id);

		$obj_result->gh_balance = 0;
		$obj_result->btc_address = "";
		if (count($data) > 0) {
			$obj_result->gh_balance = $data['gh_balance'];
			$obj_result->btc_address = $data['btc_address'];
			$obj_result->daily_bonus_earning_balance = $data['daily_bonus_earning_balance'];
			$obj_result->daily_growth_balance = $data['daily_growth_balance'];
			$obj_result->task_earning_balance = $data['task_earning_balance'];
			$obj_result->level_income_balance = $data['level_income_balance'];
		}

		//print_r($data);
		//print_r($obj_result);
		print json_encode($obj_result);
	}

	public function list_all_gh_requests() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		if (LoginAuth::is_user_have_admin_access()) {
			print BackEnd_GHRequests_View::build_list_all_gh_requests_html();
			//MatchedRequest::match_all_gh_requests();
			exit(0);
		}
		print BackEnd_Login_View::build_no_access_html();
	}


	public function get_all_gh_requests() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		if (LoginAuth::is_user_have_admin_access()) {
			$data = Array();
			
			$obj_result->is_success = true;

			$arr_search_criteria = Array();

			$db_model = new DB_GHRequests();

			$field_names = $db_model->get_field_names();

			$db_model->db->select($field_names.",  ".DB_Users::$TABLE_NAME.".is_have_additional_btc_addresses, ".DB_Users::$TABLE_NAME.".is_approved_for_matching, ".DB_Users::$TABLE_NAME.".login, ".DB_Users::$TABLE_NAME.".country")->from(DB_GHRequests::$TABLE_NAME);
			$db_model->db->join(DB_Users::$TABLE_NAME,DB_Users::$TABLE_NAME.".id = ".DB_GHRequests::$TABLE_NAME.".user_id");
			$this->db->where(DB_GHRequests::$TABLE_NAME.".status_id",DB_GHRequests::$ACTIVE_STATUS_ID);

			$db_model->db->order_by(DB_GHRequests::$TABLE_NAME.".gm_created", "asc");
			$data = $db_model->db->get();
			//print $db_model->get_last_query();
			//print_r($db_model->db->errors());
			$arr_data = $data->result_array();

			//$arr_data = $db_model->search($arr_search_criteria);
			$obj_result->is_success = true;
			$obj_result->arr_data = $arr_data;
		}
		print json_encode($obj_result);
	}

	public function get_cancelled_and_completed_ghrequests_for_user($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
		}


		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}



		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = GHRequest::get_arr_gh_requests_for_user($user_id, true);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		print json_encode($obj_result);
	}

	public function get_ghrequests_for_user($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
		}


		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}



		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = GHRequest::get_arr_gh_requests_for_user($user_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		print json_encode($obj_result);
	}

	public function approve_user_for_gh_matching($user_id = 0) {
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
		}


		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}

		$db_model = new DB_Users();
		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {

			$arr_criteria = Array();
			$arr_criteria['id'] = $user_id;

			$temp_data = Array();
			$temp_data["is_approved_for_matching"] = "Y";
			$db_model->update($temp_data, $arr_criteria);
			//print "result is $result\n";
			$obj_result->is_success = true;
		}
		print json_encode($obj_result);
	}

	public function disapprove_user_for_gh_matching($user_id = 0) {
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
		}


		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}

		$db_model = new DB_Users();
		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {

			$arr_criteria = Array();
			$arr_criteria['id'] = $user_id;

			$temp_data = Array();
			$temp_data["is_approved_for_matching"] = "N";
			$db_model->update($temp_data, $arr_criteria);
			//print "result is $result\n";
			$obj_result->is_success = true;
		}
		print json_encode($obj_result);
	}

	// cancel only un matched gh
	// do not return gh
	function cancel_gh_simple($gh_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$db_gh_model = new DB_GHRequests();
		//UPDATE tbl1 SET Status = 'Finished' WHERE NOT EXISTS (SELECT id FROM tbl1_temp WHERE tbl1.id = tbl1_temp.id)
		if (LoginAuth::is_user_have_admin_access() && $gh_id > 0) {
			// cancel only if matchedrequests.gh_request_id not exist for this gh

			$sql_set .= "".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$CANCELLED_STATUS_ID.", ";

			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
			


			$sql_set .= DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_gh_model->db->escape($now_gm_date_time).", ";

			$sql_set = rtrim($sql_set,", ");

			// do where
			$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".$db_gh_model->db->escape($gh_id)."";

			$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";

			// if no matches then allow cancel
			$sql_where .= " and NOT EXISTS (select ".DB_MatchedRequests::$TABLE_NAME.".ghrequest_id from ".DB_MatchedRequests::$TABLE_NAME." where ".DB_MatchedRequests::$TABLE_NAME.".ghrequest_id = ".DB_GHRequests::$TABLE_NAME.".id and ".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID." )";


			$sql_tables = "".DB_GHRequests::$TABLE_NAME."";
			$sql = "update $sql_tables set $sql_set where $sql_where";
			//print "sql is $sql\n";
			//exit(0);
			$query = $db_gh_model->db->query($sql);
			$number_affected = $db_gh_model->db->affected_rows();
			if ($number_affected == 1) {
				$obj_result->is_success = true;
			}
		}
		print json_encode($obj_result);
	}

	function create_ghrequest() {
		Library_Auth_Common::check_allowed_request();

		$gh_amount = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data)) {
				$arg_data = $obj_request_data->data;
			}
		}
		if ($arg_data->level_income_amount == "") {
			$arg_data->level_income_amount = 0;
		}

		if ($arg_data->task_earning_amount == "") {
			$arg_data->task_earning_amount = 0;
		}
		if ($arg_data->daily_bonus_amount  == "") {
			$arg_data->daily_bonus_amount  = 0;
		}
		if ($arg_data->daily_growth_amount  == "") {
			$arg_data->daily_growth_amount  = 0;
		}
		
		$gh_amount = $arg_data->level_income_amount + $arg_data->task_earning_amount + $arg_data->daily_bonus_amount + $arg_data->daily_growth_amount;

		$user_id = $_SESSION['user_id'];

		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_invalid_gh_amount = false;
		$obj_result->is_twenty_or_multiple_of_10 = false;
		$obj_result->is_over_max = false;
		$obj_result->is_not_enough_balance = false;
		$obj_result->is_able_to_allocate = false;
		$obj_result->is_not_enough_daily_bonus_balance = false;
		$obj_result->is_not_enough_level_income_balance = false;
		$obj_result->is_not_enough_daily_growth_balance = false;
		$obj_result->is_not_enough_task_earning_balance = false;
		$obj_result->is_account_suspended = false;
		$obj_result->is_no_active_ph = false;
		$obj_result->is_have_active_gh = false;

		$db_model = new DB_Users();
		$data = $db_model->get($user_id);


		$db_phr_model = new DB_PHRequests();
		$db_ghr_model = new DB_GHRequests();

		
		if (LoginAuth::is_user_have_admin_access()) {
		} else {
		}
		if ($db_phr_model->is_user_have_active_ph($user_id)) {
		} else {
			$obj_result->is_no_active_ph = true;
			$obj_result->is_invalid_gh_amount = true;
		}
		PlatformLogs::log("user_have_active_ph", "this is first obj_result right after");
		PlatformLogs::log("user_have_active_ph", $obj_result);

		if ($data['status_id'] == DB_Users::$SUSPENDED_STATUS_ID) {
			$obj_result->is_account_suspended = true;
			$obj_result->is_invalid_gh_amount = true;
		}

		if ($gh_amount < 20) {
			$obj_result->is_invalid_gh_amount = true;
		}

		if ($gh_amount == 20) {
			$obj_result->is_twenty_or_multiple_of_10 = true;

		} else if ($gh_amount > 20) {
			//print "gh amount is float ".fmod($gh_amount, 10)." blah\n";
			if (fmod($gh_amount, 10) == 0) {
				$obj_result->is_twenty_or_multiple_of_10 = true;
			} else {
				$obj_result->is_invalid_gh_amount = true;
			}
			if ($gh_amount > 2000) {
				$obj_result->is_over_max = true;
				$obj_result->is_invalid_gh_amount = true;
			}
		} else {
			$obj_result->is_invalid_gh_amount = true;
		}


		// need to check if there is any active ph
		
		
		// check gh balance but shouldnt be in here
		/*
		if (count($data) > 0) {
			//print "in here\n";
			$gh_balance = $data['gh_balance'];
			if ($gh_amount > $gh_balance) {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_not_enough_balance = true;
			}
		}
		*/


		// check all wallets see if there is enough
		if (count($data) > 0) {
			if (is_numeric($arg_data->level_income_amount) && ($arg_data->level_income_amount <= $data['level_income_balance'])) {
			} else {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_not_enough_balance = true;
				$obj_result->is_not_enough_level_income_balance = true;
			}

			if (is_numeric($arg_data->task_earning_amount) && ($arg_data->task_earning_amount <= $data['task_earning_balance'])) {
			} else {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_not_enough_balance = true;
				$obj_result->is_not_enough_task_earning_balance = true;
			}
			if (is_numeric($arg_data->daily_bonus_amount) && ($arg_data->daily_bonus_amount <= $data['daily_bonus_earning_balance'])) {
			} else {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_not_enough_balance = true;
				$obj_result->is_not_enough_daily_bonus_balance = true;
			}
			if (is_numeric($arg_data->daily_growth_amount) && ($arg_data->daily_growth_amount <= $data['daily_growth_balance'])) {
			} else {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_not_enough_balance = true;
				$obj_result->is_not_enough_daily_growth_balance = true;

			}
		}

		if (!$obj_result->is_invalid_gh_amount) {
			$result = $db_ghr_model->is_user_have_active_gh($user_id);
			if ($result) {
				$obj_result->is_invalid_gh_amount = true;
				$obj_result->is_have_active_gh = true;
			}
		}

		PlatformLogs::log("user_have_active_ph", "this is obj_result inside create_ghrequests");
		PlatformLogs::log("user_have_active_ph", $obj_result);

		if (!$obj_result->is_invalid_gh_amount) {
			// when creating have to account for all 4 wallets
			$result = GHRequest::create_gh_request($user_id, $arg_data, $gh_amount);
			if ($result->is_success) {
				$obj_result->is_success = true;
				$data = $db_model->get($user_id);
				$obj_result->daily_bonus_earning_balance = $data['daily_bonus_earning_balance'];
				$obj_result->daily_growth_balance = $data['daily_growth_balance'];
				$obj_result->task_earning_balance = $data['task_earning_balance'];
				$obj_result->level_income_balance = $data['level_income_balance'];
				//MatchedRequest::match_all_gh_requests();

			} else {
				// grab data again and see if we hit race condition and cannot take funds out of any accounts
				$data = $db_model->get($user_id);
				if (count($data) > 0) {
					if (is_numeric($arg_data->level_income_amount) && ($arg_data->level_income_amount <= $data['level_income_balance'])) {
					} else {
						$obj_result->is_invalid_gh_amount = true;
						$obj_result->is_not_enough_balance = true;
						$obj_result->is_not_enough_level_income_balance = true;
					}

					if (is_numeric($arg_data->task_earning_amount) && ($arg_data->task_earning_amount <= $data['task_earning_balance'])) {
					} else {
						$obj_result->is_invalid_gh_amount = true;
						$obj_result->is_not_enough_balance = true;
						$obj_result->is_not_enough_task_earning_balance = true;
					}
					if (is_numeric($arg_data->daily_bonus_amount) && ($arg_data->daily_bonus_amount <= $data['daily_bonus_earning_balance'])) {
					} else {
						$obj_result->is_invalid_gh_amount = true;
						$obj_result->is_not_enough_balance = true;
						$obj_result->is_not_enough_daily_bonus_balance = true;
					}
					if (is_numeric($arg_data->daily_growth_amount) && ($arg_data->daily_growth_amount <= $data['daily_growth_balance'])) {
					} else {
						$obj_result->is_invalid_gh_amount = true;
						$obj_result->is_not_enough_balance = true;
						$obj_result->is_not_enough_daily_growth_balance = true;
					}
				}
			}
			$obj_result->is_able_to_allocate = $result->is_able_to_allocate;
		}


		print json_encode($obj_result);
	}
}
?>
