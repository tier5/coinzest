<?

class MatchedRequest {

	// get for one or everyone
	function get_arr_matched_requests_for_user($user_id, $status_id) {
		$db_model = new DB_MatchedRequests();	
		$arr_criteria = Array();
		if ($user_id > 0) {
			$arr_criteria['user_id'] = $user_id;
		}
		
		$arr_criteria['status_id'] = $status_id;
		$arr_criteria['join_ph_user_tbl'] = $user_id;
		$arr_criteria['join_gh_user_tbl'] = $user_id;
		$arr_criteria['login']['<>'] = "";
		$arr_criteria['order_by'] = "gm_created";
		$arr_criteria['order_by_direction'] = "asc";
		$arr_data =  $db_model->search($arr_criteria);
		//print_r($arr_criteria);
		//print_r($arr_data);
		//exit(0);
		
		$db_btc_address_model = new DB_BTCAddresses();

		for($n=0;$n<count($arr_data);$n++) {
			if ($arr_data[$n]['btc_address_id'] > 0) {
				$btc_address_data = $db_btc_address_model->get($arr_data[$n]['btc_address_id']);
				if (count($btc_address_data) > 0) {
					$arr_data[$n]['gh_user_tbl_btc_address'] = $btc_address_data['btc_address'];
				}
			}
			unset($arr_data[$n]['gh_user_tbl_password']);
			unset($arr_data[$n]['ph_user_tbl_password']);
			unset($arr_data[$n]['gh_user_tbl_otp']);
			unset($arr_data[$n]['ph_user_tbl_otp']);
		}
		return $arr_data;
	}

	function get_arr_active_matched_requests_for_user($user_id) {
		$db_model = new DB_MatchedRequests();	
		$arr_criteria = Array();
		if ($user_id > 0) {
			$arr_criteria['user_id'] = $user_id;
		}
		
		$arr_criteria['status_id'] = DB_MatchedRequests::$ACTIVE_STATUS_ID;
		$arr_criteria['join_ph_user_tbl'] = $user_id;
		$arr_criteria['join_gh_user_tbl'] = $user_id;
		$arr_criteria['login']['<>'] = "";
		$arr_criteria['order_by'] = "gm_created";
		$arr_criteria['order_by_direction'] = "asc";
		$arr_data =  $db_model->search($arr_criteria);
		//print_r($arr_criteria);
		//print_r($arr_data);
		//exit(0);
		$db_btc_address_model = new DB_BTCAddresses();
		
		for($n=0;$n<count($arr_data);$n++) {
			if ($arr_data[$n]['btc_address_id'] > 0) {
				$btc_address_data = $db_btc_address_model->get($arr_data[$n]['btc_address_id']);
				if (count($btc_address_data) > 0) {
					$arr_data[$n]['gh_user_tbl_btc_address'] = $btc_address_data['btc_address'];
				}
			}
			unset($arr_data[$n]['gh_user_tbl_password']);
			unset($arr_data[$n]['ph_user_tbl_password']);
			unset($arr_data[$n]['gh_user_tbl_otp']);
			unset($arr_data[$n]['ph_user_tbl_otp']);
		}
		return $arr_data;
	}

	function get_arr_completed_cancelled_for_user($user_id) {
		$db_model = new DB_MatchedRequests();	
		$arr_criteria = Array();
		$arr_criteria['user_id'] = $user_id;
		$arr_criteria['status_id'][] = DB_MatchedRequests::$COMPLETED_STATUS_ID;
		$arr_criteria['status_id'][] = DB_MatchedRequests::$CANCELLED_STATUS_ID;
		$arr_criteria['join_ph_user_tbl'] = $user_id;
		$arr_criteria['join_gh_user_tbl'] = $user_id;
		$arr_criteria['order_by'] = "gm_created";
		$arr_criteria['order_by_direction'] = "asc";
		$arr_data =  $db_model->search($arr_criteria);

		$db_btc_address_model = new DB_BTCAddresses();

		for($n=0;$n<count($arr_data);$n++) {
			if ($arr_data[$n]['btc_address_id'] > 0) {
				$btc_address_data = $db_btc_address_model->get($arr_data[$n]['btc_address_id']);
				if (count($btc_address_data) > 0) {
					$arr_data[$n]['gh_user_tbl_btc_address'] = $btc_address_data['btc_address'];
				}
			}
			unset($arr_data[$n]['gh_user_tbl_password']);
			unset($arr_data[$n]['ph_user_tbl_password']);
			unset($arr_data[$n]['gh_user_tbl_otp']);
			unset($arr_data[$n]['ph_user_tbl_otp']);
		}
		return $arr_data;
	}

