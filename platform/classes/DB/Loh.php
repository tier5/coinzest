<?

class DB_Loh extends CI_Model {

	static $TABLE_NAME = "loh";

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
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "status_id";
		$arr[] = "gh_request_id";
		$arr[] = "user_id";
		$arr[] = "letter_contents";
		$arr[] = "video_link";
		$arr[] = "is_approved";
		$arr[] = "approved_by_user_id";
		$arr[] = "is_redo";
		$arr[] = "is_rejected";
		$arr[] = "redo_reason";
		$arr[] = "bonus_amount";

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

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".id", $arr_criteria['id']);
		}


		if (isset($arr_criteria['gm_created'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".gm_created", $arr_criteria['gm_created']);
		}


		if (isset($arr_criteria['gm_modified'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".gm_modified", $arr_criteria['gm_modified']);
		}


		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".status_id", $arr_criteria['status_id']);
		}


		if (isset($arr_criteria['gh_request_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".gh_request_id", $arr_criteria['gh_request_id']);
		}


		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".user_id", $arr_criteria['user_id']);
		}


		if (isset($arr_criteria['letter_contents'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".letter_contents", $arr_criteria['letter_contents']);
		}


		if (isset($arr_criteria['video_link'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".video_link", $arr_criteria['video_link']);
		}


		if (isset($arr_criteria['is_approved'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_approved", $arr_criteria['is_approved']);
		}


		if (isset($arr_criteria['approved_by_user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".approved_by_user_id", $arr_criteria['approved_by_user_id']);
		}




		if (isset($arr_criteria['search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " (".$table_name.".id like ".$this->db->escape("%".$arr_criteria['search']."%")." or ".$table_name.".id like ".$this->db->escape("%".$arr_criteria['search']."%").")";
                }

		if (isset($arr_criteria['exact_search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".id like ".$this->db->escape($arr_criteria['exact_search'])."";
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
