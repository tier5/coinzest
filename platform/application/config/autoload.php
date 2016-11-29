<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Drivers
| 4. Helper files
| 5. Custom config files
| 6. Language files
| 7. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packages
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/
$autoload['packages'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in system/libraries/ or your
| application/libraries/ directory, with the addition of the
| 'database' library, which is somewhat of a special case.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'email', 'session');
|
| You can also supply an alternative library name to be assigned
| in the controller:
|
|	$autoload['libraries'] = array('user_agent' => 'ua');
*/
$autoload['libraries'] = array('database');

/*
| -------------------------------------------------------------------
|  Auto-load Drivers
| -------------------------------------------------------------------
| These classes are located in system/libraries/ or in your
| application/libraries/ directory, but are also placed inside their
| own subdirectory and they extend the CI_Driver_Library class. They
| offer multiple interchangeable driver options.
|
| Prototype:
|
|	$autoload['drivers'] = array('cache');
|
| You can also supply an alternative property name to be assigned in
| the controller:
|
|	$autoload['drivers'] = array('cache' => 'cch');
|
*/
$autoload['drivers'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/
$autoload['helper'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/
$autoload['config'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/
$autoload['language'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('first_model', 'second_model');
|
| You can also supply an alternative model name to be assigned
| in the controller:
|
|	$autoload['model'] = array('first_model' => 'first');
*/
$autoload['model'] = array();

$include_path = "";

$include_path .= ":classes:system/core";
$include_path .= ":" . get_include_path();
//print "include path is $include_path\n";

set_include_path( $include_path);

if (!defined("AUTOLOAD")) {
        function __autoload( $classname) {
		//print "classname is $classname\n";
		/*
		if ($classname == "CI_Exceptions") {
			return true;
		}
		if ($classname == "CI_DB") {
			return true;
		}
		*/
	/*
		if (preg_match("/^CI_/",$classname)) {
			return true;
		}
	*/
		if (preg_match("/^CI_/",$classname)) {
			$classname = preg_replace("/^CI_/", "", $classname); 
			//$classname = "Model";
			//return true;
		}
		/*
		if (preg_match("/^CI_/",$classname)) {
			$classname = "";
		}
		*/

                $path = str_replace('_', DIRECTORY_SEPARATOR, $classname);

                /*
                $pattern = "/\\".DIRECTORY_SEPARATOR."$/";
                //print $pattern;
                if (preg_match($pattern,$path)) {
                        $path .= "common";
                }
                //print "path is $path<br>";
                 */
                $path = rtrim($path, DIRECTORY_SEPARATOR);
                //print "path is $path<br>";

                if (!is_file("$path.php")) {
                        //print "In here";
                        //print $path."<br>";
                }
                $result = @include_once("$path.php" );
                if (!$result) {
                        //print "In here";
                        $pos = strrpos($path,DIRECTORY_SEPARATOR);
                        if ($pos !== false) {
                                $path = substr($path, 0, $pos+1);
                                $path .= "$classname";
                        }
                        $result = @include_once("$path.php" );
                }

                if (!$result) {
                        print "path is $path<br>";
                        print "class name is $classname\n";
                        print "<br>";
                        print "<br>";
                        debug_print_backtrace();
                        print "<br>";
                        print "<br>";
                }
        }
        define("AUTOLOAD", true);
}

/*
header('Access-Control-Allow-Origin: https://'.$_SERVER['HTTP_HOST']);
header('Access-Control-Allow-Origin: http://'.$_SERVER['HTTP_HOST']);
$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "http://www.domain1.com" || $http_origin == "http://www.domain2.com" || $http_origin == "http://www.domain3.info")
{  
    header("Access-Control-Allow-Origin: $http_origin");
}
*/
header("Access-Control-Allow-Origin: *");

Library_Auth_Common::$PAGE_REDIRECT_URL = "/login.php";


Registry::set("site_url", "https://thebitcoinbeast.com");

Registry::set("full_site_url", "https://thebitcoinbeast.com");
Registry::set("short_site_url", "thebitcoinbeast.com");
Registry::set("is_take_any_btc_address", false);
$dir_path = dirname(__FILE__);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//print "dir path is $dir_path\n";

$uploads_dir = $_SERVER['DOCUMENT_ROOT'];
//print "uplaods dir is $uploads_dir<br>\n";
if (preg_match("/\/ayatri\./", $dir_path) == 1) {
	//print "in ayatri\n";
	Registry::set("is_dev_server", false);
	$uploads_dir = $uploads_dir."/../../uploads/ayatri";
	Registry::set("uploads_dir", $uploads_dir);
	
} elseif (preg_match("/\/kadoo\./", $dir_path) == 1) {
	Registry::set("is_dev_server", false);
	$uploads_dir = $uploads_dir."/../../uploads/kadoo";
	Registry::set("uploads_dir", $uploads_dir);
} elseif (preg_match("/\/jakiney\./", $dir_path) == 1) {
	$uploads_dir = $uploads_dir."/../../uploads/jakiney";
	Registry::set("uploads_dir", $uploads_dir);
} elseif (preg_match("/\/coinzest\//", $dir_path) == 1) {
	$uploads_dir = $uploads_dir."/../../uploads/coinzest";
	Registry::set("uploads_dir", $uploads_dir);
} elseif (preg_match("/\/bitcoinbeast\//", $dir_path) == 1) {
	$uploads_dir = $uploads_dir."/../../uploads/bitcoinbeast";
	Registry::set("uploads_dir", $uploads_dir);
} else {
	Registry::set("is_dev_server", true);
	Registry::set("site_url", "http://thebitcoinbeast.serverdatahost.com");
	Registry::set("full_site_url", "http://thebitcoinbeast.serverdatahost.com");
	Registry::set("short_site_url", "thebitcoinbeast.serverdatahost.com");
	Registry::set("is_take_any_btc_address", true);

	$uploads_dir .= "/../uploads";
	//print "uploads dir is $uploads_dir\n";
	Registry::set("uploads_dir", $uploads_dir);
}
/*
print " settings are\n";
print "uploads dir is $uploads_dir\n";
exit(0);
*/

	$_SERVER['DOCUMENT_ROOT'] .= "/platform";
	$uploads_dir = Registry::get("uploads_dir")."/";
	if (!is_dir($uploads_dir)) {
		@mkdir($uploads_dir);
	}



if (Registry::get("is_dev_server")) {
} else {
	Library_Auth_Common::set_session_timeout(500);
}
Library_Auth_Common::set_session_timeout(3600);

$callback = Array('LoginAuth','check_if_need_to_go_through_first_time_registration');
Library_Auth_Common::set_post_allowed_request_callback($callback);

ini_set('memory_limit', '64M');

/* End of file autoload.php */
