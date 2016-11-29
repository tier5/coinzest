<? 

class LoginAuth {


	function authenticate($login, $password) {
		//$this->load->database();
		$bcrypt = new Bcrypt(15);
		$db_model = new DB_Users();
		$arr_criteria = Array();
		$arr_criteria['login'] = $login;
		$arr_data = $db_model->search($arr_criteria);
		if (count($arr_data) > 0) {

			$hash = $arr_data[0]['password'];
			$is_good = $bcrypt->verify($password, $hash);
			if (($arr_data[0]['status_id'] == DB_Users::$ACTIVE_STATUS_ID || $arr_data[0]['status_id'] == DB_Users::$SUSPENDED_STATUS_ID)) {
				if ($is_good) {
					return true;
				}
			}
		}
		return false;
	}


	// check user id
	function is_user_have_access($is_admin_required = false) {
		if (Library_Auth_Common::$IS_AUTH_ENABLED) {
			session_start();
			if ($_SESSION['is_authenticated']) {
				$user_id = $_SESSION['user_id'];
				//print "user_id is $user_id\n";
				session_write_close();
				$db_model = new DB_Users();
				$arr_data = $db_model->get($user_id);
				if (count($arr_data) > 0 && ($arr_data['status_id'] == DB_Users::$ACTIVE_STATUS_ID || $arr_data['status_id'] == DB_Users::$SUSPENDED_STATUS_ID)) {
					$is_admin = $arr_data['is_admin'];
					//print_r($arr_data);
					//print "is admin is $is_admin\n";
					if ($is_admin_required && $is_admin == 'Y') {
						return true;
					}
				}
				
			}
			return false;
		} else {
			return true;
		}
		return false;
	}

	// if have admin access
	function is_user_have_manager_access() {
		if (Library_Auth_Common::$IS_AUTH_ENABLED) {
			session_start();
			if ($_SESSION['is_authenticated']) {
				$user_id = $_SESSION['user_id'];
				//print "user_id is $user_id\n";
				session_write_close();
				$db_model = new DB_Users();
				$arr_data = $db_model->get($user_id);
				if (count($arr_data) > 0 && ($arr_data['status_id'] == DB_Users::$ACTIVE_STATUS_ID || $arr_data['status_id'] == DB_Users::$SUSPENDED_STATUS_ID)) {
					$is_manager = $arr_data['is_manager'];
					//print_r($arr_data);
					//print "is admin is $is_admin\n";
					if ($is_manager == 'Y') {
						return true;
					}
				}
				
			}
			return false;
		} else {
			return true;
		}
		return false;
	}

	// if have admin access
	function is_user_have_admin_access() {
		return self::is_user_have_access(true);
	}

	function get_session_user_id() {
		return $_SESSION['user_id'];
	}

	function check_if_need_to_go_through_first_time_registration() {
		$db_model = new DB_Users();
		$user_id = self::get_session_user_id();
		$data = $db_model->get($user_id);
		// update user so that we have last_request_gm_date_time set to now
		$db_model->update_user_last_request($user_id);

		if (count($data) > 0) {
			if ($data['is_done_registration_process'] == 'N') {
				// go to registration process
				print BackEnd_Registration_View::build_html();
				exit(0);
			}
		}
	}
		
}?>
