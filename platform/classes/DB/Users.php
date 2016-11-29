<?

class DB_Users extends CI_Model {

	static $TABLE_NAME = "wp_users";

	static $ACTIVE_STATUS_ID = 1;
	static $DELETED_STATUS_ID = 2;

	static $BLOCKED_STATUS_ID = 3; /* cannot login */
	static $SUSPENDED_STATUS_ID = 4; /*  can login but can't create gh */




	static $NO_ACTIVITY_IN_30_DAYS_REASON_TYPE_ID = 30; /*  can login but can't create gh */

	static $NO_RECEIPT_REASON_TYPE_ID = 40;


	static $ADMIN_BLOCK_REASON_TYPE_ID = 50;

	static $ADMIN_SUSPENDED_REASON_TYPE_ID = 51;

	static $ADMIN_CHANGE_REASON_TYPE_ID = 52;


	static $ADMIN_CANCELLED_REQUEST_REASON_TYPE_ID = 60;


	// maybe should that
	// status reason type id



	public function __construct() {
	}


	function get($id) {
		$this->load->database();
		$field_names = self::get_field_names();
		$sql = "SELECT $field_names FROM ".self::$TABLE_NAME." where id = ".$this->db->escape($id)." limit 1";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return $row;
	}


	function arr_get_field_names() {
		$arr[] = "id";
		$arr[] = "login";
		$arr[] = "first_name";
		$arr[] = "last_name";
		$arr[] = "email";
		$arr[] = "created";
		$arr[] = "modified";
		$arr[] = "status_id";
		$arr[] = "is_admin";
		$arr[] = "otp";
		$arr[] = "country";
		$arr[] = "referral_id";
		$arr[] = "fullname";
		$arr[] = "btc_address";
		$arr[] = "gm_created";
		$arr[] = "gm_modified";
		$arr[] = "otp_expire_gm_date_time";
		// for otp verification 
		$arr[] = "is_verified";
		$arr[] = "password";
		$arr[] = "is_manager";
		//not using anymore
		$arr[] = "gh_balance";
		$arr[] = "last_ph_amount";
		$arr[] = "ph_completed_level";
		$arr[] = "phone";
		// is registration complete
		$arr[] = "is_done_registration_process";
		$arr[] = "is_have_photo_id";
		$arr[] = "complete_street_address";
		$arr[] = "date_of_birth";
		$arr[] = "photo_id_number";
		$arr[] = "merged_unique_id";
		$arr[] = "confirmed_ph";
		$arr[] = "unconfirmed_ph";
		// total ph
		$arr[] = "total_ph";
		// is set to N when uploading via csv import
		$arr[] = "is_activated";
		$arr[] = "last_bonus_game_gm_date_time";
		$arr[] = "bonus_game_amount";
		$arr[] = "daily_bonus_earning_balance";
		$arr[] = "daily_growth_balance";
		$arr[] = "task_earning_balance";
		$arr[] = "level_income_balance";
		$arr[] = "last_request_gm_date_time";
		$arr[] = "status_reason_type_id";
		$arr[] = "status_changed_by_user_id";
		// has to be approved for their stuff to match in the system
		$arr[] = "is_approved_for_matching";
		$arr[] = "last_ph_request_gm_date_time";
		$arr[] = "expiration_before_new_ph_gm_date_time";
		$arr[] = "status_reason_change_text";
		$arr[] = "is_have_government_id";
		$arr[] = "last_ph_completed_gm_date_time";
		$arr[] = "reset_password";
		$arr[] = "is_password_reset_enabled";
		$arr[] = "password_reset_expiration_gm_date_time";
		$arr[] = "is_old_data_verified";
		$arr[] = "is_have_old_data";
		$arr[] = "is_registration_reviewed";
		$arr[] = "is_allow_gh_to_match_restricted_countries";
		$arr[] = "is_test_match_account";
		$arr[] = "is_view_old_data_form";
		// ALTER TABLE  `wp_users` ADD  `is_allow_ph_to_match_restricted_countries` ENUM(  'Y',  'N' ) NOT NULL DEFAULT  'N';
		$arr[] = "is_allow_ph_to_match_restricted_countries";
		$arr[] = "is_have_additional_btc_addresses";
		return $arr;
	}

	
	function get_field_names($table_name = "") {
		$arr = self::arr_get_field_names();
		$value = "";

		$is_alt_table_name = false;
		if ($table_name == "") {
			$table_name = self::$TABLE_NAME;
		} else {
			$is_alt_table_name = true;
		}
		for($n=0;$n<count($arr);$n++) {
			if ($is_alt_table_name) {
				$value .= $table_name.".".$arr[$n]." as ".$table_name."_".$arr[$n].", ";
			} else {
				$value .= $table_name.".".$arr[$n].", ";
			}
		}
		$value = rtrim($value, ", ");
		return $value;
	}


