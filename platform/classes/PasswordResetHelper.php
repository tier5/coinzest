<?

class PasswordResetHelper extends CI_Model {


	function send_password_reset_email($user_id, $reset_password) {

		
		$db_model = new DB_Users();


		$data = $db_model->get($user_id);

		//print "user id is $user_id\n";

		if (count($data) > 0) {


			$obj_result->is_success = true;
			$is_dev_server = Registry::get("is_dev_server");

			$site_url = Registry::get("site_url");
			$short_site_url = Registry::get("short_site_url");
			$from = "no-replay@$short_site_url";

			$login = $data['login'];

			$to = "$login";
			if ($is_dev_server) {
				$to = "dino.bartolome@gmail.com";
			} else {
			}

			$referral_id = $data['referral_id'];

			$subject = "BIT MUTUAL HELP PASSWORD RESET | ".$data['login'];
			 
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			$new_data = Array();
			
			 
			// Create email headers
			$headers .= 'From: '.$from."\r\n".
			    'Reply-To: '.$from."\r\n" .
			    'X-Mailer: PHP/' . phpversion();
			 
			$data_object = new stdclass();
			$data_object->phone = $data['phone'];
			$data_object->password = $reset_password;
			$data_object->login = $data['login'];
			$data_object->country = $data['country'];
			$data_object->fullname = $data['fullname'];
			$data_object->user_id = $data['id'];
			//print_r($data_object);


			$data_object->site_url = $site_url;
			$message = BackEnd_PasswordResets_View::build_password_reset_email_html($data_object);
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
