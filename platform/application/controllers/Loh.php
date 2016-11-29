<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loh extends CI_Controller {

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

		/*
		if (LoginAuth::is_user_have_access(true)) {
			//print BackEnd_Loh_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		*/
		//$this->load->view('welcome_message');
	}

	public function edit($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_Loh_View::build_edit_html($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
		//$this->load->view('welcome_message');
	}

	public function get($id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		if (LoginAuth::is_user_have_admin_access()) {
			$db_model = new DB_Loh();
			$arr_data = $db_model->get($id);
			$obj_result->is_success = true;
			$obj_result->user_data = $arr_data;
			print json_encode($obj_result);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
	}

	public function reject_loh($loh_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
	
		$user_id = LoginAuth::get_session_user_id();
		$db_ghr_model = new DB_GHRequests();
		$obj_request_data = Common::load_request_data();
		$data = Array();
		$redo_reason = "";
		$is_redo = false;
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->redo_reason)) {
				$redo_reason =  $obj_request_data->redo_reason;
			}

			if (isset($obj_request_data->is_redo)) {
				$is_redo =  $obj_request_data->is_redo;
			}
		}
		if ($loh_id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$db_model = new DB_Loh();
				$data = $db_model->get($loh_id);
				if (count($data) > 0) {
					$gh_data = $db_ghr_model->get($data['gh_request_id']);
					if (count($gh_data) > 0) {
						$amount = $gh_data['amount'];
	
						//print "here\n";

						$sql_set = "";


						$sql_set .= DB_Loh::$TABLE_NAME.".gm_modified = ".$db_model->db->escape($now_gm_date_time).", ";
						if ($is_redo) {
							$sql_set .= DB_Loh::$TABLE_NAME.".is_redo = 'Y',  ";
							$sql_set .= DB_Loh::$TABLE_NAME.".redo_reason = ".$db_model->db->escape($redo_reason).", ";
						} else {
							$sql_set .= DB_Loh::$TABLE_NAME.".is_rejected = 'Y',  ";
						}
						$sql_set .= DB_Loh::$TABLE_NAME.".is_approved = 'N',  ";

						$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_loh_approved = 'N', ";
						$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_completed_loh = 'N', ";
						$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_loh_rejected = 'Y', ";
						$sql_set .= DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_ghr_model->db->escape($now_gm_date_time).", ";
						$sql_set = rtrim($sql_set,", ");

						// do where
						$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".DB_Loh::$TABLE_NAME.".gh_request_id";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".is_completed_loh = 'Y'";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".is_loh_approved = 'N'";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched_completed = ".DB_GHRequests::$TABLE_NAME.".amount";



						$sql_where .= " and ".DB_Loh::$TABLE_NAME.".id = ".$db_ghr_model->db->escape($loh_id)."";
						$sql_where .= " and ".DB_Loh::$TABLE_NAME.".is_approved = ".$db_ghr_model->db->escape("N")."";

						$sql_tables = "".DB_GHRequests::$TABLE_NAME.", ";
						$sql_tables .= "".DB_Loh::$TABLE_NAME.", ";

						$sql_tables = trim($sql_tables,", ");

						$sql = "update $sql_tables set $sql_set where $sql_where";
						//print "sql is $sql\n";
						//exit(0);
						$query = $db_ghr_model->db->query($sql);
						$number_affected = $db_ghr_model->db->affected_rows();
						if ($number_affected == 2) {
							$obj_result->is_success = true;
						}
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	public function approve_loh($loh_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
	
		$user_id = LoginAuth::get_session_user_id();
		$db_ghr_model = new DB_GHRequests();
		$obj_request_data = Common::load_request_data();
		$data = Array();
		$percent_bonus = 0;
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->percent_bonus)) {
				$percent_bonus =  $obj_request_data->percent_bonus;
			}
		}
		if ($loh_id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$db_model = new DB_Loh();
				$data = $db_model->get($loh_id);
				if (count($data) > 0) {
					$gh_data = $db_ghr_model->get($data['gh_request_id']);
					$gh_id = $data['gh_request_id'];
					if (count($gh_data) > 0 && ($percent_bonus ==0 || $percent_bonus == 5 || $percent_bonus == 10) && $gh_id > 0) {
						$amount = $gh_data['amount'];
						$bonus = ($percent_bonus/100)*$amount;
	
						//print "here\n";

						$sql_set = "";

						$adjust_for_user_id = $gh_data['user_id'];


						$sql_set .= DB_Loh::$TABLE_NAME.".gm_modified = ".$db_ghr_model->db->escape($now_gm_date_time).", ";
						$sql_set .= DB_Loh::$TABLE_NAME.".is_approved = 'Y',  ";
						$sql_set .= DB_Loh::$TABLE_NAME.".is_rejected = 'N',  ";
						$sql_set .= DB_Loh::$TABLE_NAME.".is_redo = 'N',  ";
						$sql_set .= DB_Loh::$TABLE_NAME.".approved_by_user_id = ".$db_ghr_model->db->escape($user_id).", ";
						if ($bonus > 0) {
							$sql_set .= DB_Loh::$TABLE_NAME.".bonus_amount = ".$db_ghr_model->db->escape($bonus).", ";
						}

						$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_loh_approved = 'Y', ";
						$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_loh_rejected = 'N', ";
						$sql_set .= DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_ghr_model->db->escape($now_gm_date_time).", ";
						$sql_set = rtrim($sql_set,", ");

						// do where
						$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".DB_Loh::$TABLE_NAME.".gh_request_id";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".is_completed_loh = 'Y'";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".is_loh_approved = 'N'";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";
						$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched_completed = ".DB_GHRequests::$TABLE_NAME.".amount";



						$sql_where .= " and ".DB_Loh::$TABLE_NAME.".id = ".$db_ghr_model->db->escape($loh_id)."";
						$sql_where .= " and ".DB_Loh::$TABLE_NAME.".is_approved = ".$db_ghr_model->db->escape("N")."";

						$sql_tables = "".DB_GHRequests::$TABLE_NAME.", ";
						$sql_tables .= "".DB_Loh::$TABLE_NAME.", ";

						$sql_tables = trim($sql_tables,", ");

						$sql = "update $sql_tables set $sql_set where $sql_where";
						//print "sql is $sql\n";
						//exit(0);
						$query = $db_ghr_model->db->query($sql);
						$number_affected = $db_ghr_model->db->affected_rows();
						$obj_result->number_affected = $number_affected;
						//$number_affected = 2;
						if ($number_affected == 2) {
							//print "bonus is $bonus\n";
							if ($bonus > 0) {
								//print "bonus is $bonus\n";
								$additional_params['is_available'] = false;
								$days_frozen = 30;
								$additional_params['available_gm_create_date_time'] = Library_DB_Util::time_to_gm_db_time($db_user_model->db,strtotime("+".$days_frozen." days", time()));
								WalletHelper::adjust_wallet_by_name("daily_growth", $adjust_for_user_id, $bonus, "", DB_WalletLogs::$LOH_BONUS_LOG_TYPE_ID, $gh_id, $additional_params);
							}
							$db_ghr_model->update_if_request_complete($gh_id);
							$obj_result->is_success = true;
						}
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	public function get_loh_data_by_gh_request($gh_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
	
		$user_id = LoginAuth::get_session_user_id();
		$db_ghr_model = new DB_GHRequests();
		$obj_request_data = Common::load_request_data();
		$data = Array();
		$obj_result->data = $data;
		if (is_object($obj_request_data)) {
		}
		if ($gh_id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$db_model = new DB_Loh();
				$arr_criteria = Array();
				$arr_criteria['gh_request_id'] = $gh_id;
				$data = $db_model->search($arr_criteria);
				if (count($data) > 0) {
					if ($user_id == $data['user_id'] || LoginAuth::is_user_have_admin_access()) {
						$obj_result->is_success = true;
						$obj_result->data = $data[0];
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	public function submit() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		$obj_result->is_already_completed_loh = false;
	
		$user_id = LoginAuth::get_session_user_id();
		$db_ghr_model = new DB_GHRequests();
		$obj_request_data = Common::load_request_data();
		$gh_id = 0;
		$data = Array();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
			if (isset($obj_request_data->gh_id)) {
				$gh_id =  $obj_request_data->gh_id;
			}
		}


		if (is_numeric($gh_id) && $gh_id > 0 && count($data) > 0 && $data['letter_contents'] != "") {
			$gh_data = $db_ghr_model->get($gh_id);
			if (count($gh_data) > 0 && $gh_data['is_completed_loh'] == "Y") {
				$obj_result->is_already_completed_loh = true;
			}
			

			if (count($gh_data) > 0 && $user_id == $gh_data['user_id'] && $gh_data['is_completed_loh'] == "N") {

				$db_loh_model = new DB_Loh();

				$temp_data = Array();
				/*
				$temp_data['letter_contents'] = $data['letter_contents'];
				if ($data['video_link'] != "") {
					$temp_data['video_link'] = $data['video_link'];
				}
				*/
				$temp_data['user_id'] = 0;

				//print_r($data);

				$db_loh_model = new DB_Loh();
				$arr_criteria = Array();
				$arr_criteria['gh_request_id'] = $gh_id;
				$loh_data = $db_loh_model->search($arr_criteria);
				if (count($loh_data) > 0) {
					$insert_id = $loh_data[0]['id'];
				} else {
					// 
					$number_affected = $db_loh_model->save(0,$temp_data);
					$insert_id = $db_loh_model->get_last_insert_id();
				}
			
				//print_r($this->db->error());
				//print "number affected is $number_affected\n";
	

		
				$db_model = new DB_GHRequests();

				//print "insert id is $insert_id\n";
				if($insert_id > 0) {
					$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();

					$sql_set = "";

					//print "here\n";
					$sql_set .= DB_Loh::$TABLE_NAME.".user_id = ".$db_ghr_model->db->escape($user_id).", ";
					$sql_set .= DB_Loh::$TABLE_NAME.".letter_contents = ".$db_ghr_model->db->escape($data['letter_contents']).", ";
					$sql_set .= DB_Loh::$TABLE_NAME.".video_link = ".$db_ghr_model->db->escape($data['video_link']).", ";
					$sql_set .= DB_Loh::$TABLE_NAME.".gm_modified = ".$db_ghr_model->db->escape($now_gm_date_time).", ";
					$sql_set .= DB_Loh::$TABLE_NAME.".gh_request_id = ".$db_ghr_model->db->escape($gh_id).", ";
					$sql_set .= DB_Loh::$TABLE_NAME.".is_redo = 'N',  ";
					$sql_set .= DB_Loh::$TABLE_NAME.".is_rejected = 'N',  ";

					$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_completed_loh = 'Y', ";
					$sql_set .= "".DB_GHRequests::$TABLE_NAME.".is_loh_rejected = 'N', ";
					$sql_set .= DB_GHRequests::$TABLE_NAME.".gm_modified = ".$db_ghr_model->db->escape($now_gm_date_time).", ";
					$sql_set = rtrim($sql_set,", ");

					// do where
					$sql_where = "".DB_GHRequests::$TABLE_NAME.".id = ".$db_ghr_model->db->escape($gh_id)."";
					$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".is_completed_loh = 'N'";
					$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".status_id = ".DB_GHRequests::$ACTIVE_STATUS_ID."";
					$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".amount_matched_completed = ".DB_GHRequests::$TABLE_NAME.".amount";
					//$sql_where .= " and ".DB_GHRequests::$TABLE_NAME.".id = ".DB_Loh::$TABLE_NAME.".gh_request_id";
					$sql_where .= " and ".DB_Loh::$TABLE_NAME.".id = ".$db_ghr_model->db->escape($insert_id)."";

					$sql_tables = "".DB_GHRequests::$TABLE_NAME.", ";
					$sql_tables .= "".DB_Loh::$TABLE_NAME.", ";

					$sql_tables = trim($sql_tables,", ");

					$sql = "update $sql_tables set $sql_set where $sql_where";
					//print "sql is $sql\n";
					//exit(0);
					$query = $db_ghr_model->db->query($sql);
					$number_affected = $db_ghr_model->db->affected_rows();
					//print "number affected is $number_affected\n";
					if ($number_affected == 2) {
						$obj_result->is_success = true;
					}
				}
			} else {

			}
			//$arr_data = $db_model->get($id);
		} else {
			//print BackEnd_Login_View::build_no_access_html();
		}
		print json_encode($obj_result);
	}

	public function save($id = 0) {
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			$original_data = Array();
			$data = Array();


			if (is_numeric($id) && $id >= 0) {
				$obj_request_data = Common::load_request_data();
				if (is_object($obj_request_data)) {
					if (isset($obj_request_data->data)) {
						$data = (Array) $obj_request_data->data;
					}
					if (isset($obj_request_data->original_data)) {
						$original_data = (Array) $obj_request_data->original_data;
					}
				}


				if (is_array($data)) {
					$arr_field_names = DB_Loh::arr_get_field_names();
					if (isset($arr_field_names['id'])) {
						unset($arr_field_names['id']);
					}
					if (isset($arr_field_names['gm_created'])) {
						unset($arr_field_names['gm_created']);
					}
					if (isset($arr_field_names['gm_modified'])) {
						unset($arr_field_names['gm_modified']);
					}
					

					// unset values that shouldn't be set here
					foreach($data as $key => $value) {
						if (!in_array($key, $arr_field_names)) {
							unset($data[$key]);
						}
					}

					// if the value is the same dont update it
					foreach($data as $key => $value) {
						if (isset($original_data[$key])) {
							if ($original_data[$key] == $value) {
								unset($data[$key]);
							}
						}
					}
				}



				//$isGood = $bcrypt->verify('password', $hash);
				//print_r($data);

				$db_model = new DB_Loh();
				$db_model->save($id,$data);


				$obj_result->is_success = true;
			}
			//$arr_data = $db_model->get($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		print json_encode($obj_result);
	}

	public function delete($id) {
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_access(true)) {
			$db_model = new DB_Loh();
			$db_model->delete($id);
			$obj_result->is_success = true;
			//$arr_data = $db_model->get($id);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		
		print json_encode($obj_result);
	}

	public function get_arr_search_results() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new \stdclass();
		$obj_result->is_success = false;
		$obj_request_data = Common::load_request_data();
		$search = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}

		if (LoginAuth::is_user_have_access(true)) {
			$db_model = new DB_Loh();
			//$arr_criteria = Array();
			//$arr_criteria['segment_id'] = $segment_id;
			$arr_criteria['search'] = $search;
			//$arr_criteria['limit'] = 200;
			$arr_data = $db_model->search($arr_criteria);
			$obj_result->arr_data = $arr_data;
			$obj_result->is_success = true;
			
			print json_encode($obj_result);
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
	}

}
