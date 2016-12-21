<?

class DB_WalletLogs extends CI_Model {

	//static $DELETED_STATUS_ID = 10;

	static $TABLE_NAME = "wallet_logs";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;
	static $COMPLETED_STATUS_ID = 3;

	// maybe dont need expired
	static $EXPIRED_STATUS_ID = 4;

	static $REJECTED_STATUS_ID = 5;

	static $AVAILABLE_STATUS_ID = 6;


	static $LEVEL_INCOME_WALLET_TYPE_ID = 1;
	static $DAILY_GROWTH_WALLET_TYPE_ID = 2;
	static $DAILY_BONUS_EARNING_WALLET_TYPE_ID = 3;
	static $TASK_EARNING_WALLET_TYPE_ID = 4;

	static $CUSTOM_LOG_TYPE_ID = 25;
	static $PENALTY_LOG_TYPE_ID = 26;
	static $BONUS_LOG_TYPE_ID = 27;

	static $WITHDRAWAL_LOG_TYPE_ID = 30;
	static $GH_CREATE_WITHDRAWAL_LOG_TYPE_ID = 31;

	static $DAILY_BONUS_GAME_DEPOSIT_LOG_TYPE_ID = 40;
	static $DAILY_GROWTH_DEPOSIT_LOG_TYPE_ID = 41;

	static $NEW_MEMBER_BONUS_FOR_FIRST_PH_LOG_TYPE_ID = 50;

	static $NEW_SPONSOR_BONUS_FOR_FIRST_10K_LOG_TYPE_ID = 51;


	static $LEVEL_INCOME_DEPOSIT_LOG_TYPE_ID = 60;


	static $DAILY_TASK_EARNING_FOR_PH_COMPLETED = 80;


	static $SYSTEM_ERROR_CORRECTION_LOG_TYPE_ID = 200;

	static $LOH_BONUS_LOG_TYPE_ID = 105;
	



	static $ADMIN_ADJUSTMENT_LOG_TYPE_ID = 100;
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
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "status_id";
		$arr[] = "user_id";
		$arr[] = "amount";
		$arr[] = "gm_date";
		$arr[] = "is_available";
		$arr[] = "wallet_type_id";
		$arr[] = "log_type_id";
		$arr[] = "is_pending_create";
		$arr[] = "custom_remark";
		$arr[] = "available_gm_create_date_time";
		$arr[] = "reference_id";
		$arr[] = "reference_user_id";
		$arr[] = "is_release_part_of_funds_on_ph_complete";
		$arr[] = "amount_available";
		$arr[] = "wallet_log_reference_id";
		$arr[] = "task_identifier_tmp";
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

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
	}

	function get_number_affected() {
                $this->load->database();
		return $this->db->affected_rows();
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


		if ($sql_where != "") {
			$sql_where = " where $sql_where";
		}

		$sql = "select $field_names FROM $from_tables $sql_where $order_by";
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


	function get_total_frozen($user_id, $wallet_type_id) {
		$this->load->database();

		$arr_criteria = Array();
		$arr_criteria['is_available'] = "N";
		$arr_criteria['wallet_type_id'] = $wallet_type_id;
		$arr_criteria['status_id'] = self::$ACTIVE_STATUS_ID ;
		$arr_criteria['user_id'] = $user_id;
		$sql_where = self::get_sql_where($arr_criteria);

		$sql = "select sum(amount) as total from ".self::$TABLE_NAME." where $sql_where";
		$query = $this->db->query($sql);
		$arr_data = $query->result_array();
		//print_r($arr_data);
		if ($arr_data[0]['total'] == "") {
			$total = 0;
		} else {
			$total = (Int) $arr_data[0]['total'];
		}
		return $total;
	}

	function get_sql_where($arr_criteria) {
		$this->load->database();
		$sql_where = "";
		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".status_id = ".$this->db->escape($arr_criteria['status_id']);
		}

		if (isset($arr_criteria['wallet_type_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".wallet_type_id = ".$this->db->escape($arr_criteria['wallet_type_id']);
		}

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".id", $arr_criteria['id']);
		}

		if (isset($arr_criteria['log_type_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".log_type_id", $arr_criteria['log_type_id']);
		}

		if (isset($arr_criteria['is_available'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".is_available", $arr_criteria['is_available']);
		}

		if (isset($arr_criteria['reference_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".reference_id", $arr_criteria['reference_id']);
		}

		if (isset($arr_criteria['is_release_part_of_funds_on_ph_complete'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".is_release_part_of_funds_on_ph_complete", $arr_criteria['is_release_part_of_funds_on_ph_complete']);
		}

		if (isset($arr_criteria['amount_available'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".amount_available", $arr_criteria['amount_available']);
		}

		if (isset($arr_criteria['wallet_log_reference_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".wallet_log_reference_id", $arr_criteria['wallet_log_reference_id']);
		}

		if (isset($arr_criteria['is_pending_create'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_pending_create = ".$this->db->escape($arr_criteria['is_pending_create']);
		}


		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, self::$TABLE_NAME.".user_id", $arr_criteria['user_id']);
		}

		if (isset($arr_criteria['number_growth_daily_updates'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_number_comparison_sql($this->db, self::$TABLE_NAME.".number_growth_daily_updates", $arr_criteria['number_growth_daily_updates']);
		}

		if (isset($arr_criteria['amount'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".amount", $arr_criteria['amount']);
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

	function update($data, $arr_criteria) {
                $this->load->database();

		$sql_where = self::get_sql_where($arr_criteria);
		
		$set_sql = "";
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
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
			//print "sql is $sql\n";
			$query = $this->db->query($sql);
		} else {
			// dont do query if no criteria
			return 0;
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

}
?>
