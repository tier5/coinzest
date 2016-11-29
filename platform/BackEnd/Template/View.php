<?

class BackEnd_Template_View {

	function __construct() { }


	function build_html($data_object = null) {
		if (is_null($data_object)) {
			$data_object = new stdclass();
			
		}
		$theme = Registry::get("theme");
		//$theme  ="GreySideNav";
		//$data = DB_Accounts::get($ItemID);
		$temp_data_object = new Stdclass();


		$compileDir = Registry::get("compileDir");
		$templateDir = $_SERVER['DOCUMENT_ROOT'];

		//$TemplateView->search_html = $this->build_search($search, $arr_options);


		$template_file = "BackEnd/Template/html/template.html";

		$template_file = $templateDir."/".$template_file;
		//print "template file is $template_file\n";

		$temp_data_object = new Stdclass();
		$temp_data_object->ID = 0;
		//$temp_data_object->content_block_unique_id = $data_object->content_block_unique_id;

		//$add_html = self::build_email_campaigns_update_html($temp_data_object,$ContainerItemID);
		//$TemplateView->add_html = $add_html;
		$is_iphone_view = Registry::get("is_iphone_view");
		$TemplateView->is_iphone_view = $is_iphone_view;
		$TemplateView->js_include_path = Registry::get("js_include_path");
		//$TemplateView->sajax_javascript = $this->sajax_javascript;
		$TemplateView->javascript_start_tag = Registry::get("javascript_start_tag");
		$Sessions = Registry::get("Session");
		//$TemplateView->SessionID = $Sessions->get_session_id();
		$TemplateView->javascript_end_tag = Registry::get("javascript_end_tag");


		$client_id = Registry::get("ClientID");
		$view = Registry::get("View");


		$user_id = $_SESSION['user_id'];
		$db_model = new DB_Users();
		$arr_user_data = $db_model->get($user_id);
		$TemplateView->login = "";
		if (count($arr_user_data) > 0) {
			$TemplateView->login = $arr_user_data['login'];
		}


		$TemplateView->page_title = "";
		$TemplateView->is_have_page_title = false;
		if (isset($data_object->page_title)) {
			$TemplateView->is_have_page_title = true;
			$TemplateView->page_title = $data_object->page_title;
		}
		$TemplateView->view = $view;
		if(LoginAuth::is_user_have_admin_access()) {
			$TemplateView->is_user_have_admin_access = "true";
			//print_r($TemplateView);
		} else {
			$TemplateView->is_user_have_admin_access = "false";
		}


		$TemplateView->session_start_gm_date_time = $_SESSION['session_start_gm_date_time'];
		$TemplateView->page_running_gm_date_time = Library_DB_Util::time_to_gm_db_time();;
		$TemplateView->remote_ip_address = $_SERVER['REMOTE_ADDR'];
		$is_user_have_manager_access = false;
		if (LoginAuth::is_user_have_manager_access()) {
			$is_user_have_manager_access = true;
		}

		
		if ($arr_user_data['is_view_old_data_form'] == 'Y') {
			$TemplateView->is_view_old_data_form = "true";
		} else {
			$TemplateView->is_view_old_data_form = "false";
		}
		$TemplateView->is_user_have_manager_access = $is_user_have_manager_access;

		//$client_id = Registry::get("ClientID");

		if (isset($data_object->contents_html)) {
			$TemplateView->contents_html = $data_object->contents_html;
		}
		//$s = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		//$s = "<!DOCTYPE html>\n";
		
		//print "template file is $template_file\n";
		//print_r($TemplateView);
		$s = Common::build_html($template_file, $TemplateView);
		//print "s is $s\n";
		return $s;
	}

