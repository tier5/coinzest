<?

class BackEnd_SendEmail_View {

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

	function build_general_email_template_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/email_template.html";

		$is_dev_server = Registry::get("is_dev_server");

		if ($is_dev_server) {
		} else {
		}
		$site_url = Registry::get("site_url");

		$t->site_url = $site_url;


		//print_r($t);

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = $data_object->contents_html;
		return $s;
	}
}
?>
