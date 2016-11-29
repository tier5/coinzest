<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserTemp extends CI_Controller {

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
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}

	public function get_proof_image($field_name, $id) {
		Library_Auth_Common::check_allowed_request();
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if ($field_name == "is_have_gh_history_proof_image") {
			UserTempHelper::get_proof_image($id, "gh_history_proof_image");
		} else if ($field_name == "is_have_ph_history_proof_image") {
			UserTempHelper::get_proof_image($id, "ph_history_proof_image");
		} else if ($field_name == "is_have_gh_request_available_balance_proof_image") {
			UserTempHelper::get_proof_image($id, "gh_request_available_balance_proof_image");
		} else if ($field_name == "is_have_income_level_gh_status_proof_image") {
			UserTempHelper::get_proof_image($id, "income_level_gh_status_proof_image");
		} else if ($field_name == "is_have_daily_growth_status_proof_image") {
			UserTempHelper::get_proof_image($id, "daily_growth_status_proof_image");
		}
		exit;
	}

	public function accept_and_set_proof_image($field_name, $id) {
		Library_Auth_Common::check_allowed_request();

		$obj_result = new stdclass();
		$obj_result->is_success = false;

		if ($field_name == "is_have_gh_history_proof_image") {
			$obj_result = UserTempHelper::accept_and_set_proof_image($id, "is_have_gh_history_proof_image");
		} else if ($field_name == "is_have_ph_history_proof_image") {
			$obj_result = UserTempHelper::accept_and_set_proof_image($id, "is_have_ph_history_proof_image");
		} else if ($field_name == "is_have_gh_request_available_balance_proof_image") {
			$obj_result = UserTempHelper::accept_and_set_proof_image($id, "is_have_gh_request_available_balance_proof_image");
		} else if ($field_name == "is_have_income_level_gh_status_proof_image") {
			$obj_result = UserTempHelper::accept_and_set_proof_image($id, "is_have_income_level_gh_status_proof_image");
		} else if ($field_name == "is_have_daily_growth_status_proof_image") {
			$obj_result = UserTempHelper::accept_and_set_proof_image($id, "is_have_daily_growth_status_proof_image");
		}
		print json_encode($obj_result);
	}
	
	/* upload image receipt */
	// could just pass in get with id
	public function upload_proof_image($field_name, $id) {
		Library_Auth_Common::check_allowed_request();

		$obj_result = new stdclass();
		$obj_result->is_success = false;
		if ($field_name == "is_have_gh_history_proof_image") {
			$obj_result = UserTempHelper::upload_proof_image($id, $_FILES, "is_have_gh_history_proof_image", "gh_history_proof_image");
		} else if ($field_name == "is_have_ph_history_proof_image") {
			$obj_result = UserTempHelper::upload_proof_image($id, $_FILES, "is_have_ph_history_proof_image", "ph_history_proof_image");
		} else if ($field_name == "is_have_gh_request_available_balance_proof_image") {
			$obj_result = UserTempHelper::upload_proof_image($id, $_FILES, "is_have_gh_request_available_balance_proof_image", "gh_request_available_balance_proof_image");
		} else if ($field_name == "is_have_income_level_gh_status_proof_image") {
			$obj_result = UserTempHelper::upload_proof_image($id, $_FILES, "is_have_income_level_gh_status_proof_image", "income_level_gh_status_proof_image");
		} else if ($field_name == "is_have_daily_growth_status_proof_image") {
			$obj_result = UserTempHelper::upload_proof_image($id, $_FILES, "is_have_daily_growth_status_proof_image", "daily_growth_status_proof_image");
		}
		print json_encode($obj_result);
	}
}
?>
