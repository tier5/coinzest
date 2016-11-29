<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PHRequests extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
		Library_Auth_Common::check_allowed_request();

		print BackEnd_PHRequests_View::build_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	public function create_phrequest() {
		Library_Auth_Common::check_allowed_request();
		$amount = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->amount)) {
				$amount = $obj_request_data->data->amount;
			}
		}
		$data = Array();
		$user_id = LoginAuth::get_session_user_id();


		$db_mr_model = new DB_MatchedRequests();


		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_invalid_ph_amount = false;
		$obj_result->is_over_max = false;
		$obj_result->is_twenty_or_multiple_of_50 = false;
		$obj_result->is_error = false;
		$obj_result->does_not_match_last_ph_amount = false;
		$obj_result->is_unable_to_allocate = false;
		$obj_result->is_amount_exceed_ph_level = false;
		$obj_result->is_already_have_active_ph = false;
		$obj_result->is_already_have_active_matched_ph = false;

		$db_ph_model = new DB_PHRequests();
		$db_user_model = new DB_Users();
		$arr_user_data = $db_user_model->get($user_id);
		if (count($arr_user_data) == 0) {
			$obj_result->is_error = true;
			return $obj_result;
		}

		$country = $arr_user_data['country'];

		$is_override_rules = false;
		$is_dev_server = Registry::get("is_dev_server");
		if ($is_dev_server) {
			$is_override_rules = true;
		} else {
			$is_override_rules = false;
		}


		// override active ph and active matched rules if in dev env
		if ($is_override_rules) {
		} else {
			if ($db_ph_model->is_user_have_active_ph($user_id)) {
				$obj_result->is_already_have_active_ph = true;
				$obj_result->is_invalid_ph_amount = true;
			}

			if ($db_mr_model->is_have_active_matched_request($user_id)) {
				$obj_result->is_already_have_active_matched_ph = true;
				$obj_result->is_invalid_ph_amount = true;
			}
		}

		$obj_result->max_ph_amount = 250;
		$min_ph_amount = 20;
		$last_ph_amount = $arr_user_data['last_ph_amount'];
		if ($last_ph_amount == 2000) {
			$last_ph_amount = 0;
			$arr_user_data['ph_completed_level'] = 0;
		}

		// user should not be able to hit 2000 as ph completed level
		if ($arr_user_data['ph_completed_level'] == 2000) {
			$arr_user_data['ph_completed_level'] = 0;
		}

		$obj_result->ph_completed_level = $arr_user_data['ph_completed_level'];

		if ($arr_user_data['ph_completed_level'] < 250) {
			$obj_result->max_ph_amount = 250;
			$min_ph_amount = $last_ph_amount;
		} else if ($arr_user_data['ph_completed_level'] == 250) {
			$obj_result->max_ph_amount = 500;
			$min_ph_amount = $last_ph_amount;
		} else if ($arr_user_data['ph_completed_level'] == 500) {
			$obj_result->max_ph_amount = 750;
			$min_ph_amount = $last_ph_amount+20;
		} else if ($arr_user_data['ph_completed_level'] == 750) {
			$obj_result->max_ph_amount = 1000;
			$min_ph_amount = $last_ph_amount+20;
		} else if ($arr_user_data['ph_completed_level'] == 1000) {
			$obj_result->max_ph_amount = 1500;
			$min_ph_amount = $last_ph_amount+50;
		} else if ($arr_user_data['ph_completed_level'] == 1500) {
			$obj_result->max_ph_amount = 2000;
			$min_ph_amount = $last_ph_amount+100;
		} else if ($arr_user_data['ph_completed_level'] == 2000) {
			$obj_result->max_ph_amount = 2000;
			$min_ph_amount = $last_ph_amount+100;
		} else {
			$min_ph_amount = 20;
			$obj_result->max_ph_amount = 250;
		}

		if ($min_ph_amount > $obj_result->max_ph_amount) {
			$min_ph_amount = $obj_result->max_ph_amount;
		}

		// min has to match at least last
		if ($min_ph_amount < $last_ph_amount) {
			$min_ph_amount = $last_ph_amount;
			
		}


		if ($min_ph_amount < 20) {
			$min_ph_amount = 20;
		} else if ($min_ph_amount > 2000) {
			$min_ph_amount = 2000;
		} else {
		}


		$obj_result->min_ph_amount = $min_ph_amount;

		$max_ph_amount = $obj_result->max_ph_amount;


		// need to use function in phrequest
		if ($amount < 20) {
			$obj_result->is_invalid_ph_amount = true;
		}

		if ($amount == 20) {
			$obj_result->is_twenty_or_multiple_of_50 = true;
			$obj_result->is_invalid_ph_amount = false;
		} else if ($amount > 20) {
			if (fmod($amount,50) == 0) {
				$obj_result->is_twenty_or_multiple_of_50 = true;
			} else {
				$obj_result->is_invalid_ph_amount = true;
			}

			
			if ($amount > 2000) {
				$obj_result->is_over_max = true;
				$obj_result->is_invalid_ph_amount = true;
			}
		} else {
			$obj_result->is_invalid_ph_amount = true;
		}

		// amount must be same as last ph amount
		if ($amount < $last_ph_amount) {
			$obj_result->does_not_match_last_ph_amount = true;
			$obj_result->is_invalid_ph_amount = true;
		}

			// need to match ph level
			// 250, 500, 750
		// this needs to be based on one transaction ph if person makes a ph of 2k at 2k level then resets back to 250 level
		//print_r($arr_user_data);
		//print "ph completed is ".$arr_user_data['ph_completed_level']."<br>\n";
		if ($amount < $min_ph_amount) {
			$obj_result->does_not_match_last_ph_amount = true;
			$obj_result->is_invalid_ph_amount = true;
		}

		if ($amount > $max_ph_amount) {
			$obj_result->is_over_max = true;
			$obj_result->is_invalid_ph_amount = true;
		}

		$obj_result->last_ph_amount = $last_ph_amount;


		if (!$obj_result->is_invalid_ph_amount) {


			// create ph
			$db_model = new DB_PHRequests();

			$amount_available = $amount;
			if ($amount >= 100) {
				$amount_available = round($amount * .20, -1);
			}
			
			$data = Array();
			$data['is_pending_create'] = "Y";
			$number_affected = $db_model->save(0, $data);
			if ($number_affected == 1) {

				$ph_id = $db_model->get_last_insert_id();
				

				$now = time();
				$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
				// race condition on bonus

				$db_wallet_logs_model = new DB_WalletLogs();


				// do new implementation
				// setup a new field in wp_users which tracks a count. update that count and make sure it is less than our total max
				// this will prevent duplicates from happening

				
				// update users table
				// set expiration
				$expire_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_user_model->db, strtotime("+25 days"));
				$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_user_model->db);

				$sql_set = "";

				$sql_set .= "".DB_Users::$TABLE_NAME.".expiration_before_new_ph_gm_date_time = ".$this->db->escape($expire_gm_date_time).", ";
				$sql_set .= "".DB_Users::$TABLE_NAME.".last_ph_request_gm_date_time = ".$this->db->escape($now_gm_date_time).", ";

				$sql_set .= "".DB_Users::$TABLE_NAME.".total_ph = ".DB_Users::$TABLE_NAME.".total_ph + ".$this->db->escape($amount).", ";
				$sql_set .= "".DB_Users::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";
				//$sql_set .= "".self::$TABLE_NAME.".country = ".$this->db->escape($country).", ";
				if ($amount > $last_ph_amount) {
					$sql_set .= "".DB_Users::$TABLE_NAME.".last_ph_amount = ".$this->db->escape($amount).", ";
				}


				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".gm_modified = ".$this->db->escape($now_gm_date_time).", ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".user_id = ".$this->db->escape($user_id).", ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount = ".$this->db->escape($amount).", ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".amount_available = ".$this->db->escape($amount_available).", ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".country = ".$this->db->escape($country).", ";
				$sql_set .= "".DB_PHRequests::$TABLE_NAME.".is_pending_create = ".$this->db->escape("N").", ";

				$sql_set = rtrim($sql_set,", ");


				// do where
				$sql_where = "".DB_Users::$TABLE_NAME.".id = ".$this->db->escape($user_id)."";
				$sql_where .= " AND ".DB_PHRequests::$TABLE_NAME.".is_pending_create = ".$this->db->escape("Y")."";
				$sql_where .= " AND ".DB_PHRequests::$TABLE_NAME.".id = ".$this->db->escape($ph_id)."";

				//$sql_where .= " and NOT EXISTS (select ".DB_PHRequests::$TABLE_NAME.".id from ".DB_PHRequests::$TABLE_NAME." where ".DB_PHRequests::$TABLE_NAME.".status_id = ".DB_PHRequests::$ACTIVE_STATUS_ID." and ".DB_PHRequests::$TABLE_NAME.".user_id = ".$this->db->escape($user_id)." )";




				$sql_tables = "".DB_Users::$TABLE_NAME.", ".DB_PHRequests::$TABLE_NAME.", ";
				$sql_tables = rtrim($sql_tables,", ");

				

				$sql = "update $sql_tables set $sql_set where $sql_where";
				//print "sql is $sql\n";
				$query = $db_user_model->db->query($sql);

				$number_affected = $db_user_model->db->affected_rows();
				//print_r($this->db->error());
				//print "number affected is $number_affected\n";



				PlatformLogs::log("ph_request_country", "query is $sql and country is $country\n");
				



				// race condition here if we get killed last_ph_amount wont get updated
				// update ph amount
				/*
				if ($amount > $last_ph_amount) {
					$data = Array();
					$data['last_ph_amount'] = $amount;
					$db_user_model->save($user_id, $data);
				}
				*/
				// race condition ph completed

				if ($number_affected == 2) {

					//print "got in here\n";
					// race condition
					$arr_active_ph_data = $db_ph_model->get_arr_user_ph($user_id);
					//print_r($arr_active_ph_data);
					if (count($arr_active_ph_data) == 1) {
					//if (is_array($arr_active_ph_data)) {
						// create bonus
						$bonus_amount = 0;
						if ($amount == 20) {
							$bonus_amount = 2;
						} else if ($amount == 50) {
							$bonus_amount = 10;
						} else if ($amount == 100) {
							$bonus_amount = 20;
						} else if ($amount == 250) {
							$bonus_amount = 50;
						}

						if ($bonus_amount > 0) {
							$temp_data = Array();
							$temp_data['user_id'] = $user_id;
							$temp_data['amount'] = $bonus_amount;
							$temp_data['wallet_type_id'] = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
							$temp_data['log_type_id'] = DB_WalletLogs::$NEW_MEMBER_BONUS_FOR_FIRST_PH_LOG_TYPE_ID;
							$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
							$temp_data['reference_id'] = $ph_id;
							$temp_data['reference_user_id'] = $user_id;
							$temp_data['is_available'] = "N";
							$temp_data['is_pending_create'] = "N";
							$available_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_wallet_logs_model->db, strtotime("+30 days"));
							$temp_data['available_gm_create_date_time'] = $available_gm_date_time;
							//print_r($temp_data);
							if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
								//print "created good\n";
							} else {
								//print "did not create\n";
							}
							// probably should count number of PHs less than 10k
							$number = $db_user_model->get_count_active_registered_users();
							//print "number is $number\n";
							if ($number <= (7000+10000)) {
								$referral_id = $arr_user_data['referral_id'];
								//print "referral id is $referral_id\n";
								if ($referral_id > 0) {
									// give that person 15% of 
									$temp_data = Array();
									$temp_data['user_id'] = $referral_id;
									//$temp_data['user_id'] = $user_id;
									$temp_data['amount'] = $amount *.15;
									$temp_data['wallet_type_id'] = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
									$temp_data['log_type_id'] = DB_WalletLogs::$NEW_SPONSOR_BONUS_FOR_FIRST_10K_LOG_TYPE_ID;
									$temp_data['gm_date'] = Library_DB_Util::time_to_gm_db_date($db_wallet_logs_model->db);
									$temp_data['reference_id'] = $ph_id;
									$temp_data['reference_user_id'] = $user_id;
									$temp_data['is_pending_create'] = "N";
									$temp_data['is_available'] = "N";
									$available_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_wallet_logs_model->db, strtotime("+30 days"));
									$temp_data['available_gm_create_date_time'] = $available_gm_date_time;
									//print_r($temp_data);
									if ($db_wallet_logs_model->save(0, $temp_data) > 0) {
									}
								}
							}
						}

					} // if first ph do bonuses

					$obj_result->is_success = true;
					$obj_result->is_unable_to_allocate = false;

					// send out to all parent managers and send out to direct sponsor and person
					Emailer::send_ph_request_created_email($user_id,$ph_id);

				} // if ph and user updated

				//MatchedRequest::match_all_gh_requests();

			} else {
				$obj_result->is_unable_to_allocate = true;
			}
		}
		print json_encode($obj_result);
	}

	public function update_if_request_complete($ph_id) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_admin_access()) {
			$ph_model = new DB_PHRequests();
			$obj_result = $ph_model->update_if_request_complete($ph_id);
		}
		print json_encode($obj_result);
	}

	


	public function cancel_ph($ph_id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
				$amount = $obj_request_data->data->user_id;
			}
		}

		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();
		
		$db_model = new DB_PHRequests();

		$is_do = false;
		$is_user = false;
		if ($ph_id > 0) {
			$data = $db_model->get($ph_id);
			if (count($data) > 0) {
				if (LoginAuth::is_user_have_admin_access()) {
					$is_do = true;
				} else {
					// check if user has access to ph
					if ($data['user_id'] == $user_id) {
						$is_user = true;
						$is_do = true;
					}
				}
			}
		}

		if ($is_do) {
			/*
			$arr_criteria = Array();
			$arr_criteria['status_id'] = DB_PHRequests::$ACTIVE_STATUS_ID;
			$new_data['status_id'] = DB_PHRequests::$CANCELLED_STATUS_ID;
			$number_affected = $db_model->update($new_data, $arr_criteria);
			if ($number_affected > 0) { 
				$obj_result->is_success = true;

				$data = $db_model->get($ph_id);

				// race condition
				// set user status to no matching for gh
				$ph_user_id = $data['user_id'];
				$db_user_model = new DB_Users();
				$user_data = Array();
				$user_data['is_approved_for_matching'] = "N";
				$db_user_model->save($ph_user_id, $user_data);

			}
			*/

			// update gh request

			$result = PHRequest::cancel_ph($ph_id, $user_id);
			if ($result->is_success) {
				$obj_result->is_success = true;
			}
		}
		print json_encode($obj_result);
	}

	public function list_all_ph_requests() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		if (LoginAuth::is_user_have_admin_access()) {
			print BackEnd_PHRequests_View::build_list_all_ph_requests_html();
			//MatchedRequest::match_all_gh_requests();
			exit(0);
		}
		print BackEnd_Login_View::build_no_access_html();
	}

	public function get_all_ph_requests() {
		Library_Auth_Common::check_allowed_request();

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
				$amount = $obj_request_data->data->user_id;
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		if (LoginAuth::is_user_have_admin_access()) {
			$data = Array();
			
			$obj_result->is_success = true;

			$arr_search_criteria = Array();

			$db_model = new DB_PHRequests();

			$field_names = $db_model->get_field_names();

			$db_model->db->select($field_names.", ".DB_Users::$TABLE_NAME.".status_id as user_status_id, ".DB_Users::$TABLE_NAME.".login, ".DB_Users::$TABLE_NAME.".country")->from(DB_PHRequests::$TABLE_NAME);
			$db_model->db->join(DB_Users::$TABLE_NAME,DB_Users::$TABLE_NAME.".id = ".DB_PHRequests::$TABLE_NAME.".user_id");
			$this->db->where(DB_PHRequests::$TABLE_NAME.".status_id",DB_PHRequests::$ACTIVE_STATUS_ID);

			$db_model->db->order_by(DB_PHRequests::$TABLE_NAME.".gm_created", "asc");
			$data = $db_model->db->get();
			//print $db_model->get_last_query();
			//print_r($db_model->db->errors());
			$arr_data = $data->result_array();

			//$arr_data = $db_model->search($arr_search_criteria);
			$obj_result->is_success = true;
			$obj_result->arr_data = $arr_data;
		}
		print json_encode($obj_result);
	}

	public function get_cancelled_and_completed_phrequests_for_user($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
				$amount = $obj_request_data->data->user_id;
			}
		}
		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}


		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = PHRequest::get_arr_ph_requests_for_user($user_id, true);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		print json_encode($obj_result);
	}

	public function get_phrequests_for_user($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
				$amount = $obj_request_data->data->user_id;
			}
		}
		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}


		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$arr_data = PHRequest::get_arr_ph_requests_for_user($user_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		print json_encode($obj_result);
	}
}
