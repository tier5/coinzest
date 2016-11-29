<?

class BackEnd_LoginHeader_View {

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


		$template_file = "BackEnd/LoginHeader/html/template.html";

		$template_file = $templateDir."/".$template_file;

		$temp_data_object = new Stdclass();

		if (isset($data_object->contents_html)) {
			$TemplateView->contents_html = $data_object->contents_html;
		}
		//$s = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		//$s = "<!DOCTYPE html>\n";
		
		//print "template file is $template_file\n";
		$s = Common::build_html($template_file, $TemplateView);
		//print "s is $s\n";
		return $s;
	}

}
?>
