<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RTests extends CI_Controller {

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
			RegressionTests::run();
			exit(0);
			$db_model = new DB_Users();
			$db_phr_model = new DB_PHRequests();
			$db_ghr_model = new DB_GHRequests();
			$db_mr_model = new DB_MatchedRequests();
			$to_user_id = 22;

			$data = Array();
			$data['user_id'] = $to_user_id;
			$data['amount'] = 40;
			$data['amount_available'] = 40;
			$number_affected = $db_ghr_model->save(0, $data);
			$gh_id_2 = $db_ghr_model->get_last_insert_id();

			$data = Array();
			$data['user_id'] = $to_user_id;
			$data['amount'] = 40;
			$data['amount_available'] = 40;
			$number_affected = $db_phr_model->save(0, $data);
			//$gh_id_2 = $db_ghr_model->get_last_insert_id();
			exit(0);
		}
		exit(0);
	}
}
?>