	// set that the iamge receipt upload has been sent
	// not using this yet will use other function in DB_MatchedRequest
	function confirm_image_receipt($id) {
		$db_mr_model = new DB_MatchedRequests();

		$arr_mr_data = $db_mr_model->get($id);
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if (count($arr_mr_data)) {
			// check to see if phrequest needs to make more available since we have 20/80 rule
			// check if we have completed match for ph
			// if we have completed match for ph then update user for ph_complete
			// update user so that we can update ph_complete
			// we don't touch gh_balance at all we let other script handle that

			// probably need to update more than one table
			$image_receipt_gm_date_time = $arr_mr_data['image_receipt_gm_date_time'];
			// update matched_requests
			//$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".is_have_image_receipt = 'Y'";

			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

			$sql_set .= DB_MatchedRequests::$TABLE_NAME.".status_id = ".$db_mr_model->db->escape(DB_MatchedRequests::$COMPLETED_STATUS_ID)."";

			// do where
			$sql_where = "".DB_MatchedRequests::$TABLE_NAME.".id = $id";

			//$gm_expiration_date_time = strtotime(self::get_image_receipt_extended_expiration_timestamp(strtotime($gm_created." UTC"))." UTC");
			$gm_expiration_rev_date_time = Library_DB_Util::time_to_gm_db_time($db_mr_model->db, self::get_receiver_confirmation_extended_expiration_rev_timestamp(strtotime($now_gm_date_time." UTC")));
			// make sure we haven't expired
			$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".image_receipt_gm_date_time >= ".$db_mr_model->db->escape($gm_expiration_rev_date_time)."";

			$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$ACTIVE_STATUS_ID."";


			$sql_tables = "".DB_MatchedRequests::$TABLE_NAME."";
			$sql = "update $sql_tables set $sql_set where $sql_where";
			$query = $db_mr_model->db->query($sql);
			$number_affected = $db_mr_model->db->affected_rows();
			//print "sql is $sql\n";
			//print "number affected is $number_affected\n";
			if ($number_affected == 1) {
				$obj_result->is_success = true;
				Emailer::build_gh_confirm_email_html($id);
			}

		}
		return $obj_result;
	}

	// set that the iamge receipt upload has been sent
	function _set_image_receipt($id) {
		$db_mr_model = new DB_MatchedRequests();

		$arr_mr_data = $db_mr_model->get($id);
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if (count($arr_mr_data)) {
			$gm_created = $arr_mr_data['gm_created'];
			// update matched_requests
			$sql_set .= "".DB_MatchedRequests::$TABLE_NAME.".is_have_image_receipt = 'Y'";

			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
			

			$expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_mr_model->db,strtotime("+1 Days"));
			$extended_expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_mr_model->db,strtotime("+2 Days"));


			$sql_set .= ", ".DB_MatchedRequests::$TABLE_NAME.".expiration_gm_date_time = ".$db_mr_model->db->escape($expiration_gm_date_time )."";
			$sql_set .= ", ".DB_MatchedRequests::$TABLE_NAME.".extended_expiration_gm_date_time = ".$db_mr_model->db->escape($extended_expiration_gm_date_time )."";


			$sql_set .= ", ".DB_MatchedRequests::$TABLE_NAME.".image_receipt_gm_date_time = ".$db_mr_model->db->escape($now_gm_date_time)."";

			// do where
			$sql_where = "".DB_MatchedRequests::$TABLE_NAME.".id = $id";

			//$gm_expiration_date_time = strtotime(self::get_image_receipt_extended_expiration_timestamp(strtotime($gm_created." UTC"))." UTC");
			$gm_expiration_rev_date_time = Library_DB_Util::time_to_gm_db_time($db_mr_model->db, self::get_image_receipt_extended_expiration_rev_timestamp(strtotime($now_gm_date_time." UTC")));
			// make sure we haven't expired
			// won't allow upload if past extended expiration
			// took out temporarily
			//$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".gm_created >= ".$db_mr_model->db->escape($gm_expiration_rev_date_time)."";

