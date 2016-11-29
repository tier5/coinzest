<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MyProfile extends CI_Controller {

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
		print BackEnd_MyProfiles_View::build_html();

		//Library_Auth_Common::check_allowed_request();

		/*
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}

	function get_btc_addresses_for_user($user_id = 0) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();

		/*
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->code)) {
				$code = $obj_request_data->code;
			}
		}
		*/

		$user_id = LoginAuth::get_session_user_id();
		

		$data = Array();

		$db_model = new DB_Users();
		$db_btc_address_model = new DB_BTCAddresses();
		
		$arr_data = $db_btc_address_model->get_arr_active_btc_addresses_for_user($user_id);
		$obj_result->is_success = true;
		$obj_result->arr_data = $arr_data;
		print json_encode($obj_result);
	}

	function delete_btc_address($btc_address_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();

		/*
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->code)) {
				$code = $obj_request_data->code;
			}
		}
		*/

		$user_id = LoginAuth::get_session_user_id();
		

		$data = Array();

		$db_model = new DB_Users();
		$db_btc_address_model = new DB_BTCAddresses();
		
		if ($btc_address_id > 0) {
			$btc_address_data = $db_btc_address_model->get($btc_address_id);
		}
		//print_r($btc_address_data);
		
		if (count($btc_address_data) > 0 && $user_id == $btc_address_data['user_id']) {
			$arr_criteria = Array();
			$arr_criteria['id'] = $btc_address_id;

			$temp_data = Array();
			//$temp_data["is_primary"] = "N";
			$temp_data["status_id"] = DB_BTCAddresses::$ARCHIVED_STATUS_ID;
			$number_affected = $db_btc_address_model->update($temp_data, $arr_criteria);


			/*
			// set everything else to N
			$arr_criteria = Array();
			$arr_criteria['is_primary'] = "Y";
			$arr_criteria['id']['<>'] = $btc_address_id;

			$temp_data = Array();
			$temp_data["is_primary"] = "N";
			$number_affected = $db_btc_address_model->update($temp_data, $arr_criteria);
			*/

			//print_r($db_btc_address_model->error);
			//print $db_btc_address_model->get_last_query();
			if ($number_affected == -1) {
			} else {
				$obj_result->is_success = true;
			}
		}
		print json_encode($obj_result);
	}

	function set_btc_address_to_primary($btc_address_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();

		/*
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->code)) {
				$code = $obj_request_data->code;
			}
		}
		*/

		$user_id = LoginAuth::get_session_user_id();
		

		$data = Array();

		$db_model = new DB_Users();
		$db_btc_address_model = new DB_BTCAddresses();
		
		if ($btc_address_id > 0) {
			$btc_address_data = $db_btc_address_model->get($btc_address_id);
		}
		//print_r($btc_address_data);
		
		if (count($btc_address_data) > 0 && $user_id == $btc_address_data['user_id']) {
			$result = $db_btc_address_model->set_btc_address_to_primary($btc_address_id);
			if ($result) {
				$obj_result->is_success = true;
				$arr_criteria = Array();
				$arr_criteria['user_id'] = $user_id;
				$arr_criteria['is_verified'] = "Y";

				$arr_data = $db_btc_address_model->search($arr_criteria);
				$obj_result->arr_data = $arr_data;
			}
		}
		print json_encode($obj_result);
	}

	function authorize_new_bitcoin_address($code) {
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		/*
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->code)) {
				$code = $obj_request_data->code;
			}
		}
		*/

		

		$data = Array();

		$db_model = new DB_Users();
		$db_btc_address_model = new DB_BTCAddresses();
		if ($code != "") {
			$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db);

			$arr_criteria = Array();
			$arr_criteria['verification_code'] = $code;
			$arr_criteria['is_verified'] = "N";
			$arr_criteria['verify_expiration_gm_date_time']['>='] = $now_gm_date_time;
			//print_r($arr_criteria);
			$arr_btc_data = $db_btc_address_model->search($arr_criteria);
			//print $db_btc_address_model->get_last_query();
			//print_r($arr_btc_data);
			if (count($arr_btc_data) > 0) {

				$btc_address_id = $arr_btc_data[0]['id'];
				$user_id = $arr_btc_data[0]['user_id'];
				$arr_criteria = Array();
				$arr_criteria['verification_code'] = $code;
				$arr_criteria['is_verified'] = "N";

				//print "in here\n";
				$temp_data = Array();
				$temp_data["is_verified"] = "Y";
				$temp_data["verification_code"] = "";
				$number_affected = $db_btc_address_model->update($temp_data, $arr_criteria);
				//print_r($db_btc_address_model->error());
				//print $db_btc_address_model->get_last_query();
				if ($number_affected == -1) {
				} else if ($number_affected == 1) {
					//$btc_address_id = $db_btc_address_model->get_last_insert_id();
					//print "btc address id is $btc_address_id\n";
					$result = $db_btc_address_model->set_btc_address_to_primary($btc_address_id);
			
					$arr_criteria = Array();
					$arr_criteria['id'] = $user_id;

					$temp_data = Array();
					$temp_data["is_have_additional_btc_addresses"] = "Y";
					$db_model->update($temp_data, $arr_criteria);
					//print "result is $result\n";
					$obj_result->is_success = true;
				}
			}
		}
		
		$data_object = new stdclass();
		$data_object->is_success = $obj_result->is_success;
		$data_object->btc_address = "";
		if ($obj_result->is_success) {
		}
		//print_r($arr_btc_data);
		$data_object->btc_address = $arr_btc_data[0]['btc_address'];
		//print_r($data_object);
		//print "blah blah\n";

		print BackEnd_MyProfiles_View::build_new_btc_address_authorize_html($data_object);
		//print json_encode($obj_result);
	}

	function add_new_btc_address() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		$btc_address = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
			if (isset($obj_request_data->btc_address)) {
				$btc_address = $obj_request_data->btc_address;
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();

		$db_model = new DB_Users();
		$db_btc_address_model = new DB_BTCAddresses();
		
		//print "user id is $user_id\n";
		$is_valid_btc_address = false;
		$obj_result->is_duplicate_found = false;

		if ($btc_address != "") {
			$is_valid_btc_address = BTCAddress::is_valid($btc_address);

			if ($is_valid_btc_address) {
				$arr_criteria = Array();
				$arr_criteria['btc_address'] = $btc_address;
				$arr_data = $db_model->search($arr_criteria);
				$is_found = false;
				if (count($arr_data) > 0) {
					$obj_result->is_duplicate_found = true;
					$is_found = true;
				}
					//print_r($arr_data);

				if (!$is_found) {
					$arr_criteria = Array();
					$arr_criteria['btc_address'] = $btc_address;
					$arr_data = $db_btc_address_model->search($arr_criteria);
					//print "last query is ".$this->db->last_query();
					//print_r($this->db->error);
					//print_r($arr_data);
					//print_r($arr_data);

					if (count($arr_data) > 0) {
						$obj_result->is_duplicate_found = true;
						$is_found = true;
					}

				}
				if (!$is_found) {
					$code = time()."_".getmypid();
					$expiration_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db,strtotime("+30 mins"));

					$temp_data = Array();
					$temp_data["btc_address"] = $btc_address;
					$temp_data["user_id"] = $user_id;
					$temp_data["is_verified"] = "N";
					$temp_data["verification_code"] = $code;
					$temp_data["verify_expiration_gm_date_time"] = $expiration_gm_date_time;

					
					$number_affected = $db_btc_address_model->save(0, $temp_data);
					//print "number affected is $number_affected\n";
					//print "in here\n";
					
					if ($number_affected == -1) {
						$obj_result->is_duplicate_info_found = true;
					} else {
						$obj_result->is_success = true;
						Emailer::send_new_btc_address_authorize_email($user_id, $code, $btc_address);
					}
				}
			}
		}

		print json_encode($obj_result);
	}
	
	function get_data() {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();

		$db_model = new DB_Users();
		$db_ut_model = new DB_UserTemp();
		
		//print "user id is $user_id\n";
		$data = $db_model->get($user_id);

		if (count($data) > 0) {
			$data = DB_Users::filter_sensitive_data($data);
			$referral_id = $data['referral_id'];
			if ($referral_id > 0) {
				$sponsor_data = $db_model->get($referral_id);
				
				$sponsor_data = DB_Users::filter_sensitive_data($sponsor_data);
				$obj_result->sponsor_data = $sponsor_data;
			}

			if (ctype_upper(preg_replace("/[\. ]/","",$data['country']))) {

				$data['country'] = ucwords(strtolower($data['country']));
			}

			if (ctype_lower(preg_replace("/[\. ]/","",$data['country']))) {
				$data['country'] = ucwords(($data['country']));
			}

			$user_temp_data = $db_ut_model->get_by_user_id($user_id);
			if (count($user_temp_data) == 0) {
				$user_temp_data = Array();
				$user_temp_data["is_have_income_level_gh_status_proof_image"] = "N";
				$user_temp_data["is_have_daily_growth_status_proof_image"] = "N";
				$user_temp_data["is_have_gh_request_available_balance_proof_image"] = "N";
				$user_temp_data["is_have_gh_history_proof_image"] = "N";
				$user_temp_data["is_have_ph_history_proof_image"] = "N";
				$user_temp_data['is_fields_locked'] = "N";
			}
			$obj_result->user_temp_data = $user_temp_data;
		}
		
			
		$obj_result->is_success = true;
		$obj_result->data = $data;
		print json_encode($obj_result);
	}






}
?>
