<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function transform_phone_number($prefix, $phone_number) {
		$temp_phone_number = preg_replace("/-/", "",$phone_number);
		$temp_phone_number = preg_replace("/\+/", "",$temp_phone_number);

		$prefix = preg_replace("/\+/", "",$prefix);
		$prefix = preg_replace("/-/", "",$prefix);

		if ($prefix == "" || $prefix == "1") {
		} else {
			$temp_phone_number = $prefix.$temp_phone_number;
		}

		return $temp_phone_number;
	}

	// use from now on
	public function check_phone_number_in_db($prefix, $phone_number, $login) {

		$temp_phone_number = self::transform_phone_number($prefix, $phone_number);


		$db_model = new DB_Users();
		$arr_criteria = Array();
		$arr_criteria['phone'] = $temp_phone_number;
		$arr_data = $db_model->search($arr_criteria);
		if (count($arr_data) > 0) {
			if ($login != "" && $login == $arr_data[0]['login']) {
				return false;
			}
			return true;
		}
		return false;
	}

	function is_user_have_admin_access($user_id) {
		if ($user_id > 0) {
			$db_model = new DB_Users();
			$arr_data = $db_model->get($user_id);
			if (count($arr_data) > 0 && ($arr_data['status_id'] == DB_Users::$ACTIVE_STATUS_ID || $arr_data['status_id'] == DB_Users::$SUSPENDED_STATUS_ID)) {
				$is_admin = $arr_data['is_admin'];
				//print_r($arr_data);
				//print "is admin is $is_admin\n";
				if ($is_admin == 'Y') {
					return true;
				}
			}
		}
		return false;
	}

}
?>
