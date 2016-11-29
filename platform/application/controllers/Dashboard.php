<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

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
		/*
		Library_Auth_Common::check_allowed_request();
		print BackEnd_PasswordResets_View::build_html();
		*/

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

	public function get_dashboard_data() {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();

		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		$data = $db_model->get($user_id);
		$obj_result->user_data = Array();
		if (is_array($data) && count($data) > 0) {
			//print_r($data);
			$obj_result->is_success = true;
			$data = DB_Users::filter_sensitive_data($data);
			$obj_result->user_data = $data;

			$db_wallet_model = new DB_WalletLogs();
			$obj_result->level_income_frozen_total = $db_wallet_model->get_total_frozen($user_id, DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID);
			$obj_result->daily_growth_frozen_total = $db_wallet_model->get_total_frozen($user_id, DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID);
			$obj_result->daily_bonus_earning_frozen_total = $db_wallet_model->get_total_frozen($user_id, DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID);
			$obj_result->task_earning_frozen_total = $db_wallet_model->get_total_frozen($user_id, DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID);
			$phr_model = new DB_PHRequests();
			$ghr_model = new DB_GHRequests();
			
			//$obj_result->total_ph = $phr_model->get_total_ph($user_id);
			$obj_result->total_gh = $ghr_model->get_total_gh($user_id);
		}
		//print_r($obj_result);
	
		print json_encode($obj_result);
	}

	public function update_daily_bonus_earnings_from_bonus_game() {
		Library_Auth_Common::check_allowed_request();

		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		$data = $db_model->get($user_id);
		//print_r($data);
		if (is_array($data) && count($data) > 0) {
			if ($data['bonus_game_amount'] > 0) {
				// need to do multiple  updates here
				$db_wallet_logs = new DB_WalletLogs();

				$bonus_game_amount = $data['bonus_game_amount'];
			

				$temp_data = Array();
				$temp_data['user_id'] = $user_id;
				$temp_data['amount'] = 0;
				$temp_data['wallet_type_id'] = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
				$temp_data['log_type_id'] = DB_WalletLogs::$DAILY_BONUS_GAME_DEPOSIT_LOG_TYPE_ID;
				$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs->db);
				$temp_data['reference_id'] = 0;
				$wallet_log_id = 0;
				if ($db_wallet_logs->save(0, $temp_data) > 0) {
					$wallet_log_id = $db_wallet_logs->get_last_insert_id();
				}

				$set_sql = "set ";
				$set_sql .= "".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance = ".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance + ".DB_Users::$TABLE_NAME.".bonus_game_amount, ";
				$set_sql .= "".DB_Users::$TABLE_NAME.".bonus_game_amount = 0, ";
				if ($wallet_log_id > 0) {
					$set_sql .= "".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$db_model->db->escape("N").", ";
					$set_sql .= "".DB_WalletLogs::$TABLE_NAME.".amount = $bonus_game_amount, ";
					//$set_sql .= "".DB_WalletLogs::$TABLE_NAME.".amount = ".DB_Users::$TABLE_NAME.".bonus_game_amount, ";
				}



				$set_sql = rtrim($set_sql,", ");

				
				$arr_criteria = Array();
				$arr_criteria['bonus_game_amount']['>'] = 0;
				$arr_criteria['id'] = $user_id;
				$sql_where = DB_Users::get_sql_where($arr_criteria);


				if ($wallet_log_id > 0) {
					$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".id = ".$db_model->db->escape($wallet_log_id)."";
					$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$this->db->escape("Y")."";
				}


				$sql_where = "where $sql_where";
				
				$sql_tables = DB_Users::$TABLE_NAME.", ";
				if ($wallet_log_id > 0) {
					$sql_tables .= "".DB_WalletLogs::$TABLE_NAME.", ";
				}
				$sql_tables = rtrim($sql_tables,", ");

				$sql = "update $sql_tables $set_sql $sql_where";
				//print "sql is $sql\n";
				
				if ($wallet_log_id > 0) {
					$query = $db_model->db->query($sql);
					$number_affected = $db_model->get_number_affected();
					if ($number_affected == 2) {
						$obj_result->is_success = true;
					}
				}
			}
		}
		//print_r($obj_result);
	
		print json_encode($obj_result);
	}

	public function check_do_bonus_game() {
		Library_Auth_Common::check_allowed_request();

		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;
		$obj_result->is_do_game = false;
		$obj_result->bonus_amount = 0;

		$user_id = LoginAuth::get_session_user_id();

		$db_model = new DB_Users();
		$data = $db_model->get($user_id);
		$obj_result->user_data = Array();
		if (is_array($data) && count($data) > 0) {
			$obj_result->is_success = true;
			$last_bonus_game_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$last_gm_date_time = $data['last_bonus_game_gm_date_time'];
			//$last_gm_date_time = null;
			if (is_null($last_gm_date_time)) {
				// check if confirmed and if is_activated
				//if ($data['is_verified'] == 'Y' && $data['is_activated'] == 'Y') {
				if ($data['is_done_registration_process'] == 'Y') {
					$obj_result->is_do_game = true;
					// do sql statement update go from null to not null
					// if num game is >= 1 then do game
					$temp_data = Array();
					$temp_data['bonus_game_amount'] = rand(10,99)/100;
					$temp_data['last_bonus_game_gm_date_time'] = $last_bonus_game_gm_date_time;
					$obj_result->bonus_amount = $temp_data['bonus_game_amount'];
					// update refresh
					$db_model->save($user_id,$temp_data);
				}
			} else {
				$next_time = strtotime("+1 Day", strtotime($last_gm_date_time." UTC"));
				$time_now = time();
				// should reall do sql statement
				if ($time_now >= $next_time) {
					// update data if num_affect >= 1 then do game
					$db_model = new DB_Users();
					$data = $db_model->get($user_id);

					$bonus_game_amount = rand(10,99)/100;

					$set_sql = "set ";
					$set_sql .= "".DB_Users::$TABLE_NAME.".bonus_game_amount = $bonus_game_amount, ";
					$set_sql .= "".DB_Users::$TABLE_NAME.".last_bonus_game_gm_date_time = ".$db_model->db->escape($last_bonus_game_gm_date_time).", ";

					$set_sql = rtrim($set_sql,", ");

					
					$cutoff_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-1 Day"));
					$arr_criteria = Array();
					$arr_criteria['last_bonus_game_gm_date_time']['>='] = $cutoff_gm_date_time;
					$arr_criteria['id'] = $user_id;
					$sql_where = DB_Users::get_sql_where($arr_criteria);

					$sql_where = "where $sql_where";
					
					$table_name = DB_Users::$TABLE_NAME;
					$sql = "update $table_name $set_sql $sql_where";
					//print "sql is $sql\n";
					$query = $db_model->db->query($sql);
					$number_affected = $db_model->get_number_affected();
					if ($number_affected == 1) {
						$obj_result->is_do_game = true;
						$obj_result->bonus_amount = $bonus_game_amount;
						$obj_result->is_success = true;
					}
				}
			}
		}
		//print_r($obj_result);
	
		print json_encode($obj_result);
	}
}
?>
