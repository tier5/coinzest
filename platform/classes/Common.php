<?php

class Common {

        /*
                build html based on file and template object
        */
        function build_html($file, $t) {
                ob_start();
                include($file);
                $s = ob_get_contents();
                ob_end_clean();
                return $s;
        }

	function load_request_data() {
		$postdata = file_get_contents("php://input");
		//print_r($postdata);
		$request = json_decode($postdata);
		//print_r($request);
		//print "in here\n";
		if (is_object($request) && isset($request->data)) {
			$obj_post_data = $request->data;
		} else {
			//$request = new stdclass();
		}
		return $request;

	}
}

?>