	function build_no_header_html($data_object = null) {
		if (is_null($data_object)) {
			$data_object = new stdclass();
			
		}
		$theme = Registry::get("theme");
		//$theme  ="GreySideNav";
		//$data = DB_Accounts::get($ItemID);
		$temp_data_object = new Stdclass();


		$compileDir = Registry::get("compileDir");
		$templateDir = $_SERVER['DOCUMENT_ROOT'];

		//$TemplateView->search_html = $this->build_search($search, $arr_options);


		$template_file = "BackEnd/Template/html/template_no_header.html";

		$template_file = $templateDir."/".$template_file;
		//print "template file is $template_file\n";

		$temp_data_object = new Stdclass();
		$temp_data_object->ID = 0;
		//$temp_data_object->content_block_unique_id = $data_object->content_block_unique_id;

		//$add_html = self::build_email_campaigns_update_html($temp_data_object,$ContainerItemID);
		//$TemplateView->add_html = $add_html;
		$is_iphone_view = Registry::get("is_iphone_view");
		$TemplateView->is_iphone_view = $is_iphone_view;
		$TemplateView->js_include_path = Registry::get("js_include_path");
		//$TemplateView->sajax_javascript = $this->sajax_javascript;
		$TemplateView->javascript_start_tag = Registry::get("javascript_start_tag");
		$Sessions = Registry::get("Session");
		//$TemplateView->SessionID = $Sessions->get_session_id();
		$TemplateView->javascript_end_tag = Registry::get("javascript_end_tag");


		$client_id = Registry::get("ClientID");
		$view = Registry::get("View");


		$user_id = $_SESSION['user_id'];
		$db_model = new DB_Users();
		$arr_user_data = $db_model->get($user_id);
		$TemplateView->login = "";
		if (count($arr_user_data) > 0) {
			$TemplateView->login = $arr_user_data['login'];
		}


		$TemplateView->page_title = "";
		$TemplateView->is_have_page_title = false;
		if (isset($data_object->page_title)) {
			$TemplateView->is_have_page_title = true;
			$TemplateView->page_title = $data_object->page_title;
		}
		$TemplateView->view = $view;
		if(LoginAuth::is_user_have_admin_access()) {
			$TemplateView->is_user_have_admin_access = "true";
			//print_r($TemplateView);
		} else {
			$TemplateView->is_user_have_admin_access = "false";
		}


		$TemplateView->session_start_gm_date_time = $_SESSION['session_start_gm_date_time'];
		$TemplateView->page_running_gm_date_time = Library_DB_Util::time_to_gm_db_time();;
		$TemplateView->remote_ip_address = $_SERVER['REMOTE_ADDR'];
		$is_user_have_manager_access = false;
		if (LoginAuth::is_user_have_manager_access()) {
			$is_user_have_manager_access = true;
		}

		$TemplateView->is_user_have_manager_access = $is_user_have_manager_access;

		//$client_id = Registry::get("ClientID");

		if (isset($data_object->contents_html)) {
			$TemplateView->contents_html = $data_object->contents_html;
		}
		//$s = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		//$s = "<!DOCTYPE html>\n";
		
		//print "template file is $template_file\n";
		//print_r($TemplateView);
		$s = Common::build_html($template_file, $TemplateView);
		//print "s is $s\n";
		return $s;
	}

	function build_no_header_min_components_html($data_object = null) {
		if (is_null($data_object)) {
			$data_object = new stdclass();
			
		}
		$theme = Registry::get("theme");
		//$theme  ="GreySideNav";
		//$data = DB_Accounts::get($ItemID);
		$temp_data_object = new Stdclass();


		$compileDir = Registry::get("compileDir");
		$templateDir = $_SERVER['DOCUMENT_ROOT'];

		//$TemplateView->search_html = $this->build_search($search, $arr_options);


		$template_file = dirname(__FILE__)."/html/no_header_min_components.html";


		$temp_data_object = new Stdclass();
		$temp_data_object->ID = 0;

		$user_id = $_SESSION['user_id'];
		$db_model = new DB_Users();
		$arr_user_data = $db_model->get($user_id);
		$TemplateView->login = "";
		if (count($arr_user_data) > 0) {
			$TemplateView->login = $arr_user_data['login'];
		}


		if(LoginAuth::is_user_have_admin_access()) {
			$TemplateView->is_user_have_admin_access = "true";
			//print_r($TemplateView);
		} else {
			$TemplateView->is_user_have_admin_access = "false";
		}


		$is_user_have_manager_access = false;
		if (LoginAuth::is_user_have_manager_access()) {
			$is_user_have_manager_access = true;
		}

		if (isset($data_object->contents_html)) {
			$TemplateView->contents_html = $data_object->contents_html;
		}

		$TemplateView->is_user_have_manager_access = $is_user_have_manager_access;

		$s = Common::build_html($template_file, $TemplateView);
		//print "s is $s\n";
		return $s;
	}

}
?>
