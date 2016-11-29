<?

class DailyMaintenance extends CI_Model {

	// need to add a field to table to prevent growths less than 24 hours that way more atomic
	function update_daily_growths() {
		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		// loop through all COMPLETED PH
		$cut_off_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-30 Days"));
		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;

		// add daily growths
		do {
			// probably should use lock
			// we do only for 30 days
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_PHRequests::$COMPLETED_STATUS_ID;
			$arr_criteria['status_id'][] = DB_PHRequests::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['gm_created']['>='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_phr_model->search($arr_criteria);
			print "last id is $last_id<br>\n";

			//print_r($arr_data);
			//exit(0);
			
			print "count data is ".count($arr_data)."<br>\n";
			for($n=0;$n<count($arr_data);$n++) {
				//print "n is $n<br>\n";
				$last_id = $arr_data[$n]['id'];
				//print_r($arr_data[$n]);
		
				// give as long as user last logged in past 24 hrs active
				$user_id = $arr_data[$n]['user_id'];


						// race condition
				$arr_user_data = $db_model->get($user_id);
				if (count($arr_user_data) > 0) {
					$last_request_timestamp = strtotime($arr_user_data['last_request_gm_date_time']." UTC");
					$is_user_accessed_within_24_hrs = false;

					if ($last_request_timestamp >= $yesterday_timestamp) {
						$is_user_accessed_within_24_hrs = true;
					}


					if ($is_user_accessed_within_24_hrs) {
						// lets add it to growth on this table
						
						// add 4% to growth
						// update phrequests table
						$amount = $arr_data[$n]['amount']*.04;

				
						// check if completed
						$db_phr_model->update_if_request_complete($arr_data[$n]['id']);
						

						// if completed add to wallet logs table and add to daily_growth wallet
						// active only increment phrequests

						// just assume active first

						$db_wallet_logs_model = new DB_WalletLogs();

						// race condition
						// insert daily growth for 1 day 4%
						$temp_data = Array();
						$temp_data['user_id'] = $arr_data[$n]['user_id'];
						//$temp_data['user_id'] = $user_id;
						$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
						$temp_data['reference_id'] = $arr_data[$n]['id'];
						$temp_data['reference_user_id'] = $arr_data[$n]['user_id'];
						$temp_data['is_pending_create'] = "N";
						/*
						// dont need this gets updated later
						if ($arr_data[$n]['status_id'] == DB_PHRequests::$COMPLETED_STATUS_ID) {
							$temp_data['is_available'] = "Y";
						} else {
							$temp_data['is_available'] = "N";
						}
						*/
						$temp_data['is_available'] = "N";

						$temp_data['amount'] = $amount;
						$temp_data['wallet_type_id'] = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
						$temp_data['log_type_id'] = DB_WalletLogs::$DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID;
						$temp_data['reference_id'] = $arr_data[$n]['id'];

						if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
						}

						print "in here\n";
						// if active then set internally

						// increment everday building up in phrequest for 30 days limit is set above
						$sql_set = "set ";
						$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + $amount, ";
						$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates + 1, ";
						$sql_set = rtrim($sql_set,", ");

						$arr_criteria = Array();
						$arr_criteria['id'] = $arr_data[$n]['id'];
						$arr_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
						$sql_where = DB_PHRequests::get_sql_where($arr_criteria);

						$sql_where = "where $sql_where";
						
						$sql_tables = DB_PHRequests::$TABLE_NAME.", ";
						$sql_tables = rtrim($sql_tables,", ");

						$sql = "update $sql_tables $sql_set $sql_where";
						//print "sql is $sql\n";
						$query = $db_phr_model->db->query($sql);

						$number_affected = $db_phr_model->get_number_affected();
						print "number affected is $number_affected\n";
						/*
						if ($number_affected == 0) {
							print "Number affected is $number_affected\n";
							print "we should be here because status id is complete\n";
							print_r($arr_data[$n]);
							//exit(0);
						}
						*/
						// here status is completed
						// we assume only run once per day and not more than one running process
						if ($number_affected == 0) {
							// if here we should be actually adding to wallet_log id
							// add growth
							// assume if above does not work
							$wallet_log_id = 0;

							// here we give ourselves growth one time only

							// here we give ourselves growth one time only
							// we set to available
							$sql_set = "set ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + $amount, ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates + 1, ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_phr_model->db->escape($now_gm_date_time).", ";

							$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_wallet_logs_model->db->escape("Y").", ";
							$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$db_model->db->escape($amount).", ";
							$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
							

							$sql_set = rtrim($sql_set,", ");

							$arr_criteria = Array();
							$arr_criteria['id'] = $arr_data[$n]['id'];
							$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
							$sql_where = DB_PHRequests::get_sql_where($arr_criteria);

							//$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".reference_id = ".$db_wallet_logs_model->db->escape($arr_data[$n]['id'])."";
							$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".reference_id = ".DB_PHRequests::$TABLE_NAME.".id";
							$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_wallet_logs_model->db->escape("N")."";
							$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".log_type_id = ".DB_WalletLogs::$DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID."";
							$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";




							$sql_where = "where $sql_where";
							
							$sql_tables = DB_PHRequests::$TABLE_NAME.", ";
							$sql_tables .= "".DB_WalletLogs::$TABLE_NAME.", ";
							$sql_tables .= DB_Users::$TABLE_NAME.", ";

							$sql_tables = rtrim($sql_tables,", ");

							$sql = "update $sql_tables $sql_set $sql_where";
							$query = $db_phr_model->db->query($sql);
							$number_affected = $db_phr_model->get_number_affected();
							print "Number affected for completed $number_affected\n";
							if ($number_affected == 3) {
							} else {
								// should send an email out if got here
								/*
								print "number affected is $number_affected\n";
								print "in here sql is $sql<br>\n";
								print "blah blah\n";
								exit(0);
								*/
							}
						}
					}
				}

			}
		} while (count($arr_data) > 0);
		Emailer::send_completed_daily_maintenance();
		print "blah done\n";
	}

