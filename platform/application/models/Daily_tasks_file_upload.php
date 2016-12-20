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
        /*print_r($data->val(2,3));
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
        $current_ph_t1 = 0.00;
        $current_ph_t2 = 0.00;
    	for ($i=2; $i <= $data->rowcount(); $i++) { 
    		$some1 = explode(' ', $data->val($i,5))[0];
	    	$some2 = explode(' ', $data->val($i,5))[1];
	    	$some3 = explode(' ', $data->val($i,12))[1];
	    	$some4 = explode(' ', $data->val($i,12))[0];
	    	$some5 = $data->val($i,6) == 'DONE' ? 1 : 0;
	    	$some6 = $data->val($i,10) == 'DONE' ? 1 : 0 ;
            //check repeat email
            if (self::isEmailExist($data->val($i,3), date('Y-m-d',strtotime($update_date)))) {
    			$sql = "INSERT INTO file_upload_log (update_date, name, bmh_user_id, fb_account_url, date_of_1st_ph, first_ph_amount, task_one,task_one_details, task_one_id, modarator_comment_task_one,task_two, task_two_details, task_two_id,modarator_comment_task_two, current_ph, date_of_ph_2nd) VALUES ('".$update_date."','".$data->val($i,2)."','".$data->val($i,3)."','".$data->val($i,4)."','".$some1."','".$some2."','".$some5."','".$data->val($i,7)."','".$data->val($i,8)."','".$data->val($i,9)."','".$some6."','".$data->val($i,11)."','".$data->val($i,13)."','".$data->val($i,14)."','".$some3."','".$some4."')";
    			$query = $this->db->query($sql);
            }
            $current_ph_t1 = self::getCurrentPh($update_date,$user_id);
            //print_r($current_ph_t1);
            //exit(0);
            $current_ph_t2 = self::getCurrentPh($update_date,$user_id, 2);
            //task one calculation
            if ($some5 == 1 && $data->val($i,9) == "3.33 % Earned") {
                $task_wise_amount_task_one = ($current_ph_t1*3.33)/100;
            } else {
                $task_wise_amount_task_one = 0.00;
            }
            //task two calculation
            if ($some6 == 1 && $data->val($i,14) == "3.33 % Earned") {
                $task_wise_amount_task_two = ($current_ph_t2*3.33)/100;
            } else {
                $task_wise_amount_task_two = 0.00;
            }
            //check repeat email
            if (self::isEmailExist($data->val($i,3), date('Y-m-d',strtotime($update_date)))) {
                //task one insertaion
                $sql_wallet_logs_t1 = "INSERT INTO wallet_logs(user_id, amount,gm_created,gm_modified,gm_date,is_available,wallet_type_id,log_type_id,is_pending_create,reference_id) VALUES ('".self::_helpEmailToId($data->val($i,3))."','".$task_wise_amount_task_one."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".date('Y-m-d',strtotime($update_date))."','N','4','80','N', '".$data->val($i,8)."')";
                //task two insertaion
                $sql_wallet_logs_t2 = "INSERT INTO wallet_logs(user_id, amount,gm_created,gm_modified,gm_date,is_available,wallet_type_id,log_type_id,is_pending_create,reference_id) VALUES ('".self::_helpEmailToId($data->val($i,3))."','".$task_wise_amount_task_two."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".date('Y-m-d',strtotime($update_date))."','N','4','80','N', '".$data->val($i,13)."')";
                    /*echo $sql_wallet_logs_t2;
                    exit();*/
                $this->db->query($sql_wallet_logs_t1);
                $this->db->query($sql_wallet_logs_t2);
                //update daily_bonus_earning_balance in wp-users
                $total_earning = $task_wise_amount_task_one + $task_wise_amount_task_two;
            }
		}
        $sql_update_bonus_amt = "UPDATE wp_users SET task_earning_balance = task_earning_balance+".$total_earning." WHERE ID = ".self::_helpEmailToId($data->val($i,3));
        $this->db->query($sql_update_bonus_amt);
        
		$insert_date = "INSERT INTO file_on_date(upload_date,is_active) VALUES ('".$update_date."', 1)";
		$run_query = $this->db->query($insert_date);
        //unlink('../file_uploads/'.$update_date.'.xls');
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
    public function getCurrentPh($update_date, $user_id, $identifier=null) {

        if ($update_date != null && $user_id != null && $identifier == null) {
            //task 1
            $tot_cost_t1=0.00;
            $date = strtotime(date("d-m-Y", strtotime($update_date)) . " -29 days");
            $query_date = date("Y-m-d", $date);
            $date_reordered = date('Y-m-d',strtotime($update_date));
            $sql_query = "SELECT amount,amount_matched_completed FROM phrequests WHERE date_format(gm_created, '%Y-%m-%d') BETWEEN '".$query_date."' AND '".$date_reordered."' AND user_id = '".$user_id."'";
            $no_of_data = $this->db->query($sql_query);
            if ($no_of_data->num_rows() > 0) {
                if (count($no_of_data->result()) > 0) {
                   foreach ($no_of_data->result() as $key => $value) {
                        //check active or not
                        if ($value->amount === $value->amount_matched_completed) {
                             $tot_cost += $value->amount;
                        } else {
                            $tot_cost +=0;
                        }
                    }
                    return $tot_cost;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else if($update_date != null && $user_id != null && $identifier != null) {
            //task 2
            $date = strtotime(date("d-m-Y", strtotime($update_date)) . " -120 days");

            $query_date = date("Y-m-d", $date);

            $date_range_2 = strtotime(date("d-m-Y", strtotime($update_date)) . " -90 days");

            $query_date_2 = date("Y-m-d", $date_range_2);
            $tot_cost_2 = 0.00;
            

            $sql_query = "SELECT amount,amount_matched_completed FROM phrequests WHERE date_format(gm_created, '%Y-%m-%d') BETWEEN '".$query_date."' AND '".$query_date_2."' AND user_id = '".$user_id."'";
            $no_of_data = $this->db->query($sql_query);
            /*echo $no_of_data->num_rows();
            exit();*/
            if ($no_of_data->num_rows() > 0) {
                if (count($no_of_data->result()) > 0) {
                   foreach ($no_of_data->result() as $key => $value) {
                        //check active or not
                        if ($value->amount === $value->amount_matched_completed) {
                             $tot_cost_2 += $value->amount;
                        } else {
                            $tot_cost_2 +=0;
                        }
                    }
                    return $tot_cost_2;
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            //bad case just to make sure 
            return 0;
        }
        //exit();
    }
    public function isEmailExist($email, $date) {
        if ($email != null) {
            $id = self::_helpEmailToId($email);
            $struc_qry = "SELECT * FROM wallet_logs WHERE user_id = '".$id."' AND gm_date = '".$date."'";
            $result = $this->db->query($struc_qry);
            /*echo $result->num_rows();
            exit();*/
            if ($result->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
 }