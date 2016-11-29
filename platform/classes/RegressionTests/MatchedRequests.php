<?

class RegressionTests_MatchedRequests extends CI_Model {


	function test_confirm_request() {
		print "start confirm request <br>\n";

		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		$db_ghr_model = new DB_GHRequests();
		$db_mr_model = new DB_MatchedRequests();

		$from_user_id = 1;
		$to_user_id = 22;

		$data = $db_model->get($from_user_id);
		$prev_total_ph = $data['total_ph'];
		$prev_unconfirmed_ph = $data['unconfirmed_ph'];
		//print_r($data);

		// to gh 20
		$data = Array();
		$data['user_id'] = $to_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$number_affected = $db_ghr_model->save(0, $data);
		$gh_id_1 = $db_ghr_model->get_last_insert_id();

		// create ph for 20
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id = $db_phr_model->get_last_insert_id();

		$data = Array();
		//$data['phrequest_user_id'] = $from_user_id;
		//$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_1 = $db_mr_model->get_last_insert_id();

		if ($mr_id_1 > 0) {
			$amount = 20;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id_1, $ph_id, $mr_id_1, $amount);
			//print "number affected is $number_affected<br>\n";
		}

		if ($number_affected != 4) {
			print "number affected should be 4<br>\n";
			$arr_mr_data = $db_mr_model->get($mr_id_1);
			print_r($arr_mr_data);
			exit(0);
		}
		$arr_mr_data = $db_mr_model->get($mr_id_1);
		if ($arr_mr_data['phrequest_user_id'] != $from_user_id) {
			print "ph user id not set in matchedrequest to $from_user_id<br>\n";
			$arr_mr_data = $db_mr_model->get($mr_id_1);
			print_r($arr_mr_data);
			//print_r($data);
			exit(0);
		}

		if ($arr_mr_data['ghrequest_user_id'] != $to_user_id) {
			print "gh user id not set in matchedrequest to $to_user_id<br>\n";
			$arr_mr_data = $db_mr_model->get($mr_id_1);
			print_r($arr_mr_data);
			//print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id);
		if ($data['amount_unconfirmed'] != 20) {
			print "unconfirmed ph should be 20<br>\n";
			$arr_mr_data = $db_mr_model->get($mr_id_1);
			print_r($arr_mr_data);
			//print_r($data);
			exit(0);
		}

		MatchedRequest::_set_image_receipt($mr_id_1);
		$db_mr_model->confirm_request($mr_id_1);

		$arr_mr_data = $db_mr_model->get($mr_id_1);
		//print_r($arr_mr_data);

		print "done confirm matched requests<br>\n";

		return true;
	}

	function test_confirm_request_2() {
		print "start confirm request <br>\n";

		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		$db_ghr_model = new DB_GHRequests();
		$db_mr_model = new DB_MatchedRequests();

		$from_user_id = 20;
		$to_user_id = 22;

		$data = $db_model->get($from_user_id);
		$prev_total_ph = $data['total_ph'];
		$prev_unconfirmed_ph = $data['unconfirmed_ph'];
		//print_r($data);

		// to gh 20
		$data = Array();
		$data['user_id'] = $to_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$number_affected = $db_ghr_model->save(0, $data);
		$gh_id_1 = $db_ghr_model->get_last_insert_id();

		// create ph for 20
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id = $db_phr_model->get_last_insert_id();

		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_1 = $db_mr_model->get_last_insert_id();

		if ($mr_id_1 > 0) {
			$amount = 20;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id_1, $ph_id, $mr_id_1, $amount);
			//print "number affected is $number_affected<br>\n";
		}

		$data = $db_phr_model->get($ph_id);
		if ($data['amount_unconfirmed'] != 20) {
			print "unconfirmed ph should be 20<br>\n";
			print_r($data);
			exit(0);
		}

		MatchedRequest::_set_image_receipt($mr_id_1);
		$db_mr_model->confirm_request($mr_id_1);

		$arr_mr_data = $db_mr_model->get($mr_id_1);
		print_r($arr_mr_data);

		print "done confirm matched requests<br>\n";

		return true;
	}


}
?>
