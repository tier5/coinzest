<?

class DB_DailyTasks extends CI_Model {

	static $TABLE_NAME = "daily_tasks";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;

	// maybe should that
	// status reason type id



	public function __construct() {
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
		$arr[] = "title";
		$arr[] = "description";
		$arr[] = "external_url";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "status_id";
		$arr[] = "display_counter";
		$arr[] = "long_description";
		$arr[] = "current_round_id";
		return $arr;
	}


	
	function get_field_names($table_name = "") {
		$arr = self::arr_get_field_names();
		$value = "";

		$is_alt_table_name = false;
		if ($table_name == "") {
			$table_name = self::$TABLE_NAME;
		} else {
			$is_alt_table_name = true;
		}
		for($n=0;$n<count($arr);$n++) {
			if ($is_alt_table_name) {
				$value .= $table_name.".".$arr[$n]." as ".$table_name."_".$arr[$n].", ";
			} else {
				$value .= $table_name.".".$arr[$n].", ";
			}
		}
		$value = rtrim($value, ", ");
		return $value;
	}


	/*
	function get_field_names() {
		$value = "".self::$TABLE_NAME.".id, ".self::$TABLE_NAME.".login, ".self::$TABLE_NAME.".first_name, ".self::$TABLE_NAME.".last_name, ".self::$TABLE_NAME.".email, ".self::$TABLE_NAME.".password, ".self::$TABLE_NAME.".created, ".self::$TABLE_NAME.".modified, ".self::$TABLE_NAME.".status_id, ".self::$TABLE_NAME.".is_admin, ".self::$TABLE_NAME.".otp, ".self::$TABLE_NAME.".country, ".self::$TABLE_NAME.".referral_id, ".self::$TABLE_NAME.".fullname, ".self::$TABLE_NAME.".btc_address, ".self::$TABLE_NAME.".gm_created, ".self::$TABLE_NAME.".gm_modified, ".self::$TABLE_NAME.".phone, ".self::$TABLE_NAME.".otp_expire_gm_date_time, ".self::$TABLE_NAME.".is_verified";
		return $value;
	}
	*/

	function search($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);
		//print_r($arr_criteria);
		//print "sql where is $sql_where<br>\n";

		if ($order_by == "") {
                        $order_by = "order by id  asc";
                }
		if (isset($arr_criteria['order_by']) && isset($arr_criteria['order_by_direction'])) {
			$order_by = "order by ". $arr_criteria['order_by']." ".$arr_criteria['order_by_direction'];
		}

		$limit = "";
		if (isset($arr_criteria['limit'])) {
			$limit = "limit ".$this->db->escape($arr_criteria['limit'])."";
		}
		if (isset($arr_criteria['number_results_per_page'])) {
			$limit = "limit ".$this->db->escape($arr_criteria['number_results_per_page'])."";
		}
		//print "sql where is $sql_where\n";


		if ($sql_where == "") {
		} else {
			$sql_where .= " and ";
		}

		$sql_where .= " ".self::$TABLE_NAME.".status_id <> ".self::$DELETED_STATUS_ID."";

		if ($sql_where != "") {
			$sql_where = " where $sql_where";
		}


		$field_names = self::get_field_names();
		$sql = "select $field_names from ".self::$TABLE_NAME."  $sql_where $order_by $limit";
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


