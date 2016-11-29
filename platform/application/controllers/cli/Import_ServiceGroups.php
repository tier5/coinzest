<?php
class Import_ServiceGroups extends CI_Controller {

	public function import() {
		$filename = "csv/service_groups.csv";
		if (is_file($filename)) {

			// set group_id

			// 1 is phase 1 invoices
			// 2 is 30 day
			// 3 is TX
			$invoice_group_id = 2;


			$list = file_get_contents($filename);
			$arr_list = preg_split("/\n/", $list);

			$arr_headers_data = preg_split("/\t/", $arr_list[0]);
			print_r($arr_headers_data);

			

			$arr_column_meta_data = Array();
			print_r($arr_headers_data);
			
			for($n=0;$n<count($arr_headers_data);$n++) {
				$arr_headers_data[$n] = trim($arr_headers_data[$n]);
				$arr_headers_data[$n] = trim($arr_headers_data[$n],"\"");
				if ($arr_headers_data[$n] == "servicegroup_name") {
					$arr_column_meta_data['name'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "segment_name") {
					$arr_column_meta_data['segment_name'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "total.spend") {
					$arr_column_meta_data['total_spend'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "total.client.days") {
					$arr_column_meta_data['total_client_days'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "spend.per.client.day") {
					$arr_column_meta_data['spend_per_client_day'] = $n;
					continue;
				}
			}
			//print_r($arr_column_meta_data);
			//exit(0);

			$this->load->database();
			for($n=1;$n<count($arr_list);$n++) {
				$data = Array();
				$is_do_insert = true;
				$is_do_update = false;

				$temp_data = preg_split("/\t/", $arr_list[$n]);
				for($r=0;$r<count($temp_data);$r++) {
					$temp_data[$r] = trim($temp_data[$r],"\"");
				}
				$arr_temp = $temp_data;
				$temp_data = Array();
				foreach ($arr_column_meta_data as $key => $value) {
					print "$key and $value\n";
					$temp_data[$key] = $arr_temp[$value];
				}
				$data = $temp_data;
				if ($data['name'] == "") {
					continue;
				}
				$segment_id = 0;

				// grab segment id
				$sql = "SELECT id,name FROM segments where name like '".$this->db->escape_like_str($data['segment_name'])."' LIMIT 1";
				//print "sql is $sql\n";
				$query = $this->db->query($sql);
				$row = $query->row_array();
				$row_id = $row['id'];
				if ($row_id > 0) {
					$data['segment_id'] = $row['id'];
					$segment_id = $row['id'];
					unset($data['segment_name']);
				} else {
					$temp_name = $data['segment_name'];
					$temp_name = preg_replace("/[ ]/",".", $temp_name);
					$temp_name = preg_replace("/[,]/",".", $temp_name);
					$sql = "SELECT id,name FROM segments where name like '".$this->db->escape_like_str($temp_name)."' LIMIT 1";
					$query = $this->db->query($sql);
					$row = $query->row_array();
					$row_id = $row['id'];
					if ($row_id > 0) {
						$data['segment_id'] = $row['id'];
						$segment_id = $row['id'];
						unset($data['segment_name']);
					} else {
						if ($temp_name == "Lawyer") {
						} else if ($temp_name == "Vacation.Rental.Company") {
						} else if ($temp_name == "Dentist") {
						} else if ($temp_name == "General.Contractor") {
						} else if ($temp_name == "Plumber") {
						} else if ($temp_name == "Roofer") {
						} else if ($temp_name == "HVAC.Contractor") {
						} else if ($temp_name == "Insurance") {
						} else if ($temp_name == "Auto.Repair.Shop") {
						} else if ($temp_name == "Courier,.Messenger.and.Delivery.Services") {
						} else if ($temp_name == "Banquet,.Conference,.and.Convention.Room") {
						} else {
						}
						if ($temp_name == "Vacation.Rental.Company") {
						} else if ($temp_name == "Rheumatologists") {
						} else if ($temp_name == "Watch.Repair") {
						} else if ($temp_name == "Beds.&.Mattresses") {
						} else if ($temp_name == "Standardized.Test.Prep") {
						} else if ($temp_name == "Summer.Camp") {
						} else if ($temp_name == "Life.Coach") {
						} else if ($temp_name == "Mass.Tort") {
						} else if ($temp_name == "Mobile.Auto.Glass.Repair") {
						} else if ($temp_name == "Sports.Injury.Doctor") {
						} else if ($temp_name == "Window.Tinting") {
						} else if ($temp_name == "Gas.Station") {
						} else if ($temp_name == "Motorcycles") {
						} else if ($temp_name == "Neurologists") {
						} else if ($temp_name == "Alternative.Medicine.I.DEPRECATED.11.11.08") {
						} else if ($temp_name == "Immunologists") {
						} else if ($temp_name == "Gastroenterologists") {
						} else if ($temp_name == "Marketing") {
						} else {
							print "could not find segment id\n";
							print "$sql is $sql\n";
							exit(0);
						}
					}
				}
				//print_r($temp_data);
				//exit(0);


				if (isset($arr_column_meta_data['name']) && $segment_id > 0) {
					$sql = "SELECT id,name FROM service_groups where name like '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
					//print "sql is $sql\n";
					$query = $this->db->query($sql);
					$row = $query->row_array();
					$row_id = $row['id'];
					if ($row_id > 0) {
					// if customer not found
					} else {
						$is_do_insert = false;
						$temp_data = Array();
						$temp_data['name'] = $data['name'];
						$temp_data['modified'] = Library_DB_Util::time_to_db_time();
						$temp_data['created'] = Library_DB_Util::time_to_db_time();
						$this->db->insert('service_groups', $temp_data);

						$sql = "SELECT id,name FROM service_groups where name like '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
						//print "sql is $sql\n";
						$query = $this->db->query($sql);
						$row = $query->row_array();
						$row_id = $row['id'];
						if ($row_id > 0) {
						} else {
							//print "company could not be found\n";
							//print_r($data);
							//exit(0);
						}
					}
					//$data['customer_id'] = $row_id;
					//echo $row['id'];
				}

				if ($is_do_insert) {
					$data['created'] = Library_DB_Util::time_to_db_time();
				}
				$data['modified'] = Library_DB_Util::time_to_db_time();

				print_r($data);
				//exit(0);

				if ($segment_id > 0) {
					$sql = "SELECT id FROM service_groups where name = '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
					print "sql is $sql\n";
					$query = $this->db->query($sql);
					$row = $query->row_array();
					// update if invoice found
					if (count($row) > 0) {
						$is_do_insert = false;
						$arr_sql_where = Array();
						$arr_sql_where['id'] = $row['id'];
						unset($data['created']);
						// if invoice already paid do not set to uncollected status
						$this->db->update('service_groups', $data, $arr_sql_where);
						//print_r($arr_sql_where);
						print_r($data);
					} else {
						if ($is_do_insert){
							$this->db->insert('service_groups', $data);
							print_r($data);
						}
					}
				}
				
			}
			//print_r($arr_list);
			
			/*
			$this->load->database();
			for($n=0;$n<count($arr__list);$n++) {
				$data = Array();
				$data['name'] = $arr_unique_list[$n];
				if ($arr_unique_list[$n] != "") {
					$this->db->insert('customers', $data);
				}
			}
			*/
		}
	}
}
?>