	// need to add a field to table to prevent growths less than 24 hours that way more atomic
	function update_daily_growths_old() {
		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		// loop through all COMPLETED PH
		$cut_off_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-30 Days"));
		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;

		// add daily growths
		do {
			// probably should use lock
			// we do only for 30 days
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_PHRequests::$COMPLETED_STATUS_ID;
			$arr_criteria['status_id'][] = DB_PHRequests::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['gm_created']['>='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_phr_model->search($arr_criteria);
			//print "last id is $last_id<br>\n";

			//print_r($arr_data);
			//exit(0);
			
			for($n=0;$n<count($arr_data);$n++) {
				//print "n is $n<br>\n";
				$last_id = $arr_data[$n]['id'];
				//print_r($arr_data[$n]);
		
				// give as long as user last logged in past 24 hrs active
				$user_id = $arr_data[$n]['user_id'];
				$arr_user_data = $db_model->get($user_id);
				if (count($arr_user_data) > 0) {
					$last_request_timestamp = strtotime($arr_user_data['last_request_gm_date_time']." UTC");
					$is_user_accessed_within_24_hrs = false;

					if ($last_request_timestamp >= $yesterday_timestamp) {
						$is_user_accessed_within_24_hrs = true;
					}


					if ($is_user_accessed_within_24_hrs) {
						// lets add it to growth on this table
						
						// add 4% to growth
						// update phrequests table
						$amount = $arr_data[$n]['amount']*.04;

				
						// check if completed
						$db_phr_model->update_if_request_complete($arr_data[$n]['id']);
						

						// if completed add to wallet logs table and add to daily_growth wallet
						// active only increment phrequests

						// just assume active first

						// increment everday building up in phrequest for 30 days limit is set above
						$sql_set = "set ";
						$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + $amount, ";
						$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates + 1, ";
						$sql_set = rtrim($sql_set,", ");

						$arr_criteria = Array();
						$arr_criteria['id'] = $arr_data[$n]['id'];
						$arr_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
						$sql_where = DB_PHRequests::get_sql_where($arr_criteria);

						$sql_where = "where $sql_where";
						
						$sql_tables = DB_PHRequests::$TABLE_NAME.", ";
						$sql_tables = rtrim($sql_tables,", ");

						$sql = "update $sql_tables $sql_set $sql_where";
						//print "sql is $sql\n";
						$query = $db_phr_model->db->query($sql);

						$number_affected = $db_phr_model->get_number_affected();
						//print "number affected is $number_affected\n";
						/*
						if ($number_affected == 0) {
							print "Number affected is $number_affected\n";
							print "we should be here because status id is complete\n";
							print_r($arr_data[$n]);
							//exit(0);
						}
						*/
						// here status is completed
						// we assume only run once per day and not more than one running process
						if ($number_affected == 0) {
							// if here we should be actually adding to wallet_log id
							// add growth
							// assume if above does not work
							$wallet_log_id = 0;
							$db_wallet_logs_model = new DB_WalletLogs();

							// two of these could get created
							$temp_data = Array();
							$temp_data['user_id'] = $user_id;
							$temp_data['amount'] = $amount;
							$temp_data['wallet_type_id'] = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
							$temp_data['log_type_id'] = DB_WalletLogs::$DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID;
							$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
							$temp_data['reference_id'] = $arr_data[$n]['id'];;
							//print_r($temp_data);
							if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
								$wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
							}



							

							// here we give ourselves growth one time only
							$sql_set = "set ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + $amount, ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates + 1, ";
							$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_phr_model->db->escape($now_gm_date_time).", ";
							if ($wallet_log_id > 0) {
								$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";
								$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".amount = $amount, ";
								$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$db_model->db->escape($amount).", ";
								$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
							
							}

							$sql_set = rtrim($sql_set,", ");

							$arr_criteria = Array();
							$arr_criteria['id'] = $arr_data[$n]['id'];
							$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
							$sql_where = DB_PHRequests::get_sql_where($arr_criteria);

							if ($wallet_log_id > 0) {
								$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".id = ".$db_wallet_logs_model->db->escape($wallet_log_id)."";
								$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_pending_create = ".$db_wallet_logs_model->db->escape("Y")."";
								$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";

							}



							$sql_where = "where $sql_where";
							
							$sql_tables = DB_PHRequests::$TABLE_NAME.", ";
							if ($wallet_log_id > 0) {
								$sql_tables .= "".DB_WalletLogs::$TABLE_NAME.", ";
								$sql_tables .= DB_Users::$TABLE_NAME.", ";
							}
							$sql_tables = rtrim($sql_tables,", ");

							$sql = "update $sql_tables $sql_set $sql_where";
							$query = $db_phr_model->db->query($sql);
							$number_affected = $db_phr_model->get_number_affected();
							//print "Number affected is $number_affected\n";
							if ($number_affected == 3) {
							} else {
								// should send an email out if got here
								/*
								print "number affected is $number_affected\n";
								print "in here sql is $sql<br>\n";
								print "blah blah\n";
								exit(0);
								*/
							}
						}
					}
				}

			}
		} while (count($arr_data) > 0);
		Emailer::send_completed_daily_maintenance();
		print "done\n";
	}