	/*
	function get_field_names() {
		$value = "".self::$TABLE_NAME.".id, ".self::$TABLE_NAME.".login, ".self::$TABLE_NAME.".first_name, ".self::$TABLE_NAME.".last_name, ".self::$TABLE_NAME.".email, ".self::$TABLE_NAME.".password, ".self::$TABLE_NAME.".created, ".self::$TABLE_NAME.".modified, ".self::$TABLE_NAME.".status_id, ".self::$TABLE_NAME.".is_admin, ".self::$TABLE_NAME.".otp, ".self::$TABLE_NAME.".country, ".self::$TABLE_NAME.".referral_id, ".self::$TABLE_NAME.".fullname, ".self::$TABLE_NAME.".btc_address, ".self::$TABLE_NAME.".gm_created, ".self::$TABLE_NAME.".gm_modified, ".self::$TABLE_NAME.".phone, ".self::$TABLE_NAME.".otp_expire_gm_date_time, ".self::$TABLE_NAME.".is_verified";
		return $value;
	}
	*/

	function search($arr_criteria) {
		$this->load->database();
		$sql_where = self::get_sql_where($arr_criteria);
		//print_r($arr_criteria);
		//print "sql where is $sql_where<br>\n";

		if ($order_by == "") {
                        $order_by = "order by login  asc";
                }
		if (isset($arr_criteria['order_by']) && isset($arr_criteria['order_by_direction'])) {
			$order_by = "order by ". $arr_criteria['order_by']." ".$arr_criteria['order_by_direction'];
		}

		$limit = "";
		if (isset($arr_criteria['limit'])) {
			$limit = "limit ".$this->db->escape($arr_criteria['limit'])."";
		}
		if (isset($arr_criteria['number_results_per_page'])) {
			$limit = "limit ".$this->db->escape($arr_criteria['number_results_per_page'])."";
		}
		//print "sql where is $sql_where\n";


		if ($sql_where == "") {
		} else {
			$sql_where .= " and ";
		}

		$sql_where .= " ".self::$TABLE_NAME.".status_id <> ".self::$DELETED_STATUS_ID."";

		if ($sql_where != "") {
			$sql_where = " where $sql_where";
		}


		$field_names = self::get_field_names();
		$sql = "select $field_names from ".self::$TABLE_NAME."  $sql_where $order_by $limit";
		//print "sql is $sql\n";
		// where name like ".$this->db->escape($data['company_name'])." LIMIT 1";
		//print "sql is $sql\n";
		//exit(0);
		$query = $this->db->query($sql);
		$row = $query->row_array();
		//$row_id = $row['total'];
		//print_r($row);
		$arr_data = Array();
		foreach ($query->result_array() as $row) {
			$arr_data[] = $row;
		}

		//print_r($arr_data);

		return $arr_data;
		//exit(0);
		
	}


