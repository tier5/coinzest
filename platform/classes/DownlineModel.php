<?

class DownlineModel {

	function get_arr_ph_requests_for_user($user_id) {
		$db_model = new DB_PHRequests();	
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
		$arr_criteria['join_user_table'] = $user_id;
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

	// go through all completed ph with number_growth_updates < 30
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

	

	// arr excludes ids in case of circular referrals
	function get_levels_left_join_sql($levels, $arr_exclude_ids) {
		$left_join = "";

		if ($levels >= 2) {
			for($n = 2;$n <= $levels; $n++) {
				$table_name ="t$n";
				$prev_level_table_name = "t" . ($n-1);


				if ($n == $levels) {
					$table_name = DB_Users::$TABLE_NAME;
					// final left join
					$left_join .= "
						LEFT JOIN ".DB_Users::$TABLE_NAME." on ($table_name.referral_id = $prev_level_table_name.id";
				} else {
					$left_join .= "
						LEFT JOIN ".DB_Users::$TABLE_NAME." as $table_name on ($table_name.referral_id = $prev_level_table_name.id";
				}
				if (count($arr_exclude_ids) > 0) {
					$temp = "";
					for($r=0;$r<count($arr_exclude_ids);$r++) {
						$arr_exclude_data = $arr_exclude_ids[$r];
						
						if ($n > $arr_exclude_data['level']) {
							if ($temp == "") {
								$temp .= "$table_name.id <> ".$arr_exclude_data['id']."";
							} else {
								$temp .= " and $table_name.id <> ".$arr_exclude_data['id']."";
							}
						}
					}
					if ($temp != "") {
						$left_join .= " and (";
						$left_join .= "$temp)";
					}
				}
				$left_join .= ")\n";
			}
		}
		return $left_join;
	}

	// levels have to be 
	function get_downline_sql($user_id, $levels, $arr_exclude_ids) {

		if ($levels >= 1) {
			$left_join = "";
			$sql_where = "";
			if ($levels == 1) { 
				$sql_tables = "".DB_Users::$TABLE_NAME."";
				$sql_where = "where ".DB_Users::$TABLE_NAME.".referral_id = $user_id and ".DB_Users::$TABLE_NAME.".id <> $user_id";
			} else if ($levels >= 2) {
				$sql_tables = "".DB_Users::$TABLE_NAME." as t1";
				$left_join = self::get_levels_left_join_sql($levels, $arr_exclude_ids);
				$left_join = " $left_join";
				$sql_where = "where t1.referral_id = $user_id and t1.id <> $user_id";
			}
			$from = "$sql_tables$left_join";
			$field_names = DB_Users::get_field_names();
			//$field_names = "*";
			$sql = "select $field_names from $from $sql_where";
			return $sql;
		}
		return "";
	}

	function get_arr_user_downline_data($user_id, $number_of_levels = 0) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$db_model = new DB_Users();

		$arr_data = Array();
		$arr_exclude_ids = Array();
		$arr_exclude_data = Array();
		$arr_exclude_data['id'] = $user_id;
		$arr_exclude_data['level'] = 0;
		$arr_exclude_ids[] = $arr_exclude_data;
		$n = 1;
		//for($n = 1;$n<= 7;$n++) {
		// keep going until we dont have any more data
		$is_have_data = true;
		while($is_have_data) {
			$is_have_data = false;
			//print "level $n\n";
			$sql = self::get_downline_sql($user_id, $n, $arr_exclude_ids);

			//print "sql is $sql\n";
			$temp_sql = "SET SQL_BIG_SELECTS=1";
			$query = $this->db->query($temp_sql);

			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row) {
				if ($row['id'] > 0)  {
					$row['downline_level'] = $n;
					$arr_data[] = $row;
					$arr_exclude_data = Array();
					$arr_exclude_data['id'] = $row['id'];
					$arr_exclude_data['level'] = $n;
					$arr_exclude_ids[] = $arr_exclude_data;
					$is_have_data = true;

				}
			}
			if ($number_of_levels > 0 && $n == $number_of_levels) {
				$is_have_data = false;
			}
			$n++;
		}
		//print_r($arr_data);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		return $obj_result;
	}
}
?>
