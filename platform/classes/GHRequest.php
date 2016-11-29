<?

class GHRequest {

	// inserts blank gh request and then updates both balance for user and phrequest
	function create_gh_request($user_id, $arg_data, $gh_amount) {

		$obj_result = new stdclass();
		$obj_result->is_success = false;
		$obj_result->is_able_to_allocate = false;

		$db_ghr_model = new DB_GHRequests();	

		$data = Array();
		$data['user_id'] = $user_id;
		$db_ghr_model->save(0, $data);
		$insert_id = $db_ghr_model->get_last_insert_id();

		
		
		//print "insert id is $insert_id\n";
		if ($insert_id > 0) {
			// update gh request
			$result = $db_ghr_model->create_gh_request($user_id, $insert_id, $arg_data, $gh_amount);
			if ($result->is_success) {
				// send out an email
				Emailer::send_gh_request_created_email($user_id, $insert_id);
				
				// 
				// take this out eventually
				$is_dev_server = Registry::get("is_dev_server");

				if ($is_dev_server) {
					//$db_ghr_model->create_tmp_ph();
				}

				$obj_result->is_success = true;
				$obj_result->is_able_to_allocate = true;

			
				// take out the below two
				$db_mr_model =  new DB_MatchedRequests();
				$db_mr_model->match_to_ph_request($insert_id);

			} else {
				$obj_result->is_able_to_allocate = false;
				$data = Array();
				$data['status_id'] = DB_GHRequests::$DELETED_STATUS_ID;
				$db_ghr_model->save($insert_id, $data);
			}
			//$sql = "update 
		}

		return $obj_result;
	}

	function is_valid_gh_amount($amount) {
		if ($amount < 20) {
			return false;
		} else if (fmod($amount,10) == 0) {
			return true;
		}
		return false;
	}


	function get_arr_gh_requests_for_user($user_id, $is_get_cancelled_and_completed_gh = false) {
		$db_model = new DB_GHRequests();	
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		if ($is_get_cancelled_and_completed_gh) {
			$arr_criteria['status_id'][] = DB_GHRequests::$COMPLETED_STATUS_ID;
			$arr_criteria['status_id'][] = DB_GHRequests::$CANCELLED_STATUS_ID;
		} else {
			$arr_criteria['status_id'] = DB_GHRequests::$ACTIVE_STATUS_ID;
		}
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


	// dont think this is being used
	public function cancel_gh($gh_id, $cancelled_by_user_id) {
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$db_model = new DB_GHRequests();

		$now_time = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db, $now_time);


		



		$sql_set = "";

		// ph info
		if ($gh_id > 0) {
			$gh_data = $db_model->get($gh_id);
			$user_id = $gh_data['user_id'];
		} else {
			return $obj_result;
		}

		// set is approved for matching for user to N

		// update user
		$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";


		$current_table_name = "sum_ph_table";
		//$subselect = "select SUM($current_table_name.amount_matched - $current_table.amount_matched_completed) from ".DB_PHRequests::$TABLE_NAME." group by ".DB_PHRequests::$TABLE_NAME.".user_id group by ".DB_PHRequests::$TABLE_NAME.".user_id as $current_table_name group by ".DB_PHRequests::$TABLE_NAME.".user_id"; 
		// have to remove uncofirmed ph 
		$sql_set .= "".DB_Users::$TABLE_NAME.".unconfirmed_ph = ".DB_Users::$TABLE_NAME.".unconfirmed_ph - ".DB_PHRequests::$TABLE_NAME.".amount_matched + ".DB_PHRequests::$TABLE_NAME.".amount_matched_completed, ";



		// update gh request
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_available = 0, ";



		// updated ph request
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".DB_PHRequests::$TABLE_NAME.".amount_available + ".DB_MatchedRequests::$TABLE_NAME.".amount, ";

		// remove matched amount
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_matched = ".DB_PHRequests::$TABLE_NAME.".amount_matched - ".DB_MatchedRequests::$TABLE_NAME.".amount, ";
		$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";




		// update matched_requests
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$CANCELLED_STATUS_ID.", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".cancelled_by_user_id = ".$this->db->escape($cancelled_by_user_id).", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".cancelled_reason_type_id = ".DB_MatchedRequests::$GENERAL_CANCELLED_REASON_TYPE_ID.", ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".rejected_by = '', ";
		$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";


		$sql_set = rtrim($sql_set,", ");

	
		// do where
		$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".$db_model->db->escape($gh_id)."";

		$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".ghrequest_id = ".DB_GHRequests::$TABLE_NAME.".id";

		$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".id = ".DB_MatchedRequests::$TABLE_NAME.".phrequest_id";

		$sql_where .= " and ".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";

		$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$ACTIVE_STATUS_ID."";

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
			// update user
			$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
			//$sql_set .= "".DB_Users::$TABLE_NAME.".total_ph = ".DB_Users::$TABLE_NAME.".total_ph - ".DB_PHRequests::$TABLE_NAME.".amount, ";


			// updated gh request
			$sql_set .= "".DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
			$sql_set .= "".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$CANCELLED_STATUS_ID.", ";
			$sql_set .= "".DB_GHRequests::$TABLE_NAME.".amount_available = 0, ";

			$sql_set = rtrim($sql_set,", ");

		
			// do where
			$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".$db_model->db->escape($gh_id)."";

			$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";

			$sql_where .= " and ".DB_Users::$TABLE_NAME.".id = ".DB_GHRequests::$TABLE_NAME.".user_id";


			$sql_tables = "";
			$sql_tables .= "".DB_GHRequests::$TABLE_NAME.", ";
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



	// do match to ph
	function match_to_ph_request($gh_request_id) {
		// order by oldest to newest
		// match available

		//update users, matched_requests set matched_requests.amount = 40, users.gh_balance = users.gh_balance - 40 WHERE matched_requests.id = 1 and users.id = 1 and (users.gh_balance - 40 >= 0)
		
	}

	// do match to gh put in PHRequest
	function match_to_gh_request($ph_request_id) {
		// order by oldest to newest
		// match availableghss
	}
	
}
?>