	// just checks if frozen bonuses should be freed
	function check_and_update_frozen_bonuses() {
		$db_user_model = new DB_Users();
		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		$db_wallet_logs_model = new DB_WalletLogs();
		$cut_off_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-30 Days"));
		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;
		do {
			// probably should use lock
			// look for wallet logs
			$arr_criteria = Array();
			$arr_criteria['wallet_type_id'] = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
			$arr_criteria['log_type_id'] = DB_WalletLogs::$NEW_MEMBER_BONUS_FOR_FIRST_PH_LOG_TYPE_ID;
			$arr_criteria['is_available'] = "N";
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['gm_created']['<='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_wallet_logs_model->search($arr_criteria);
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				$last_id = $arr_data[$n]['id'];

				$ph_data = $db_phr_model->get($arr_data[$n]['reference_id']);
				if (count($ph_data) > 0) {
					if ($ph_data['status_id'] == DB_PHRequests::$COMPLETED_STATUS_ID) {
						// set to free
						/*
						$arr_criteria = Array();
						$arr_criteria['id'] = $arr_data[$n]['id'];
						$temp_data = Array();
						$temp_data['is_available'] = "Y";
						$db_wallet_logs_model->update($temp_data, $arr_criteria);
						*/

						$amount = $arr_data[$n]['amount'];
						$sql_set = "set ";
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_model->db->escape("Y").", ";
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";

						$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$db_model->db->escape($amount).", ";
						$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
						$sql_set = rtrim($sql_set,", ");


						$sql_where = "";
						$sql_where .= "".DB_WalletLogs::$TABLE_NAME.".id = ".$db_model->db->escape($arr_data[$n]['id'])."";
						$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_model->db->escape("N")."";
						$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";

						$sql_where = "where $sql_where";
						
						$sql_tables = DB_WalletLogs::$TABLE_NAME.", ";
						$sql_tables .= DB_Users::$TABLE_NAME.", ";

						$sql_tables = rtrim($sql_tables,", ");

						$sql = "update $sql_tables $sql_set $sql_where";


						$query = $db_model->db->query($sql);
						$number_affected = $db_model->get_number_affected();
						print "number affected is $number_affected\n";

					}
				}

			}
		} while (count($arr_data) > 0);

		// do new member bonuses
		$last_id = 0;
		do {
			// probably should use lock
			// look for wallet logs
			$arr_criteria = Array();
			$arr_criteria['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
			$arr_criteria['log_type_id'] = DB_WalletLogs::$NEW_SPONSOR_BONUS_FOR_FIRST_10K_LOG_TYPE_ID;
			$arr_criteria['is_available'] = "N";
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['gm_created']['<='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_wallet_logs_model->search($arr_criteria);
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				$last_id = $arr_data[$n]['id'];

				$ph_data = $db_phr_model->get($arr_data[$n]['reference_id']);
				if (count($ph_data) > 0) {
					if ($ph_data['status_id'] == DB_PHRequests::$COMPLETED_STATUS_ID) {
					
						// set to free
						/*
						$arr_criteria = Array();
						$arr_criteria['id'] = $arr_data[$n]['id'];
						$temp_data = Array();
						$temp_data['is_available'] = "Y";
						$db_wallet_logs_model->update($temp_data, $arr_criteria);
						*/


						$amount = $arr_data[$n]['amount'];

						$sql_set = "set ";
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_model->db->escape("Y").", ";
						$sql_set .= "".DB_WalletLogs::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";

						$sql_set .= "".DB_Users::$TABLE_NAME.".level_income_balance = ".DB_Users::$TABLE_NAME.".level_income_balance + ".$db_model->db->escape($amount).", ";
						$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
						$sql_set = rtrim($sql_set,", ");


						$sql_where = "";
						$sql_where .= "".DB_WalletLogs::$TABLE_NAME.".id = ".$db_model->db->escape($arr_data[$n]['id'])."";
						$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".is_available = ".$db_model->db->escape("N")."";
						$sql_where .= " and ".DB_WalletLogs::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";

						$sql_where = "where $sql_where";
						
						$sql_tables = DB_WalletLogs::$TABLE_NAME.", ";
						$sql_tables .= DB_Users::$TABLE_NAME.", ";

						$sql_tables = rtrim($sql_tables,", ");

						$sql = "update $sql_tables $sql_set $sql_where";


						$query = $db_model->db->query($sql);
						$number_affected = $db_model->get_number_affected();
						print "number affected is $number_affected\n";
					}
				}

			}
		} while (count($arr_data) > 0);
	}

	// block users no activity in 30 days
	function block_non_active_users() {
		$db_model = new DB_Users();
		// loop through all COMPLETED PH
		$cut_off_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-30 Days"));
		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_Users::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['last_request_gm_date_time']['<='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				// update record to suspend
				$id = $arr_data[$n]['id'];
				$data = Array();
				$data['status_id'] = DB_Users::$BLOCKED_STATUS_ID;
				$data['status_reason_type_id'] = DB_Users::$NO_ACTIVITY_IN_30_DAYS_REASON_TYPE_ID;
				$db_model->save($id,$data);
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}

	// find users to suspend or block based on last ph
	function find_users_to_suspend_block_on_no_ph() {
		$db_model = new DB_Users();
		// loop through all COMPLETED PH
		$cut_off_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("-30 Days"));
		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_Users::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['expiration_before_new_ph_gm_date_time']['<'] = $now_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				// update record to suspend
				$id = $arr_data[$n]['id'];
				$data = Array();
				$data['status_id'] = DB_Users::$SUSPENDED_STATUS_ID;
				$data['status_reason_type_id'] = DB_Users::$NO_PH_IN_25_DAYS_REASON_TYPE_ID;
				$db_model->save($id,$data);
				$number_affected = $db_model->get_number_affected();
				if ($number_affected == 1) {
					Emailer::send_suspended_user_email($id);
				}
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);


		$last_id = 0;
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_Users::$SUSPENDED_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['expiration_before_new_ph_gm_date_time']['<'] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_model->search($arr_criteria);
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				// update record to suspend
				$id = $arr_data[$n]['id'];
				$data = Array();
				$data['status_id'] = DB_Users::$BLOCKED_STATUS_ID;
				$data['status_reason_type_id'] = DB_Users::$NO_PH_AFTER_ACCOUNT_SUSPENDED_30_DAYS_REASON_TYPE_ID;
				$db_model->save($id,$data);
				$number_affected = $db_model->get_number_affected();
				if ($number_affected == 1) {
					Emailer::send_blocked_user_email($id);
				}
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}

	// runs once a day
	function run_daily_process() {


		print "rund aily process\n";
		self::update_daily_growths();
		self::check_and_update_frozen_bonuses();
		//self::block_non_active_users();
		print "in done\n";

		// check if ph new ph request expire
		// expiration_before_new_ph_gm_date_time
	
		// cancel matched requests that have expired an additional 24 hours on top of expire times set to rejected

		// do penalties

		// need to go through all frozen wallet logs and check if ph is completed and if so and after expiration has expired and PH is completed

		// check if user needs to expire
	}

}
?>
