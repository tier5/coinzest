<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BTCAddress extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function is_valid($btc_address) {
		$is_take_any_btc_address = Registry::get("is_take_any_btc_address");
		if ($is_take_any_btc_address) {
			return true;
		}

		$url = 'https://blockchain.info/rawaddr/' . $btc_address;

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL, $url);
		//curl_setopt($ch,CURLOPT_POST, count($fields));
		//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result)) {
			return true;
		}
		return false;
	}
}
