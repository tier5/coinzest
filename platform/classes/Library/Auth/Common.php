<?

class Library_Auth_Common {

	static $IS_AUTH_ENABLED = true;
	static $PAGE_REDIRECT_URL = "";

	static $post_allowed_request_callback = null;


	static $session_timeout = 3600;

	static $is_site_maintenance_mode = false;

	function set_post_allowed_request_callback($callback) {
		self::$post_allowed_request_callback = $callback;
	}

	function set_session_timeout($timeout) {
		self::$session_timeout = $timeout;
		
	}


	// check if we have allowed access if not then exit
	// this is for xmlrpc requests
	function check_allowed_request() {
		ini_set('session.gc_maxlifetime', self::$session_timeout);

		// each client should remember their session id for EXACTLY 1 hour
		session_set_cookie_params(self::$session_timeout);

		session_start();
		$is_authenticated = false;
		if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated']) {
			$is_authenticated = true;
		}
		if (self::$is_site_maintenance_mode) {
			$is_authenticated = false;
			self::$PAGE_REDIRECT_URL = "/platform/maintenance";
		}
		if (!$is_authenticated && self::$IS_AUTH_ENABLED) {
			// do redirect
			
			$url = $_SERVER['SERVER_NAME'];
			if (self::$PAGE_REDIRECT_URL == "") {
				$url = "http://$url/login";
			} else {
				$url = self::$PAGE_REDIRECT_URL;
			}

			//print "$url\n";
			//print "redirect to $url\n";
			header("Location: $url");
			//header($url);
			exit(0);
		} else {
		}
		session_write_close();
		
		
		if (!is_null(self::$post_allowed_request_callback)) {
			call_user_func(self::$post_allowed_request_callback);
		}
	}

}

?>
