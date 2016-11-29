<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sitecontents extends CI_Controller {

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
			print BackEnd_SiteContents_View::build_html();
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
			print BackEnd_SiteContents_View::build_edit_html();
			//$result = DownlineModel::get_arr_user_downline_data($user_id);
		}
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
		
			
			if ($content_name == "daily_tasks") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../BackEnd/DailyTasks/html/contents.html");
			} else if ($content_name == "support") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../BackEnd/Support/html/contents.html");
			} else if ($content_name == "front_page_media") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../../FrontEnd/contents/video_section.html");
			} else if ($content_name == "front_page_faq") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../../FrontEnd/contents/faq.html");
			} else if ($content_name == "facebook_bottom_left_footer") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../../FrontEnd/contents/facebook_footer_left_corner.html");
			} else if ($content_name == "introduction") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../BackEnd/Introduction/html/introduction.html");
			} else if ($content_name == "notifications_pop_up") {
				$obj_result->contents_html = file_get_contents(dirname(__FILE__)."/../../BackEnd/Home/html/notifications_pop_up.html");
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
		
			
			if ($content_name == "daily_tasks") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../BackEnd/DailyTasks/html/contents.html", $contents_html);
			} else if ($content_name == "support") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../BackEnd/Support/html/contents.html", $contents_html);
			} else if ($content_name == "front_page_media") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../../FrontEnd/contents/video_section.html", $contents_html);
			} else if ($content_name == "front_page_faq") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../../FrontEnd/contents/faq.html", $contents_html);
			} else if ($content_name == "facebook_bottom_left_footer") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../../FrontEnd/contents/facebook_footer_left_corner.html", $contents_html);
			} else if ($content_name == "introduction") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../BackEnd/Introduction/html/introduction.html", $contents_html);
			} else if ($content_name == "notifications_pop_up") {
				$obj_result->contents_html = file_put_contents(dirname(__FILE__)."/../../BackEnd/Home/html/notifications_pop_up.html", $contents_html);
			}
			$obj_result->is_success = true;
		}
		print json_encode($obj_result);
	}

}
?>
