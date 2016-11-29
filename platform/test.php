<?
ini_set('session.gc_maxlifetime', 3600);

// each client should remember their session id for EXACTLY 1 hour
session_set_cookie_params(3600);

	session_start();
	print_r($_SESSION);
	print_r($_COOKIE);
	$_SESSION['cookie'] = $_SESSION['cookie']+1;
if (!isset($_SESSION['count'])) {
  $_SESSION['count'] = 0;
} else {
  $_SESSION['count']++;
}
	print_r($_SERVER);
$salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
$salt = base64_encode($salt);
$salt = str_replace('+', '.', $salt);
$hash = crypt('rasmuslerdorf', '$2y$10$'.$salt.'$');

echo $hash;
?>
