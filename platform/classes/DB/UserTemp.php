<?

class DB_UserTemp extends CI_Model {

	//static $DELETED_STATUS_ID = 10;

	static $TABLE_NAME = "user_temp";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;
	static $COMPLETED_STATUS_ID = 3;

	// maybe dont need expired
	static $EXPIRED_STATUS_ID = 4;

	static $REJECTED_STATUS_ID = 5;

	static $AVAILABLE_STATUS_ID = 6;



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

	function get_by_user_id($id) {
		$this->load->database();
		$field_names = self::get_field_names();
		$sql = "SELECT $field_names FROM ".self::$TABLE_NAME." where user_id = ".$this->db->escape($id)." limit 1";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return $row;
	}

	function arr_get_field_names() {
		$arr[] = "id";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "ph_amount_20";
		$arr[] = "is_ph_amount_20_paid";
		$arr[] = "ph_amount_80";
		$arr[] = "is_ph_amount_80_paid";
		$arr[] = "ph_amount_100";
		$arr[] = "is_ph_amount_100_paid";
		$arr[] = "is_have_gh_history_proof_image";
		$arr[] = "last_gh_amount";
		$arr[] = "last_gh_payment_received";
		$arr[] = "level_income_status_balance";
		$arr[] = "daily_growth_balance";
		$arr[] = "daily_bonus_balance";
		$arr[] = "is_have_income_level_gh_status_proof_image";
		$arr[] = "is_have_daily_growth_status_proof_image";
		$arr[] = "is_have_gh_request_available_balance_proof_image";
		$arr[] = "user_id";
		$arr[] = "is_fields_locked";
		$arr[] = "is_have_ph_history_proof_image";
		$arr[] = "stored_data";
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


	function search($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);




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


	function get_sql_where($arr_criteria) {
		$this->load->database();
		$sql_where = "";
		$is_found = false;


		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$is_found = true;
		}

		if (isset($arr_criteria['is_fields_locked'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_fields_locked = ".$this->db->escape($arr_criteria['is_fields_locked']);
			$is_found = true;
		}
		if (isset($arr_criteria['is_have_gh_history_proof_image'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_have_gh_history_proof_image = ".$this->db->escape($arr_criteria['is_have_gh_history_proof_image']);
			$is_found = true;
		}

		if (isset($arr_criteria['is_have_income_level_gh_status_proof_image'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_have_income_level_gh_status_proof_image = ".$this->db->escape($arr_criteria['is_have_income_level_gh_status_proof_image']);
			$is_found = true;
		}

		if (isset($arr_criteria['is_have_daily_growth_status_proof_image'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_have_daily_growth_status_proof_image = ".$this->db->escape($arr_criteria['is_have_daily_growth_status_proof_image']);
			$is_found = true;
		}

		if (isset($arr_criteria['is_have_gh_request_available_balance_proof_image'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= "".self::$TABLE_NAME.".is_have_gh_request_available_balance_proof_image = ".$this->db->escape($arr_criteria['is_have_gh_request_available_balance_proof_image']);
			$is_found = true;
		}


		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= "".self::$TABLE_NAME.".user_id = ".$this->db->escape($arr_criteria['user_id']);
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, self::$TABLE_NAME.".user_id", $arr_criteria['user_id']);
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


}
?>
