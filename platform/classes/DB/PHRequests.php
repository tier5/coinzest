<?

class DB_PHRequests extends CI_Model {

	//static $DELETED_STATUS_ID = 10;

	static $TABLE_NAME = "phrequests";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;
	static $COMPLETED_STATUS_ID = 3;

	// maybe dont need expired
	static $EXPIRED_STATUS_ID = 4;

	// dont use rejected
	static $REJECTED_STATUS_ID = 5;


	static $CANCELLED_STATUS_ID = 6;
	
	// maybe have a STATE_ID

	public function __construct() {
           //$this->load->database();
		//print "in here\n";
	}

	function run_query() {
		$this->load->database();
		$data['company_name'] = "%B%";
		$sql = "SELECT id,name FROM customers where name like ".$this->db->escape($data['company_name'])." LIMIT 1";
		print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		$row_id = $row['id'];
		print_r($row);
		exit(0);
	}


	// this will be global
	function get_total($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);

		$sql = "SELECT sum(total_clicks) as total_clicks, sum(total_leads) as total_leads FROM ".self::$TABLE_NAME. "";
		// where name like ".$this->db->escape($data['company_name'])." LIMIT 1";
		//print "sql is $sql\n";
		//exit(0);
		$query = $this->db->query($sql);
		$row = $query->row_array();
		//$row_id = $row['total'];
		//print_r($row);
		$arr_data = Array();
		foreach ($query->result_array() as $row) {
			$arr_data[] = $row;
		}

		//print_r($arr_data);

