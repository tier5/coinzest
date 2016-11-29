<?php

class ImportMembers extends CI_Model {

	public function import_csv($filename) {
		$result_object = new stdclass();
		$result_object->unable_to_find_proper_fields = false;
		$result_object->is_error = false;
		$result_object->number_records_processed = 0;
		$result_object->number_records_updated = 0;
		$result_object->number_records_inserted = 0;
		$result_object->number_records_skipped = 0;
		$result_object->number_sending_emails_skipped = 0;
		$result_object->number_emails_sent = 0;
		$import_summary_html = "";

		//$filename = "csv/segments.csv";
		if (is_file($filename)) {

			$list = file_get_contents($filename);
			$arr_list = preg_split("/\n/", $list);
			if($arr_list[count($arr_list)-1] == "") {
				unset($arr_list[count($arr_list)-1]);
			}

			$arr_headers_data = preg_split("/\t/", $arr_list[0]);
			//print_r($arr_headers_data);

			

			$arr_column_meta_data = Array();
			//print_r($arr_headers_data);
			
			for($n=0;$n<count($arr_headers_data);$n++) {
				$arr_headers_data[$n] = trim($arr_headers_data[$n]);
				$arr_headers_data[$n] = trim($arr_headers_data[$n],"\"");
				if ($arr_headers_data[$n] == "PH TOTAL") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "PH_TOTAL") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "ph_total") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "TOTAL PH") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "TOTAL_PH") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "total_ph") {
					$arr_column_meta_data['total_ph'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "COMMITMENT") {
					$arr_column_meta_data['last_ph_amount'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "add_daily_growth_frozen_on_ph_completed") {
					$arr_column_meta_data['add_daily_growth_frozen_on_ph_completed'] = $n;
					continue;
				}
				if ($arr_headers_data[$n] == "CONFIRMED PH") {
					$arr_column_meta_data['confirmed_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "CONFIRMED_PH") {
					$arr_column_meta_data['confirmed_ph'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "UNCONFIRMED PH") {
					$arr_column_meta_data['unconfirmed_ph'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "UNCONFIRMED_PH") {
					$arr_column_meta_data['unconfirmed_ph'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "fullname") {
					$arr_column_meta_data['fullname'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "FULLNAME") {
					$arr_column_meta_data['fullname'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "FULL NAME") {
					$arr_column_meta_data['fullname'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "full name") {
					$arr_column_meta_data['fullname'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "NAME") {
					$arr_column_meta_data['fullname'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "referral_id") {
					$arr_column_meta_data['referral_id'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "sponsor_id") {
					$arr_column_meta_data['referral_id'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "sponsor") {
					$arr_column_meta_data['sponsor'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "SPONSOR ID") {
					$arr_column_meta_data['sponsor'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "phone") {
					$arr_column_meta_data['phone'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "MOBILE") {
					$arr_column_meta_data['phone'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "login") {
					$arr_column_meta_data['login'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "USER_ID") {
					$arr_column_meta_data['login'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "USERNAME_ID") {
					$arr_column_meta_data['login'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "country") {
					$arr_column_meta_data['country'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "btc_address") {
					$arr_column_meta_data['btc_address'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "Send Email") {
					$arr_column_meta_data['send_email'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "send email") {
					$arr_column_meta_data['send_email'] = $n;
					continue;
				} else if ($arr_headers_data[$n] == "SendEmail") {
					$arr_column_meta_data['send_email'] = $n;
					continue;
				}
			}
			//print_r($arr_column_meta_data);

			if (isset($arr_column_meta_data['login']) && 
				isset($arr_column_meta_data['fullname']) &&
				isset($arr_column_meta_data['sponsor']) &&
				isset($arr_column_meta_data['country'])
			) { 
			} else if (isset($arr_column_meta_data['login']) && 
				isset($arr_column_meta_data['add_daily_growth_frozen_on_ph_completed'])
			) { 
			} else {
				$result_object->unable_to_find_proper_fields = true;
				$result_object->is_error = true;
				return $result_object;
			}

			//print "got here\n";
			//print_r($arr_column_meta_data);
			//exit(0);

			//print_r($arr_list);
			$this->load->database();
			for($n=1;$n<count($arr_list);$n++) {
				$data = Array();
				$is_do_insert = true;
				$is_do_update = false;

				$temp_data = preg_split("/\t/", $arr_list[$n]);
				//print_r($temp_data);
				for($r=0;$r<count($temp_data);$r++) {
					$temp_data[$r] = trim($temp_data[$r],"\"");
					$temp_data[$r] = trim($temp_data[$r],"\r\n");
					$temp_data[$r] = trim($temp_data[$r],"\r");
					$temp_data[$r] = trim($temp_data[$r],"\n");
					$temp_data[$r] = trim($temp_data[$r]);
				}
				$arr_temp = $temp_data;
				$temp_data = Array();
				foreach ($arr_column_meta_data as $key => $value) {
					//print "$key and $value\n";
					$temp_data[$key] = $arr_temp[$value];
					if ($temp_data[$key] == "") {
						unset($temp_data[$key]);
					}
				}

				//print_r($temp_data);

				if ($temp_data['country'] != "") {
					if (ctype_upper(preg_replace("/[\. ]/","",$temp_data['country']))) {
						$temp_data['country'] = ucwords(strtolower($temp_data['country']));
					}

					if (ctype_lower(preg_replace("/[\. ]/","",$temp_data['country']))) {
						$temp_data['country'] = ucwords(strtolower($temp_data['country']));
					}

					//$temp_data['country'] = ucwords($temp_data['country']);
					if ($temp_data['country'] == "Hong Kong" || $temp_data['country'] == "Hongkong") {
						$temp_data['country'] = "Hong Kong S.A.R.";
					}
					$temp_data['country'] = trim($temp_data['country']);
				}

				if ($temp_data['phone'] != "") {
					$temp_data['phone'] = User::transform_phone_number("",$temp_data['phone']);
				}



				$data = $temp_data;
				if ($data['login'] == "") {
					//print "skipping here\n";
					$result_object->number_records_skipped++;
					$result_object->number_records_processed++;
					$import_summary_html .= "Detected login blank. skipping record.<br>";
					continue;
				}



				//print_r($temp_data);
				//exit(0);
				//$data['total_ph'] = preg_replace("/\$/", "", $data['total_ph']);
				$data['total_ph'] = preg_replace("/\\$/", "", $data['total_ph']);
				$data['total_ph'] = preg_replace("/,/", "", $data['total_ph']);

				//print_r($data);

				$data['referral_id'] = 0;
				// grab sponsor
				if (isset($data['sponsor']) && $data['sponsor'] != "") {
					$sql = "SELECT id,login FROM ".DB_Users::$TABLE_NAME." where ".DB_Users::$TABLE_NAME.".login like '".$this->db->escape_like_str($data['sponsor'])."' LIMIT 1";
					//$sql = "SELECT id,login FROM ".DB_Users::$TABLE_NAME." where ".DB_Users::$TABLE_NAME.".login like '".$this->db->escape($data['sponsor'])."' LIMIT 1";
					PlatformLogs::log("csv_import", "sql for searching sponsor is: ".$sql."\n");
					//print "sql is $sql\n";
					$query = $this->db->query($sql);
					$row = $query->row_array();
					$row_id = $row['id'];
					if ($row_id > 0) {
						$data['referral_id'] = $row['id'];
					} else {
					}
				}
				if (isset($data['sponsor'])) {
					unset($data['sponsor']);
				}


				if ($data['referral_id'] == 0) {
					unset($data['referral_id']);
				}


				$data['gm_created'] = Library_DB_Util::time_to_gm_db_time();
				$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();

				//print_r($data);
				//exit(0);
				$db_model = new DB_Users();

				$sql = "SELECT id FROM ".DB_Users::$TABLE_NAME." where login = ".$this->db->escape($data['login'])." LIMIT 1";
				PlatformLogs::log("csv_import", "sql is: ".$sql."\n");
				//print "sql is $sql\n";
				$query = $this->db->query($sql);
				$row = $query->row_array();
				PlatformLogs::log("csv_import", "row array is\n");
				PlatformLogs::log("csv_import", $row);

				//print "send email is '".$data['send_email']."'\n";
				// only try to send email if this is set to Y
				$is_send_email = false;
				if (isset($data['send_email'])) {
					if ($data['send_email'] == "Y" || $data['send_email'] == "y") {
						//print "send email set to Y\n";
						$is_send_email = true;
						PlatformLogs::log("csv_import", "send email set to y fo: ".$data['login']."\n");
					}
				}
	
				$is_skip_send_email = false;
				$user_id = 0;

				if (count($row) > 0) {
					$user_id = $row['id'];
		
					if (isset($data['send_email'])) {
						if ($data['send_email'] == "Y" || $data['send_email'] == "y") {
							$temp_data = $db_model->get($user_id);
							if ($temp_data['is_done_registration_process'] == "Y" || $temp_data['is_verified'] == "Y") {
								$is_skip_send_email = true;
								$is_send_email = false;
								$result_object->number_sending_emails_skipped++;
								$import_summary_html .= "Not Allowed To Send Email To ".$data['login'].". Account has already signed up.<br>";
								PlatformLogs::log("csv_import", "email skipped: ".$data['login']."\n");
							}
						}
					}
				}

				$is_wallet_adj_only = false;
				// 
				if (isset($data['add_daily_growth_frozen_on_ph_completed']) && $data['add_daily_growth_frozen_on_ph_completed'] != "") {
					if (is_numeric($data['add_daily_growth_frozen_on_ph_completed']) && $data['add_daily_growth_frozen_on_ph_completed'] > 0 && $user_id > 0) {
						$additional_params = Array();
						$adjust_reason = "Balance Approved and Imported From Previous System";
						$adjust_user_id = $user_id;
;
						$wallet_name = "daily_growth";
						$additional_params['is_release_part_of_funds_on_ph_complete'] = true;
						$additional_params['is_available'] = false;

						$amount = $data['add_daily_growth_frozen_on_ph_completed'];
	
						//print "amount is $amount\n";
						//print_r($data);
						//print "wallet type id is $wallet_type_id\n";
						//print "wallet type id is $wallet_type_id and adjust user id is $adjust_user_id\n";
						
						$obj_result = WalletHelper::adjust_wallet_by_name($wallet_name, $adjust_user_id, $amount,$adjust_reason,"","", $additional_params);
						$result_object->number_records_updated++;

						// add frozen
					} else {
						$result_object->number_records_skipped++;
						$import_summary_html .= "".$data['login']." skipped<br>";
					}
					unset($data['add_daily_growth_frozen_on_ph_completed']);
					$is_wallet_adj_only = true;
				}

				unset($data['send_email']);

				if (!$is_wallet_adj_only) {
				
					if ($is_send_email) {
						$password = rand(100000, 999999);
						$data['password'] = $password;
						$bcrypt = new Bcrypt(5);
						$data['password'] = $bcrypt->hash($data['password']);
						$data['otp'] = rand(1000,9999);
						$data['is_activated'] = "Y";
					}
					if (isset($data['phone']) && $data['phone'] == "") {
						unset($data['phone']);
					}

					//print "here\n";
					// update if found
					$is_skipped = false;
					if (count($row) > 0) {
						$is_do_insert = false;
						$arr_sql_where = Array();
						$arr_sql_where['id'] = $row['id'];
						$user_id = $row['id'];
						unset($data['gm_created']);
						// if invoice already paid do not set to uncollected status
						//$this->db->update(DB_Users::$TABLE_NAME, $data, $arr_sql_where);
						//print "end of update\n";
						$db_model->update($data, $arr_sql_where);

						PlatformLogs::log("csv_import", "update data is\n");
						PlatformLogs::log("csv_import", $data);
						$last_query = $db_model->get_last_query();
						PlatformLogs::log("csv_import", "last query is \n");
						PlatformLogs::log("csv_import", $last_query);


						//print_r($arr_sql_where);
						//print_r($data);
						$numbers_affected = $db_model->get_number_affected();
						if ($numbers_affected >= 1) {
							$result_object->number_records_updated++;
						} else if ($numbers_affected == 0) {
							$result_object->number_records_updated++;
						} else {
							
							// either duplicate phone or same record in twice
							//print "Number affected is $numbers_affected\n";
							// if 0 mean we have duplicate phone
							//print "duplicate phone\n";
							//print_r($data);
							$last_query = $db_model->get_last_query();
							$is_skipped = true;
							PlatformLogs::log("csv_import", "trying to update number affected is $numbers_affected record skipped: ".$data['login']."\n");
							PlatformLogs::log("csv_import", "last query is $last_query\n");
							$result_object->number_records_skipped++;
							$import_summary_html .= "".$data['login']." update skipped due to duplicate data in the system. (phone number field)<br>";
						}

					} else {
						//print "insert\n";
						$data['is_activated'] = "N";
						$data['is_verified'] = "N";
						//print_r($data);
						$db_model->save(0,$data);
						// if 0 then means that we have duplicate phone
						//print "got here\n";
						PlatformLogs::log("csv_import", "insert data is\n");
						PlatformLogs::log("csv_import", $data);
						$last_query = $db_model->get_last_query();
						PlatformLogs::log("csv_import", "last query is \n");
						PlatformLogs::log("csv_import", $last_query);

						$user_id = $db_model->get_last_insert_id();



						//print "user id is $user_id\n";
						//print_r($data);
						$numbers_affected = $db_model->get_number_affected();
						if ($numbers_affected >= 1) {
							$result_object->number_records_inserted++;
						} else {
							$is_skipped = true;
							//print "error inserting\n";
							//print_r($data);
							$last_query = $db_model->get_last_query();
							PlatformLogs::log("csv_import", "trying to insert number affected is $numbers_affected. record skipped: ".$data['login']."\n");
							PlatformLogs::log("csv_import", "last query is $last_query\n");
							$result_object->number_records_skipped++;
							$import_summary_html .= "".$data['login']." skipped due to duplicate data in the system (phone number field)<br>";
						}
					}

					if (!$is_skipped) {
						if ($is_send_email) {
							//print "sending email\n";
							//print "user id is $user_id\n";
							$is_dev_server = Registry::get("is_dev_server");
							if (!$is_dev_server) {
								Emailer::send_signup_email($user_id, $password);
							}
							$result_object->number_emails_sent++;
							PlatformLogs::log("csv_import", "email sent to: ".$data['login']."\n");
							$import_summary_html .= "Email sent to ".$data['login']."<br>";
						}
					}
				}
				$result_object->number_records_processed++;
			}
		}
		$is_dev_server = Registry::get("is_dev_server");
		$subject = "BIT MUTUAL HELP Import Summary";

		$site_url = Registry::get("site_url");
		$short_site_url = Registry::get("short_site_url");
		$from = "no-replay@$short_site_url";
			 
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		 
		// Create email headers
		$headers .= 'From: '.$from."\r\n".
		    'Reply-To: '.$from."\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		
		if ($is_dev_server) {
			$to = "dino.bartolome@gmail.com";
		} else {
			$to = "sdoron713@gmail.com, dino.bartolome@gmail.com";
		}
		$message = "<html><body><p>Import Summary</p>";
		$message .= "<p>";
		$message .= "Number Processed: ".$result_object->number_records_processed."<br>";
		$message .= "Number Inserted: ".$result_object->number_records_inserted."<br>";
		$message .= "Number Updated: ".$result_object->number_records_updated."<br>";
		$message .= "Number Skipped: ".$result_object->number_records_skipped."<br>";
		$message .= "Number Emails Sent: ".$result_object->number_emails_sent."<br>";
		$message .= "Number Emails Skipped: ".$result_object->number_sending_emails_skipped."<br>";
		
		$message .= "</p>";
		$message .= "$import_summary_html</body></html>";
		//print "html is $html\n";
		if (mail($to, $subject, $message, $headers, "-f$from")) {
		    //echo 'Your mail has been sent successfully.';
		} else {
		    //echo 'Unable to send email. Please try again.';
		}

		return $result_object;
	}
}
?>
