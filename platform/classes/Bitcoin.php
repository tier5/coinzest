<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bitcoin extends CI_Controller {

	function get_bitcoin_price() {
		$value =  self::get_bitcoin_price_from_blockchain();
		if ($value == "") {
			Emailer::send_alert("failed getting bitcoin price from blockchain", "failed getting bitcoin price", "",$value);
			$value =  self::get_bitcoin_price_from_bitstamp();
			if ($value == "") {
				Emailer::send_alert("failed getting bitcoin price from bitstamp", "failed getting bitcoin price", "",$value);
			}
		}
		return $value;
	}

	function get_bitcoin_price_from_blockchain() {
		$url = 'https://blockchain.info/ticker';


		//open connection
		$ch = curl_init();
		//print_r($fields_string);

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result) && isset($result->USD)) {
			return $result->USD->last;
		} else {
			// error
		}
		return "";
	}

	function get_bitcoin_price_from_bitstamp() {
		$url = 'https://www.bitstamp.net/api/v2/ticker/btcusd/';


		//open connection
		$ch = curl_init();
		//print_r($fields_string);

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result) && isset($result->last)) {
			return $result->last;
		} else {
			// error
		}
		return "";
	}

	// will be deprecated
	function get_bitcoin_price_from_bitcoinaverage() {
		$url = 'https://api.bitcoinaverage.com/ticker/USD/';


		//open connection
		$ch = curl_init();
		//print_r($fields_string);

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result) && isset($result->last)) {
			return $result->last;
		} else {
			// error
		}
		return "";
	}
}
