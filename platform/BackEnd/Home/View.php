<?

class BackEnd_Home_View {

	function __construct() { }


	function build_html($data_object = null) {

		$t = new stdclass();

		$template_file = "BackEnd/Home/html/template.html";

		$template_file = $_SERVER['DOCUMENT_ROOT']."/".$template_file;
		//print "template file is $template_file\n";

		$data_object = new stdclass();
		
		//$db_segments = new DB_Segments();

		//print "in here\n";
		//$this->load->database();
		//$t->segments_drop_down_options = $db_segments->get_drop_down_options();
		//print_r($t);

		//$data_object->contents_html = BackEnd_Dashboard_View::build_html();
		//Common::build_html($template_file, $t);
		
		$s = Common::build_html($template_file,$t);



		$data_object->contents_html = $s;
		
		$s = BackEnd_Template_View::build_html($data_object);
		return $s;
	}

}
?>
