<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MembersAdmin extends CI_Controller {

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

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			print BackEnd_MembersAdmin_View::build_html();
		}
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

	// temp function
	public function get_user_arr_managers($user_id, $ph_id) {
		Library_Auth_Common::check_allowed_request();
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			$db_user_model = new DB_Users();

			$arr_manager_data = $db_user_model->arr_get_all_user_managers_and_level($user_id);
			print_r($arr_manager_data);
			DB_PHRequests::_build_commission_sql($ph_id, $sql_tables_more, $sql_set_more, $sql_where_more, $additional_tables, true);
			$sql = "update $sql_tables_more set $sql_set_more where $sql_where_more";
			print "sql is $sql\n";
			print "additional tables is $additional_tables\n";
		
		}
	}

	public function change_to_user($user_id) {
		Library_Auth_Common::check_allowed_request();
		//print_r($_SESSION);
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			session_start();
			$_SESSION['user_id'] = $user_id;
			session_write_close();
			//print "user id is ".$_SESSION['user_id'];
			print_r($_SESSION);
		}
	}

	public function update_password($user_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$db_model = new DB_Users();

		
		$password = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$password = $obj_request_data->data->password;
				//print_r($obj_request_data);
			}
		}
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			if ($user_id > 0 && $password != "") {
				$data = Array();
				$data['password'] = $password;
				//print_r($data);
				$bcrypt = new Bcrypt(5);
				$data['password'] = $bcrypt->hash($data['password']);
				$arr_sql_where = Array();
				$arr_sql_where['id'] = $user_id;
				$db_model->update($data, $arr_sql_where);

				$obj_result->is_success = true;
			}
		}
		print json_encode($obj_result);
	}

	public function send_signup_email($user_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$db_model = new DB_Users();

		
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			if ($user_id > 0) {
				$data = Array();
				$password = rand(100000, 999999);
				$data['password'] = $password;
				$bcrypt = new Bcrypt(5);
				$data['password'] = $bcrypt->hash($data['password']);
				$data['otp'] = rand(1000,9999);
				$data['is_activated'] = "Y";
				$arr_sql_where = Array();
				$arr_sql_where['id'] = $user_id;
				// if invoice already paid do not set to uncollected status
				//$this->db->update(DB_Users::$TABLE_NAME, $data, $arr_sql_where);
				//print "end of update\n";
				$db_model->update($data, $arr_sql_where);


				Emailer::send_signup_email($user_id, $password);

				$obj_result->is_success = true;
			}
			//move_uploaded_file( $_FILES['file']['tmp_name'], $destination);
			//file_put_contents("blah.log", "importing items\n", FILE_APPEND);
			//print "completed\n";
			
			//file_put_contents("blah.log", "importing items\n", FILE_APPEND);
		}
		print json_encode($obj_result);
	}

	public function csv_import() {
		Library_Auth_Common::check_allowed_request();
		
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		} else {
			//print_r($_FILES);
			$filename = $_FILES['file']['name'];
			$destination = $_SERVER['DOCUMENT_ROOT']."/". $filename;
			$filename = $_FILES['file']['tmp_name'];
			//$filename = $_SERVER['DOCUMENT_ROOT']."/zipcode_population.csv";
			$db_model = new ImportMembers();
			$obj_result = $db_model->import_csv($filename);

			@unlink($_FILES['file']['tmp_name']);
			print json_encode($obj_result);
			//move_uploaded_file( $_FILES['file']['tmp_name'], $destination);
			//file_put_contents("blah.log", "importing items\n", FILE_APPEND);
			//print "completed\n";
			
			//file_put_contents("blah.log", "importing items\n", FILE_APPEND);
		}
	}

	public function edit() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}
		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}

		$user_id = LoginAuth::get_session_user_id();
		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();


		if (LoginAuth::is_user_have_admin_access()) {
			print BackEnd_MembersAdmin_View::build_edit_html();
			//$result = DownlineModel::get_arr_user_downline_data($user_id);
		}
	}

	public function suspend_account($user_id) {
		Library_Auth_Common::check_allowed_request();
		$search = "";
		$obj_request_data = Common::load_request_data();
		$passed_data = Array();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$passed_data = (Array) $obj_request_data->data;
			}
		}
		//print "adjust_amount is $adjust_amount\n";


		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		if (count($passed_data) > 0 && $user_id > 0) {
			$db_user_model = new DB_Users();

			$current_user_id = LoginAuth::get_session_user_id();

			$data = Array();
			$data['status_reason_type_id'] = DB_Users::$ADMIN_SUSPENDED_REASON_TYPE_ID;
			$data['status_id'] = DB_Users::$SUSPENDED_STATUS_ID;
			$data['status_changed_by_user_id'] = $current_user_id;
			$data['status_reason_change_text'] = $passed_data['suspend_reason'];
			$number_affected = $db_user_model->save($user_id, $data);
			//print "number affected is $number_affected\n";
			if ($number_affected == 1) {
				$obj_result->is_success = true;
				Emailer::send_suspended_user_email($user_id);
			}
		}
		
		print json_encode($obj_result);
	}

	public function block_account($user_id) {
		Library_Auth_Common::check_allowed_request();
		$search = "";
		$obj_request_data = Common::load_request_data();
		$passed_data = Array();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$passed_data = (Array) $obj_request_data->data;
			}
		}
		//print "adjust_amount is $adjust_amount\n";


		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		if (count($passed_data) > 0 && $user_id > 0) {
			$db_user_model = new DB_Users();

			$current_user_id = LoginAuth::get_session_user_id();

			$data = Array();
			$data['status_reason_type_id'] = DB_Users::$ADMIN_BLOCK_REASON_TYPE_ID;
			$data['status_id'] = DB_Users::$BLOCKED_STATUS_ID;
			$data['status_changed_by_user_id'] = $current_user_id;
			$data['status_reason_change_text'] = $passed_data['block_reason'];
			$number_affected = $db_user_model->save($user_id, $data);
			if ($number_affected == 1) {
				$obj_result->is_success = true;
				Emailer::send_blocked_user_email($user_id);
			}
		}
		
		print json_encode($obj_result);
	}

	public function adjust_wallet($wallet_name, $adjust_user_id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
		}
		//print "adjust_amount is $adjust_amount\n";


		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();

		$db_user_model = new DB_Users();

		//print "wallet name is $wallet_name\n";
		$wallet_type_id = 0;
		if ($wallet_name == 'task_earnings' ) {
			$wallet_type_id = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == 'level_income' ) {
			$wallet_type_id = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
		} else if ($wallet_name == 'daily_bonus_earnings' ) {
			$wallet_type_id = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == 'daily_growth' ) {
			$wallet_type_id = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
		}

		$addditional_params = Array();
		if (isset($data['is_frozen'])) {
			if ($data['is_frozen'] == "Y") {
				$additional_params['is_available'] = false;
			} else {
				$additional_params['is_available'] = true;
			}
		}
		$adjust_reason = "";
		if (isset($data['adjust_reason'])) {
			$adjust_reason = $data['adjust_reason'];
		}
		if (isset($data['is_release_part_of_balance_on_ph_complete'])) {
			if ($data['is_release_part_of_balance_on_ph_complete'] == "Y") {
				$additional_params['is_release_part_of_funds_on_ph_complete'] = true;
			} else {
				$additional_params['is_release_part_of_funds_on_ph_complete'] = false;
			}
		}
		//print_r($data);

		if (isset($data['number_days_frozen'])) {
			if (is_numeric($data['number_days_frozen']) && $data['number_days_frozen'] > 0) {
				$additional_params['available_gm_create_date_time'] = Library_DB_Util::time_to_gm_db_time($db_user_model->db,strtotime("+".$data['number_days_frozen']." days", time())); 
			}
		}
		//print_r($additional_params);

		$user_id = LoginAuth::get_session_user_id();
		$amount = 0;
		if (count($data) > 0) {
			$amount = $data['amount'];
		}
	
		//print_r($data);
		//print "wallet type id is $wallet_type_id\n";
		//print "wallet type id is $wallet_type_id and adjust user id is $adjust_user_id\n";
		if ($adjust_user_id > 0 && $user_id > 0 &&  count($data) > 0 && $wallet_type_id > 0 && is_numeric($amount)) {

			$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $adjust_user_id, $amount,$adjust_reason,"","", $additional_params);
		}
		print json_encode($obj_result);
	}

	public function save($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->data)) {
				$data = (Array) $obj_request_data->data;
			}
		}

		if (!LoginAuth::is_user_have_admin_access()) {
			print BackEnd_Login_View::build_no_access_html();
			exit(0);
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();

		$user_id = LoginAuth::get_session_user_id();
	
		if ($id > 0) {
			unset($data['password']);
			unset($data['gm_created']);
			unset($data['gm_password']);
			unset($data['user_pass']);
			unset($data['otc']);
			foreach($data as $key => $value) {
				if ($key == "login") {
				} else if ($key == "fullname") {
				} else if ($key == "country") {
				} else if ($key == "btc_address") {
				} else if ($key == "date_of_birth") {
				} else if ($key == "complete_street_address") {
				} else if ($key == "phone") {
				} else if ($key == "is_approved_for_matching") {
				} else if ($key == "is_manager") {
				} else if ($key == "is_admin") {
				} else if ($key == "status_id") {
				} else if ($key == "is_old_data_verified") {
				} else if ($key == "is_registration_reviewed") {
				} else if ($key == "is_allow_gh_to_match_restricted_countries") {
				} else if ($key == "ph_completed_level") {
				} else if ($key == "is_test_match_account") {
				} else if ($key == "is_view_old_data_form") {
				} else if ($key == "is_allow_ph_to_match_restricted_countries") {
				} else {
					unset($data[$key]);
				}
			}

			$db_model = new DB_Users();
				
			$number_affected = $db_model->save($id, $data);
			if ($number_affected > 0) {
				$obj_result->is_success = true;
			}
			$data = Array();
			
		}

		print json_encode($obj_result);

	}

	public function get_balance_photos($id) {
		Library_Auth_Common::check_allowed_request();
		if ($id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$destination = $_SERVER['DOCUMENT_ROOT']."/../../uploads/balance_photos/".$id.".png";
				//print "destination is $destination\n";
				//exit(0);
				if (is_file($destination)) {
					//print "in here\n";
					header("Content-type: image/png");
					readfile("$destination");
					exit;
				} 
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}

	public function get_government_id($id) {
		Library_Auth_Common::check_allowed_request();
		if ($id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$destination = Registry::get("uploads_dir")."/government_ids/".$id.".png";
				//print "destination is $destination\n";
				//exit(0);
				if (is_file($destination)) {
					//print "in here\n";
					header("Content-type: image/png");
					readfile("$destination");
					exit;
				}
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}

	public function get_photo_id($id) {
		Library_Auth_Common::check_allowed_request();
		if ($id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
				$destination = Registry::get("uploads_dir")."/personal_ids/".$id.".png";
				//print "destination is $destination\n";
				//exit(0);
				if (is_file($destination)) {
					//print "in here\n";
					header("Content-type: image/png");
					readfile("$destination");
					exit;
				}
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}


	// get data for user
	public function get($id) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
		}


		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->data = Array();


		if (is_numeric($id) && $id > 0) {
			if (LoginAuth::is_user_have_admin_access()) {
		
				$db_model = new DB_Users();
				$data = $db_model->get($id);
				$data['sponsor_email'] = "";

				if ($data['referral_id'] > 0) {
					$sponsor_data = $db_model->get($data['referral_id']);
					if (count($sponsor_data) > 0) {
						$data['sponsor_email'] = $sponsor_data['login'];
					}
				}
				if (ctype_upper(preg_replace("/[\. ]/","",$data['country']))) {
					$data['country'] = ucwords(strtolower($data['country']));
				}
				if (ctype_lower(preg_replace("/[\. ]/","",$data['country']))) {
					$data['country'] = ucwords(($data['country']));
				}

				$db_btc_address_model = new DB_BTCAddresses();
				$arr_btc_a_data = $db_btc_address_model->get_arr_btc_addresses_for_user($id);
				$obj_result->arr_btc_a_data = $arr_btc_a_data;

				
				$obj_result->data = $data;
				$obj_result->is_success = true;
			}
		}
		print json_encode($obj_result);
	}

	public function create_gh($user_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->data = Array();
		$obj_result->is_invalid_gh_amount = false;

		

		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
			$search = "";
			$obj_request_data = Common::load_request_data();
			if (is_object($obj_request_data)) {
				if (isset($obj_request_data->data)) {
					$arr_form_data = (Array)$obj_request_data->data;
				}
			}

			if (is_numeric($arr_form_data['level_income_amount']) && $arr_form_data['level_income_amount'] > 0) {
				$amount = $arr_form_data['level_income_amount'];
				$total_amount += $amount;
			}

			if (is_numeric($arr_form_data['daily_bonus_earnings_amount']) && $arr_form_data['daily_bonus_earnings_amount'] > 0) {
				$amount = $arr_form_data['daily_bonus_earnings_amount'];
				$total_amount += $amount;
			}

			if (is_numeric($arr_form_data['task_earnings_amount']) && $arr_form_data['task_earnings_amount'] > 0) {
				$amount = $arr_form_data['task_earnings_amount'];
				$total_amount += $amount;
			}
			if (is_numeric($arr_form_data['daily_growth_amount']) && $arr_form_data['daily_growth_amount'] > 0) {
				$amount = $arr_form_data['daily_growth_amount'];
				$total_amount += $amount;
			}

			if (!GHRequest::is_valid_gh_amount($total_amount)) {
				$obj_result->is_invalid_gh_amount = true;
			}

			if (is_array($arr_form_data) && $user_id > 0 && !$obj_result->is_invalid_gh_amount) {
				$db_model = new DB_GHRequests();

			
				$arg_data = new stdclass();
				$arg_data->daily_growth_amount = 0;
				$arg_data->daily_bonus_amount = 0;
				$arg_data->task_earning_amount = 0;
				$arg_data->level_income_amount = 0;


				$total_amount = 0;
				// race condition
				if (is_numeric($arr_form_data['level_income_amount']) && $arr_form_data['level_income_amount'] > 0) {
					$amount = $arr_form_data['level_income_amount'];
					$arg_data->level_income_amount = $amount;
					$wallet_name = "level_income";
					$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $user_id, $amount);
					$total_amount += $amount;
				} else {
					$arg_data->level_income_amount = 0;
				}

				if (is_numeric($arr_form_data['daily_bonus_earnings_amount']) && $arr_form_data['daily_bonus_earnings_amount'] > 0) {
					$amount = $arr_form_data['daily_bonus_earnings_amount'];
					$arg_data->daily_bonus_amount = $amount;
					$wallet_name = "daily_bonus_earnings";
					$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $user_id, $amount);
					$total_amount += $amount;
				} else {
					$arg_data->daily_bonus_amount = 0;
				}

				if (is_numeric($arr_form_data['task_earnings_amount']) && $arr_form_data['task_earnings_amount'] > 0) {
					$amount = $arr_form_data['task_earnings_amount'];
					$arg_data->task_earning_amount = $amount;
					$wallet_name = "task_earnings";
					$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $user_id, $amount);
					$total_amount += $amount;
				} else {
					$arg_data->task_earning_amount = 0;
				}

				if (is_numeric($arr_form_data['daily_growth_amount']) && $arr_form_data['daily_growth_amount'] > 0) {
					$amount = $arr_form_data['daily_growth_amount'];
					$arg_data->daily_growth_amount = $amount;
					$wallet_name = "daily_growth";
					$obj_result = MembersAdminHelper::adjust_wallet_by_name($wallet_name, $user_id, $amount);
					$total_amount += $amount;
				} else {
					$arg_data->daily_growth_amount = 0;
				}
				// race condition
				$obj_result = GHRequest::create_gh_request($user_id, $arg_data, $total_amount);
			}
		}
		print json_encode($obj_result);
	}

	public function create_ph($user_id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->data = Array();
		$obj_result->is_invalid_ph_amount = false;


		
		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
			$search = "";
			$obj_request_data = Common::load_request_data();
			if (is_object($obj_request_data)) {
				if (isset($obj_request_data->data)) {
					$arr_form_data = (Array)$obj_request_data->data;
				}
			}
			//print_r($obj_request_data);
			//print_r($arr_form_data);

			if (is_array($arr_form_data) && $arr_form_data['amount'] > 0 && $user_id > 0) {
				if (!PHRequest::is_valid_ph_amount($arr_form_data['amount'])) {
					$obj_result->is_invalid_ph_amount = true;
				} else {
					$db_model = new DB_PHRequests();
					$db_user_model = new DB_Users();
					$user_data = $db_user_model->get($user_id);
					if (count($user_data) > 0) {
						$country = $user_data['country'];
						// race condition for country

						$amount = $arr_form_data['amount'];
						$amount_available = $amount;
						if ($amount >= 100) {
							$amount_available = round($amount * .20, -1);
						}

						$data = Array();
						$data['user_id'] = $user_id;
						$data['amount'] = $arr_form_data['amount'];
						$data['country'] = $country;
						$data['amount_available'] = $amount_available;
						$number_affected = $db_model->save(0, $data);
						if ($number_affected > 0) {
							$obj_result->is_success = true;
						}
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	// get data for user
	public function get_data() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$is_have_old_data = "";
		$is_needs_review = "";
		$btc_address = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->search)) {
				$search = $obj_request_data->search;
			}
			if (isset($obj_request_data->is_have_old_data)) {
				$is_have_old_data = $obj_request_data->is_have_old_data;
			}
			if (isset($obj_request_data->is_registration_reviewed)) {
				$is_needs_review = $obj_request_data->is_registration_reviewed;
			}
			if (isset($obj_request_data->btc_address)) {
				$btc_address = $obj_request_data->btc_address;
			}
		}
		//print_r($obj_request_data);

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();


		if (LoginAuth::is_user_have_admin_access()) {
			$db_model = new DB_Users();
			//$result = DownlineModel::get_arr_user_downline_data($user_id);
			$arr_criteria = Array();
			$arr_criteria['search'] = $search;
			$arr_criteria['login']['!='] = "";
			if ($btc_address != "") {
				$arr_criteria['btc_address'] = $btc_address;
			}

			if ($is_needs_review == "Y") {
				$arr_criteria['is_registration_reviewed'] = "N";
				$arr_criteria['is_done_registration_process'] = "Y";
			}
			if ($is_have_old_data == "Y") {
				$arr_criteria['is_have_old_data'] = $is_have_old_data;
				$arr_criteria['is_old_data_verified'] = "N";
			}
			$arr_criteria['number_results_per_page'] = 1000;
			//print_r($arr_criteria);
			$arr_data = $db_model->search($arr_criteria);
			$obj_result->is_success = true;
			for($n=0;$n<count($arr_data);$n++) {
				$arr_data[$n] = DB_Users::filter_sensitive_data($arr_data[$n]);
			}
			$obj_result->arr_data = $arr_data;
		}
		print json_encode($obj_result);
	}
}
?>