		return $arr_data;
		//exit(0);
		
	}


	// every completed within 30 days of time now
	function get_daily_growth($user_id) {
		$arr_criteria = Array();
		$arr_criteria['status_id'] = self::$COMPLETED_STATUS_ID;
		$cut_off_timestamp = strtotime("-30 days");
		$gm_date_time = Library_DB_Util::time_to_gm_db_time($this->db, $cut_off_timestamp);
		//print "gm_date_time start time $gm_date_time\n";
		$arr_criteria['completed_gm_date_time']['>='] = $gm_date_time;
		$arr_criteria['user_id'] = $user_id;
		$arr_data = $this->search($arr_criteria);
		$total = 0;
		for($n=0;$n<count($arr_data);$n++) {
			$total += $arr_data[$n]['amount']*.04;
		}
		return $total;
	}


	function get($id) {
		$this->load->database();
		$field_names = self::get_field_names();
		$sql = "SELECT $field_names FROM ".self::$TABLE_NAME." where id = ".$this->db->escape($id)." limit 1";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return $row;
	}

	// get total ph (beast ph)
	function get_total_ph($user_id) {
		$arr_criteria = Array();
		$arr_criteria['status_id'][] = self::$COMPLETED_STATUS_ID;
		$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['user_id'] = $user_id;
		$arr_data = $this->search($arr_criteria);
		$total = 0;
		for($n=0;$n<count($arr_data);$n++) {
			$total += $arr_data[$n]['amount'];
		}
		return $total;
	}


	function arr_get_field_names() {
		$arr[] = "id";
		$arr[] = "amount";
		$arr[] = "status_id";
		$arr[] = "user_id";
		$arr[] = "ghrequest_id";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "order_number";
		$arr[] = "amount_matched";
		$arr[] = "amount_available";
		$arr[] = "amount_matched_completed";
		$arr[] = "completed_gm_date_time";
		// update ghbalance where last_growth_update_date_time greaterthan or equal to 24 hrs
		$arr[] = "last_growth_update_gm_date_time";
		// need to make it always 30
		$arr[] = "number_growth_daily_updates";
		$arr[] = "amount_growth_given";
		$arr[] = "number_rejects";
		$arr[] = "is_pending_create";
		$arr[] = "amount_unconfirmed";
		$arr[] = "country";
		return $arr;
	}

	function get_field_names() {
		$arr = self::arr_get_field_names();
		$value = "";
		for($n=0;$n<count($arr);$n++) {
			$value .= self::$TABLE_NAME.".".$arr[$n].", ";
		}
		$value = rtrim($value, ", ");
		return $value;
	}

	// check if we have any active ph
	function is_user_have_active_ph($user_id) {
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['status_id'] = self::$ACTIVE_STATUS_ID;
		$arr_data = $this->search($arr_criteria);
		PlatformLogs::log("user_have_active_ph", "sql is ".$this->get_last_query()." for user_id $user_id and count is ".count($arr_data)."\n");
		if (count($arr_data) > 0) {
			return true;
		}
		return false;
	}

	// check if we have any active ph
	function get_arr_user_ph($user_id) {
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['status_id'][] = self::$COMPLETED_STATUS_ID;
		$arr_data = $this->search($arr_criteria);
		return $arr_data;
		/*
		if (count($arr_data) > 0) {
		}
		*/
		return false;
	}

	function search($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);


		if ($sql_where == "") {
		} else {
			$sql_where .= " and ";
		}

		$sql_where .= " ".self::$TABLE_NAME.".status_id <> ".self::$DELETED_STATUS_ID."";


		if (isset($arr_criteria['order_by'])) {
			$order_by_direction = "asc";
			if (isset($arr_criteria['order_by_direction'])) {
				$order_by_direction = $arr_criteria['order_by_direction'];
			}
                        $order_by = "order by ".self::$TABLE_NAME.".".$arr_criteria['order_by']." $order_by_direction";
		} else {
                        $order_by = "order by ".self::$TABLE_NAME.".id asc";
                }

		$offset = 0;
		$number_results_per_page = "";
		if (isset($arr_criteria['number_results_per_page_offset'])) {
			$offset = $arr_criteria['number_results_per_page_offset'];
		}

		if (isset($arr_criteria['number_results_per_page'])) {
			$number_results_per_page = $arr_criteria['number_results_per_page'];
		}


		$limit = "";
		 if ($offset > 0 || ($number_results_per_page != "" && $number_results_per_page != 0)) {
                        $limit = "limit $offset";
                        if ($number_results_per_page != "") {
                                $limit .= ", $number_results_per_page";
                        } else {
			}
		}  else if ($number_results_per_page != "" && $number_results_per_page != 0) {
			$limit = "limit $number_results_per_page";
		}


		$field_names = self::get_field_names();
		$from_tables = self::$TABLE_NAME;

		//print_r($arr_criteria);

		if (isset($arr_criteria['join_user_table']) && $arr_criteria['join_user_table'] > 0) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= " ".self::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";
			$field_names = DB_Users::get_field_names().", " . $field_names;
			$from_tables .= ", ".DB_Users::$TABLE_NAME;
		}

		if ($sql_where != "") {
			$sql_where = " where $sql_where";
		}

		$sql = "select $field_names FROM $from_tables $sql_where $order_by $limit";

		//print "sql is $sql\n";
		// where name like ".$this->db->escape($data['company_name'])." LIMIT 1";
		//print "sql is $sql\n";
		//exit(0);
		$query = $this->db->query($sql);
		$row = $query->row_array();
		//$row_id = $row['total'];
		//print_r($row);
		$arr_data = Array();
		foreach ($query->result_array() as $row) {
			$arr_data[] = $row;
		}

		//print_r($arr_data);

		return $arr_data;
		//exit(0);
		
	}


	function get_sql_where($arr_criteria) {
		$this->load->database();
		$sql_where = "";

		// this matches for in users table
		if (isset($arr_criteria['join_user_table'])) {
		
			// ALTER TABLE  `wp_users` ADD  `is_allow_ph_to_match_restricted_countries` ENUM(  'Y',  'N' ) NOT NULL DEFAULT  'N';
			// this matches for in users table
			if (isset($arr_criteria['is_test_match_account'])) {
				if ($sql_where == "") {
				} else {
					$sql_where .= " and ";
				}
				$sql_where .= Library_DB_Util::build_comparison_sql($this->db, "is_test_match_account", $arr_criteria['is_test_match_account']);
			}

			if (isset($arr_criteria['is_allow_ph_to_match_restricted_countries'])) {
				if ($sql_where == "") {
				} else {
					$sql_where .= " and ";
				}
				$sql_where .= Library_DB_Util::build_comparison_sql($this->db, "is_allow_ph_to_match_restricted_countries", $arr_criteria['is_allow_ph_to_match_restricted_countries']);
			}
		}

		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".status_id", $arr_criteria['status_id']);
		}

		if (isset($arr_criteria['completed_gm_date_time'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".completed_gm_date_time", $arr_criteria['completed_gm_date_time']);
		}

		if (isset($arr_criteria['gm_created'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".gm_created", $arr_criteria['gm_created']);
		}

		if (isset($arr_criteria['country'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".country", $arr_criteria['country']);
		}


		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".id", $arr_criteria['id']);
		}

		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".user_id", $arr_criteria['user_id']);
		}

		if (isset($arr_criteria['number_growth_daily_updates'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, self::$TABLE_NAME.".number_growth_daily_updates", $arr_criteria['number_growth_daily_updates']);
		}

		if (isset($arr_criteria['amount_available'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, self::$TABLE_NAME.".amount_available", $arr_criteria['amount_available']);
		}

		return $sql_where;
	}

	function get_drop_down_options($id) {
		$arr_options = $this->get_arr_values();
		//print_r($arr_options);
		$s = Library_Html_Util::build_drop_down_options($id, $arr_options);
		return $s;

	}

	function get_arr_values() {
		//print "blah\n";
		//print_r($this);
		//print_r($this->db);
		//exit(0);
		$sql = "SELECT id,name FROM ".self::$TABLE_NAME." order by name";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);

		foreach ($query->result_array() as $row) {
			$option['key'] = $row['id'];
			$option['value'] = $row['name'];
			$arr_options[] = $option;
		}
		return $arr_options;
	}


	function update($data, $arr_criteria) {
                $this->load->database();

                if ($id > 0) {

			$sql_where = self::get_sql_where($arr_criteria);
			
			$set_sql = "";
			if (is_array($data) && count($data) > 0) {
				foreach($data as $key => $value) {
					$set_sql .= "$key = ".$this->db->escape($value).", ";
				}
				$set_sql = rtrim($set_sql, ", ");
				$set_sql = "set $set_sql";
			}

			if ($sql_where != "") {
				if ($sql_where != "") {
					$sql_where = "where $sql_where";
				}
				$table_name = self::$TABLE_NAME;

				$sql = "update $table_name $set_sql $sql_where";
				$query = $this->db->query($sql);
			} else {
				// dont do query if no criteria
				return 0;
			}

                }
                return $this->db->affected_rows();
        }


	function save($id, $data) {
		$this->load->database();

		/*
		if (isset($data['id') && $data['id'] != "" && $data['id'] > 0) {
			if ($id == 0) {
				$id = $data['id'];
			}
		}
		*/
		
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
		unset($data['id']);
		if ($id > 0) {
			$arr_sql_where = Array();
			$arr_sql_where['id'] = $id;
			unset($data['gm_created']);
			$this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
		} else {
			$data['gm_created'] = Library_DB_Util::time_to_gm_db_time();
			$this->db->insert(self::$TABLE_NAME, $data);
		}
		return $this->db->affected_rows();
	}

	function delete($id) {
                $this->load->database();

                if ($id > 0) {
                        $arr_sql_where = Array();
                        $arr_sql_where['id'] = $id;
                        unset($data['created']);

                        $data = Array();
                        $data['status_id'] = self::$DELETED_STATUS_ID;
                        $this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
                }
                return $this->db->affected_rows();
        }

	function get_number_affected() {
                $this->load->database();
		return $this->db->affected_rows();
	}

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
	}
	function get_last_query() {
                $this->load->database();
		return $this->db->last_query();
	}



	// does more than build it actuall inserts our db_wallet records
	// old does not work if more than 61 table joins using v2 below
	function _build_commission_sql($id, &$sql_tables_more, &$sql_set_more, &$sql_where_more, &$additional_tables, $is_test = false) {

		$db_ph_model = new DB_PHRequests();
		$ph_data = $db_ph_model->get($id);


		if (count($ph_data) > 0) {
			$user_id = $ph_data['user_id'];

			//print_r($ph_data);
			if (!is_array($ph_data) || count($ph_data) == 0) {
				$obj_result->is_unable_to_find_ph_data = true;
				return $obj_result;
			}
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$amount = $ph_data['amount'];


			$db_wallet_logs_model = new DB_WalletLogs();
			$db_user_model = new DB_Users();

			$arr_manager_data = Array();
			// level
			// and id
			//print "user id is $user_id<br>\n";
			$arr_manager_data = $db_user_model->arr_get_all_user_managers_and_level($user_id);

			//print_r($arr_manager_data);

			// grab manager ids and give 
			if (count($arr_manager_data) > 0) {

				for($n=0;$n<count($arr_manager_data);$n++) {
					// how far up level 1 is direct
					$manager_data = $arr_manager_data[$n];
					$level = $manager_data['level'];
					// skip first level we already have it set to update above
					if ($level == 1) {
						continue;
					}
					$manager_user_id = $manager_data['id'];

					
					$temp_data = Array();
					$temp_data['user_id'] = $manager_user_id;
					$temp_data['amount'] = 0;
					$temp_data['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
					$temp_data['log_type_id'] = DB_WalletLogs::$LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID;
					$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
					$temp_data['reference_id'] = $id;
					$temp_data['reference_user_id'] = $user_id;

					$manager_wallet_log_id = 0;
					if (!$is_test) {
						if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
							$manager_wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
						} else {
							// send email error we have a problem here
							$obj_result->is_success = false;
							return $obj_result;
						}
					} else {
						// just doing a test
						$manager_wallet_log_id = 0;
					}

					$level_income_amount = 0;
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

					$current_table_name = "manager_wallet_$n";

					$sql_tables_more .= "".DB_WalletLogs::$TABLE_NAME." as $current_table_name, ";

					$sql_set_more .= "".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";

					$sql_set_more .= "".$current_table_name.".amount = ".$db_wallet_logs_model->db->escape($level_income_amount).", ";

					$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_wallet_log_id)."";
					$sql_where_more .= " and ".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("Y")."";

					$additional_tables++;


					$current_table_name = "manager_user_table_$n";
					$sql_tables_more .= "".DB_Users::$TABLE_NAME." as $current_table_name, ";

					$sql_set_more .= "".$current_table_name.".level_income_balance = ".$current_table_name.".level_income_balance + ".$db_wallet_logs_model->db->escape($level_income_amount).", ";
					$sql_set_more .= "".$current_table_name.".gm_modified = ".$db_wallet_logs_model->db->escape($now_gm_date_time).", ";

					$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_user_id)."";

					$additional_tables++;
				}
			}
		}
	}

	// does more than build it actuall inserts our db_wallet records
	// new version there is a 61 table limit 
	// still working
	function _build_commission_sql_2($id, &$sql_tables_more, &$sql_set_more, &$sql_where_more, &$additional_tables, $is_test = false) {

		$db_ph_model = new DB_PHRequests();
		$ph_data = $db_ph_model->get($id);


		if (count($ph_data) > 0) {
			$user_id = $ph_data['user_id'];

			//print_r($ph_data);
			if (!is_array($ph_data) || count($ph_data) == 0) {
				$obj_result->is_unable_to_find_ph_data = true;
				return $obj_result;
			}
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$amount = $ph_data['amount'];


			// check if user already has more than one ph
			$arr_active_ph_data = $db_ph_model->get_arr_user_ph($user_id);
			
			$is_second_commission = false;
			if (count($arr_active_ph_data) > 1) {
				$is_second_commission = true;
				PlatformLogs::log("ph_update_if_complete", "this is not the first completed ph\n");
			} else {
				PlatformLogs::log("ph_update_if_complete", "this is the first completed ph\n");
			}


			$db_wallet_logs_model = new DB_WalletLogs();
			$db_user_model = new DB_Users();

			$arr_manager_data = Array();
			// level
			// and id
			//print "user id is $user_id<br>\n";
			$arr_manager_data = $db_user_model->arr_get_all_user_managers_and_level($user_id);

			//print_r($arr_manager_data);

			
			// grab manager ids and give 
			if (count($arr_manager_data) > 0) {
				$saved_sql_where_more = "";
				$saved_sql_where_more_user_manager = "";
				$is_have_level_6 = false;

				for($n=0;$n<count($arr_manager_data);$n++) {
					// how far up level 1 is direct
					$manager_data = $arr_manager_data[$n];
					$level = $manager_data['level'];
					// skip first level we already have it set to update above
					if ($level == 1) {
						continue;
					}
					$manager_user_id = $manager_data['id'];

					
					$temp_data = Array();
					$temp_data['user_id'] = $manager_user_id;
					$temp_data['amount'] = 0;
					$temp_data['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
					$temp_data['log_type_id'] = DB_WalletLogs::$LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID;
					$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
					$temp_data['reference_id'] = $id;
					$temp_data['reference_user_id'] = $user_id;

					$manager_wallet_log_id = 0;
					if (!$is_test) {
						if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
							$manager_wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
						} else {
							// send email error we have a problem here
							$obj_result->is_success = false;
							return $obj_result;
						}
					} else {
						// just doing a test
						$manager_wallet_log_id = 0;
					}

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

					
					// if level is 6 or we don't have a level 6
					if ($level <= 6 || ($level >= 6 && !$is_have_level_6)) {

						$current_level = $n;
						if ($level >= 6 && !$is_have_level_6) {
							$current_level = 6;
							$is_have_level_6 = true;
						}
						
						$current_table_name = "manager_wallet_$current_level";

						$sql_tables_more .= "".DB_WalletLogs::$TABLE_NAME." as $current_table_name, ";

						$sql_set_more .= "".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";

						$sql_set_more .= "".$current_table_name.".amount = ".$db_wallet_logs_model->db->escape($level_income_amount).", ";

						$sql_where_more .= " and ".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("Y")."";

						if ($level >= 6) {
							$saved_sql_where_more_wallet .= " or ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_wallet_log_id)."";
						} else {
							$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_wallet_log_id)."";
						}

						$additional_tables++;


						$current_table_name = "manager_user_table_$current_level";
						$sql_tables_more .= "".DB_Users::$TABLE_NAME." as $current_table_name, ";

						$sql_set_more .= "".$current_table_name.".level_income_balance = ".$current_table_name.".level_income_balance + ".$db_wallet_logs_model->db->escape($level_income_amount).", ";
						$sql_set_more .= "".$current_table_name.".gm_modified = ".$db_wallet_logs_model->db->escape($now_gm_date_time).", ";

						if ($level >= 6) {
							$saved_sql_where_more_user_manager .= " or ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_user_id)."";
						} else {
							$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_user_id)."";
						}

						$additional_tables++;
					} else if ($level > 6) {
						$table_level = 6;
						// use same table
						$current_table_name = "manager_wallet_$table_level";

						$saved_sql_where_more_wallet .= " or ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_wallet_log_id)."";

						$additional_tables++;


						$current_table_name = "manager_user_table_$table_level";

						$saved_sql_where_more_user_manager .= " or ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($manager_user_id)."";

						$additional_tables++;
					}
				} // end for

				if ($saved_sql_where_more_wallet != "") {
					$saved_sql_where_more_wallet = trim($saved_sql_where_more_wallet, " or ");
					$saved_sql_where_more_wallet = " and (".$saved_sql_where_more_wallet.")";
					$sql_where_more .= $saved_sql_where_more_wallet;
				}
				if ($saved_sql_where_more_user_manager != "") {
					$saved_sql_where_more_user_manager = trim($saved_sql_where_more_user_manager, " or ");
					$saved_sql_where_more_user_manager = " and (". $saved_sql_where_more_user_manager . ")";
					$sql_where_more .= $saved_sql_where_more_user_manager;
				}
			}
		}
	}



	// needs to implement code that
	// searches for all frozen bonuses. based on reference ID
	// update the available dates to be 30 days after confirmation
	// this is when request is complete

	
	// updates to complete if ph complete
	// update all or nothing
	function update_if_request_complete($id) {


		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_ph_data = false;

                $this->load->database();

		$additional_tables = 0;

		$db_ph_model = new DB_PHRequests();


		$ph_data = $this->get($id);
		$user_id = $ph_data['user_id'];

		//print_r($ph_data);
		if (!is_array($ph_data) || count($ph_data) == 0) {
			$obj_result->is_unable_to_find_ph_data = true;
			return $obj_result;
		}
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

		$amount = $ph_data['amount'];

		// growth amount has been updated earlier no need to increase it
		$growth_amount = $ph_data['amount_growth_given'];


		// we need to insert into GH Growth if amount and amount_matched_completed are equal and active
		

		$sql_where = "";
		$sql_where .= "".self::$TABLE_NAME.".id = $id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".self::$TABLE_NAME.".amount = ".self::$TABLE_NAME.".amount_matched_completed";


		$sql_tables = "".self::$TABLE_NAME.", ";
		$sql_tables = rtrim($sql_tables,", ");

		$sql_fields = self::get_field_names();
		$sql = "select $sql_fields from $sql_tables where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);

		$arr_data = Array();
		foreach ($query->result_array() as $row) {
			$arr_data[] = $row;
		}
		//print_r($arr_data);

		$wallet_log_id = 0;
		$db_wallet_logs_model = new DB_WalletLogs();

		$db_user_model = new DB_Users();

		// check if we have conditions for setting completed. if we do then we want to try to grab parent referral and set their level income. (non manager)
		// we should also try to get all the managers above and give them level income.

		$sql_where = "";

		$sql_where .= "".self::$TABLE_NAME.".id = $id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".self::$TABLE_NAME.".amount = ".self::$TABLE_NAME.".amount_matched_completed";

		$sql_tables = "".self::$TABLE_NAME.", ";
		$sql_tables = rtrim($sql_tables,", ");


		$sql = "select count(*) as number from $sql_tables where $sql_where";
		$query = $this->db->query($sql);


		$sql_set_more = "";
		$sql_where_more = "";
		$sql_tables_more = "";
		$row = $query->row();
		//print_r($row);
		// no race condition here
		// if conditions are that we need to update info
		if (is_object($row) && isset($row->number)) {
			$current_count = $row->number;
			if ($current_count > 0) {
				$wallet_log_id = 0;

				// add to daily tasks 
				
				$temp_data = Array();
				$temp_data['user_id'] = 0;
				$temp_data['amount'] = 0;
				$temp_data['reference_id'] = 0;
				$temp_data['is_available'] = "N";
				$temp_data['wallet_type_id'] = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
				$temp_data['log_type_id'] = DB_WalletLogs::$DAILY_TASK_EARNING_FOR_PH_COMPLETED;
				$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_model->db);
				//print_r($temp_data);
				//print_r($temp_data);
				$number_affected = 0;
				//print_r($temp_data);
				$number_affected = $db_wallet_logs_model->save(0, $temp_data);
				$wallet_log_id = 0;
				if ($number_affected > 0) {
					$wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
				}
				if ($wallet_log_id > 0) {
					$current_table = "daily_task_earning_for_ph_completed";
					$sql_tables_more .= "".DB_WalletLogs::$TABLE_NAME." as $current_table, ";
					$sql_where_more .= " and ".$current_table.".id = ".$db_wallet_logs_model->db->escape($wallet_log_id)."";
					$sql_set_more .= "".$current_table.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";
					$sql_set_more .= "".$current_table.".is_available = ".$db_wallet_logs_model->db->escape("N").", ";
					$sql_set_more .= "".$current_table.".reference_id = ".$db_wallet_logs_model->db->escape($id).", ";
					$sql_set_more .= "".$current_table.".user_id = ".self::$TABLE_NAME.".user_id, ";
					$sql_set_more .= "".$current_table.".amount = ".self::$TABLE_NAME.".amount, ";
					$additional_tables++;
				}

				// add wallet log data

				// insert wallet log for parent level income
				$user_data = $db_user_model->get($user_id);
				if (count($user_data) > 0) {
					$referral_id = $user_data['referral_id'];
				}

				$sponsor_wallet_log_id = 0;
				// bonus for sponsor 10%
				if ($referral_id > 0) {
					$temp_data = Array();
					$temp_data['user_id'] = $referral_id;
					$temp_data['amount'] = 0;
					$temp_data['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
					$temp_data['log_type_id'] = DB_WalletLogs::$LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID;
					$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
					$temp_data['reference_id'] = $id;
					$temp_data['reference_user_id'] = $user_id;

					if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
						$sponsor_wallet_log_id = $db_wallet_logs_model->get_last_insert_id();
					} else {
						// send email error we have a problem here
						$obj_result->is_success = false;
						return $obj_result;
					}

					// must only give 10% on first commit 
					$arr_active_ph_data = $db_ph_model->get_arr_user_ph($user_id);
					$is_second_commission = false;

					if (count($arr_active_ph_data) > 1) {
						$level_income_amount = $amount*.02;
						$is_second_commission = true;
					} else {
						$level_income_amount = $amount*.10;
					}


					$current_table_name = "sponsor_wallet";
					$sql_tables_more .= "".DB_WalletLogs::$TABLE_NAME." as $current_table_name, ";

					$sql_set_more .= "".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("N").", ";
					// 10 %
					$sql_set_more .= "".$current_table_name.".amount = ".$db_wallet_logs_model->db->escape($level_income_amount).", ";

					$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($sponsor_wallet_log_id)."";
					$sql_where_more .= " and ".$current_table_name.".is_pending_create = ".$db_wallet_logs_model->db->escape("Y")."";

					$additional_tables++;

					// we need to update the referral ids user table to add balance

					$current_table_name = "sponsor_user_table";
					$sql_tables_more .= "".DB_Users::$TABLE_NAME." as $current_table_name, ";

					$sql_set_more .= "".$current_table_name.".level_income_balance = ".$current_table_name.".level_income_balance + ".$db_wallet_logs_model->db->escape($level_income_amount).", ";

					$sql_set_more .= "".$current_table_name.".gm_modified = ".$db_wallet_logs_model->db->escape($now_gm_date_time).", ";

					$sql_where_more .= " and ".$current_table_name.".id = ".$db_wallet_logs_model->db->escape($referral_id)."";

					// we skip first level when updating manager otherwise if we add below we dont update all tables
					//$sql_where_more .= " and ".$current_table_name.".is_manager = ".$db_wallet_logs_model->db->escape("N")."";

					$additional_tables++;
				}
				if (count($user_data) > 0) {

					if ($user_data['ph_completed_level'] < 250 && $ph_data['amount'] == 250) {
						$new_ph_completed_level = 250;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] == 250 && $ph_data['amount'] == 500) {
						$new_ph_completed_level = 500;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] == 500 && $ph_data['amount'] == 750) {
						$new_ph_completed_level = 750;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] == 750 && $ph_data['amount'] == 1000) {
						$new_ph_completed_level = 1000;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] == 1000 && $ph_data['amount'] == 1500) {
						$new_ph_completed_level = 1500;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] == 1500 && $ph_data['amount'] == 2000) {
						$new_ph_completed_level = 0;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else {
						if ($user_data['ph_completed_level'] < $ph_data['amount']) {
							if ($ph_data['amount'] == 250) {
								$new_ph_completed_level = 250;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							} else if ($ph_data['amount'] == 500) {
								$new_ph_completed_level = 500;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							} else if ($ph_data['amount'] == 750) {
								$new_ph_completed_level = 750;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							} else if ($ph_data['amount'] == 1000) {
								$new_ph_completed_level = 1000;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							} else if ($ph_data['amount'] == 1500) {
								$new_ph_completed_level = 1500;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							} else if ($ph_data['amount'] == 2000) {
								$new_ph_completed_level = 0;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							}
						} else {
							if ($ph_data['amount'] == 2000) {
								$new_ph_completed_level = 0;
								$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
							}
						}
					}

					/*
					} else if ($user_data['ph_completed_level'] == 2000 && $ph_data['amount'] == 2000) {
						$new_ph_completed_level = 0;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					}
					*/

					/*
					if ($user_data['ph_completed_level'] == $ph_data['amount']) {
						// increase to next level
						// if were at max set back to 250
						if ($user_data['ph_completed_level'] == 250) {
							$new_ph_completed_level = 500;
						} else if ($arr_user_data['ph_completed_level'] <= 500) {
							$new_ph_completed_level = 750;
						} else if ($arr_user_data['ph_completed_level'] <= 750) {
							$new_ph_completed_level = 1000;
						} else if ($arr_user_data['ph_completed_level'] <= 1000) {
							$new_ph_completed_level = 1500;
						} else if ($arr_user_data['ph_completed_level'] <= 1500) {
							$new_ph_completed_level = 2000;
						} else if ($arr_user_data['ph_completed_level'] <= 2000) {
							$new_ph_completed_level = 250;
						}


						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					} else if ($user_data['ph_completed_level'] <= 250 && $ph_data['amount'] == 250) {
						$new_ph_completed_level = 500;
						$sql_set_more .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".$this->db->escape($new_ph_completed_level).", ";
					}
					*/
				}


				self::_build_commission_sql_2($id, $sql_tables_more, $sql_set_more, $sql_where_more, $additional_tables, false);
			}
		}
		
		
		
		$original_sql_set = "";
		$original_sql_tables = "";
		$original_sql_where = "";
		$original_additional_tables = "";

		// we insert into
	

		$sql_wallet_log_tables_more = "";
		$sql_wallet_log_set_more = "";
		$sql_wallet_log_where_more = "";
		$wallet_log_additional_tables = 0;
		if ($growth_amount > 0) {
			$sql_wallet_log_tables_more .= DB_WalletLogs::$TABLE_NAME.", ";
			$sql_wallet_log_set_more .= DB_WalletLogs::$TABLE_NAME.".is_available = 'Y', ";
			$sql_wallet_log_where_more .= " and ".DB_WalletLogs::$TABLE_NAME.".is_available = 'N'";
			$sql_wallet_log_where_more .= " and ".DB_WalletLogs::$TABLE_NAME.".log_type_id = ".DB_WalletLogs::$DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID."";
			$sql_wallet_log_where_more .= " and ".DB_WalletLogs::$TABLE_NAME.".reference_id = ".self::$TABLE_NAME.".id";
			$wallet_log_additional_tables += 1;
		}
	

		
		$sql_set = "";
		// set to complete
		$sql_set .= "".self::$TABLE_NAME.".status_id = ".self::$COMPLETED_STATUS_ID.", ";
		
		$sql_set .= "".self::$TABLE_NAME.".completed_gm_date_time = ".$this->db->escape($now_gm_date_time).", ";
		$sql_set .= "".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";


		// update users table
		// add to column 1 on completed confirmed request
		// confirmed and unconfirmed already updated
		//$sql_set .= "".DB_Users::$TABLE_NAME.".confirmed_ph = ".DB_Users::$TABLE_NAME.".confirmed_ph + ".$this->db->escape($amount).", ";
		//$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".$this->db->escape($amount).", ";

		// need to think about this. we need to reset at the appropriate time and not lose completed levels
		//$sql_set .= "".DB_Users::$TABLE_NAME.".ph_completed_level = ".DB_Users::$TABLE_NAME.".ph_completed_level + ".$this->db->escape($amount).", ";

		// need to update our daily growth balance if were setting to complete

		// growth is increased by daily maintenance
		if ($growth_amount > 0) {
			$sql_wallet_log_set_more .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".self::$TABLE_NAME.".amount_growth_given, ";
		}
		//$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance + ".$this->db->escape($growth_amount).", ";

		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".last_ph_completed_gm_date_time = ".$this->db->escape($now_gm_date_time).", ";


		$sql_set .= $sql_set_more;
		$original_sql_set = $sql_set;
		$sql_set .= $sql_wallet_log_set_more;
		$sql_set = rtrim($sql_set,", ");



	
		// do where

		$sql_where = "";

		$sql_where .= "".self::$TABLE_NAME.".id = ".$this->db->escape($id)."";


		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".self::$TABLE_NAME.".amount = ".self::$TABLE_NAME.".amount_matched_completed";

		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id";



		$sql_where .= $sql_where_more;
		$original_sql_where = $sql_where;
		$sql_where .= $sql_wallet_log_where_more;


		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount

		
		// make sure PH amounts are still valid


		$sql_tables = "".self::$TABLE_NAME.", ".DB_Users::$TABLE_NAME.", ";


		$sql_tables .= $sql_tables_more;

		// backup before adding wallet_log_tables_more
		$original_sql_tables = $sql_tables;
		$sql_tables .= $sql_wallet_log_tables_more; 
		$sql_tables = rtrim($sql_tables,", ");



		// set additional tables
		$original_additional_tables = $additional_tables;
		$additional_tables += $wallet_log_additional_tables;




		$sql = "update $sql_tables set $sql_set where $sql_where";
		PlatformLogs::log("income_level_commission", "user_id is: $user_id ".$sql);
		//print "sql is $sql<br>\n";

		//print "blah blah blah in here\n";
		$user_data = $db_user_model->get($user_id);
		PlatformLogs::log("ph_update_if_complete", "user data is for $user_id\n");
		PlatformLogs::log("ph_update_if_complete", $user_data);


		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();
		PlatformLogs::log("ph_update_if_complete", "query is $sql and number affected is $number_affected\n");
		PlatformLogs::log("ph_update_if_complete", "number affected is $number_affected looking for ".($additional_tables+2)." to be updated\n");
		PlatformLogs::log("ph_update_if_complete", "if number affected is 0 need to run the query without wallet logs references\n");

		$ph_data = $this->get($id);
		PlatformLogs::log("ph_update_if_complete", "ph data is for $id\n");
		PlatformLogs::log("ph_update_if_complete", $ph_data);


		//MatchedRequest::match_all_gh_requests();
		//print "sql is $sql\n";
		//print "number affected is $number_affected<br>\n";
		//print "additional tables is should be ".($additional_tables+2)."<br>\n";
		//exit(0);

		$obj_result->is_success = false;
		if ($growth_amount > 0) {
			// cant tell how many tables
			if ($number_affected >= (2+$additional_tables)) {
				$obj_result->is_success = true;
				return $obj_result;
			}

			if ($number_affected == 0) {
				// do update without wallet log tables
				$sql_set = $original_sql_set;
				$sql_set = rtrim($sql_set,", ");


				$sql_where = $original_sql_where;


				$sql_tables = $original_sql_tables;
				$sql_tables = rtrim($sql_tables,", ");


				$additional_tables = $original_additional_tables;
				
				$sql = "update $sql_tables set $sql_set where $sql_where";
				// means that 0 rows for growth
				// lets try updating without all the growth stuff
				$query = $this->db->query($sql);
				$number_affected = $this->db->affected_rows();
				PlatformLogs::log("ph_update_if_complete", "ran query again without wallet_log references $sql and number affected is $number_affected\n");
				PlatformLogs::log("ph_update_if_complete", "number affected is $number_affected looking for ".($additional_tables+2)." to be updated\n");
			}
		}

		
		if ($number_affected == (2+$additional_tables)) {
			//print "is success\n";
			// we should be good here
			$obj_result->is_success = true;

			// insert new for parent and if not manager 
			// need to insert into wallet and increase 

		} else {
			// error
			// means ph was not set to completed not completed conditions met

		}
		return $obj_result;
	}
}
?>
