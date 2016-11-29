<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SMS extends CI_Controller {

	function send_otp_code($otp_code, $phone_number) {
		$url = 'https://app.eztexting.com/sending/messages?format=json';
		$url = "https://rest.nexmo.com/sms/json?";
		
		$api_key="32687a81";
		$api_secret="dbda6226e7ef0582";
		$from = "12028525565";

		if (strlen($phone_number) == 10) {
			$phone_number = "1" . $phone_number;
		}
		//curl "https://rest.nexmo.com/sms/json?api_key=32687a81&api_secret=dbda226e7ef0582&from=12028525565&to=3109234078&text=Welcome+to+Nexmo"

		$site_url = Registry::get("site_url");
		$message = "Please enter your One-Time Password $otp_code to verify your mobile number on $site_url/login.php. This OTP is valid for 90 seconds";

		$fields = array(
			'api_key' => urlencode($api_key),
			'api_secret' => urlencode($api_secret),
			'from' => urlencode($from),
			'to' => urlencode($phone_number),
			'text' => urlencode($message)
		);

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();
		//print_r($fields_string);

		//print " field string is $fields_string\n";
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url."$fields_string");
		//curl_setopt($ch,CURLOPT_POST, count($fields));
		//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result) && is_object($result->Response)) {
			if ($result->Response->Status == "Success") {
				return true;
			}
		}
		return false;
	}
	/*
	function send_otp_code($otp_code, $phone_number) {
		$url = 'https://app.eztexting.com/sending/messages?format=json';

		$site_url = Registry::get("site_url");
		$message = "Please enter your One-Time Password $otp_code to verify your mobile number on $site_url. This OTP is valid for 90 seconds";

		$username = "offset27";
		$password = "";
		$fields = array(
			'User' => urlencode($username),
			'Password' => urlencode($password),
			'PhoneNumbers[]' => urlencode($phone_number),
			'Message' => urlencode($message)
		);

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');

		//open connection
		$ch = curl_init();
		//print_r($fields_string);

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		$json_result = &curl_exec($ch);
		//print_r($json_result);
		$result = json_decode($json_result);
		curl_close($ch);

		if (is_object($result) && is_object($result->Response)) {
			if ($result->Response->Status == "Success") {
				return true;
			}
		}
		return false;
	}
*/

/*
	function send_otp_code($otp_code, $phone_number) {
		$file =$_SERVER['DOCUMENT_ROOT']."/classes/CheckMobiRest.php";
		//print "file is $file\n";
		include_once($file);
		//print "in here\n";
		$api = new CheckMobiRest("19C6E827-AE04-493C-A2B3-403EAC150F96");
		$phone_number = "+8324228283";
		$response = $api->RequestValidation(array("type" => "reverse_cli", "number" => $phone_number));
		print_r($response);
		//print "phone number is $phone_number\n";
		$response = $api->CheckNumber(array("number" => $phone_number));
		$site_url = Registry::get("site_url");

		if (is_array($response)) {
			print_r($response);
			if ($response['response']['code'] == 2) {
				return false;
			}

			//print "response is $response\n";
			$e164_number = $response['response']['e164_format'];
			//print "e164 number is $e164_number\n";

			$message = "Please enter your One-Time Password $otp_code to verify your mobile number on $site_url. This OTP is valid for 90 seconds";
			$response = $api->SendSMS(array("to" => $e164_number, "text" => $message));
			print_r($response);
			if ($response['response']['code'] == 5) {
				return false;
			}
			
		}
		//print_r($response);
		//exit(0);
		//$url = 'https://api.checkmobi.com/v1/prefixes';
		return false;
	}
*/
}
