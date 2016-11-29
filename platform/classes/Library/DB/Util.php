<?

class Library_DB_Util {

	function time_to_db_time($db, $value = null) {
                if ($value === null || $value === "") {
                        $value = time();
                }
                // grab the db and determine the proper format
                return date("Y-m-d H:i:s", $value);
        }
        function time_to_gm_db_time($db, $value = null) {
                if ($value === null || $value === "") {
                        $value = time();
                }
                // grab the db and determine the proper format
                return gmdate("Y-m-d H:i:s", $value);
        }

	// need to create time_to_gm_date
        function time_to_gm_db_date($db, $value = null) {
                if ($value === null || $value === "") {
                        $value = time();
                }
                // grab the db and determine the proper format
                return gmdate("Y-m-d", $value);
        }
	

	// much better
	// pass $arr_criteria[$field_name]
	// can pass array of ors
	// or can pass >= or <= type comparison
	function build_comparison_sql($db, $field_name, $criteria_data) {
		$sql_where = "";
		//print_r($criteria_data);
		if (!is_array($criteria_data)) {
			$sql_where .= " $field_name = ".$db->escape($criteria_data);
		} else if (is_array($criteria_data)) {
			// do a bunch of ors
			foreach($criteria_data as $key => $value) {
				//print "key is $key and value is $value\n";
				// skip these will be handled later
				
				if (is_numeric($key)) {
					//print "key is $key and value is $value\n";
					if ($sql_where != "") { $sql_where .= " or";
					} else {
						$sql_where .= " (";
					}
					$sql_where .= " $field_name = ".$db->escape($value);
				} else {
					if ($key == ">" || $key == "<" || $key == "<>" || $key == "=" || $key == "!=" || $key == ">=" || $key == "<=") {
						//print "in here\n";
					} else {
					}
				}
			}
			//print "sql where is $sql_where\n";
			if ($sql_where != "") {
				$sql_where .= ")";
			}
			
			if (
				(isset($criteria_data['>']) || isset($criteria_data['>='])) && 
				(isset($criteria_data['<']) || isset($criteria_data['<=']))
			) {
				if (isset($criteria_data['>'])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " ($field_name > ".$db->escape($criteria_data['>']);
				} else {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " ($field_name >= ".$db->escape($criteria_data['>=']);
				}
				if (isset($criteria_data['<'])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name < ".$db->escape($criteria_data['<']).")";
				} else {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name <= ".$db->escape($criteria_data['<=']).")";
				}
			} else {
				if (isset($criteria_data['>'])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name > ".$db->escape($criteria_data['>']);
				} else if (isset($criteria_data['<'])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name < ".$db->escape($criteria_data['<']);
				} else if (isset($criteria_data['>='])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name >= ".$db->escape($criteria_data['>=']);
				} else if (isset($criteria_data['<='])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name <= ".$db->escape($criteria_data['<=']);
				}
			}

			if (isset($criteria_data['='])) {
				if ($sql_where != "") { $sql_where .= " and"; }
				$sql_where .= " $field_name = ".$db->escape($criteria_data['>']);
			}
			if (isset($criteria_data['<>']) || isset($criteria_data['!='])) {
				if (isset($criteria_data['<>'])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name <> ".$db->escape($criteria_data['<>']);
				} else if (isset($criteria_data['!='])) {
					if ($sql_where != "") { $sql_where .= " and"; }
					$sql_where .= " $field_name <> ".$db->escape($criteria_data['!=']);
				}
			}
		}
		return $sql_where;
	}

	// pass $arr_criteria[$field_name]
	function build_number_comparison_sql($db, $field_name, $criteria_data) {
		$sql_where = "";
		if (!is_array($criteria_data)) {
			$sql_where .= " $field_name = ".$db->escape($criteria_data);
		} else if (is_array($criteria_data)) {
			if (
				(isset($criteria_data['>']) || isset($criteria_data['>='])) && 
				(isset($criteria_data['<']) || isset($criteria_data['<=']))
			) {
				if (isset($criteria_data['>'])) {
					$sql_where .= " ($field_name > ".$db->escape($criteria_data['>']);
				} else {
					$sql_where .= " ($field_name >= ".$db->escape($criteria_data['>=']);
				}
				if (isset($criteria_data['<'])) {
					$sql_where .= " and $field_name < ".$db->escape($criteria_data['<']).")";
				} else {
					$sql_where .= " and $field_name <= ".$db->escape($criteria_data['<=']).")";
				}
			} else if (isset($criteria_data['='])) {
				$sql_where .= " $field_name = ".$db->escape($criteria_data['>']);
			} else if (isset($criteria_data['<>']) || isset($criteria_data['!='])) {
				if (isset($criteria_data['<>'])) {
					$sql_where .= " $field_name <> ".$db->escape($criteria_data['<>']);
				} else if (isset($criteria_data['!='])) {
					$sql_where .= " $field_name <> ".$db->escape($criteria_data['!=']);
				}
			} else {
				if (isset($criteria_data['>'])) {
					$sql_where .= " $field_name > ".$db->escape($criteria_data['>']);
				} else if (isset($criteria_data['<'])) {
					$sql_where .= " $field_name < ".$db->escape($criteria_data['<']);
				} else if (isset($criteria_data['>='])) {
					$sql_where .= " $field_name >= ".$db->escape($criteria_data['>=']);
				} else if (isset($criteria_data['<='])) {
					$sql_where .= " $field_name <= ".$db->escape($criteria_data['<=']);
				}
			}
		}
		return $sql_where;
	}



}
?>
