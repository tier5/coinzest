<?

class BackEnd_CreateMember_View {

	function __construct() { }



	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		//print "in here\n";
		$t->countries_drop_down_html = CountriesHelper::build_countries_drop_down_html();
		//print "in ehre".$t->countries_drop_down_html."\n";
		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_signup_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/signup_email.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}

	function build_signup_email_to_sponsor_html($data_object = null) {

		$template_file = dirname(__FILE__)."/html/signup_email_to_sponsor.html";

		//$arr_options = InvoiceGroups::get_arr_values();
		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$s = Common::build_html($template_file, $data_object);
		
		return $s;
	}


}
?>
