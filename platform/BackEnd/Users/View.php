<?

class BackEnd_Users_View {

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

	function build_list_html($data_object = null) {
		$this->load->database();

		$sql_where = "";
		if (isset($data_object)) {
			if (isset($data_object->action)) {
				if ($data_object->action == "search") {
					if ($sql_where == "") {
					} else {
						$sql_where .= " and ";
					}
					$sql_where .= " (";
					$search = $data_object->search;
					$sql_where .= " name like '%".$this->db->escape_like_str($search)."%'";

					$sql_where .= " )";
				}
				
			}
		}



		if ($sql_where != "") {
			$sql_where = "where $sql_where";
		}

		$template_file = dirname(__FILE__)."/html/list.html";
		$t = new stdclass();
		$data_object = new stdclass();

		$query = $this->db->query("SELECT id, segments.name, segments.total_leads, segments.total_clicks, segments.total_cost, segments.impressions, segments.clicks $sql_where order by name limit 1000");

		


		$t->is_header = true;
		$t->is_content = false;
		$t->is_footer = false;
		$s = Common::build_html($template_file, $t);

		$t->is_header = false;
		$t->is_content = true;
		$t->is_footer = false;
		$total = 0;
		$n = 0;
		foreach ($query->result_array() as $row) {
			if (($n % 2)== 0) {
				$is_alt = false;
			} else {
				$is_alt = true;
			}
			$t->is_alt_row = $is_alt;

			$t->invoice_no = $row['invoice_no'];
			$t->name = $row['name'];
			$t->id = $row['id'];
			$t->amount = $row['amount'];
			$t->is_uncollected_invoice = false;

			$t->purchase_date = date("m/d/Y",strtotime($row['purchased_date_time']));
			$t->paid_date = date("m/d/Y",strtotime($row['paid_date_time']));
			$t->invoice_group_name = $row['invoice_group_name'];

			$s .= Common::build_html($template_file, $t);
			$total += $row['amount'];
			$n++;
		}

		$total_count = $n;
		

		$t->is_header = false;
		$t->is_content = false;
		$t->is_footer = true;
		$t->total = $total;
		$t->total_count = $total_count;
		$s .= Common::build_html($template_file, $t);
		
		return $s;
		
	}

	function build_search_results_html($data_object = null) {

		$t = new stdclass();

		$invoice_group_id = 0;

		//$arr_options = InvoiceGroups::get_arr_values();

                $arr_option = Array();
                $arr_option['key'] = "0";
                $arr_option['value'] = "ALL";
                array_unshift($arr_options, $arr_option);
                $invoice_groups_drop_down_options = Library_Html_Util::build_drop_down_options($invoice_group_id, $arr_options);
                $t->invoice_group_drop_down_options =  $invoice_groups_drop_down_options;

		$template_file = dirname(__FILE__)."/html/template.html";

		

		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;

		$t->invoice_list_html = self::build_invoice_list_html($data_object);
		$t->search_value = $data_object->search;
		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}


	function build_edit_html($id) {

		$t = new stdclass();

		$template_file = dirname(__FILE__)."/html/edit.html";

		//$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;
		$this->load->database();
		$t->is_add = false;
		$row = Array();
		if ($id > 0) {

			$db_model = new DB_Segments();
			//$row = $db_model->get($id);
		} else {
			$t->is_add = true;
		}
		$t->is_not_found = false;


		$t->id = $id;

		$data_object = new stdclass();
		$data_object->contents_html = Common::build_html($template_file, $t);
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

	function build_suspended_user_email_html($data_object) {

		$template_file = dirname(__FILE__)."/html/suspended_email.html";

		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$data_object->site_url = Registry::get("site_url");

		$s = Common::build_html($template_file, $data_object);
		return $s;
	}

	function build_blocked_user_email_html($data_object) {

		$template_file = dirname(__FILE__)."/html/blocked_email.html";

		if (!is_object($data_object)) {
			$data_object = new stdclass();
		}

		$data_object->site_url = Registry::get("site_url");

		$s = Common::build_html($template_file, $data_object);
		return $s;
	}


}
?>