			$sql_where .= " and ".DB_MatchedRequests::$TABLE_NAME.".status_id = ".DB_MatchedRequests::$ACTIVE_STATUS_ID."";


			$sql_tables = "".DB_MatchedRequests::$TABLE_NAME."";
			$sql = "update $sql_tables set $sql_set where $sql_where";
			$query = $db_mr_model->db->query($sql);
			$number_affected = $db_mr_model->db->affected_rows();
			//print "sql is $sql\n";
			//print "number affected is $number_affected\n";
			if ($number_affected == 1) {
				$obj_result->is_success = true;
			}

		}
		return $obj_result;
	}

	// set that the iamge receipt upload has been sent
	function set_image_receipt_wrapper($id) {
		$db_mr_model = new DB_MatchedRequests();

		$arr_mr_data = $db_mr_model->get($id);
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if (count($arr_mr_data)) {
			$obj_result = self::_set_image_receipt($id);
			$number_affected = $db_mr_model->db->affected_rows();
			//print "sql is $sql\n";
			//print "number affected is $number_affected\n";
			if ($number_affected == 1) {
				Emailer::build_image_receipt_upload_email_html($id);
				$obj_result->is_success = true;
			}

		}
		return $obj_result;
	}

	// 72 hours
	function get_image_receipt_extended_expiration_timestamp($timestamp) {
		return strtotime("+3 days", $timestamp);
	}

	// -72 hours reverse timestamp
	function get_image_receipt_extended_expiration_rev_timestamp($timestamp) {
		return strtotime("-3 days", $timestamp);
	}

	// 48 hours
	function get_image_receipt_expiration_timestamp($timestamp) {
		return strtotime("+2 days", $timestamp);
	}

	// 48 hours
	function get_receiver_confirmation_extended_expiration_rev_timestamp($timestamp) {
		return strtotime("12 days", $timestamp);
	}

	// 24 hours
	function get_receiver_confirmation_expiration_timestamp($timestamp) {
		return strtotime("+1 day", $timestamp);
	}

	// 24 hour additional after timeout
	function get_extended_expiration_timestamp($timestamp) {
		return strtotime("+1 day", $timestamp);
	}

	function match_all_gh_requests() {

		$db_mr_model =  new DB_MatchedRequests();

		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();

		$now = time();
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		$yesterday_timestamp = strtotime("-1 DAY");
		$last_id = 0;
		//$db_ghr_model->create_tmp_gh();
		// add daily growths
		do {
			// probably should use lock
			$arr_criteria = Array();
			$arr_criteria['status_id'][] = DB_GHRequests::$ACTIVE_STATUS_ID;
			$arr_criteria['id']['>'] = $last_id;
			$arr_criteria['amount_available']['>'] = 0;
			$arr_criteria['order_by'] = "gm_created";
			$arr_criteria['order_by_direction'] = "asc";
			$arr_criteria['number_results_per_page'] = 100;
			$arr_data = Array();
			$arr_data = $db_ghr_model->search($arr_criteria);
			PlatformLogs::log("matched_all_gh_requests", "last query is: ".$db_ghr_model->get_last_query()."\n");
			//print_r($arr_data);
			$current_count = count($arr_data);
			PlatformLogs::log("matched_all_gh_requests", "current count is $current_count and starting from $last_id\n");
		

			for($n=0;$n<count($arr_data);$n++) {
				//print "in here\n";
				//print "n is $n\n";

				$result = $db_mr_model->match_to_ph_request($arr_data[$n]['id']);
				if ($result->is_success) {
					//print "match created<br>\n";
				}
				$last_id = $arr_data[$n]['id'];
				PlatformLogs::log("matched_all_gh_requests", "n is $n last id is $last_id and current id is $last_id\n");
				PlatformLogs::log("matched_all_gh_requests", $arr_data[$n]['amount_available']."\n");
			}
		} while (count($arr_data) > 0);
		PlatformLogs::log("matched_all_gh_requests", "done"."\n");

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
