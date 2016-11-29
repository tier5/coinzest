<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daily_maintenance extends CI_Controller {

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
		$is_dev_server = Registry::get("is_dev_server");
		if ($is_dev_server) {
			DailyMaintenance::run_daily_process();
			print "done<br>\n";
		}
		exit(0);
	}

	public function run() {
		if($this->input->is_cli_request()) {
			DailyMaintenance::run_daily_process();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_completed_phrequests_temp() {
		if($this->input->is_cli_request()) {
			PHRequest::fix_completed_phrequests_temp();
			print "done<br>\n";
		}
		exit(0);
	}
	public function check_all_users_diff_daily_growth_temp()  {
		if($this->input->is_cli_request()) {
			WalletHelper::check_all_users_diff_daily_growth_temp();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_level_income_completed_phrequests_temp()  {
		if($this->input->is_cli_request()) {
			PHRequest::fix_level_income_completed_phrequests_temp();
			print "done<br>\n";
		}
		exit(0);
	}
}
?>