	function get_sql_where($arr_criteria, $table_name = "") {
		$this->load->database();
		$sql_where = "";

		if ($table_name == "") {
			$table_name = self::$TABLE_NAME;
		}

		if (isset($arr_criteria['status_id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".status_id", $arr_criteria['status_id']);
		}

		if (isset($arr_criteria['login'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".login", $arr_criteria['login']);
		}

		if (isset($arr_criteria['is_done_registration_process'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_done_registration_process", $arr_criteria['is_done_registration_process']);
		}

		if (isset($arr_criteria['is_test_match_account'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_test_match_account", $arr_criteria['is_test_match_account']);
		}

		if (isset($arr_criteria['is_allow_ph_to_match_restricted_countries'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_allow_ph_to_match_restricted_countries", $arr_criteria['is_allow_ph_to_match_restricted_countries']);
		}

		if (isset($arr_criteria['is_registration_reviewed'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_registration_reviewed", $arr_criteria['is_registration_reviewed']);
		}
		//print_r($arr_criteria);
		//print "sql where is $sql_where<br>\n";

		if (isset($arr_criteria['is_activated'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_activated", $arr_criteria['is_activated']);
		}

		if (isset($arr_criteria['is_have_old_data'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_have_old_data", $arr_criteria['is_have_old_data']);
		}

		if (isset($arr_criteria['is_old_data_verified'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".is_old_data_verified", $arr_criteria['is_old_data_verified']);
		}

		if (isset($arr_criteria['id'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			//$sql_where .= " ".$table_name.".id = ".$this->db->escape($arr_criteria['id']);
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".id", $arr_criteria['id']);
		}


		if (isset($arr_criteria['search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".login like ".$this->db->escape("%".$arr_criteria['search']."%")."";
                }

		if (isset($arr_criteria['exact_search'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".login like ".$this->db->escape($arr_criteria['exact_search'])."";
                }


		if (isset($arr_criteria['otp'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".otp = ".$this->db->escape("%".$arr_criteria['search']."%")."";
                }


		if (isset($arr_criteria['country'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".country like ".$this->db->escape($arr_criteria['country'])."";
                }


		if (isset($arr_criteria['bonus_game_amount'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".bonus_game_amount", $arr_criteria['bonus_game_amount']);
		}

		if (isset($arr_criteria['last_request_gm_date_time'])) {
			if ($sql_where == "") {
			} else {
				$sql_where .= " and ";
			}
			$sql_where .= Library_DB_Util::build_comparison_sql($this->db, $table_name.".last_request_gm_date_time", $arr_criteria['last_request_gm_date_time']);
		}

		if (isset($arr_criteria['btc_address'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".btc_address like ".$this->db->escape($arr_criteria['btc_address'])."";
                }

		if (isset($arr_criteria['phone'])) {
                        if ($sql_where == "") {
                        } else {
                                $sql_where .= " and ";
                        }
                        $sql_where .= " ".$table_name.".phone like ".$this->db->escape($arr_criteria['phone'])."";
                }

		return $sql_where;
	}

	function get_drop_down_options($id) {
		$arr_options = $this->get_arr_values();
		//print_r($arr_options);
		$s = Library_Html_Util::build_drop_down_options($id, $arr_options);
		return $s;

	}

	/*
	// lock table to prevent duplicate btc address
	function update_btc_address($id, $btc_address) {
		$sql = "LOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);


		$arr_criteria = Array();
		$arr_criteria['btc_address'] = $btc_address;
		$arr_data = $this->search($arr_criteria);
		if (count($arr_data) == 0) {
			$data = Array();
			$data['btc_address'] = $btc_address;
			$this->save($id, $data);
		}

		$sql = "UNLOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);
	}
	*/

	/*
	// lock table to prevent duplicate phone number
	function update_phone_number($id, $phone_number) {
		
		$sql = "LOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);


		$arr_criteria = Array();
		$arr_criteria['phone'] = $phone_number;
		$arr_data = $this->search($arr_criteria);
		if (count($arr_data) == 0) {
			$data = Array();
			$data['phone'] = $phone_number;
			$this->save($id, $data);
		}

		$sql = "UNLOCK TABLE ".self::$TABLE_NAME."";
		$query = $this->db->query($sql);
	}
	*/

	function get_arr_values() {
		//print "blah\n";
		//print_r($this);
		//print_r($this->db);
		//exit(0);
		$sql = "SELECT id,login FROM ".self::$TABLE_NAME." order by login";
		//print "sql is $sql\n";
		$query = $this->db->query($sql);

		foreach ($query->result_array() as $row) {
			$option['key'] = $row['id'];
			$option['value'] = $row['zipcode'];
			$arr_options[] = $option;
		}
		return $arr_options;
	}


	function save($id, $data) {
		$this->load->database();

		/*
		if (isset($data['id') && $data['id'] != "" && $data['id'] > 0) {
			if ($id == 0) {
				$id = $data['id'];
			}
		}
		*/
		
		$data['modified'] = Library_DB_Util::time_to_db_time();
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
		//print_r($data);
		unset($data['id']);
		if ($id > 0) {
			$arr_sql_where = Array();
			$arr_sql_where['id'] = $id;
			unset($data['created']);
			unset($data['gm_created']);
			$this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
		} else {
			$data['created'] = Library_DB_Util::time_to_db_time();
			$data['gm_created'] = Library_DB_Util::time_to_gm_db_time();
			$this->db->insert(self::$TABLE_NAME, $data);
		}
		return $this->db->affected_rows();
	}

	function update_user_last_request($user_id) {
                $this->load->database();
		if ($user_id > 0) {
			$arr_sql_where = Array();
			$arr_sql_where['id'] = $user_id;
			$data = Array();
			$data['last_request_gm_date_time'] = Library_DB_Util::time_to_gm_db_time();
			$this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
		}
	}


	function get_count_active_registered_users() {
                $this->load->database();
		$arr_criteria = Array();
		$arr_criteria['status_id'][] = self::$ACTIVE_STATUS_ID;
		$arr_criteria['status_id'][] = self::$SUSPENDED_STATUS_ID;
		// just do a regular count i guess
		$sql_where = self::get_sql_where($arr_criteria);

		$sql = "select count(*) as amount from ".self::$TABLE_NAME." where $sql_where";
		//print "sql is $sql\n";
		//$query = $this->db->query($sql);
		//$res = $query->result();  // this returns an object of all results
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row) {
			if (is_array($row)) {
				//print_r($row);
				return $row['amount'];
			}
			
		}
		return 0;
	}

	function get_number_affected() {
                $this->load->database();
		return $this->db->affected_rows();
	}

	function get_last_insert_id() {
		$this->load->database();
		return $this->db->insert_id();
	}

	
	function update($data, $arr_criteria) {
                $this->load->database();

		$sql_where = self::get_sql_where($arr_criteria);
		
		$set_sql = "";
		$data['gm_modified'] = Library_DB_Util::time_to_gm_db_time();
		if (is_array($data) && count($data) > 0) {
			foreach($data as $key => $value) {
				$set_sql .= "$key = ".$this->db->escape($value).", ";
			}
			$set_sql = rtrim($set_sql, ", ");
			$set_sql = "set $set_sql";
		}

		if ($sql_where != "") {
			if ($sql_where != "") {
				$sql_where = "where $sql_where";
			}
			$table_name = self::$TABLE_NAME;

			$sql = "update $table_name $set_sql $sql_where";
			//print "sql is $sql\n";
			$query = $this->db->query($sql);
		} else {
			// dont do query if no criteria
			return 0;
		}
                return $this->db->affected_rows();
        }

	function get_last_query() {
                $this->load->database();
		return $this->db->last_query();
	}

	function delete($id) {
                $this->load->database();

                if ($id > 0) {
                        $arr_sql_where = Array();
                        $arr_sql_where['id'] = $id;
                        unset($data['created']);

                        $data = Array();
                        $data['status_id'] = self::$DELETED_STATUS_ID;
                        $this->db->update(self::$TABLE_NAME, $data, $arr_sql_where);
                }
                return $this->db->affected_rows();
        }


	// get the users manager recursive
	function _recur_get_user_manager($original_user_id, $user_id, $arr_seen_user_ids = null) {
		// loop through until we find a manager have an array of array just in case we are in a loop
		if (is_null($arr_seen_user_ids)) {
			$arr_seen_user_ids = Array();
		}
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				// check to see if manager
				if ($user_data['is_manager'] == "Y" && $user_id != $original_user_id) {
					// we don't want original user to be the admin
					return $user_id;
				} else {
					// go up one higher
					$referral_id = $user_data['referral_id'];
					if ($referral_id > 0) {
						//print "user id $user_id\n";
						//print_r($arr_seen_user_ids);
						if (!in_array($referral_id, $arr_seen_user_ids)) {
							$arr_seen_user_ids[] = $user_id;
							return $this->_recur_get_user_manager($original_user_id, $referral_id, $arr_seen_user_ids);
						} else {
							//print "we are in a loop\n";
							// we are in loop
							return 0;
						}
					}
					return 0;
				}
				return 0;
			}
			return 0;
		}
		return 0;
	}

	// need to ask if user already manager do we need to go higher?
	function arr_get_user_manager($user_id) {
		$arr_seen_user_ids = Array();
		
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				$referral_id = $user_data['referral_id'];
				if ($referral_id > 0) {
					// go higher
					$manager_user_id = $this->_recur_get_user_manager($user_id, $referral_id, $arr_seen_user_ids);
					//print "manager user id is $manager_user_id\n";
					//exit(0);
					if ($manager_user_id > 0) {
						$user_data = $this->get($manager_user_id);
						return $user_data;
					}
				}
			}
		}
		return Array();
	}




	// get the users manager recursive
	function _recur_get_all_user_managers($original_user_id, $user_id, $arr_seen_user_ids = null) {
		// loop through until we find a manager have an array of array just in case we are in a loop
		if (is_null($arr_seen_user_ids)) {
			$arr_seen_user_ids = Array();
		}
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				// check to see if manager
				if ($user_data['is_manager'] == "Y" && $user_id != $original_user_id) {
					// we don't want original user to be the admin
					$arr_manager_ids[] = $user_id;
				}
				// go up one higher
				$referral_id = $user_data['referral_id'];
				if ($referral_id > 0) {
					//print "user id $user_id\n";
					//print_r($arr_seen_user_ids);
					if (!in_array($referral_id, $arr_seen_user_ids)) {
						$arr_seen_user_ids[] = $user_id;
						$temp_arr_managers = $this->_recur_get_all_user_managers($original_user_id, $referral_id, $arr_seen_user_ids);
						$arr_manager_ids = array_merge($arr_manager_ids, $temp_arr_managers);
					} else {
						// we seen this person already stop here
					}
				}
			}
		}
		return $arr_manager_ids;
	}

	// grabs all higher user managers
	function arr_get_all_user_managers($user_id) {
		$arr_seen_user_ids = Array();
		
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				$referral_id = $user_data['referral_id'];
				if ($referral_id > 0) {
					// go higher
					$arr_manager_ids = $this->_recur_get_all_user_managers($user_id, $referral_id, $arr_seen_user_ids);
					//print "manager user id is $manager_user_id\n";
					//exit(0);
					return $arr_manager_ids;
				}
			}
		}
		return Array();
	}

	// get the users manager recursive
	function _recur_get_all_user_managers_and_level($original_user_id, $user_id, $level, $arr_seen_user_ids = null) {
		// loop through until we find a manager have an array of array just in case we are in a loop
		if (is_null($arr_seen_user_ids)) {
			$arr_seen_user_ids = Array();
		}
		//print "user id is $user_id<br>\n";
		$arr_managers_data = Array();
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				// check to see if manager
				if ($user_data['is_manager'] == "Y" && $user_id != $original_user_id) {
					// we don't want original user to be the admin
					$manager_data = Array();
					$manager_data['id'] = $user_id;
					$manager_data['level'] = $level;
					$arr_managers_data[] = $manager_data;
				}
				// go up one higher
				$referral_id = $user_data['referral_id'];
				if ($referral_id > 0) {
					//print "going into referral id $referral_id<br>\n";
					//print "user id $user_id\n";
					//print_r($arr_seen_user_ids);
					if (!in_array($referral_id, $arr_seen_user_ids)) {
						$arr_seen_user_ids[] = $user_id;
						$temp_arr_managers = $this->_recur_get_all_user_managers_and_level($original_user_id, $referral_id, $level+1, $arr_seen_user_ids);
						$arr_managers_data = array_merge($arr_managers_data, $temp_arr_managers);
						//print_r($arr_managers_data);
						//print_r($arr_managers_data);
					} else {
						//print "already seen $referral_id<br>\n";
						//print_r($arr_managers_data);
						// we seen this person already stop here
					}
				}
			}
		}
		return $arr_managers_data;
	}

	// grabs all higher user managers
	function arr_get_all_user_managers_and_level($user_id) {
		$arr_seen_user_ids = Array();
		
		if ($user_id > 0) {
			$user_data = $this->get($user_id);
			if (count($user_data) > 0) {
				$referral_id = $user_data['referral_id'];
				if ($referral_id > 0) {
					// go higher
					$arr_seen_user_ids[] = $user_id;
					$arr_managers_data = $this->_recur_get_all_user_managers_and_level($user_id, $referral_id, 1, $arr_seen_user_ids);
					//print "manager user id is $manager_user_id\n";
					//exit(0);
					//print "in ehre\n";
					//print_r($arr_managers_data);
					return $arr_managers_data;
				}
			}
		}
		return Array();
	}


	// filter out password otp codes
	function filter_sensitive_data($data) {
		unset($data['password']);
		unset($data['photo_id_number']);
		unset($data['is_done_registration_process']);
		unset($data['otp']);
		
		return $data;
	}

	// if ph level >= 2000 then we need to go back to the first level
	// if ph level within 2000 then we want to stay at current level
	// reset ph completed level if we have higheset
	function reset_ph_completed_level_if_max_level($id) {
	}

}
?>
