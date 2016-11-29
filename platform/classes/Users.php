<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

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

	// do not use
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
}
?>
