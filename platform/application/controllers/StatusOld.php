<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Status extends CI_Controller {

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

		//DailyMaintenance::run_daily_process();
		//$result = SMS::send_otp_code("3888", "3109234078");
		//$result = SMS::send_otp_code("3888", "8324228283");
		//$result = SMS::send_otp_code("3888", "8324228283");
		//$result = SMS::send_otp_code("3888", "7123581853");
		//MatchedRequest::match_all_gh_requests();
		//exit(0);

		print BackEnd_ComingSoon_View::build_html();
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


}
?>
