<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Emailcontents extends CI_Controller {

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
			print BackEnd_EmailContents_View::build_html();
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
			print BackEnd_EmailContents_View::build_edit_html();
			//$result = DownlineModel::get_arr_user_downline_data($user_id);
		}
	}


	private function get_arr_contents_list() {
		$arr_data['reset_otp_email'] = (dirname(__FILE__)."/../../BackEnd/Login/html/reset_otp_email.html");
		$arr_data['signup_email'] = (dirname(__FILE__)."/../../BackEnd/CreateMember/html/signup_email.html");
		$arr_data['signup_email_to_sponsor'] = (dirname(__FILE__)."/../../BackEnd/CreateMember/html/signup_email_to_sponsor.html");
		$arr_data['ph_created_email'] = (dirname(__FILE__)."/../../BackEnd/PHRequests/html/phrequest_email.html");
		$arr_data['gh_created_email'] = (dirname(__FILE__)."/../../BackEnd/GHRequests/html/ghrequest_email.html");
		$arr_data['gh_match_email'] = (dirname(__FILE__)."/../../BackEnd/MatchedRequests/html/gh_email.html");
		$arr_data['ph_match_email'] = (dirname(__FILE__)."/../../BackEnd/MatchedRequests/html/ph_email.html");
		$arr_data['image_receipt_upload_email'] = (dirname(__FILE__)."/../../BackEnd/MatchedRequests/html/image_receipt_upload_email.html");
		$arr_data['match_confirm_email'] = (dirname(__FILE__)."/../../BackEnd/MatchedRequests/html/gh_confirm_email.html");
		return $arr_data;
	}

	// get data for user
	public function get($content_name) {
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


		if (LoginAuth::is_user_have_admin_access()) {
		
			$arr_contents_list = $this->get_arr_contents_list();
			foreach($arr_contents_list as $key => $value) {
				if ($key == $content_name) {
					$obj_result->contents_html = file_get_contents($value);
					
				}
			}
			$obj_result->is_success = true;
		}
		print json_encode($obj_result);
	}

	// save contents
	public function save($content_name) {
		Library_Auth_Common::check_allowed_request();
		$user_id = 0;
		$search = "";
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->contents_html)) {
				$contents_html = $obj_request_data->contents_html;
			}
		}


		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->data = Array();


		if (LoginAuth::is_user_have_admin_access()) {
		
			$arr_contents_list = $this->get_arr_contents_list();
			foreach($arr_contents_list as $key => $value) {
				if ($key == $content_name) {
					$obj_result->contents_html = file_put_contents($value, $contents_html);
					
				}
			}
			$obj_result->is_success = true;
		}
		print json_encode($obj_result);
	}

}
?>
