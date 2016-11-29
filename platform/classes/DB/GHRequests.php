<?

class DB_GHRequests extends CI_Model {

	//static $DELETED_STATUS_ID = 10;

	static $TABLE_NAME = "ghrequests";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;
	static $COMPLETED_STATUS_ID = 3;

	static $CANCELLED_STATUS_ID = 6;


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

	// check if we have any active gh
	function is_user_have_active_gh($user_id) {
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['status_id'] = self::$ACTIVE_STATUS_ID;
		$arr_data = $this->search($arr_criteria);
		if (count($arr_data) > 0) {
			return true;
		}
		return false;
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

	function get($id) {
		$this->load->database();
		$field_names = self::get_field_names();
		$sql = "SELECT $field_names FROM ".self::$TABLE_NAME." where id = ".$this->db->escape($id)." limit 1";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return $row;
	}

	function arr_get_field_names() {
		$arr[] = "id";
		$arr[] = "user_id";
		$arr[] = "amount";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "magic_bonus";
		$arr[] = "growth";
		$arr[] = "status_id";
		$arr[] = "order_number";
		$arr[] = "amount_matched";
		$arr[] = "amount_available";
		$arr[] = "amount_matched_completed";
		$arr[] = "completed_gm_date_time";
		$arr[] = "number_rejects";
		$arr[] = "is_completed_loh";
		$arr[] = "country";
		$arr[] = "is_loh_approved";
		$arr[] = "is_loh_rejected";
		return $arr;
	}

	// get total gh (beast gh)
	function get_total_gh($user_id) {
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

	// get total matched gh 
	function get_total_matched_gh($user_id) {
		$arr_criteria = Array();
		$arr_criteria['status_id'][] = self::$COMPLETED_STATUS_ID;
		$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['user_id'] = $user_id;
		$arr_data = $this->search($arr_criteria);
		$total = 0;
		for($n=0;$n<count($arr_data);$n++) {
			$total += $arr_data[$n]['amount_matched_completed'];
		}
		return $total;
	}

	function get_total_completed_gh($user_id) {
		$arr_criteria = Array();
		$arr_criteria['status_id'][] = self::$COMPLETED_STATUS_ID;
		//$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['user_id'] = $user_id;
		$arr_data = $this->search($arr_criteria);
		$total = 0;
		for($n=0;$n<count($arr_data);$n++) {
			$total += $arr_data[$n]['amount_matched_completed'];
		}
		return $total;
	}


	function get_field_names() {
		//$value = "".self::$TABLE_NAME.".id, ".self::$TABLE_NAME.".name, ".self::$TABLE_NAME.".segment_id, ".self::$TABLE_NAME.".total_spend, ".self::$TABLE_NAME.".total_client_days, ".self::$TABLE_NAME.".spend_per_client_day, ".self::$TABLE_NAME.".created, ".self::$TABLE_NAME.".modified, ".self::$TABLE_NAME.".status_id";
		$arr = self::arr_get_field_names();
		$value = "";
		for($n=0;$n<count($arr);$n++) {
			$value .= self::$TABLE_NAME.".".$arr[$n].", ";
		}
		$value = rtrim($value, ", ");
		return $value;
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

		$sql = "select $field_names FROM $from_tables $sql_where $order_by";

		//print "sql is $sql\n";

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

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".id", $arr_criteria['id']);
		}

		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".status_id", $arr_criteria['status_id']);
		}

		if (isset($arr_criteria['country'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".country", $arr_criteria['country']);
		}

		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, "user_id", $arr_criteria['user_id']);
		}

		if (isset($arr_criteria['is_loh_approved'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, "is_loh_approved", $arr_criteria['is_loh_approved']);
		}

		if (isset($arr_criteria['amount_available'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".amount_available", $arr_criteria['amount_available']);
		}
		//print "sql where is $sql_where\n";


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

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
	}

	function get_last_query() {
                $this->load->database();
		return $this->db->last_query();
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

	function create_tmp_ph() {
		$db_model = new DB_PHRequests();

		$data = Array();
		$data['user_id'] = 22;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$number_affected = $db_model->save(0, $data);
		
	}

	function create_tmp_gh() {
		$db_model = new DB_GHRequests();

			$data = Array();
			$data['user_id'] = $user_id;
			$data['amount'] = $amount;
			$data['amount_available'] = $amount_available;
			$number_affected = $db_model->save(0, $data);

		$data = Array();
		$data['user_id'] = 22;
		$data['amount'] = 40;
		$data['amount_available'] = 40;
		$number_affected = $db_model->save(0, $data);
		
	}


	function match_active_gh_requests() {
		$arr_criteria = Array();
		$arr_criteria['status_id'] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['amount_available']['>'] = 0;
		$arr_criteria['order_by'] = "gm_created";
		$arr_criteria['order_by_direction'] = "asc";
		$arr_data = $this->search($arr_criteria);
		$db_model = new DB_MatchedRequests();
		//$this->create_tmp_ph();
		//exit(0);
		for($n=0;$n<count($arr_data);$n++) {
			//print_r($arr_data[$n]);
			$db_model->match_to_ph_request($arr_data[$n]['id']);
		}
	}

	// do update to two tables
	function create_gh_request($user_id, $insert_id, $arg_data, $gh_amount) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_able_to_allocate = false;

                $this->load->database();

		// get user country
		$db_user_model = new DB_Users();
		$user_data = $db_user_model->get($user_id);

		$country = "";

		if (count($user_data) > 0) {
			$country = $user_data['country'];
		}

		// insert into wallet logs
		
		$db_wallet_logs = new DB_WalletLogs();

		$number_additional_tables = 0;
		

		$daily_growth_insert_id = 0;
		if ($arg_data->daily_growth_amount > 0) {
			$data = Array();
			$data['user_id'] = $user_id;
			$data['amount'] = -$arg_data->daily_growth_amount;
			$data['wallet_type_id'] = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
			$data['log_type_id'] = DB_WalletLogs::$GH_CREATE_WITHDRAWAL_LOG_TYPE_ID;
			$data['gm_date'] = Library_DB_Util::time_to_gm_db_date($this->db);
			$data['reference_id'] = $insert_id;
			if ($db_wallet_logs->save(0, $data) > 0) {
				$daily_growth_insert_id = $db_wallet_logs->get_last_insert_id();
				$number_additional_tables++;
			}
		}

		$daily_bonus_insert_id = 0;
		if ($arg_data->daily_bonus_amount > 0) {
			$data = Array();
			$data['user_id'] = $user_id;
			$data['amount'] = -$arg_data->daily_bonus_amount;
			$data['wallet_type_id'] = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
			$data['log_type_id'] = DB_WalletLogs::$GH_CREATE_WITHDRAWAL_LOG_TYPE_ID;
			$data['gm_date'] = Library_DB_Util::time_to_gm_db_date($this->db);
			$data['reference_id'] = $insert_id;
			if ($db_wallet_logs->save(0, $data) > 0) {
				$daily_bonus_insert_id = $db_wallet_logs->get_last_insert_id();
				$number_additional_tables++;
			}
		}

		$task_earning_insert_id = 0;
		if ($arg_data->task_earning_amount > 0) {
			$data = Array();
			$data['user_id'] = $user_id;
			$data['amount'] = -$arg_data->task_earning_amount;
			$data['wallet_type_id'] = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
			$data['log_type_id'] = DB_WalletLogs::$GH_CREATE_WITHDRAWAL_LOG_TYPE_ID;
			$data['gm_date'] = Library_DB_Util::time_to_gm_db_date($this->db);
			$data['reference_id'] = $insert_id;
			if ($db_wallet_logs->save(0, $data) > 0) {
				$task_earning_insert_id = $db_wallet_logs->get_last_insert_id();
				$number_additional_tables++;
			}
		}

		$level_income_insert_id = 0;
		if ($arg_data->level_income_amount > 0) {
			$data = Array();
			$data['user_id'] = $user_id;
			$data['amount'] = -$arg_data->level_income_amount;
			$data['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
			$data['log_type_id'] = DB_WalletLogs::$GH_CREATE_WITHDRAWAL_LOG_TYPE_ID;
			$data['gm_date'] = Library_DB_Util::time_to_gm_db_date($this->db);
			$data['reference_id'] = $insert_id;
			if ($db_wallet_logs->save(0, $data) > 0) {
				$level_income_insert_id = $db_wallet_logs->get_last_insert_id();
				$number_additional_tables++;
			}
		}

		// update gh balance and update gh request
		//$sql = "update ".self::$TABLE_NAME.", ".DB_Users::$TABLE_NAME." set ".DB_Users::$TABLE_NAME.".gh_balance =  ".DB_Users::$TABLE_NAME.".gh_balance - ".$this->db->escape($gh_amount).", ".self::$TABLE_NAME.".amount = ".$this->db->escape($gh_amount).", ".self::$TABLE_NAME.".amount_available = ".$this->db->escape($gh_amount)." WHERE ".DB_Users::$TABLE_NAME.".id = $user_id and ".self::$TABLE_NAME.".id = $insert_id and ".DB_Users::$TABLE_NAME.".gh_balance - ".$this->db->escape($gh_amount)." >= 0";
		
		$sql_set = "";

		$sql_set .= "".self::$TABLE_NAME.".amount = ".$this->db->escape($gh_amount).", ";
		$sql_set .= "".self::$TABLE_NAME.".amount_available = ".$this->db->escape($gh_amount).", ";
		$sql_set .= "".self::$TABLE_NAME.".country = ".$this->db->escape($country).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance = ".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance - ".$this->db->escape($arg_data->daily_bonus_amount).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".daily_growth_balance = ".DB_Users::$TABLE_NAME.".daily_growth_balance - ".$this->db->escape($arg_data->daily_growth_amount).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".task_earning_balance = ".DB_Users::$TABLE_NAME.".task_earning_balance - ".$this->db->escape($arg_data->task_earning_amount).", ";
		$sql_set .= "".DB_Users::$TABLE_NAME.".level_income_balance = ".DB_Users::$TABLE_NAME.".level_income_balance - ".$this->db->escape($arg_data->level_income_amount).", ";

		if ($level_income_insert_id > 0) {
			$sql_set .= "level_income_log_table.is_pending_create = ".$this->db->escape("N").", ";
		}

		if ($task_earning_insert_id > 0) {
			$sql_set .= "task_earning_log_table.is_pending_create = ".$this->db->escape("N").", ";
		}
		if ($daily_bonus_insert_id > 0) {
			$sql_set .= "daily_bonus_log_table.is_pending_create = ".$this->db->escape("N").", ";
		}

		if ($daily_growth_insert_id > 0) {
			$sql_set .= "daily_growth_log_table.is_pending_create = ".$this->db->escape("N").", ";
		}

		$sql_set = rtrim($sql_set,", ");

		$sql_where = "".DB_Users::$TABLE_NAME.".id = $user_id";
		$sql_where .= " and ".self::$TABLE_NAME.".id = $insert_id";
		$sql_where .= " and ".DB_Users::$TABLE_NAME.".daily_bonus_earning_balance - ".$this->db->escape($arg_data->daily_bonus_amount)." >= 0";
		$sql_where .= " and ".DB_Users::$TABLE_NAME.".daily_growth_balance - ".$this->db->escape($arg_data->daily_growth_amount)." >= 0";
		$sql_where .= " and ".DB_Users::$TABLE_NAME.".task_earning_balance - ".$this->db->escape($arg_data->task_earning_amount)." >= 0";
		$sql_where .= " and ".DB_Users::$TABLE_NAME.".level_income_balance - ".$this->db->escape($arg_data->level_income_amount)." >= 0";

		if ($level_income_insert_id > 0) {
			$sql_where .= " and level_income_log_table.id = ".$this->db->escape($level_income_insert_id)."";
			$sql_where .= " and level_income_log_table.is_pending_create = ".$this->db->escape("Y")."";
		}

		if ($task_earning_insert_id > 0) {
			$sql_where .= " and task_earning_log_table.id = ".$this->db->escape($task_earning_insert_id)."";
			$sql_where .= " and task_earning_log_table.is_pending_create = ".$this->db->escape("Y")."";
		}
		if ($daily_bonus_insert_id > 0) {
			$sql_where .= " and daily_bonus_log_table.id = ".$this->db->escape($daily_bonus_insert_id)."";
			$sql_where .= " and daily_bonus_log_table.is_pending_create = ".$this->db->escape("Y")."";
		}

		if ($daily_growth_insert_id > 0) {
			$sql_where .= " and daily_growth_log_table.id = ".$this->db->escape($daily_growth_insert_id)."";
			$sql_where .= " and daily_growth_log_table.is_pending_create = ".$this->db->escape("Y")."";
		}

		// handle sql tables

		$sql_tables = "".self::$TABLE_NAME.", ".DB_Users::$TABLE_NAME.", ";
		if ($level_income_insert_id > 0) {
			$sql_tables .= "".DB_WalletLogs::$TABLE_NAME." as level_income_log_table, ";
		}

		if ($task_earning_insert_id > 0) {
			$sql_tables .= "".DB_WalletLogs::$TABLE_NAME." as task_earning_log_table, ";
		}
		if ($daily_bonus_insert_id > 0) {
			$sql_tables .= "".DB_WalletLogs::$TABLE_NAME." as daily_bonus_log_table, ";
		}

		if ($daily_growth_insert_id > 0) {
			$sql_tables .= "".DB_WalletLogs::$TABLE_NAME." as daily_growth_log_table, ";
		}

		$sql_tables = rtrim($sql_tables,", ");
		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";

		$query = $this->db->query($sql);

		if ($this->db->affected_rows() == (2+$number_additional_tables)) {
			// we should be good here
			$obj_result->is_success = true;
			$obj_result->is_able_to_allocate = true;
			return $obj_result;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
			$obj_result->is_able_to_allocate = false;
			return $obj_result;
		}
		return $obj_result;
	}


	// updates to complete if gh complete
	// update all or nothing
	// so far just update one record
	function update_if_request_complete($id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_unable_to_find_gh_data = false;

                $this->load->database();

		$gh_data = $this->get($id);
		if (!is_array($gh_data) || count($gh_data) == 0) {
			$obj_result->is_unable_to_find_gh_data = true;
			return $obj_result;
		}
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();


		
		// set to complete
		$sql_set .= "".self::$TABLE_NAME.".status_id = ".self::$COMPLETED_STATUS_ID."";
		
		$sql_set .= ", ".self::$TABLE_NAME.".completed_gm_date_time = ".$this->db->escape($now_gm_date_time)."";
		$sql_set .= ", ".self::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time)."";


		// update users table

	
		// do where
		$sql_where .= "".self::$TABLE_NAME.".id = $id";

		$sql_where .= " and ".self::$TABLE_NAME.".status_id = ".self::$ACTIVE_STATUS_ID."";
		$sql_where .= " and ".self::$TABLE_NAME.".is_loh_approved = 'Y'";

		$sql_where .= " and ".self::$TABLE_NAME.".amount = ".self::$TABLE_NAME.".amount_matched_completed";


		// make sure gh amounts are still valid
		// should do the below but need to calculate gh_total_amount

		
		// make sure PH amounts are still valid


		$sql_tables = "".self::$TABLE_NAME."";
		$sql = "update $sql_tables set $sql_set where $sql_where";
		//print "sql is $sql\n";


		$query = $this->db->query($sql);
		$number_affected = $this->db->affected_rows();
		//print "number affected is $number_affected\n";


		// should clean up gh request and ph request if match_completed == amount
		if ($number_affected == 1) {
			//print "is success\n";
			// we should be good here
			$obj_result->is_success = true;
		} else {
			// query above all or nothing if not updated then it means 
			// error
			$obj_result->is_success = false;
		}
		return $obj_result;

	}

}
?>
