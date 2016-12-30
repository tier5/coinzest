<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallets extends CI_Controller {

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
	public function index() {
		Library_Auth_Common::check_allowed_request();

		print BackEnd_Wallets_View::build_html();
		/*
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}

	public function view($wallet_name, $user_id = 0) {

		Library_Auth_Common::check_allowed_request();

		print BackEnd_Wallets_View::build_html();
		/*
		if (LoginAuth::is_user_have_access(true)) {
			print BackEnd_GHRequests_View::build_html();
		} else {
			print BackEnd_Login_View::build_no_access_html();
		}
		//print BackEnd_Segments_View::build_html();
		//$this->load->view('welcome_message');
		*/
	}

	// get downline data for user
	public function get_data($user_id = 0) {
		/*echo $user_id;
		die();*/
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$wallet_name = "";
		if (is_object($obj_request_data)) {
			if (isset($obj_request_data->wallet_name)) {
				$wallet_name = $obj_request_data->wallet_name;
			}
		}
		//print_r($obj_request_data);

		if (LoginAuth::is_user_have_admin_access() && $user_id > 0) {
		} else {
			$user_id = LoginAuth::get_session_user_id();
		}

		$data = Array();
		
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->arr_data = Array();
		//print "wallet name is $wallet_name\n";

		$wallet_type_id = 0;
		if ($wallet_name == "level_income") {
			 $wallet_type_id = DB_WalletLogs::$LEVEL_INCOME_WALLET_TYPE_ID;
		} else if ($wallet_name == "daily_growth") {
			 $wallet_type_id = DB_WalletLogs::$DAILY_GROWTH_WALLET_TYPE_ID;
		} else if ($wallet_name == "daily_bonus_earnings") {
			 $wallet_type_id = DB_WalletLogs::$DAILY_BONUS_EARNING_WALLET_TYPE_ID;
		} else if ($wallet_name == "task_earnings") {
			 $wallet_type_id = DB_WalletLogs::$TASK_EARNING_WALLET_TYPE_ID;
		}
		if ($wallet_type_id > 0) {
			$db_model = new DB_WalletLogs();
			$arr_criteria = Array();
			/*print_r($user_id);
			exit();*/
			//wallet type id should be 4
			$arr_criteria['user_id'] = $user_id;
			$arr_criteria['wallet_type_id'] = $wallet_type_id;
			$arr_criteria['is_pending_create'] = "N";
			$arr_criteria['order_by'] = "gm_created";
			$arr_data = $db_model->search($arr_criteria);
			//print "in here\n";
			//exit(0);
			$obj_result->is_success = true;
			$new_array = array();
			$jan = $feb = $mar = $apr = $may = $june = $july = $aug = $sept = $oct = $nov = $dec =   array();
			$janTotal = $febTotal = $marTotal = $aprTotal = $mayTotal = $juneTotal = $julyTotal = $augTotal = $septTotal = $octTotal = $novTotal = $decTotal = 0;
			$janGmDate = $febGmDate = $marGmDate = $aprGmDate = $mayGmDate = $juneGmDate = $julyGmDate = $augGmDate = $septGmDate = $octGmDate = $novGmDate = $decGmDate =   "";
			for ($i=0; $i < count($arr_data) ; $i++) { 
				switch (explode("-", $arr_data[$i]['gm_date'])[1]) {
					case 1:
						array_push($jan, $arr_data[$i]);
						$janTotal += $arr_data[$i]['amount'];
						$janGmDate = $arr_data[$i]['gm_date'];
						break;
					case 2:
						array_push($feb, $arr_data[$i]);
						$febTotal += $arr_data[$i]['amount'];
						$febGmDate = $arr_data[$i]['gm_date'];
						break;
					case 3:
						array_push($mar, $arr_data[$i]);
						$marTotal += $arr_data[$i]['amount'];
						$marGmDate = $arr_data[$i]['gm_date'];
						break;
					case 4:
						array_push($apr, $arr_data[$i]);
						$aprTotal += $arr_data[$i]['amount'];
						$aprGmDate = $arr_data[$i]['gm_date'];
						break;
					case 5:
						array_push($may, $arr_data[$i]);
						$mayTotal += $arr_data[$i]['amount'];
						$mayGmDate = $arr_data[$i]['gm_date'];
						break;
					case 6:
						array_push($june, $arr_data[$i]);
						$juneTotal += $arr_data[$i]['amount'];
						$juneGmDate = $arr_data[$i]['gm_date'];
						break;
					case 7:
						array_push($july, $arr_data[$i]);
						$julyTotal += $arr_data[$i]['amount'];
						$julyGmDate = $arr_data[$i]['gm_date'];
						break;
					case 8:
						array_push($aug, $arr_data[$i]);
						$augTotal += $arr_data[$i]['amount'];
						$augGmDate = $arr_data[$i]['gm_date'];
						break;
					case 9:
						array_push($sept, $arr_data[$i]);
						$septTotal += $arr_data[$i]['amount'];
						$septGmDate = $arr_data[$i]['gm_date'];
						break;
					case 10:
						array_push($oct, $arr_data[$i]);
						$octTotal += $arr_data[$i]['amount'];
						$octGmDate = $arr_data[$i]['gm_date'];
						break;
					case 11:
						array_push($nov, $arr_data[$i]);
						$novTotal += $arr_data[$i]['amount'];
						$novGmDate = $arr_data[$i]['gm_date'];
						break;
					case 12:
						array_push($dec, $arr_data[$i]);
						$decTotal += $arr_data[$i]['amount'];
						$decGmDate = $arr_data[$i]['gm_date'];
						break;
					default:
						# code...
						break;
				}

			}
			/*print_r($nov);*/
			//january
			if(count($jan)){
				$janArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $janTotal,
							'gm_date' => $janGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal,
							'task_identifier_tmp' =>3

						);
				array_push($jan, $janArray);
				array_push($new_array, $jan);
			}
			//february
			/*print_r(date('Y'));
			exit();*/
			
			if(count($feb)){
				$febArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $febTotal,
							'gm_date' => $febGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal,
							'task_identifier_tmp' =>3
						);
				array_push($feb, $febArray);
				array_push($new_array, $feb);
			}
			//march
			if(count($mar)){
				$marArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $marTotal,
							'gm_date' => $marGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal,
							'task_identifier_tmp' =>3

						);
				array_push($mar, $marArray);
				array_push($new_array, $mar);
			}
			//april
			if(count($apr)){
				$aprArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $aprTotal,
							'gm_date' => $aprGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal,
							'task_identifier_tmp' =>3

						);
				array_push($apr, $aprArray);
				array_push($new_array, $apr);
			}
			//may
			if(count($may)){
				$mayArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $mayTotal,
							'gm_date' => $mayGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal,
							'task_identifier_tmp' =>3

						);
				array_push($may, $mayArray);
				array_push($new_array, $may);
			}
			//june
			if(count($june)){
				$juneArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $juneTotal,
							'gm_date' => $juneGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal,
							'task_identifier_tmp' =>3

						);
				array_push($june, $juneArray);
				array_push($new_array, $june);
			}
			//july
			if(count($july)){
				$julyArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $julyTotal,
							'gm_date' => $julyGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal,
							'task_identifier_tmp' =>3

						);
				array_push($july, $julyArray);
				array_push($new_array, $july);
			}
			//aug
			if(count($aug)){
				$augArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $augTotal,
							'gm_date' => $augGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal+$augTotal,
							'task_identifier_tmp' =>3

						);
				array_push($aug, $augArray);
				array_push($new_array, $aug);
			}
			//sept
			if(count($sept)){
				$septArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $septTotal,
							'gm_date' => $septGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal+$augTotal+$septTotal,
							'task_identifier_tmp' =>3

						);
				array_push($sept, $septArray);
				array_push($new_array, $sept);
			}
			//oct
			if(count($oct)){
				$octArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $octTotal,
							'gm_date' => $octGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal+$augTotal+$septTotal+$octTotal,
							'task_identifier_tmp' =>3

						);
				array_push($oct, $octArray);
				array_push($new_array, $oct);
			}
			//november
			if(count($nov)){
				$novArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $novTotal,
							'gm_date' => $novGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal+$augTotal+$septTotal+$octTotal+$novTotal,
							'task_identifier_tmp' =>3

						);
				array_push($nov, $novArray);
				array_push($new_array, $nov);
			}
			//december
			if(count($dec)){
				$decArray = array(
							'id' => rand(),
							'gm_created' => time(),
							'gm_modified' => time(),
							'status_id' => 1,
							'user_id' => $user_id,
							'amount' => $decTotal,
							'gm_date' => $decGmDate,
							'is_available' => 'Y',
							'wallet_type_id' => $wallet_type_id,
							'log_type_id' => 80,
							'is_pending_create' => 'N',
							'identifier' => 3,
							'balance' => $janTotal+$febTotal+$marTotal+$aprTotal+$mayTotal+$juneTotal+$julyTotal+$augTotal+$septTotal+$octTotal+$novTotal+$decTotal,
							'task_identifier_tmp' =>3

						);
				array_push($dec, $decArray);
				array_push($new_array, $dec);
			}
			$obj_result->arr_data = $new_array;
		}

		print json_encode($obj_result);
	}
	public function isLeapYear($year=null) {
      if ($year == null) {
        return 0;
      } else {
        if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
          return 1;
        } else {
          return 0;
        }
      }
    }
}
?>
