<?

// class of temporary ofuntions to fix things
class SiteFixes {

	function fix_take_back_admin_adjustment_for_not_available() {

		$ci =& get_instance();

		//$field_names = DB_PHRequests::get_field_names();
		//$field_names_2 = DB_WalletLogs::get_field_names();
		
		/*
		$ci->db->select("$field_names")->from(DB_PHRequests::$TABLE_NAME);
		$ci->db->where('t.branch_id',$branch_id);
		*/
		$db_model = new DB_PHRequests();
		$db_user_model = new DB_Users();
		$db_wallet_model = new DB_WalletLogs();	

		$arr_criteria = Array();
		$arr_criteria['log_type_id'] = DB_WalletLogs::$ADMIN_ADJUSTMENT_LOG_TYPE_ID;
		$arr_criteria['is_available'] = "N";
		//$arr_criteria['gm_created']['<=' = "2016-09-01 10:04:48";
		$arr_data = $db_wallet_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$amount = $arr_data[$n]['amount'];

			$sql_set = "set ";
			if ($arr_data[$n]['wallet_type_id'] ==  DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID) {
				$sql_set .= "".DB_Users::$TABLE_NAME.".task_earning_balance = ".DB_Users::$TABLE_NAME.".task_earning_balance - ".$db_user_model->db->escape($amount).", ";
			} else if ($arr_data[$n]['wallet_type_id'] == DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID) {
				$sql_set .= "".DB_Users::$TABLE_NAME.".level_income_balance = ".DB_Users::$TABLE_NAME.".level_income_balance - ".$db_user_model->db->escape($amount).", ";
			} else if ($arr_data[$n]['wallet_type_id'] == DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID) {
				$sql_set .= "".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance = ".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance - ".$db_user_model->db->escape($amount).", ";
			} else if ($arr_data[$n]['wallet_type_id'] == DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID) {
				$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance - ".$db_user_model->db->escape($amount).", ";
			}

			$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_user_model->db->escape($now_gm_date_time).", ";

			$sql_set = rtrim($sql_set,", ");

			$sql_where = "";
			$sql_where .= "".DB_Users::$TABLE_NAME.".id = ".$db_user_model->db->escape($arr_data[$n]['user_id'])."";

			$sql_where = "where $sql_where";
			
			$sql_tables = "";

			$sql_tables .= DB_Users::$TABLE_NAME.", ";

			$sql_tables = rtrim($sql_tables,", ");

			$sql = "update $sql_tables $sql_set $sql_where";
			print "data was created at ".$arr_data[$n]['gm_created']."<br>";
			print "sql is $sql\n";
			/*
				$query = $db_user_model->db->query($sql);
				$number_affected = $db_user_model->get_number_affected();
			*/
		}
	}


	// only do 1st level
	function fix_2nd_time_level_income_completed_phrequests_temp() {

		$ci =& get_instance();

		//$field_names = DB_PHRequests::get_field_names();
		//$field_names_2 = DB_WalletLogs::get_field_names();

		
		/*
		$ci->db->select("$field_names")->from(DB_PHRequests::$TABLE_NAME);
		$ci->db->where('t.branch_id',$branch_id);
		*/
		$db_user_model = new DB_Users();
		$db_model = new DB_PHRequests();	
		$db_wallet_model = new DB_WalletLogs();	
		$arr_criteria = Array();
		$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
		$arr_data =  $db_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			// do a search for all walletlogs
			$user_id = $arr_data[$n]['user_id'];
			print "user id is $user_id\n";

			$arr_active_ph_data = $db_model->get_arr_user_ph($user_id);
			if (count($arr_active_ph_data) > 1) {
				print "have more than one ph\n";

				$arr_criteria = Array();
				$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
				$arr_criteria['user_id'] = $user_id;
				$arr_criteria['order_by'] = "completed_gm_date_time";
				$arr_criteria['order_by_direction'] = "asc";
				$arr_completed_ph_data =  $db_model->search($arr_criteria);
				if (count($arr_completed_ph_data) > 0) {
					print "earliest completed gmdate time is ".$arr_completed_ph_data[0]['completed_gm_date_time']."\n";
				}

				// get completed ph past the first all of these should be giving 2% to parent
				if (count($arr_completed_ph_data) > 1) {
					for($r=1;$r<count($arr_completed_ph_data);$r++) {
						print "completed gmdate time is ".$arr_completed_ph_data[$r]['completed_gm_date_time']."\n";
						print "total amount is ".$arr_completed_ph_data[$r]['amount']."\n";
						// go through first past first one
						// check direct sponsor

						$user_data = $db_user_model->get($user_id);
						$sponsor_id = $user_data['referral_id'];

						
						// get all level incomes for this ph that the user is the direct sponsor
						$arr_criteria = Array();
						$arr_criteria['reference_id'] = $arr_completed_ph_data[$r]['id'];
						$arr_criteria['log_type_id'] = DB_WalletLogs::$LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID;
						$arr_criteria['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
						$arr_criteria['user_id'] = $sponsor_id;
						$arr_log_data = $db_wallet_model->search($arr_criteria);
						// these are all for fist level
						$adjust_amount = 0;
						$number_adjustments = 0;
						$income_level_amount = 0;
						$correct_amount = $arr_completed_ph_data[$r]['amount']*.02;

						for($m=0;$m<count($arr_log_data);$m++) {
							if (count($arr_log_data) > 1) {
								print "we have more than 1 level income wallet transaction that has same ref ph\n";
							}

							
							print "log data amount is ".$arr_log_data[$m]['amount']."\n";
							print "total amount is ".$arr_completed_ph_data[$r]['amount']."\n";


							$income_level_amount += $arr_log_data[$m]['amount'];

							// find all corrections

							$arr_criteria = Array();
							$arr_criteria['reference_id'] = $arr_log_data[$m]['id'];
							$arr_criteria['log_type_id'] = DB_WalletLogs::$SYSTEM_ERROR_CORRECTION_LOG_TYPE_ID;
							$arr_criteria['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
							$arr_existing_correction_data = $db_wallet_model->search($arr_criteria);
							for($k=0;$k<count($arr_existing_correction_data);$k++) {
								$number_adjustments++;
								$adjust_amount += $arr_existing_correction_data[$k]['amount'];
							}

							
						}
						if ( (($income_level_amount + $adjust_amount) - $correct_amount) >= .01) {
							print "".$number_adjustments." adjustments for total $adjust_amount and correct amount is $correct_amount and first level income is $income_level_amount\n";
							print "doing update\n";
							$wallet_name = "level_income";
							$log_type_id = DB_WalletLogs::$SYSTEM_ERROR_CORRECTION_LOG_TYPE_ID;
							if (count($arr_log_data) > 0) {
								$adjust_user_id = $arr_log_data[0]['user_id'];
								$reference_id = $arr_log_data[0]['id'];
							} else {
								print "error no level income fo rph\n";
								exit(0);
							}
							$adjust_reason = "Adjustment System Error Correction For PH: ".$arr_completed_ph_data[$r]['id']."";
							print "adjust reason ".$adjust_reason."\n";
							$new_adjust_amount = $correct_amount - ($income_level_amount + $adjust_amount);
							print "reference id is  ".$reference_id."\n";
							$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $adjust_user_id, $new_adjust_amount, $adjust_reason, $log_type_id, $reference_id);
							print_r($obj_result);
							$last_query = $db_user_model->db->last_query();
							print "last query is $last_query\n";
							$number_affected = $db_user_model->get_number_affected();
							print "number affected is $number_affected\n";
							//exit(0);
						}


					}
				}
			}
		}
		print "done\n";
	}



	// suspend users no activity in 30 days
	function fix_primary_btc_addresses() {
		$db_model = new DB_Users();

		// loop through all COMPLETED PH
		$now = time();
		$last_id = 0;
		// add daily growths
		$db_btc_address_model = new DB_BTCAddresses();
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_Users::$ACTIVE_STATUS_ID;
			$arr_criteria['status_id'][] = DB_Users::$SUSPENDED_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			//$arr_criteria['last_request_gm_date_time']['<='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_criteria['order_by_direction'] = "asc";
			$arr_criteria['order_by'] = "id";
			$arr_data = Array();
			$arr_data = $db_model->search($arr_criteria);
			
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
			
				$result = $db_btc_address_model->is_user_have_additional_btc_addresses($arr_data[$n]['id']);
				if ($result) {
					$primary_btc_address_id = $db_btc_address_model->get_primary_btc_address_id_for_user($arr_data[$n]['id']);
				
					if ($primary_btc_address_id == 0) {
						$arr_btc_data = $db_btc_address_model->get_arr_btc_addresses_for_user($arr_data[$n]['id']);
						for($r=0;$r<count($arr_btc_data);$r++) {
							//print "user id ".$arr_data[$n]['id']." needs to set btc address to primary of ".$arr_btc_data[$r]['id']."\n";
							$db_btc_address_model->set_btc_address_to_primary($arr_btc_data[$r]['id']);
							$arr_criteria = Array();
							$arr_criteria['id'] = $arr_data[$n]['id'];

							$temp_data = Array();
							$temp_data["is_have_additional_btc_addresses"] = "Y";
							$db_model->update($temp_data, $arr_criteria);
							$number_affected = $db_model->get_number_affected();
							print "number affeted is $number_affected\n";

							break;
						}
						
					}
				}
			
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}


	// go through all matched requests and set btc price
	function fix_0_btc_price() {
		$db_model = new DB_Users();
		$db_mr_model = new DB_MatchedRequests();

		// loop through all COMPLETED PH
		$now = time();
		$last_id = 0;
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_MatchedRequests::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['bitcoin_amount'] = 0;
			$arr_criteria['amount']['>'] = 0;
			//$arr_criteria['last_request_gm_date_time']['<='] = $cut_off_gm_date_time;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_criteria['order_by_direction'] = "asc";
			$arr_criteria['order_by'] = "id";
			$arr_data = Array();
			$arr_data = $db_mr_model->search($arr_criteria);
			
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				print_r($arr_data[$n]);
			
				$amount = $arr_data[$n]['amount'];
				$bitcoin_price = Bitcoin::get_bitcoin_price();
				print "bitcoin price is $bitcoin_price\n";
				if ($bitcoin_price != "") {
					$bitcoin_amount = $amount/$bitcoin_price;
					
				}
			
				print "bitcoin amount is $bitcoin_amount\n";

				$arr_criteria = Array();
				$arr_criteria['id'] = $arr_data[$n]['id'];

				$temp_data = Array();
				$temp_data["bitcoin_amount"] = $bitcoin_amount;
				$db_mr_model->update($temp_data, $arr_criteria);
				//$number_affected = $db_mr_model->get_number_affected();
			
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}

	// go through all matched requests and set btc price
	function fix_completed_gh_requests() {
		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();

		// loop through all COMPLETED PH
		$now = time();
		$last_id = 0;
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_GHRequests::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['is_loh_approved'] = "Y";
			$arr_criteria['number_results_per_page'] = 100;
			$arr_criteria['order_by_direction'] = "asc";
			$arr_criteria['order_by'] = "id";
			$arr_data = Array();
			$arr_data = $db_ghr_model->search($arr_criteria);
			
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				print_r($arr_data[$n]);
				$db_ghr_model->update_if_request_complete($arr_data[$n]['id']);
			
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}

	// go through all matched requests and set btc price
	function fix_loh() {
		$db_user_model = new DB_Users();
		$db_model = new DB_WalletLogs();
		$db_ghr_model = new DB_GHRequests();

		// loop through all COMPLETED PH
		$now = time();
		$last_id = 0;
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['user_id'] = 10000;
			$arr_criteria['log_type_id'] = DB_WalletLogs::$LOH_BONUS_LOG_TYPE_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['number_results_per_page'] = 100;
			$arr_criteria['order_by_direction'] = "asc";
			$arr_criteria['order_by'] = "id";
			$arr_data = Array();
			$arr_data = $db_model->search($arr_criteria);
			
			//print_r($arr_data);
			for($n=0;$n<count($arr_data);$n++) {
				print_r($arr_data[$n]);
				$gh_data = $db_ghr_model->get($arr_data[$n]['reference_id']);
				if (count($gh_data) > 0) {
					
					$user_id = $gh_data['user_id'];
					if ($user_id > 0) {
						$data = Array();
						$data['user_id'] = $user_id;
						$db_model->save($arr_data[$n]['id'], $data);
					}
				}
			
				$last_id = $arr_data[$n]['id'];
			}
		} while (count($arr_data) > 0);
	}

}
?>
