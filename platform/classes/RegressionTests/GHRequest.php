<?

class RegressionTests_GHRequest extends CI_Model {


	function test_cancel_gh() {
		print "start cancel_gh<br>\n";

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

		// to gh 250
		$data = Array();
		$data['user_id'] = $to_user_id;
		$data['amount'] = 250;
		$data['amount_available'] = 250;
		$number_affected = $db_ghr_model->save(0, $data);
		$gh_id = $db_ghr_model->get_last_insert_id();


		// create ph for 60
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = 17;
		$data['amount'] = 60;
		$data['amount_available'] = 60;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_1 = $db_phr_model->get_last_insert_id();

		// create ph for 80
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 80;
		$data['amount_available'] = 80;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_2 = $db_phr_model->get_last_insert_id();

		// create ph for 20
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_3 = $db_phr_model->get_last_insert_id();


		$data = Array();
		$data['phrequest_user_id'] = 17;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_1 = $db_mr_model->get_last_insert_id();

		if ($mr_id_1 > 0) {
			$amount = 60;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_1, $mr_id_1, $amount);
			//print "number affected is $number_affected<br>\n";
		}

		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_unconfirmed'] != 60) {
			print "unconfirmed ph should be 60<br>\n";
			print_r($data);
			exit(0);
		}

		/*
		print "prev $prev_unconfirmed_ph<br>\n";
		$data = $db_model->get(17);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		print "new $new_unconfirmed_ph<br>\n";
		if (($prev_unconfirmed_ph + 60) != $new_unconfirmed_ph) {
			print "unconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;
		*/

		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_2 = $db_mr_model->get_last_insert_id();
		if ($mr_id_2 > 0) {
			$amount = 40;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_2, $mr_id_2, $amount);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph + 40) != $new_unconfirmed_ph) {
			print "aunconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;

		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_3 = $db_mr_model->get_last_insert_id();
		if ($mr_id_3 > 0) {
			$amount = 20;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_3, $mr_id_3, $amount);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph + 20) != $new_unconfirmed_ph) {
			print "aunconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;



		// amount available == 0
		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_matched'] != 60) {
			print "amount matched should be 60<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_2);
		if ($data['amount_matched'] != 40) {
			print "amount matched should be 40<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_ghr_model->get($gh_id);
		//print_r($data);
		if ($data['amount_matched'] != 120) {
			print "amount matched should be 120<br>\n";
			print_r($data);
			exit(0);
		}

		// from here cancel gh
		GHRequest::cancel_gh($gh_id, $from_user_id);


		$number_affected = $db_ghr_model->db->affected_rows();
		if($number_affected != 9) {
			print "number affected should be 9 is $number_affected\n";
			
			exit(0);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph - 120) != $new_unconfirmed_ph) {
			print "after cancel gh unconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;

		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_available'] != 60) {
			print "amount available should be 60<br>\n";
			print_r($data);
			exit(0);
		}
		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_2);
		if ($data['amount_available'] != 80) {
			print "amount available should be 80<br>\n";
			print_r($data);
			exit(0);
		}

		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_3);
		if ($data['amount_available'] != 20) {
			print "amount available should be 20<br>\n";
			print_r($data);
			exit(0);
		}

		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_model->get($from_user_id);

		// total ph is scewed here since we don't add to total ph when creating ph
		$new_total_ph = $data['total_ph'];
		// should be -120
		if (($prev_total_ph - 0) != $new_total_ph) {
			print "new total does not match<br>\n";
			print_r($data);
			exit(0);
			
		}
		//print_r($data);

		print "done cancel_ph<br>\n";

		return true;
	}

	function test_cancel_gh_2() {
		print "start cancel_ph<br>\n";

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

		// create ph for 120
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 120;
		$data['amount_available'] = 120;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id = $db_phr_model->get_last_insert_id();

		$data = Array();

		// from here cancel ph
		GHRequest::cancel_gh($gh_id, $from_user_id);


		$number_affected = $db_phr_model->db->affected_rows();
		if($number_affected != 2) {
			print "number affected should be 2 is $number_affected\n";
			
			exit(0);
		}

		$data = $db_model->get($from_user_id);

		// total ph is scewed here since we don't add to total ph when creating ph
		$new_total_ph = $data['total_ph'];
		// should be -120
		if (($prev_total_ph - 120) != $new_total_ph) {
			print "new total does not match<br>\n";
			print_r($data);
			exit(0);
			
		}
		//print_r($data);

		print "done cancel_ph<br>\n";

		return true;
	}

	function test_cancel_gh_3() {
		print "start cancel_gh<br>\n";

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

		// to gh 250
		$data = Array();
		$data['user_id'] = $to_user_id;
		$data['amount'] = 250;
		$data['amount_available'] = 250;
		$number_affected = $db_ghr_model->save(0, $data);
		$gh_id = $db_ghr_model->get_last_insert_id();


		// create ph for 60
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 60;
		$data['amount_available'] = 60;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_1 = $db_phr_model->get_last_insert_id();

		// create ph for 80
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 80;
		$data['amount_available'] = 80;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_2 = $db_phr_model->get_last_insert_id();

		// create ph for 20
		// we should really add to ph_total
		$data = Array();
		$data['user_id'] = $from_user_id;
		$data['amount'] = 20;
		$data['amount_available'] = 20;
		$data['is_pending_create'] = "N";
		$number_affected = $db_phr_model->save(0, $data);
		$ph_id_3 = $db_phr_model->get_last_insert_id();


		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_1 = $db_mr_model->get_last_insert_id();

		if ($mr_id_1 > 0) {
			$amount = 60;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_1, $mr_id_1, $amount);
			//print "number affected is $number_affected<br>\n";
		}

		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_unconfirmed'] != 60) {
			print "unconfirmed ph should be 60<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph + 60) != $new_unconfirmed_ph) {
			print "unconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;

		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_2 = $db_mr_model->get_last_insert_id();
		if ($mr_id_2 > 0) {
			$amount = 40;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_2, $mr_id_2, $amount);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph + 40) != $new_unconfirmed_ph) {
			print "aunconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;

		$data = Array();
		$data['phrequest_user_id'] = $from_user_id;
		$data['ghrequest_user_id'] = $to_user_id;
		$db_mr_model->save(0, $data);
		$mr_id_3 = $db_mr_model->get_last_insert_id();
		if ($mr_id_3 > 0) {
			$amount = 20;
			$number_affected = $db_mr_model->match_gh_id_to_ph_id($gh_id, $ph_id_3, $mr_id_3, $amount);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph + 20) != $new_unconfirmed_ph) {
			print "aunconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;



		// amount available == 0
		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_matched'] != 60) {
			print "amount matched should be 60<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_2);
		if ($data['amount_matched'] != 40) {
			print "amount matched should be 40<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_ghr_model->get($gh_id);
		//print_r($data);
		if ($data['amount_matched'] != 120) {
			print "amount matched should be 120<br>\n";
			print_r($data);
			exit(0);
		}

		// from here cancel gh
		GHRequest::cancel_gh($gh_id, $from_user_id);


		$number_affected = $db_ghr_model->db->affected_rows();
		if($number_affected != 8) {
			print "number affected should be 8 is $number_affected\n";
			
			exit(0);
		}

		$data = $db_model->get($from_user_id);
		$new_unconfirmed_ph = $data['unconfirmed_ph'];
		if (($prev_unconfirmed_ph - 120) != $new_unconfirmed_ph) {
			print "after cancel gh unconfirmed ph do not match<br>\n";
			print_r($data);
			$data = $db_model->get($to_user_id);
			print_r($data);
			exit(0);
		}
		$prev_unconfirmed_ph = $new_unconfirmed_ph;

		$data = $db_phr_model->get($ph_id_1);
		if ($data['amount_available'] != 60) {
			print "amount available should be 60<br>\n";
			print_r($data);
			exit(0);
		}
		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_2);
		if ($data['amount_available'] != 80) {
			print "amount available should be 80<br>\n";
			print_r($data);
			exit(0);
		}

		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_phr_model->get($ph_id_3);
		if ($data['amount_available'] != 20) {
			print "amount available should be 20<br>\n";
			print_r($data);
			exit(0);
		}

		if ($data['amount_matched'] != 0) {
			print "amount matched should be 0<br>\n";
			print_r($data);
			exit(0);
		}

		$data = $db_model->get($from_user_id);

		// total ph is scewed here since we don't add to total ph when creating ph
		$new_total_ph = $data['total_ph'];
		// should be -120
		if (($prev_total_ph - 0) != $new_total_ph) {
			print "new total does not match<br>\n";
			print_r($data);
			exit(0);
			
		}
		//print_r($data);

		print "done cancel_ph<br>\n";

		return true;
	}

}
?>
