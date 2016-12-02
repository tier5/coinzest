<?

class BackEnd_DailyTasks_View {

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
		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		//$t->invoice_list_html = self::build_invoice_list_html($data_object);

		$t->contents_html = file_get_contents(dirname(__FILE__)."/html/contents.html");

		if(Registry::get("is_dev_server") == true) {
			$t->contents_html .= BackEnd_DailyTasks_View::build_display_task_choices_html();
		}

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_need_approvals_user_daily_tasks_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/need_approvals_user_daily_tasks.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		//$t->invoice_list_html = self::build_invoice_list_html($data_object);

		//$t->contents_html = file_get_contents(dirname(__FILE__)."/html/contents.html");


		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);

		$s = BackEnd_Template_View::build_html($data_object);
		
		return $s;
	}

	function build_display_task_choices_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/display_task_choices_template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		//$t->invoice_list_html = self::build_invoice_list_html($data_object);

		//$t->contents_html = file_get_contents(dirname(__FILE__)."/html/contents.html");


		$data_object = new stdclass();
		$s = Common::build_html($template_file, $t);
		
		return $s;
	}

	function build_manage_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/manage_template.html";

		//$arr_options = InvoiceGroups::get_arr_values();

                $arr_option = Array();
                $arr_option['key'] = "0";
                $arr_option['value'] = "ALL";
                array_unshift($arr_options, $arr_option);
                $invoice_groups_drop_down_options = Library_Html_Util::build_drop_down_options($invoice_group_id, $arr_options);
		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		//$t->invoice_list_html = self::build_invoice_list_html($data_object);

		//$t->contents_html = file_get_contents(dirname(__FILE__)."/html/contents.html");


		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_edit_html($data_object = null) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/edit.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}
	function build_approve_clients_html() {
		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/show_pending_users_task.html";

		//$arr_options = InvoiceGroups::get_arr_values();

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

}
?>
