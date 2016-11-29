<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Downline extends CI_Controller {

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

		print BackEnd_Downline_View::build_html();
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
	public function get_user_downline_data() {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->user_id)) {
			}
		}

		$user_id = LoginAuth::get_session_user_id();

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;


		$result = DownlineModel::get_arr_user_downline_data($user_id, 14);
		if ($result->is_success) {
			$obj_result->is_success = true;
			$obj_result->arr_data = $result->arr_data;
		}
		print json_encode($obj_result);
	}
}
?>
