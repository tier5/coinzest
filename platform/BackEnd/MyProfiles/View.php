<?

class BackEnd_MyProfiles_View {

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

	function build_new_btc_address_authorize_email_html($data_object) {

		$template_file = dirname(__FILE__)."/html/new_btc_address_authorize_email.html";

		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$data_object->site_url = Registry::get("site_url");

		$s = Common::build_html($template_file, $data_object);
		return $s;
	}

	function build_new_btc_address_authorize_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/new_btc_address_authorize_results.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		if (is_null($data_object)) {
			$data_object = new stdclass();
		} else {
			$t = $data_object;
		}
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_no_header_min_components_html($data_object);
		//$s = BackEnd_Template_View::build_no_header_html($data_object);
		return $s;
	}




}
?>
