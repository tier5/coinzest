<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RegressionTests extends CI_Controller {

	function run() {
		//RegressionTests_PlatformLogs::test_log();
		//exit(0);

		RegressionTests_MatchedRequests::test_confirm_request();
		//RegressionTests_MatchedRequests::test_confirm_request_2();
		exit(0);
		RegressionTests_MatchedRequests::test_confirm_request();
		RegressionTests_PHRequest::test_cancel_ph();
		RegressionTests_PHRequest::test_cancel_ph_2();
		RegressionTests_GHRequest::test_cancel_gh();
	}
}
?>
