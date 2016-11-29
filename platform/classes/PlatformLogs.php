<?

class PlatformLogs extends CI_Model {


	function log($log_name, $str) {

		if (Registry::get("is_dev_server")) {
			$log_dir = $_SERVER['DOCUMENT_ROOT']."/../../platform_logs";
		} else {
			$log_dir = $_SERVER['DOCUMENT_ROOT']."/../../../platform_logs";
		}

		if (!is_dir($log_dir)) {
			@mkdir($log_dir);
		}
		if (is_array($str) || is_object($str)) {
			ob_start();
			print_r($str);
			$str = ob_get_contents();
			ob_end_clean();
		}

		file_put_contents("$log_dir/$log_name".".log", date("m/d/Y"). ": ".$str."\n", FILE_APPEND);
        }

}
?>
