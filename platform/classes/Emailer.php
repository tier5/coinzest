<?php

class Emailer {

	function send_completed_daily_maintenance() {
		$subject = "BIT MUTUAL HELP Daily Maintenance";

		$site_url = Registry::get("site_url");
		$short_site_url = Registry::get("short_site_url");
		$from = "no-reply@$short_site_url";
			 
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		 
		// Create email headers
		$headers .= 'From: '.$from."\r\n".
		    'Reply-To: '.$from."\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		

		$to = "dino.bartolome@gmail.com";
		$message = "<html><body>completed daily maintenance</body></html>";
		//print "html is $html\n";
		if (mail($to, $subject, $message, $headers, "-f$from")) {
		    //echo 'Your mail has been sent successfully.';
		} else {
		    //echo 'Unable to send email. Please try again.';
		}
	}

	function send_otp_code_email($to, $data_object) {
		$subject = "BIT MUTUAL HELP OTP Code";

		$site_url = Registry::get("site_url");
		$short_site_url = Registry::get("short_site_url");
		$from = "no-reply@$short_site_url";
			 
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		 
		// Create email headers
		$headers .= 'From: '.$from."\r\n".
		    'Reply-To: '.$from."\r\n" .
		    'X-Mailer: PHP/' . phpversion();

		$message = BackEnd_Login_View::build_otp_code_html($data_object);
		//print "html is $html\n";
		if (mail($to, $subject, $message, $headers, "-f$from")) {
		    //echo 'Your mail has been sent successfully.';
		} else {
		    //echo 'Unable to send email. Please try again.';
		}
	}

	function send_reset_otp_code_email($to, $data_object) {
		$subject = "BIT MUTUAL HELP Reset OTP Code";

		$site_url = Registry::get("site_url");
		$short_site_url = Registry::get("short_site_url");
		$from = "no-reply@$short_site_url";
			 
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		 
		// Create email headers
		$headers .= 'From: '.$from."\r\n".
		    'Reply-To: '.$from."\r\n" .
		    'X-Mailer: PHP/' . phpversion();

		$message = BackEnd_Login_View::build_reset_otp_code_html($data_object);
		//print "html is $html\n";
		if (mail($to, $subject, $message, $headers, "-f$from")) {
		    //echo 'Your mail has been sent successfully.';
		} else {
		    //echo 'Unable to send email. Please try again.';
		}
	}

