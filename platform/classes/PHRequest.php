<?

class PHRequest {

	function get_arr_ph_requests_for_user($user_id, $is_get_cancelled_and_completed = false) {
		$db_model = new DB_PHRequests();	
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		if ($is_get_cancelled_and_completed) {
			$arr_criteria['status_id'][] = DB_PHRequests::$CANCELLED_STATUS_ID;
			$arr_criteria['status_id'][] = DB_PHRequests::$COMPLETED_STATUS_ID;
		} else {
			$arr_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
		}
		$arr_criteria['join_user_table'] = $user_id;
		//print_r($arr_criteria);
		$arr_data =  $db_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			unset($arr_data[$n]['password']);
			unset($arr_data[$n]['first_name']);
			unset($arr_data[$n]['last_name']);
			unset($arr_data[$n]['otp']);
			unset($arr_data[$n]['otp_expire_gm_date_time']);
			//$arr_data[$n]['user_fullname'] = $;
		}
		return $arr_data;
	}


	// release partial funds

	// should really be part of update with phrequest to make it atomic
	// release 20% of ph 
	function release_partial_amount_on_frozen_adjustments_for_user($user_id, $amount_to_unfreeze) {
		// when we're doing it this way we create new wallet logs and never unfreeze original amount vs
		// if set to only not available 
		$ci =& get_instance();


		$db_wallet_model = new DB_WalletLogs();	

		$arr_criteria = Array();
		$arr_criteria['is_release_part_of_funds_on_ph_complete'] = "Y";
		$arr_criteria['amount_available'] = "Y";
		$arr_criteria['is_available'] = "N";
		$arr_criteria['order_by'] = "id";
		$arr_criteria['order_by_direction'] = "asc";
		// should add is_released_all_funds field
		$arr_data = $db_wallet_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			// this could be potential race condition
			// find all our amount available and release
			
		}
	}

	function is_valid_ph_amount($amount) {
		if ($amount < 20) {
			return false;
		} else if ($amount == 20) {
			return true;
		} else if ($amount > 2000) {
			return false;
		} else if (fmod($amount,50) == 0) {
			return true;
		}
		return false;
	}

	function fix_give_daily_tasks_earnings_for_completed_phrequest_temp() {

		$ci =& get_instance();

		//$field_names = DB_PHRequests::get_field_names();
		//$field_names_2 = DB_WalletLogs::get_field_names();

		
		/*
		$ci->db->select("$field_names")->from(DB_PHRequests::$TABLE_NAME);
		$ci->db->where('t.branch_id',$branch_id);
		*/
		$db_model = new DB_PHRequests();	
		$db_wallet_model = new DB_WalletLogs();	
		$arr_criteria = Array();
		$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
		$arr_data =  $db_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			// do a search for all walletlogs
			$ph_growth_given = $arr_data[$n]['amount_growth_given'];
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$arr_criteria = Array();
			$arr_criteria['reference_id'] = $arr_data[$n]['id'];
			$arr_criteria['log_type_id'] = DB_WalletLogs::$DAILY_TASK_EARNING_FOR_PH_COMPLETED;
			$arr_criteria['wallet_type_id'] = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
			$arr_existing_correction_data = $db_wallet_model->search($arr_criteria);
			if (count($arr_existing_correction_data) == 0 && $arr_data[$n]['amount'] > 0) {
				$temp_data = Array();
				$temp_data['user_id'] = $arr_data[$n]['user_id'];
				$temp_data['amount'] = $arr_data[$n]['amount'];
				$temp_data['reference_id'] = $arr_data[$n]['id'];
				$temp_data['is_available'] = "N";
				$temp_data['is_pending_create'] = "N";
				$temp_data['wallet_type_id'] = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
				$temp_data['log_type_id'] = DB_WalletLogs::$DAILY_TASK_EARNING_FOR_PH_COMPLETED;
				$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_model->db);
				print_r($temp_data);
				//print_r($temp_data);
				$number_affected = 0;
				//print_r($temp_data);
				$number_affected = $db_wallet_model->save(0, $temp_data);
				$wallet_log_id = 0;
				if ($number_affected > 0) {
					$wallet_log_id = $db_wallet_model->get_last_insert_id();
					print "wallet log id is $wallet_log_id\n";
				}
			} else {
				print "has existing daily task earning for ph completed skipping\n";
			}
		}
	}

	function fix_completed_phrequests_temp() {

		$ci =& get_instance();

		//$field_names = DB_PHRequests::get_field_names();
		//$field_names_2 = DB_WalletLogs::get_field_names();

		
		/*
		$ci->db->select("$field_names")->from(DB_PHRequests::$TABLE_NAME);
		$ci->db->where('t.branch_id',$branch_id);
		*/
		$db_model = new DB_PHRequests();	
		$db_wallet_model = new DB_WalletLogs();	
		$arr_criteria = Array();
		$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
		$arr_data =  $db_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			// do a search for all walletlogs
			$arr_criteria = Array();
			$arr_criteria['reference_id'] = $arr_data[$n]['id'];
			$arr_criteria['log_type_id'] = DB_WalletLogs::$DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID;
			$arr_log_data = $db_wallet_model->search($arr_criteria);
			$total = 0;
			for($r=0;$r<count($arr_log_data);$r++) {
				// add up all the wallet data
				$total += $arr_log_data[$r]['amount'];
			}
			$ph_growth_given = $arr_data[$n]['amount_growth_given'];
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			if ($total > $ph_growth_given) {

				print "total is $total and ph growth given is $ph_growth_given\n";
				
				$amount = $arr_data[$n]['amount']*.04;
				$sql_set = "set ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + $amount, ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates + 1, ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";

				$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$db_model->db->escape($amount).", ";
				$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
				

				$sql_set = rtrim($sql_set,", ");

				$arr_criteria = Array();
				$arr_criteria['id'] = $arr_data[$n]['id'];
				$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
				$sql_where = DB_PHRequests::get_sql_where($arr_criteria);

				$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";

				$sql_where = "where $sql_where";
				
				$sql_tables = DB_PHRequests::$TABLE_NAME.", ";
				$sql_tables .= DB_Users::$TABLE_NAME.", ";

				$sql_tables = rtrim($sql_tables,", ");

				$sql = "update $sql_tables $sql_set $sql_where";
				/*
				print "sql is $sql\n";
				$query = $db_model->db->query($sql);
                                $number_affected = $db_model->get_number_affected();
                                print "Number affected for completed $number_affected\n";
				exit(0);
				*/

			}
		}
	}


	function get_level_income_amount($level, $amount, $is_second_commission = false) {
		$level_income_amount = 0;
		if (!$is_second_commission) {
			if ($level == 0 || $level < 0) {
				$level_income_amount = 0;
			} else if ($level == 1) {
				$level_income_amount = $amount*.10;
			} else if ($level == 2) {
				$level_income_amount = $amount*.05;
			} else if ($level == 3) {
				$level_income_amount = $amount*.03;
			} else if ($level == 4) {
				$level_income_amount = $amount*.02;
			} else if ($level == 5) {
				$level_income_amount = $amount*.01;
			} else {
				$level_income_amount = $amount*.0001;
			}
		} else {
			// on second commmit
			if ($level == 0 || $level < 0) {
				$level_income_amount = 0;
			} else if ($level == 1) {
				$level_income_amount = $amount*.02;
			} else if ($level == 2) {
				$level_income_amount = $amount*.01;
			} else if ($level == 3) {
				$level_income_amount = $amount*.005;
			} else if ($level == 4) {
				$level_income_amount = $amount*.0025;
			} else if ($level == 5) {
				$level_income_amount = $amount*.001;
			} else {
				$level_income_amount = $amount*.0001;
			}

		}
		return $level_income_amount;
	}

	// based on amount and if first or second commission
	function determine_level_for_level_income_by_amount($amount, $total_amount, $is_second_commission = false) {
		$is_found = false;
		$n = 0;
		$epsilon = 0.00001;
		while(!$is_found && $n <= 6) {
			$a = $amount;
			$b = self::get_level_income_amount($n, $total_amount,  $is_second_commission);
			if(abs($a-$b) < $epsilon) {
				$is_found = true;
				return $n;
				// we're at level $n
			}
			$n++;
		}
		return null;
	}

	function fix_level_income_completed_phrequests_temp() {

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
				if (count($arr_completed_ph_data) > 1) {
					for($r=1;$r<count($arr_completed_ph_data);$r++) {
						print "completed gmdate time is ".$arr_completed_ph_data[$r]['completed_gm_date_time']."\n";
						$cut_off_gm_timestamp = strtotime("2016-08-28 21:30:00 UTC");
						if (strtotime($arr_completed_ph_data[$r]['gm_created']." UTC") <= $cut_off_gm_timestamp) {
						} else {
							print "skipping timestamp has been cut off\n";
							continue;
						}
						print "total amount is ".$arr_completed_ph_data[$r]['amount']."\n";
						// go through first past first one
						// check direct sponsor
						$arr_criteria = Array();
						$arr_criteria['reference_id'] = $arr_completed_ph_data[$r]['id'];
						$arr_criteria['log_type_id'] = DB_WalletLogs::$LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID;
						$arr_criteria['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
						$arr_log_data = $db_wallet_model->search($arr_criteria);
						for($m=0;$m<count($arr_log_data);$m++) {
							print "log data amount is ".$arr_log_data[$m]['amount']."\n";
							print "total amount is ".$arr_completed_ph_data[$r]['amount']."\n";
							$level = self::determine_level_for_level_income_by_amount($arr_log_data[$m]['amount'], $arr_completed_ph_data[$r]['amount']);
							print "level is $level\n";
							
							if (!is_null($level)) {
								// we need to make adjustment
								// need amount
								// name
								$new_level_income_amount = self::get_level_income_amount($level, $arr_completed_ph_data[$r]['amount'], true);
								$adjust_amount = $new_level_income_amount-$arr_log_data[$m]['amount'];
								if ($adjust_amount == 0) {
									print "skipping\n";
								}
								print "new level income amount is $new_level_income_amount\n";
								print "old level income amount is ".$arr_log_data[$m]['amount']."\n";
								print "amount to adjust $adjust_amount\n";
								$arr_criteria = Array();
								$arr_criteria['reference_id'] = $arr_log_data[$m]['id'];
								$arr_criteria['log_type_id'] = DB_WalletLogs::$SYSTEM_ERROR_CORRECTION_LOG_TYPE_ID;
								$arr_criteria['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
								$arr_existing_correction_data = $db_wallet_model->search($arr_criteria);
								if (count($arr_existing_correction_data) == 0 && $adjust_amount != 0) {
									print "doing update\n";
									$wallet_name = "level_income";
									$adjust_user_id = $arr_log_data[$m]['user_id'];
									$log_type_id = DB_WalletLogs::$SYSTEM_ERROR_CORRECTION_LOG_TYPE_ID;
									$reference_id = $arr_log_data[$m]['id'];
									$adjust_reason = "Adjustment System Error Correction For PH: ".$arr_completed_ph_data[$r]['id']."";
									print "adjust reason ".$adjust_reason."\n";
									print "reference id is  ".$reference_id."\n";
									$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $adjust_user_id, $adjust_amount, $adjust_reason, $log_type_id, $reference_id);
									print_r($obj_result);
									$last_query = $db_user_model->db->last_query();
									print "last query is $last_query\n";
									$number_affected = $db_user_model->get_number_affected();
									print "number affected is $number_affected\n";
									
									//exit(0);
								} else {
									print "has existing correction skipping\n";
								}
							}
						}
					}
				}
			}
		}
		print "done\n";

	}

	// go through all completed ph with number_growth_updates < 30
	// can't do this need to check if user logs in
	function update_daily_growth_for_all() {
		$db_model = new DB_PHRequests();	
		$arr_criteria = Array();
		$arr_criteria['status_id'] = DB_PHRequests::$COMPLETED_STATUS_ID;
		$arr_criteria['number_growth_daily_updates']['<'] = 30;
		$arr_data =  $db_model->search($arr_criteria);
		for($n=0;$n<count($arr_data);$n++) {
			$id = $arr_data[$n]['id'];
			self::update_daily_growth_for_ph($id);
		}
	}

	// can't do this need to check if user logs in
	function update_daily_growth_for_ph($id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$db_model = new DB_PHRequests();
		$data = $db_model->get($id);
		if(count($data) >0) {
			// loop through from completed_gm_date_time
			$completed_gm_date_time = $data['completed_gm_date_time'];
			$status_id = $data['status_id'];


			$now = time();
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			//print "completed gm date time is $completed_gm_date_time<br>";
			//print "now gm date time is $now_gm_date_time<br>";
			

			$current_time = strtotime($completed_gm_date_time." UTC");
			$number_growth_daily_updates = $data['number_growth_daily_updates'];

			if ($number_growth_daily_updates < 30 && $status_id == DB_PHRequests::$COMPLETED_STATUS_ID) {
				// get total number of days
				if ($current_time <= $now) {
					$number_days = 0;
					$current_time = strtotime("+1 day", $current_time);
					while($current_time <= $now) {
						// keep adding days until no longer greater
						
						$number_days++;
						$current_time = strtotime("+1 day", $current_time);
					}

					// subtract 1 because we should have overshot
					$number_days--;
					print "number days is $number_days\n";

					if ($number_days >= 30) {
						$number_days = 30;
					}
					if ($number_growth_daily_updates < $number_days) {
						// we need to update it to match and give user growth
						// get diff
						$diff = $number_days - $number_growth_daily_updates;
						$amount = $data['amount_matched_completed'];
						$amount_growth = $diff*$amount*.04;

						// we need to update both user table and phrequest table
						// set to complete
						$sql_set .= "".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = $number_days";
						$sql_set .= ", ".DB_Users::$TABLE_NAME.".gh_balance = ".DB_Users::$TABLE_NAME.".gh_balance + ".$this->db->escape($amount_growth)."";
						$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".amount_growth_given = ".DB_PHRequests::$TABLE_NAME.".amount_growth_given + ".$this->db->escape($amount_growth)."";
						$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";


						// update users table
						// add to column 1 on completed confirmed request
						$sql_set .= ", ".DB_Users::$TABLE_NAME.".gh_balance = ".DB_Users::$TABLE_NAME.".gh_balance + ".$this->db->escape($amount_growth)."";
						$sql_set .= ", ".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";

					
						// do where
						$sql_where .= "".DB_PHRequests::$TABLE_NAME.".id = $id";

						$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".DB_PHRequests::$COMPLETED_STATUS_ID."";

						$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".number_growth_daily_updates = $number_growth_daily_updates";

						$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";

						// make sure gh amounts are still valid
						// should do the below but need to calculate gh_total_amount

						
						// make sure PH amounts are still valid


						$sql_tables = "".DB_PHRequests::$TABLE_NAME.", ".DB_Users::$TABLE_NAME."";
						$sql = "update $sql_tables set $sql_set where $sql_where";
						print "sql is $sql\n";


						$query = $this->db->query($sql);
						$number_affected = $this->db->affected_rows();
						print "number affected is $number_affected\n";


						// should clean up gh request and ph request if match_completed == amount
						if ($number_affected == 2) {
							//print "is success\n";
							// we should be good here
							$obj_result->is_success = true;
						} else {
							// query above all or nothing if not updated then it means 
							// error
							$obj_result->is_success = false;
						}

					} 
				}
			}
		}
		return $obj_result;
	}


	public function cancel_ph($ph_id, $cancelled_by_user_id) {
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$db_model = new DB_PHRequests();

		$now_time = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db, $now_time);


		



		$sql_set = "";

		// ph info
		if ($ph_id > 0) {
			$ph_data = $db_model->get($ph_id);
			$user_id = $ph_data['user_id'];
		} else {
			return $obj_result;
		}

		// set is approved for matching for user to N

		// check if user is admin if it is then don't approve
		if (!User::is_user_have_admin_access($user_id)) {
			$sql_set .= "".DB_Users::$TABLE_NAME.".is_approved_for_matching = 'N', ";
		}

		// update user
		$sql_set .= "".DB_Users::$TABLE_NAME.".total_ph = ".DB_Users::$TABLE_NAME.".total_ph - ".DB_PHRequests::$TABLE_NAME.".amount, ";

		// have to remove uncofirmed ph 
		$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".DB_PHRequests::$TABLE_NAME.".amount_matched + ".DB_PHRequests::$TABLE_NAME.".amount_matched_completed, ";

		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";


		// update gh request
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_available = ".DB_GHRequests::$TABLE_NAME.".amount_available + ".DB_MatchedRequests::$TABLE_NAME.".amount, ";

		// remove matched amount
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_matched = ".DB_GHRequests::$TABLE_NAME.".amount_matched - ".DB_MatchedRequests::$TABLE_NAME.".amount, ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";



		// updated ph request

		// keep matched the same

		// set amount available to 0 or do not touch
		//$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed - ".$this->db->escape($amount).", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = 0, ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = 0, ";

		// increase number of rejects we will only grab from ph requests that dont have rejects
		// dont set to REJECTED
		//$sql_set .= ", ".DB_PHRequests::$TABLE_NAME.".number_rejects = ".DB_PHRequests::$REJECTED_STATUS_ID.".amount_available + ".$this->db->escape($amount)."";

		// update matched_requests
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".cancelled_by_user_id = ".$this->db->escape($cancelled_by_user_id).", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".cancelled_reason_type_id = ".DB_MatchedRequests::$GENERAL_CANCELLED_REASON_TYPE_ID.", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".rejected_by = '', ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";


		$sql_set = rtrim($sql_set,", ");

	
		// do where
		$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = ".$db_model->db->escape($ph_id)."";

		$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".phrequest_id = ".DB_PHRequests::$TABLE_NAME.".id";

		$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = ".DB_MatchedRequests::$TABLE_NAME.".ghrequest_id";

		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".DB_Users::$TABLE_NAME.".id = ".DB_PHRequests::$TABLE_NAME.".user_id";


		$sql_tables = "";
		$sql_tables .= "".DB_MatchedRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_GHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_PHRequests::$TABLE_NAME.", ";
		$sql_tables .= "".DB_Users::$TABLE_NAME.", ";

		$sql_tables = rtrim($sql_tables,", ");

		$sql = "update $sql_tables set $sql_set where $sql_where";
		/*
		print "sql is $sql\n";
		$db_phr_model = new DB_PHRequests();
		$data = $db_phr_model->get($ph_id);
		print_r($data);

		$db_ghr_model = new DB_GHRequests();
		$data = $db_ghr_model->get($ph_id);
		print_r($data);

		*/

		$query = $db_model->db->query($sql);
		$number_affected = $db_model->db->affected_rows();

		if ($number_affected == 0) {
			//print "in here<br>\n";
			// if thing doesn't have any matchedrequrests no rows updated

			// try again without any matching
			$sql_set = "";

			// ph info
			if ($ph_id > 0) {
				$ph_data = $db_model->get($ph_id);
				$user_id = $ph_data['user_id'];
			} else {
				return $obj_result;
			}

			// set is approved for matching for user to N

			// check if user is admin if it is then don't approve
			if (!User::is_user_have_admin_access($user_id)) {
				$sql_set .= "".DB_Users::$TABLE_NAME.".is_approved_for_matching = 'N', ";
			}
			// update user
			$sql_set .= "".DB_Users::$TABLE_NAME.".total_ph = ".DB_Users::$TABLE_NAME.".total_ph - ".DB_PHRequests::$TABLE_NAME.".amount, ";
			// have to remove uncofirmed ph 
			$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".DB_PHRequests::$TABLE_NAME.".amount_matched + ".DB_PHRequests::$TABLE_NAME.".amount_matched_completed, ";

			$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";


			// updated ph request

			// keep matched the same

			// set amount available to 0 or do not touch
			//$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = ".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed - ".$this->db->escape($amount).", ";
			$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
			$sql_set .= "".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$CANCELLED_STATUS_ID.", ";
			$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = 0, ";
			$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_unconfirmed = 0, ";


			$sql_set = rtrim($sql_set,", ");

		
			// do where
			$sql_where = "".DB_PHRequests::$TABLE_NAME.".id = ".$db_model->db->escape($ph_id)."";

			$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$ACTIVE_STATUS_ID."";

			$sql_where .= " and ".DB_Users::$TABLE_NAME.".id = ".DB_PHRequests::$TABLE_NAME.".user_id";


			$sql_tables = "";
			$sql_tables .= "".DB_PHRequests::$TABLE_NAME.", ";
			$sql_tables .= "".DB_Users::$TABLE_NAME.", ";

			$sql_tables = rtrim($sql_tables,", ");

			$sql = "update $sql_tables set $sql_set where $sql_where";
			$query = $db_model->db->query($sql);
			$number_affected = $db_model->db->affected_rows();
			//print "Number affected is $number_affected<br>\n";
			if ($number_affected > 0) {
				$obj_result->is_success = true;
			}
			/*
			print "sql is $sql\n";
			$db_phr_model = new DB_PHRequests();
			$data = $db_phr_model->get($ph_id);
			print_r($data);

			$db_ghr_model = new DB_GHRequests();
			$data = $db_ghr_model->get($ph_id);
			print_r($data);

			*/

		} else if ($number_affected > 0) {
			$obj_result->is_success = true;
		}
		//print "Number affected is $number_affected<br>\n";


		return $obj_result;
	}

}
?>
