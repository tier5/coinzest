<?

class DB_UserDailyTasks extends CI_Model {

	static $TABLE_NAME = "user_daily_tasks";

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
		$arr[] = "user_id";
		$arr[] = "round_id";
		$arr[] = "is_user_completed";
		$arr[] = "is_have_user_image_proof";
		$arr[] = "is_approved";
		$arr[] = "user_approved_id";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "gm_user_completed_date_time";
		$arr[] = "gm_approved_date_time";
		$arr[] = "daily_task_id";
		$arr[] = "notes";
		$arr[] = "status_id";
		$arr[] = "year_month";
		$arr[] = "is_have_user_proof_link";
		$arr[] = "proof_link";
		return $arr;
	}

	// get active data tasks for not submitted tasks
	function get_all_user_active_daily_tasks_for_current_round_not_submitted($user_id) {

		$field_names = DB_DailyTasks::get_field_names();
		$this->db->select($field_names . ", ".DB_UserDailyTasks::$TABLE_NAME.".id as user_daily_task_id, ".DB_UserDailyTasks::$TABLE_NAME.".proof_link")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		//$this->db->where('round_id',$round_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('is_have_user_proof_link',"N");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->order_by("year_month", "asc");
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		//print_r($this->db->error());
		//print $this->get_last_query();
		//exit(0);
		$arr_data = $query->result_array();
		return $arr_data;
	}

	// get active data tasks
	function get_all_needs_approval() {

		$field_names = DB_DailyTasks::get_field_names();
		$user_field_names = DB_Users::get_field_names();
		$this->db->select($field_names . ", $user_field_names, ".DB_UserDailyTasks::$TABLE_NAME.".id as user_daily_task_id, ".DB_UserDailyTasks::$TABLE_NAME.".year_month, ".DB_UserDailyTasks::$TABLE_NAME.".gm_created as user_daily_task_gm_created, ".DB_UserDailyTasks::$TABLE_NAME.".proof_link")->from(DB_UserDailyTasks::$TABLE_NAME);
		//$this->db->where('round_id',$round_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('is_have_user_proof_link',"Y");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->order_by("year_month", "asc");
		$this->db->join(DB_DailyTasks::$TABLE_NAME,"".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$this->db->join(DB_Users::$TABLE_NAME,"".DB_UserDailyTasks::$TABLE_NAME.".user_id = ".DB_Users::$TABLE_NAME.".id");
		$query = $this->db->get();
		//print_r($this->db->error());
		//print $this->get_last_query();
		//exit(0);
		$arr_data = $query->result_array();
		return $arr_data;
	}

	// get active data tasks
	function get_all_user_active_daily_tasks_for_current_round($user_id) {

		$field_names = DB_DailyTasks::get_field_names();
		$this->db->select($field_names . ", ".DB_UserDailyTasks::$TABLE_NAME.".id as user_daily_task_id")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		//$this->db->where('round_id',$round_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->order_by("year_month", "asc");
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		//print_r($this->db->error());
		//print $this->get_last_query();
		//exit(0);
		$arr_data = $query->result_array();
		return $arr_data;
	}

	// get active data tasks
	function get_arr_user_active_daily_tasks_for_current_round_month_year($user_id, $month = "", $year = "") {
		/*
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['round_id'] = $round_id;
		$this->search($arr_criteria);
		*/
		$month = "";
		$year = "";
		if ($month == "") {
			$month = gmdate("n");
		}
		if ($year == "") {
			$year = gmdate("Y");
		}

		if (strlen($month) == 1) {
			$month = "0" . $month;
		}

		$this->db->select(DB_UserDailyTasks::$TABLE_NAME.".id")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		//$this->db->where('round_id',$round_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('year_month',"$year-$month");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		if($query->num_rows > 0) {
			return true;
		}
		return false;
	}

	// get whether user has current tasks for round
	function is_user_have_active_tasks_for_current_round_month_year($user_id, $month = "", $year = "") {
		/*
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['round_id'] = $round_id;
		$this->search($arr_criteria);
		*/
		$month = "";
		$year = "";
		if ($month == "") {
			$month = gmdate("n");
		}
		if ($year == "") {
			$year = gmdate("Y");
		}

		if (strlen($month) == 1) {
			$month = "0" . $month;
		}

		$this->db->select(DB_UserDailyTasks::$TABLE_NAME.".id")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		//$this->db->where('round_id',$round_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('year_month',"$year-$month");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		if($query->num_rows > 0) {
			return true;
		}
		return false;
	}

	/*
	// this should gorup and then count how many
	update table 
	   set valid = -1
	 where id in (select id
			from table 
		       where id = GIVEN_ID
		    group by id
		      having count(1) >3)
	// doesnt work can't do update when doing select inside same table
	update `daily_tasks` set round_id = round_id +1 WHERE daily_tasks.status_id not in (select daily_tasks.status_id 
			from daily_tasks 
		    group by daily_tasks.status_id
		      having count(1) >3)
	*/
	// get whether user has current tasks for round

	function get_user_number_of_active_tasks_for_current_round_not_submitted($user_id) {
		$this->db->select("count(".DB_UserDailyTasks::$TABLE_NAME.".id) as total")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('is_have_user_proof_link',"N");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		$data = $query->row_array();
		if (count($data > 0)) {
			return $data['total'];
		}
		return 0;
	}

	// get whether user has current tasks for round
	function get_user_number_of_active_tasks_for_current_round($user_id) {

		$this->db->select("count(".DB_UserDailyTasks::$TABLE_NAME.".id) as total")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		$data = $query->row_array();
		if (count($data > 0)) {
			return $data['total'];
		}
		return 0;
	}

	// get whether user has current tasks for round
	function get_user_number_of_active_tasks_for_current_round_month_year($user_id, $month = "", $year = "") {
		/*
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['round_id'] = $round_id;
		$this->search($arr_criteria);
		*/
		$month = "";
		$year = "";
		if ($month == "") {
			$month = gmdate("n");
		}
		if ($year == "") {
			$year = gmdate("Y");
		}

		if (strlen($month) == 1) {
			$month = "0" . $month;
		}
		/*
		if ($year_month != "") {
			$arr_split = preg_split("/-/", $year_month);
			if (count($arr_split) == 2 && is_numeric($month) && is_numeric($year)) {
				$month = $arr_split[1];
				$year = $arr_split[0];
			}
			
		}
		if ($month == "" && $year == "") {
			$year = gmdate("Y");
			$month = gmdate("n");
		}
		*/


		

		$this->db->select("count(".DB_UserDailyTasks::$TABLE_NAME.".id) as total")->from(DB_UserDailyTasks::$TABLE_NAME);
		$this->db->where('user_id',$user_id);
		$this->db->where('is_user_completed',"N");
		$this->db->where('year_month',"$year-$month");
		$this->db->where(DB_UserDailyTasks::$TABLE_NAME.'.status_id',self::$ACTIVE_STATUS_ID);
		$this->db->join(DB_DailyTasks::$TABLE_NAME, "".DB_DailyTasks::$TABLE_NAME.".current_round_id = ".DB_UserDailyTasks::$TABLE_NAME.".round_id and ".DB_DailyTasks::$TABLE_NAME.".id = ".DB_UserDailyTasks::$TABLE_NAME.".daily_task_id");
		$query = $this->db->get();
		$data = $query->row_array();
		if (count($data > 0)) {
			return $data['total'];
		}
		return 0;
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

		if (isset($arr_criteria['user_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".user_id", $arr_criteria['user_id']);
		}

		if (isset($arr_criteria['is_user_completed'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".user_id", $arr_criteria['is_user_completed']);
		}

		if (isset($arr_criteria['round_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".round_id", $arr_criteria['round_id']);
		}

		if (isset($arr_criteria['is_have_user_proof_link'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_have_user_proof_link", $arr_criteria['is_have_user_proof_link']);
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
