<?

class BackEnd_PHRequests_View {

	function __construct() { }



	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_list_all_ph_requests_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/admins_list.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_ph_request_created_email_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/phrequest_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}


}
?>
