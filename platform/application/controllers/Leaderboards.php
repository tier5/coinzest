<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leaderboards extends CI_Controller {

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

		//print BackEnd_PHRequests_View::build_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	public function highest_sponsored_members_weekly() {
		Library_Auth_Common::check_allowed_request();

		print BackEnd_Leaderboards_View::build_most_refs_weekly_leaderboard_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}

	public function highest_completed_ph_weekly() {
		Library_Auth_Common::check_allowed_request();

		print BackEnd_Leaderboards_View::build_highest_completed_ph_weekly_leaderboard_html();
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
	}



	public function get_refs_ph_amount_sponsored_stats_weekly_leaderboard($order_by = "") {
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
		}


		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		// sign ups 

		
		//print date_default_timezone_set('UTC');
		
		
		$start_timestamp = strtotime("last monday");
		$end_timestamp = strtotime("next monday");

		$gm_start_date_time = Library_DB_Util::time_to_gm_db_time($this->db, $start_timestamp);

		$gm_end_date_time = Library_DB_Util::time_to_gm_db_time($this->db, strtotime("+1 day", $end_timestamp));

		

		date_default_timezone_set(ini_get('date.timezone'));
		

		// try with amount matched completed
		$fields = DB_Users::$TABLE_NAME.".login, ".DB_Users::$TABLE_NAME.".fullname, ".DB_Users::$TABLE_NAME.".first_name, ".DB_Users::$TABLE_NAME.".last_name, ".DB_Users::$TABLE_NAME.".country, ".DB_Users::$TABLE_NAME.".id";
		$this->db->select($fields.", SUM(".DB_PHRequests::$TABLE_NAME.".amount_matched_completed) as amount, count(ph_user_table.id) as number_refs")->from(DB_Users::$TABLE_NAME);
		$this->db->join(DB_Users::$TABLE_NAME." as ph_user_table","ph_user_table.referral_id = ".DB_Users::$TABLE_NAME.".id");
		$this->db->join(DB_PHRequests::$TABLE_NAME,"".DB_PHRequests::$TABLE_NAME.".user_id = ph_user_table.id");
		$this->db->where(DB_PHRequests::$TABLE_NAME.".gm_created >=",$gm_start_date_time);
		$this->db->where(DB_PHRequests::$TABLE_NAME.".gm_created <",$gm_end_date_time);
		$this->db->where(DB_PHRequests::$TABLE_NAME.".amount_matched_completed >",0);
		$this->db->where("ph_user_table.gm_created >=",$gm_start_date_time);
		$this->db->where("ph_user_table.gm_created <",$gm_end_date_time);
		$this->db->group_by(DB_Users::$TABLE_NAME.".id");


		/*
		$fields = DB_Users::$TABLE_NAME.".login, ".DB_Users::$TABLE_NAME.".first_name, ".DB_Users::$TABLE_NAME.".last_name, ".DB_Users::$TABLE_NAME.".country, ".DB_Users::$TABLE_NAME.".id";
		$this->db->select($fields.", SUM(".DB_PHRequests::$TABLE_NAME.".amount) as amount, count(ph_user_table.id) as number_refs")->from(DB_Users::$TABLE_NAME);
		$this->db->join(DB_Users::$TABLE_NAME." as ph_user_table","ph_user_table.referral_id = ".DB_Users::$TABLE_NAME.".id");
		$this->db->join(DB_PHRequests::$TABLE_NAME,"".DB_PHRequests::$TABLE_NAME.".user_id = ph_user_table.id");
		$this->db->where(DB_PHRequests::$TABLE_NAME.".gm_created >=",$gm_start_date_time);
		$this->db->where(DB_PHRequests::$TABLE_NAME.".gm_created <",$gm_end_date_time);
		$this->db->where(DB_PHRequests::$TABLE_NAME.".status_id",DB_PHRequests::$COMPLETED_STATUS_ID);
		$this->db->where("ph_user_table.gm_created >=",$gm_start_date_time);
		$this->db->where("ph_user_table.gm_created <",$gm_end_date_time);
		$this->db->group_by(DB_Users::$TABLE_NAME.".id");
		*/
		if ($order_by == "amount") {
			$this->db->order_by("amount","desc");
		} else {
			$this->db->order_by("number_refs","desc");
		}
		$data = $this->db->get();
		//print_r($data);
		//echo $this->db->last_query();
		//print_r($this->db->error());

		
		//print "In here\n";


		$res = $data->result_array();
		for($n=0;$n<count($res);$n++) {
			$arr_split = preg_split("/\@/", $res[$n]['login']); 
			if (count($arr_split) == 2) {
				if (strlen($arr_split[0]) >= 5) {
					$res[$n]['login'] = substr($arr_split[0], 0, 3)."...@".$arr_split[1]."";
				} else {
					$res[$n]['login'] = "...@".$arr_split[1];
				}
			} else {
				$res[$n]['login'] = "...xxx...@gmail.com";
			}
		}
		//print_r($res);
		
		$obj_result->is_success = true;
		$obj_result->arr_data = $res;
		print json_encode($obj_result);
	}
}