	function get_sql_where($arr_criteria, $table_name = "") {
		$this->load->database();
		$sql_where = "";

		if ($table_name == "") {
			$table_name = self::$TABLE_NAME;
		}

		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".status_id", $arr_criteria['status_id']);
		}

		if (isset($arr_criteria['login'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".login", $arr_criteria['login']);
		}

		if (isset($arr_criteria['is_done_registration_process'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_done_registration_process", $arr_criteria['is_done_registration_process']);
		}

		if (isset($arr_criteria['is_test_match_account'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_test_match_account", $arr_criteria['is_test_match_account']);
		}

		if (isset($arr_criteria['is_allow_ph_to_match_restricted_countries'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_allow_ph_to_match_restricted_countries", $arr_criteria['is_allow_ph_to_match_restricted_countries']);
		}

		if (isset($arr_criteria['is_registration_reviewed'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_registration_reviewed", $arr_criteria['is_registration_reviewed']);
		}
		//print_r($arr_criteria);
		//print "sql where is $sql_where<br>\n";

		if (isset($arr_criteria['is_activated'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_activated", $arr_criteria['is_activated']);
		}

		if (isset($arr_criteria['is_have_old_data'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_have_old_data", $arr_criteria['is_have_old_data']);
		}

		if (isset($arr_criteria['is_old_data_verified'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_old_data_verified", $arr_criteria['is_old_data_verified']);
		}

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= " ".$table_name.".id = ".$this->db->escape($arr_criteria['id']);
		}


		if (isset($arr_criteria['search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".login like ".$this->db->escape("%".$arr_criteria['search']."%")."";
                }

		if (isset($arr_criteria['exact_search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".login like ".$this->db->escape($arr_criteria['exact_search'])."";
                }




		return $sql_where;
	}

	function get_drop_down_options($id) {
		$arr_options = $this->get_arr_values();
		//print_r($arr_options);
		$s = Library_Html_Util::build_drop_down_options($id, $arr_options);
		return $s;

	}

	/*
	// lock table to prevent duplicate btc address
	function update_btc_address($id, $btc_address) {
		$sql = "LOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);


		$arr_criteria = Array();
		$arr_criteria['btc_address'] = $btc_address;
		$arr_data = $this->search($arr_criteria);
		if (count($arr_data) == 0) {
			$data = Array();
			$data['btc_address'] = $btc_address;
			$this->save($id, $data);
		}

		$sql = "UNLOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);
	}
	*/

	/*
	// lock table to prevent duplicate phone number
	function update_phone_number($id, $phone_number) {
		
		$sql = "LOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);


		$arr_criteria = Array();
		$arr_criteria['phone'] = $phone_number;
		$arr_data = $this->search($arr_criteria);
		if (count($arr_data) == 0) {
			$data = Array();
			$data['phone'] = $phone_number;
			$this->save($id, $data);
		}

		$sql = "UNLOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);
	}
	*/

	function get_arr_values() {
		//print "blah\n";
		//print_r($this);
		//print_r($this->db);
		//exit(0);
		$sql = "SELECT id,login FROM ".self::$TABLE_NAME." order by login";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);

		foreach ($query->result_array() as $row) {
			$option['key'] = $row['id'];
			$option['value'] = $row['zipcode'];
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
		//print_r($data);
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

	function update_user_last_request($user_id) {
                $this->load->database();
		if ($user_id > 0) {
			$arr_sql_where = Array();
			$arr_sql_where['id'] = $user_id;
			$data = Array();
			$data['last_request_gm_date_time'] = Library_DB_Util::time_to_gm_db_time();
			$this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
		}
	}


	function get_count_active_registered_users() {
                $this->load->database();
		$arr_criteria = Array();
		$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['status_id'][] = self::$SUSPENDED_STATUS_ID;
		// just do a regular count i guess
		$sql_where = self::get_sql_where($arr_criteria);

		$sql = "select count(*) as amount from ".self::$TABLE_NAME." where $sql_where";
		//print "sql is $sql\n";
		//$query = $this->db->query($sql);
		//$res = $query->result();  // this returns an object of all results
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row) {
			if (is_array($row)) {
				//print_r($row);
				return $row['amount'];
			}
			
		}
		return 0;
	}

	function get_number_affected() {
                $this->load->database();
		return $this->db->affected_rows();
	}

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
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

	function get_last_query() {
                $this->load->database();
		return $this->db->last_query();
	}

	function delete($id) {
                $this->load->database();

                if ($id > 0) {
                        $arr_sql_where = Array();
                        $arr_sql_where['id'] = $id;

                        $data = Array();
                        $data['status_id'] = self::$DELETED_STATUS_ID;
                        $this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
                }
                return $this->db->affected_rows();
        }



}
?>
