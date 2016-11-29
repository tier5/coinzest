<?php
class Import_Segments extends CI_Controller {

	public function import() {
		$filename = "csv/segments.csv";
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
				if ($arr_headers_data[$n] == "segment_name") {
					$arr_column_meta_data['name'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "total_leads") {
					$arr_column_meta_data['total_leads'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "total_clicks") {
					$arr_column_meta_data['total_clicks'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "total_cost") {
					$arr_column_meta_data['total_cost'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "imps") {
					$arr_column_meta_data['impressions'] = $n;
					continue;
				}

				if ($arr_headers_data[$n] == "clicks") {
					$arr_column_meta_data['clicks'] = $n;
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
				//print_r($temp_data);
				//exit(0);


				if (isset($arr_column_meta_data['name'])) {
					$sql = "SELECT id,name FROM segments where name like '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
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
						$this->db->insert('segments', $temp_data);

						$sql = "SELECT id,name FROM segments where name like '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
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

				$sql = "SELECT id FROM segments where name = '".$this->db->escape_like_str($data['name'])."' LIMIT 1";
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
					$this->db->update('segments', $data, $arr_sql_where);
					//print_r($arr_sql_where);
					print "invoice found\n";
					print_r($data);
				} else {
					if ($is_do_insert){
						$this->db->insert('segments', $data);
						print_r($data);
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
