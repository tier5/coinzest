<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CountriesHelper extends CI_Controller {


	function build_countries_drop_down_html() {
		
		return file_get_contents(dirname(__FILE__)."/../../FrontEnd/countries_drop_down.html");
	}
}
