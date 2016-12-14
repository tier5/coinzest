<?php
 /**
 * @author Tier5 llc
 */
 class Daily_tasks_file_upload extends CI_Model
 {
 	public function __construct()
    {
            // Call the CI_Model constructor
            parent::__construct();
            $this->load->database();
    }
    public function index($data, $update_date, $user_id) {
        /*print_r($user_id);
        exit();*/
    	//search for that day in databse is any file uploaded
    	$search_date = "SELECT * FROM file_on_date WHERE upload_date = '".$update_date."'";
    	$check_date = $this->db->query($search_date);
    	if (isset($check_date) && $check_date->row() != null) {
    		if ($check_date->row()->upload_date === $update_date && $check_date->row()->is_active == 1) {
	    		return -1;
	    	} else {
	    		//call that function
	    		return self::databaseOperations($data, $update_date, $user_id);
	    	}
    	} else {
    		//call that function 
    		return self::databaseOperations($data, $update_date, $user_id);
    	}
    }
    public function databaseOperations($data, $update_date, $user_id) {
        /*print_r($data->val(2,8));
        print_r($data->val(2,13));
        exit();*/
    	$some1 = "";
    	$some2 = "";
    	$some3 = "";
    	$some4 = "";
    	$some5 = "";
    	$some6 = "";
        $total_earning = 0.00;
        $task_wise_amount_task_one = 0.00;
        $task_wise_amount_task_two = 0.00;
        $current_ph = 0.00;
        $date_format_wallet_logs = array();
        $date_format_wallet_logs_string = "";
        $date_format_wallet_logs = explode("-", $update_date);
        $date_format_wallet_logs_string = $date_format_wallet_logs[2]."-".$date_format_wallet_logs[0]."-".$date_format_wallet_logs[1];
        /*$last_ph = 0.00;
        //get current amount 
        $sql_amount = "SELECT * FROM wp_users WHERE ID = ".$user_id;
        $result = $this->db->query($sql_amount);
        //getting last ph to calculate % afterwards
        if (count($result) > 0 && $result != null) {
            $last_ph = $result->row()->last_ph_amount;
        } else {
            $last_ph = 0.00;
        }*/
    	for ($i=2; $i <= $data->rowcount(); $i++) { 
    		$some1 = explode(' ', $data->val($i,5))[0];
	    	$some2 = explode(' ', $data->val($i,5))[1];
	    	$some3 = explode(' ', $data->val($i,12))[1];
	    	$some4 = explode(' ', $data->val($i,12))[0];
	    	$some5 = $data->val($i,6) == 'DONE' ? 1 : 0;
	    	$some6 = $data->val($i,10) == 'DONE' ? 1 : 0 ;
			$sql = "INSERT INTO file_upload_log (update_date, name, bmh_user_id, fb_account_url, date_of_1st_ph, first_ph_amount, task_one,task_one_details, task_one_id, modarator_comment_task_one,task_two, task_two_details, task_two_id,modarator_comment_task_two, current_ph, date_of_ph_2nd) VALUES ('".$update_date."','".$data->val($i,2)."','".$data->val($i,3)."','".$data->val($i,4)."','".$some1."','".$some2."','".$some5."','".$data->val($i,7)."','".$data->val($i,8)."','".$data->val($i,9)."','".$some6."','".$data->val($i,11)."','".$data->val($i,13)."','".$data->val($i,14)."','".$some3."','".$some4."')";
			$query = $this->db->query($sql);
            $current_ph = explode("$", explode(' ', $data->val($i,12))[1])[1];

            $current_ph = is_numeric($current_ph) == 1 ? $current_ph : 0.00; 
            //$task_wise_amount_task_one = $current_ph;
            //task one calculation
            if ($some5 == 1 && $data->val($i,9) == "3.33 % Earned") {
                $task_wise_amount_task_one += ($current_ph*3.33)/100;
            } else {
                $task_wise_amount_task_one += 0.00;
            }
            $total_earning += $task_wise_amount_task_one;
            //task two calculation
            if ($some6 == 1 && $data->val($i,14) == "3.33 % Earned") {
                $task_wise_amount_task_two += ($current_ph*3.33)/100;
            } else {
                $task_wise_amount_task_two += 0.00;
            }
            $total_earning += $task_wise_amount_task_two;
            //task one insertaion
            $sql_wallet_logs_t1 = "INSERT INTO wallet_logs(user_id, amount,gm_created,gm_modified,gm_date,is_available,wallet_type_id,log_type_id,is_pending_create,reference_id) VALUES ('".self::_helpEmailToId($data->val($i,3))."','".$task_wise_amount_task_one."','2016-08-01 07:01:04','2016-08-01 07:01:04','".$date_format_wallet_logs_string."','N','4','80','N', '".$data->val($i,8)."')";
            

            //task two insertaion
            $sql_wallet_logs_t2 = "INSERT INTO wallet_logs(user_id, amount,gm_created,gm_modified,gm_date,is_available,wallet_type_id,log_type_id,is_pending_create,reference_id) VALUES ('".self::_helpEmailToId($data->val($i,3))."','".$task_wise_amount_task_two."','2016-08-01 07:01:04','2016-08-01 07:01:04','".$date_format_wallet_logs_string."','N','4','80','N', '".$data->val($i,13)."')";

            $this->db->query($sql_wallet_logs_t1);
            $this->db->query($sql_wallet_logs_t2);
            //update daily_bonus_earning_balance in wp-users
            $sql_update_bonus_amt = "UPDATE wp_users SET daily_bonus_earning_balance = daily_bonus_earning_balance+".$total_earning." WHERE ID = ".self::_helpEmailToId($data->val($i,3));
            $this->db->query($sql_update_bonus_amt);
		}
		$insert_date = "INSERT INTO file_on_date(upload_date,is_active) VALUES ('".$update_date."', 1)";
		$run_query = $this->db->query($insert_date);
		/*print_r($run_query);
		exit();*/
		if ($run_query == 1) {
            unlink('../file_uploads/'.$update_date.'.xls');
			return 1; //this is only valid
		} else {
			return 0;
			//debug the sql error here dev guide -Tier5 llc
			//$error = $this->db->error(); print $error;
		}
    } 
    public function getEmail($id) {
        $sql = "SELECT login FROM wp_users WHERE ID = ".$id;
        $get_email = $this->db->query($sql);
        return $get_email->row();
    }
    public function _helpEmailToId($email) {
        $sql = "SELECT ID FROM wp_users WHERE login = '".$email."'";
        $get_ID = $this->db->query($sql);
        if (count($get_ID) > 0) {
            return $get_ID->row()->ID;
        } else {
            return time();
        }
        
    }
    public function getTableData($email) {
        if (isset($email) && $email!= null && $email->login) {
            $qry = "SELECT * FROM file_upload_log WHERE bmh_user_id = '".$email->login."'";
            $get_data = $this->db->query($qry);
            if ($get_data->num_rows() > 0) {
                return $get_data->result();
            } else {
                //returns null value
                return 0;
            }
        } else {
            //no email
            return -1;
        }
    }
 }