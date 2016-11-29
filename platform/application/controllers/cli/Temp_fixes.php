<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Temp_fixes extends CI_Controller {

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


	//php index.php cli temp_fixes fix_give_daily_tasks_earnings_for_completed_phrequest_temp
	public function fix_give_daily_tasks_earnings_for_completed_phrequest_temp()  {
		if($this->input->is_cli_request()) {
			PHRequest::fix_give_daily_tasks_earnings_for_completed_phrequest_temp();
			print "done<br>\n";
		}
		exit(0);
	}

	public function check_and_update_frozen_bonuses()  {
		if($this->input->is_cli_request()) {
			DailyMaintenance::check_and_update_frozen_bonuses();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_take_back_admin_adjustment_for_not_available()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_take_back_admin_adjustment_for_not_available();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_primary_btc_addresses()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_primary_btc_addresses();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_2nd_time_level_income_completed_phrequests_temp()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_2nd_time_level_income_completed_phrequests_temp();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_0_btc_price()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_0_btc_price();
			print "done<br>\n";
		}
		exit(0);
	}

	public function fix_completed_gh_requests()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_completed_gh_requests();
			print "done<br>\n";
		}
		exit(0);
	}


	public function fix_loh()  {
		if($this->input->is_cli_request()) {
			SiteFixes::fix_loh();
			print "done<br>\n";
		}
		exit(0);
	}
}
?>
