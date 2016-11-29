<?

class BackEnd_GhHistory_View {

	function __construct() { }



	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

                $arr_option = Array();
                $arr_option['key'] = "0";
                $arr_option['value'] = "ALL";
                array_unshift($arr_options, $arr_option);
                $invoice_groups_drop_down_options = Library_Html_Util::build_drop_down_options($invoice_group_id, $arr_options);
                $t->invoice_group_drop_down_options =  $invoice_groups_drop_down_options;
		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		//$t->invoice_list_html = self::build_invoice_list_html($data_object);
		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}
}
?>
