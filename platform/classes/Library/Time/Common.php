<?

class Library_Time_Common {

        function get_end_of_month_time($month, $year) {
                $temp_time = mktime(0,0,0, $month+1, 1, $year);
                return ($temp_time-1);
        }

        function get_start_of_month_time($month, $year) {
                $temp_time = mktime(0,0,0, $month, 1, $year);
                return $temp_time;
        }

        function get_start_of_this_week_time() {
		$this_month = date("m");
                $this_year = date("Y");
                $day = date("D");
                //print "day is $day\n";
		if ($day == "Sun") {
			$start_date = strtotime(date("m/d/Y"));
			//print "start date is $start_date<br>";
			//$end_date = (strtotime(date("m/d/Y"))+86399);
			//print "end date is $end_date<br>";
		} else {
			$start_date = (strtotime("last Sunday"));
			//print "start date is $start_date<br>";
			//$end_date = (strtotime("this Sunday")-1);
		}

		return $start_date;
	}

        function get_start_of_last_week_time() {
		$this_month = date("m");
                $this_year = date("Y");
                $day = date("D");
		if ($day == "Sun") {
			$start_date = (strtotime("last Sunday"));
			//$end_date = (strtotime("this Sunday")-1);
		} else {
			$start_date = (strtotime("last Sunday", strtotime("-1 week")));
			//$end_date = (strtotime("last sunday")-1);
		}
		return $start_date;
	}

        function get_start_of_next_week_time() {
		$this_month = date("m");
                $this_year = date("Y");
                $day = date("D");
		if ($day == "Sun") {
			$start_date = (strtotime("next sunday"));
			//$end_date = (strtotime("next sunday", strtotime("+1 week"))-1);
		} else {
			$start_date = (strtotime("This sunday"));
			//$end_date = (strtotime("This sunday", strtotime("+1 week"))-1);
		}
		return $start_date;
	}


	function get_end_of_this_week_time() {
		$current_time = time();
                $this_month = date("m", $current_time);
                $this_year = date("Y", $current_time);
                $day = date("D");

		if ($day == "Sun") {
			//$start_date = strtotime(date("m/d/Y"));
			//print "start date is $start_date<br>";
			//$end_date = (strtotime(date("m/d/Y"))+86399);
			$end_date = strtotime(date("m/d/Y", strtotime("+1 day", $current_time)))-1;
			//print "end date is $end_date<br>";
		} else {
			//$start_date = (strtotime("last Sunday"));
			//print "start date is $start_date<br>";
			//$end_date = (strtotime("this Sunday")-1);
			$end_date = (strtotime("this Sunday", $current_time)-1);
		}
		return $end_date;
	}

	function get_end_of_last_week_time() {
                $this_month = date("m");
                $this_year = date("Y");
                $day = date("D");
		if ($day == "Sun") {
			$end_date = (strtotime("this Sunday")-1);
		} else {
			$end_date = (strtotime("last sunday")-1);
		}
		return $end_date;
	}


}

?>
