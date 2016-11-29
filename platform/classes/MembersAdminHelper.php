<?

class MembersAdminHelper {


	// adjust wallet log and user account balance at the same time
	public function adjust_wallet_by_name($wallet_name, $adjust_user_id, $amount, $adjust_reason = "", $log_type_id = "", $reference_id = "", $additional_params = null) {
		$search = "";

		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$is_available = true;
		$available_gm_create_date_time = null;
		$is_release_part_of_funds_on_ph_complete = false;
		if (!is_null($additional_params) && is_array($additional_params)) {
			if (isset($additional_params['is_release_part_of_funds_on_ph_complete'])) {
				if ($additional_params['is_release_part_of_funds_on_ph_complete']) {
					$is_release_part_of_funds_on_ph_complete = true;
				} else {
					$is_release_part_of_funds_on_ph_complete = false;
				}
			}
			if (isset($additional_params['is_available'])) {
				$is_available = $additional_params['is_available'];
			}
			if (isset($additional_params['available_gm_create_date_time'])) {
				$available_gm_create_date_time = $additional_params['available_gm_create_date_time'];
			}
		}
		//print_r($additional_params);

		$db_user_model = new DB_Users();

		$wallet_type_id = 0;
		if ($wallet_name == 'task_earnings' ) {
			$wallet_type_id = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == 'level_income' ) {
			$wallet_type_id = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
		} else if ($wallet_name == 'daily_bonus_earnings' ) {
			$wallet_type_id = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == 'daily_growth' ) {
			$wallet_type_id = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
		}

		if ($log_type_id == "") {
			$log_type_id = DB_WalletLogs::$ADMIN_ADJUSTMENT_LOG_TYPE_ID;
		}

	
		//print_r($data);
		//print "wallet type id is $wallet_type_id\n";
		//print "wallet type id is $wallet_type_id and adjust user id is $adjust_user_id\n";
		if ($adjust_user_id > 0 && $wallet_type_id > 0 && $log_type_id != "" && $log_type_id > 0 && is_numeric($log_type_id) && is_numeric($amount)) {

			//print "in here\n";
			
			$wallet_log_id = 0;
			$db_wallet_logs_model = new DB_WalletLogs();

			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();



			// two of these could get created
			$temp_data = Array();
			if ($reference_id != "" && is_numeric($reference_id)) {
			} else {
				$reference_id = 0;
			}
			$temp_data['user_id'] = 0;
			$temp_data['amount'] = 0;
			$temp_data['wallet_type_id'] = $wallet_type_id;
			$temp_data['log_type_id'] = $log_type_id;
			if ($adjust_reason != "") {
				$temp_data['custom_remark'] = $adjust_reason;
			}
			$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
			//print_r($temp_data);
			$number_affected = 0;
			//print_r($temp_data);
			$number_affected = $db_wallet_logs_model->save(0, $temp_data);
			$wallet_log_id = 0;
			if ($number_affected > 0) {
				$wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
			}
			//print "wallet log id is $wallet_log_id\n";

			//$temp_data['suspend_reason'] = $data['suspend_reason'];

			if ($wallet_log_id > 0) {
				$sql_set = "set ";

				// do this if available
				if ($is_available) {
					if ($wallet_name == 'task_earnings' ) {
						$sql_set .= "".DB_Users::$TABLE_NAME.".task_earning_balance = ".DB_Users::$TABLE_NAME.".task_earning_balance + ".$db_user_model->db->escape($amount).", ";
					} else if ($wallet_name == 'level_income' ) {
						$sql_set .= "".DB_Users::$TABLE_NAME.".level_income_balance = ".DB_Users::$TABLE_NAME.".level_income_balance + ".$db_user_model->db->escape($amount).", ";
					} else if ($wallet_name == 'daily_bonus_earnings' ) {
						$sql_set .= "".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance = ".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance + ".$db_user_model->db->escape($amount).", ";
					} else if ($wallet_name == 'daily_growth' ) {
						$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$db_user_model->db->escape($amount).", ";
					}
				}

				$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_user_model->db->escape($now_gm_date_time).", ";

				if (!$is_available) {
					$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_wallet_logs_model->db->escape("N").", ";
					if (!is_null($available_gm_create_date_time)) {
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".available_gm_create_date_time = ".$db_wallet_logs_model->db->escape($available_gm_create_date_time).", ";
					}
					if ($is_release_part_of_funds_on_ph_complete) {
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_release_part_of_funds_on_ph_complete  = ".$db_wallet_logs_model->db->escape("Y").", ";
					}
				}
				$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".reference_id = ".$db_wallet_logs_model->db->escape($reference_id).", ";
				$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".user_id = ".$db_wallet_logs_model->db->escape($adjust_user_id).", ";
				$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";
				$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".amount = ".$db_wallet_logs_model->db->escape($amount).", ";
				

				$sql_set = rtrim($sql_set,", ");

				$sql_where .= "".DB_WalletLogs::$TABLE_NAME.".id = ".$db_wallet_logs_model->db->escape($wallet_log_id)."";
				$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$db_wallet_logs_model->db->escape("Y")."";
				$sql_where .= " and ".DB_Users::$TABLE_NAME.".id = ".$db_wallet_logs_model->db->escape($adjust_user_id)."";

				$sql_where = "where $sql_where";
				
				$sql_tables = "";

				$sql_tables .= "".DB_WalletLogs::$TABLE_NAME.", ";
				$sql_tables .= DB_Users::$TABLE_NAME.", ";

				$sql_tables = rtrim($sql_tables,", ");

				$sql = "update $sql_tables $sql_set $sql_where";
				//print "sql is $sql\n";

				$query = $db_user_model->db->query($sql);

				$number_affected = $db_user_model->get_number_affected();
				//print "number affected is $number_affected\n";

				if ($number_affected == 2) {
					$obj_result->is_success = true;
				}
			}
		}
		return $obj_result;
	}

}
?>
