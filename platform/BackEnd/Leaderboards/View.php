<?

class BackEnd_Leaderboards_View {

	function __construct() { }



	function build_most_refs_weekly_leaderboard_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/most_refs_leaderboard.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_highest_completed_ph_weekly_leaderboard_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/highest_completed_ph_weekly.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}


}
?>
