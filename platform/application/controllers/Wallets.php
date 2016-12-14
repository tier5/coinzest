<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallets extends CI_Controller {

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

		print BackEnd_Wallets_View::build_html();
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

	public function view($wallet_name, $user_id = 0) {

		Library_Auth_Common::check_allowed_request();

		print BackEnd_Wallets_View::build_html();
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

	// get downline data for user
	public function get_data($user_id = 0) {
		/*echo $user_id;
		die();*/
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$wallet_name = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->wallet_name)) {
				$wallet_name = $obj_request_data->wallet_name;
			}
		}
		//print_r($obj_request_data);

		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();
		//print "wallet name is $wallet_name\n";

		$wallet_type_id = 0;
		if ($wallet_name == "level_income") {
			 $wallet_type_id = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
		} else if ($wallet_name == "daily_growth") {
			 $wallet_type_id = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
		} else if ($wallet_name == "daily_bonus_earnings") {
			 $wallet_type_id = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == "task_earnings") {
			 $wallet_type_id = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
		}
		if ($wallet_type_id > 0) {
			$db_model = new DB_WalletLogs();
			$arr_criteria = Array();
			/*print_r($user_id);
			exit();*/
			//wallet type id should be 4
			$arr_criteria['user_id'] = $user_id;
			$arr_criteria['wallet_type_id'] = $wallet_type_id;
			$arr_criteria['is_pending_create'] = "N";
			$arr_criteria['order_by'] = "gm_created";
			$arr_data = $db_model->search($arr_criteria);
			//print "in here\n";
			//exit(0);
			$obj_result->is_success = true;
			$obj_result->arr_data = $arr_data;
		}

		print json_encode($obj_result);
	}
}
?>
