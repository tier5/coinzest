<?

class BackEnd_Login_View {

	function __construct() { }



	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		

		/*
		$obj_login = new Login();

		$login = "dino";
		$password = "password1";
		if ($obj_login->authenticate($login, $password)) {
			print "success\n";
		}
	exit(0);
		*/
		$s = BackEnd_LoginHeader_View::build_html($data_object);
		return $s;
	}

	function build_site_maintenance_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/site_maintenance.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		

		$s = BackEnd_Template_View::build_no_header_html($data_object);
		return $s;
	}

	function build_no_access_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/no_access.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		

		/*
		$obj_login = new Login();

		$login = "dino";
		$password = "password1";
		if ($obj_login->authenticate($login, $password)) {
			print "success\n";
		}
	exit(0);
		*/
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_reset_otp_code_html($data_object) {

		$template_file = dirname(__FILE__)."/html/reset_otp_email.html";

		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$data_object->site_url = Registry::get("site_url");

		$s = Common::build_html($template_file, $data_object);
		return $s;
	}


	function build_otp_code_html($data_object) {

		$template_file = dirname(__FILE__)."/html/otp_email.html";

		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$data_object->site_url = Registry::get("site_url");

		$s = Common::build_html($template_file, $data_object);
		return $s;
	}

}
?>