	function send_ph_request_created_email($user_id, $ph_id) {
		$db_model = new DB_Users();
		$db_phr_model = new DB_PHRequests();
		$arr_phr_data = Array();

		if ($ph_id > 0) {
			$arr_phr_data = $db_phr_model->get($ph_id);
		}

		$arr_user_data = $db_model->get($user_id);

		$arr_temp_data = Array();
		$referral_id = 0;

		if (count($arr_user_data) > 0) {
			$referral_id = $arr_user_data['referral_id'];
		}

		if ($referral_id > 0) {
			$arr_temp_data = $db_model->get($referral_id);
		}


		if (count($arr_temp_data) > 0 && count($arr_user_data) > 0 && count($arr_phr_data) > 0) {
			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

			$subject = "BIT MUTUAL HELP | PH REQUEST CREATED FROM ". $arr_user_data['login'];


				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $arr_user_data['login'];
			$data_object->amount = $arr_phr_data['amount'];
			$data_object->phr_id = $ph_id;

			$data_object->fullname = $arr_user_data['fullname'];
			$data_object->is_sending_to_user_who_created_ph = true;
			$data_object->site_url = $site_url;

			// send to user
			$message = BackEnd_PHRequests_View::build_ph_request_created_email_html($data_object);
			//print "message is $message\n";
			$to = $arr_user_data['login'];
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			} else {
			}

			// send to sponsor
			$data_object->fullname = $arr_temp_data['fullname'];
			$data_object->is_sending_to_user_who_created_ph = false;
			$message = BackEnd_PHRequests_View::build_ph_request_created_email_html($data_object);
			$to = $arr_temp_data['login'];

			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				if (mail($to, $subject, $message, $headers, "-f$from")) {
				} else {
				}

				// need to send to all parent managers
				// need to check if this is actually working
				$arr_ids = $db_model->arr_get_all_user_managers($user_id);
				//print_r($arr_ids);
				for($n=0;$n<count($arr_ids);$n++) {
					if ($arr_ids[$n] > 0) {
						$arr_manager_data = $db_model->get($arr_ids[$n]);
						if (count($arr_manager_data) > 0) {
							$data_object->fullname = $arr_manager_data['fullname'];
							$message = BackEnd_PHRequests_View::build_ph_request_created_email_html($data_object);
							$to = $arr_manager_data['login'];
							if (mail($to, $subject, $message, $headers, "-f$from")) {
							} else {
							}
							
						}
					}
				}
			}
		}
	}

	function send_suspended_user_email($user_id) {
		$db_model = new DB_Users();
		$arr_phr_data = Array();

		$arr_user_data = $db_model->get($user_id);

		$arr_temp_data = Array();
		$referral_id = 0;

		if (count($arr_user_data) > 0) {
			$referral_id = $arr_user_data['referral_id'];
		}

		if ($referral_id > 0) {
			$arr_temp_data = $db_model->get($referral_id);
		}


		if (count($arr_user_data) > 0) {
			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

			$subject = "BIT MUTUAL HELP | YOUR ACCOUNT HAS BEEN SUSPENDED";


				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $arr_user_data['login'];
			$data_object->amount = $arr_phr_data['amount'];

			$data_object->fullname = $arr_user_data['fullname'];
			$data_object->site_url = $site_url;

			// send to user
			$message = BackEnd_Users_View::build_suspended_user_email_html($data_object);
			//print "message is $message\n";
			$to = $arr_user_data['login'];
			$is_dev_server = Registry::get("is_dev_server");
			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			}
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			} else {
			}
		}
	}

	function send_blocked_user_email($user_id) {
		$db_model = new DB_Users();
		$arr_phr_data = Array();

		$arr_user_data = $db_model->get($user_id);

		$arr_temp_data = Array();
		$referral_id = 0;

		if (count($arr_user_data) > 0) {
			$referral_id = $arr_user_data['referral_id'];
		}

		if ($referral_id > 0) {
			$arr_temp_data = $db_model->get($referral_id);
		}


		if (count($arr_user_data) > 0) {
			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

			$subject = "BIT MUTUAL HELP | YOUR ACCOUNT HAS BEEN BLOCKED";


				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $arr_user_data['login'];
			$data_object->amount = $arr_phr_data['amount'];
			$data_object->phr_id = $ph_id;

			$data_object->fullname = $arr_user_data['fullname'];
			$data_object->site_url = $site_url;

			// send to user
			$message = BackEnd_Users_View::build_blocked_user_email_html($data_object);
			//print "message is $message\n";
			$to = $arr_user_data['login'];
			$is_dev_server = Registry::get("is_dev_server");
			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			}
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			} else {
			}
		}
	}


	function send_gh_request_created_email($user_id, $gh_id) {
		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();
		$arr_ghr_data = Array();

		if ($gh_id > 0) {
			$arr_ghr_data = $db_ghr_model->get($gh_id);
		}

		$arr_user_data = $db_model->get($user_id);

		$arr_temp_data = Array();
		$referral_id = 0;

		if (count($arr_user_data) > 0) {
			$referral_id = $arr_user_data['referral_id'];
		}

		if ($referral_id > 0) {
			$arr_temp_data = $db_model->get($referral_id);
		}


		if (count($arr_temp_data) > 0 && count($arr_user_data) > 0 && count($arr_ghr_data) > 0) {
			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

			$subject = "BIT MUTUAL HELP | GH REQUEST CREATED FROM ". $arr_user_data['login'];


				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $arr_user_data['login'];
			$data_object->amount = $arr_ghr_data['amount'];
			$data_object->ghr_id = $gh_id;

			$data_object->fullname = $arr_user_data['fullname'];
			$data_object->site_url = $site_url;
			$data_object->person_text = "You have";

			// send only to user
			$message = BackEnd_GHRequests_View::build_gh_request_created_email_html($data_object);
			//print "message is $message\n";
			$to = $arr_user_data['login'];
			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				if (mail($to, $subject, $message, $headers, "-f$from")) {
				} else {
				}
			}

			/*
			// send to sponsor
			$data_object->fullname = $arr_temp_data['fullname'];
			$message = BackEnd_GHRequests_View::build_gh_request_created_email_html($data_object);
			$to = $arr_temp_data['login'];
			if (mail($to, $subject, $message, $headers)) {
			} else {
			}
			*/

			// need to send to all parent managers
			// need to check if this is actually working
			/*
			// disable for managers
			$arr_managers_data = $db_model->arr_get_all_user_managers_and_level($user_id);
			//print_r($arr_ids);
			for($n=0;$n<count($arr_managers_data);$n++) {
				$manager_id = $arr_managers_data[$n]['id'];
				if ($manager_id > 0) {
					$arr_manager_data = $db_model->get($manager_id);
					if (count($arr_manager_data) > 0) {
						$data_object->fullname = $arr_manager_data['fullname'];
						$data_object->person_text = $arr_manager_data['login']." has";
						$message = BackEnd_GHRequests_View::build_gh_request_created_email_html($data_object);
						$to = $arr_manager_data['login'];
						if (mail($to, $subject, $message, $headers, "-f$from")) {
						} else {
						}
						
					}
				}
			}
			*/
		}
	}


	function send_signup_email($user_id, $password) {

		
		$db_model = new DB_Users();


		$data = $db_model->get($user_id);

		//print "user id is $user_id\n";

		if (count($data) > 0) {

			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";
			$login = $data['login'];
	

			$to = "$login";
			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			} else {
			}

			$referral_id = $data['referral_id'];

			$subject = "BIT MUTUAL HELP NEW SIGNUP | ".$data['login'];
			 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			 
			$data_object = new stdclass();
			$data_object->phone = $data['phone'];
			$data_object->password = $password;
			$data_object->login = $data['login'];
			$data_object->country = $data['country'];
			$data_object->otp = $data['otp'];
			$data_object->fullname = $data['fullname'];
			$data_object->sponsor_email = "";
			if ($referral_id > 0) {
				$db_model = new DB_Users();
				$arr_temp_data = $db_model->get($referral_id);
				if (count($arr_temp_data) > 0) {
					$data_object->sponsor_email = $arr_temp_data['login'];
				}
			}


			$data_object->site_url = $site_url;
			$message = BackEnd_CreateMember_View::build_signup_html($data_object);

					 
			/*
			print "in here<br>\n";
			print "to is $to\n";
			print "headers is $headers\n";
			print "message is $message\n";
			*/
			//print "message is $message\n";
			// Sending email
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			    //echo 'Your mail has been sent successfully.';
			} else {
			    //echo 'Unable to send email. Please try again.';
			}
		}
	}

	function send_signup_to_sponsor_email($user_id) {

		
		$db_model = new DB_Users();


		$data = $db_model->get($user_id);


		if (count($data) > 0) {

			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");


			$to = "$login";
			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";
			if ($is_dev_server) {
			} else {
			}

			$referral_id = $data['referral_id'];

			$subject = "BIT MUTUAL HELP NEW SIGNUP | ".$data['login'];
			 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			 
			$data_object = new stdclass();
			$data_object->login = $data['login'];
			$data_object->country = $data['country'];
			$data_object->phone = $data['phone'];
			if ($referral_id > 0) {
				$arr_temp_data = $db_model->get($referral_id);
				if (count($arr_temp_data) > 0) {
					$to = $arr_temp_data['login'];
					$data_object->fullname = $arr_temp_data['fullname'];

					$data_object->site_url = $site_url;
					$message = BackEnd_CreateMember_View::build_signup_email_to_sponsor_html($data_object);
					 
					// Sending email
					if (mail($to, $subject, $message, $headers, "-f$from")) {
					    //echo 'Your mail has been sent successfully.';
					} else {
					    //echo 'Unable to send email. Please try again.';
					}
				}
			}
		}
	}

	function send_alert($message, $subject = "", $additional_email = "",  $str = "") {
		$subject = "ALERT from bitcoinbeast: $subject";


		if (is_array($str) || is_object($str)) {
			ob_start();
			print_r($str);
			$str = ob_get_contents();
			ob_end_clean();
		}

		$site_url = Registry::get("site_url");
		$short_site_url = Registry::get("short_site_url");
		$from = "no-reply@$short_site_url";
			 
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		 
		// Create email headers
		$headers .= 'From: '.$from."\r\n".
		    'Reply-To: '.$from."\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		

		$to = "dino.bartolome@gmail.com";
		$message = "<html><body>ALERT FROM BITCOIN BEAST:<br>$message.<br><br>Variable is: $str</body></html>";
		//print "html is $html\n";
		if (mail($to, $subject, $message, $headers, "-f$from")) {
		    //echo 'Your mail has been sent successfully.';
		} else {
		    //echo 'Unable to send email. Please try again.';
		}
	}

	// match created
	function send_matched_created_email($mr_id) {
		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		$db_mr_model = new DB_MatchedRequests();


		if ($mr_id > 0) {
			$mr_data = $db_mr_model->get($mr_id);
		}
	

		$gh_id = $mr_data['ghrequest_id'];
		$ph_id = $mr_data['phrequest_id'];

		if ($gh_id > 0) {
			$ghr_user_data = $db_model->get($mr_data['ghrequest_user_id']);
		}
		if ($ph_id > 0) {
			$phr_user_data = $db_model->get($mr_data['phrequest_user_id']);
		}



		if (count($mr_data) > 0 && count($ghr_user_data) > 0 && count($phr_user_data) > 0) {

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $phr_user_data['login'];
			$data_object->amount = $mr_data['amount'];
			$data_object->mr_id = $mr_id;

			$data_object->fullname = $phr_user_data['fullname'];
			$data_object->site_url = $site_url;
			$subject = "BIT MUTUAL HELP | MATCH CREATED TO ". $phr_user_data['login'];

			// send to ph
			$message = BackEnd_MatchedRequests_View::build_matched_request_ph_email_html($data_object);
			//print "message is $message\n";
			$to = $phr_user_data['login'];
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			} else {
			}

			$data_object = new stdclass();
			$data_object->login = $ghr_user_data['login'];
			$data_object->amount = $mr_data['amount'];
			$data_object->mr_id = $mr_id;

			$data_object->fullname = $ghr_user_data['fullname'];
			$data_object->site_url = $site_url;
			$subject = "BIT MUTUAL HELP | MATCH CREATED FROM ". $ghr_user_data['login'];

			// send to gh
			$message = BackEnd_MatchedRequests_View::build_matched_request_gh_email_html($data_object);
			$to = $ghr_user_data['login'];

			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				if (mail($to, $subject, $message, $headers, "-f$from")) {
				} else {
				}
			}
		}
	}

	// image receipt upload
	function build_image_receipt_upload_email_html($mr_id) {
		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		$db_mr_model = new DB_MatchedRequests();


		if ($mr_id > 0) {
			$mr_data = $db_mr_model->get($mr_id);
		}
	

		$gh_id = $mr_data['ghrequest_id'];
		$ph_id = $mr_data['phrequest_id'];

		if ($gh_id > 0) {
			$ghr_user_data = $db_model->get($mr_data['ghrequest_user_id']);
		}
		if ($ph_id > 0) {
			$phr_user_data = $db_model->get($mr_data['phrequest_user_id']);
		}



		if (count($mr_data) > 0 && count($ghr_user_data) > 0 && count($phr_user_data) > 0) {

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $ghr_user_data['login'];
			$data_object->amount = $mr_data['amount'];
			$data_object->mr_id = $mr_id;

			$data_object->fullname = $ghr_user_data['fullname'];
			$data_object->site_url = $site_url;
			$subject = "BIT MUTUAL HELP | IMAGE RECEIPT HAS BEEN UPLOADED FROM ". $phr_user_data['login'];

			// send to ph
			$message = BackEnd_MatchedRequests_View::build_image_receipt_upload_email_html($data_object);
			//print "message is $message\n";
			$to = $ghr_user_data['login'];
			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				if (mail($to, $subject, $message, $headers, "-f$from")) {
				} else {
				}
			}
		}
	}

	// image receipt upload
	function build_gh_confirm_email_html($mr_id) {
		$db_model = new DB_Users();
		$db_ghr_model = new DB_GHRequests();
		$db_phr_model = new DB_PHRequests();
		$db_mr_model = new DB_MatchedRequests();


		if ($mr_id > 0) {
			$mr_data = $db_mr_model->get($mr_id);
		}
	

		$gh_id = $mr_data['ghrequest_id'];
		$ph_id = $mr_data['phrequest_id'];

		if ($gh_id > 0) {
			$ghr_user_data = $db_model->get($mr_data['ghrequest_user_id']);
		}
		if ($ph_id > 0) {
			$phr_user_data = $db_model->get($mr_data['phrequest_user_id']);
		}



		if (count($mr_data) > 0 && count($ghr_user_data) > 0 && count($phr_user_data) > 0) {

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

				 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();

			$data_object = new stdclass();
			$data_object->login = $phr_user_data['login'];
			$data_object->amount = $mr_data['amount'];
			$data_object->mr_id = $mr_id;

			$data_object->fullname = $phr_user_data['fullname'];
			$data_object->site_url = $site_url;
			$subject = "BIT MUTUAL HELP | IMAGE RECEIPT HAS BEEN CONFIRM FROM". $ghr_user_data['login'];

			// send to ph
			$message = BackEnd_MatchedRequests_View::build_gh_confirm_email_html($data_object);
			//print "message is $message\n";
			$to = $phr_user_data['login'];
			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				if (mail($to, $subject, $message, $headers, "-f$from")) {
				} else {
				}
			}
		}
	}

	function send_new_btc_address_authorize_email($user_id, $code, $btc_address) {

		
		$db_model = new DB_Users();


		$data = $db_model->get($user_id);

		//print "user id is $user_id\n";

		if (count($data) > 0) {


			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-reply@$short_site_url";

			$login = $data['login'];

			$to = "$login";
			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			} else {
			}

			$referral_id = $data['referral_id'];

			$subject = "BIT MUTUAL HELP NEW BITCOIN ADDRESS REQUEST | ".$data['login'];
			 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			$new_data = Array();
			
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			 
			$data_object = new stdclass();
			$data_object->btc_address = $btc_address;
			$data_object->code = $code;
			$data_object->login = $data['login'];
			$data_object->fullname = $data['fullname'];
			$data_object->user_id = $data['id'];
			//print_r($data_object);


			$data_object->site_url = $site_url;
			$message = BackEnd_MyProfiles_View::build_new_btc_address_authorize_email_html($data_object);
			/*
			print "message is $message\n";

					 
			print "in here<br>\n";
			print "to is $to\n";
			print "headers is $headers\n";
			print "message is $message\n";
			*/
			//print "message is $message\n";
			// Sending email
			if (mail($to, $subject, $message, $headers, "-f$from")) {
			    //echo 'Your mail has been sent successfully.';
			} else {
			    //echo 'Unable to send email. Please try again.';
			}
		}
	}

}
?>
