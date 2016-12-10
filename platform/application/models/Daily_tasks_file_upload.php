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
    public function index($data, $update_date) {
    	//search for that day in databse is any file uploaded
    	$search_date = "SELECT * FROM file_on_date WHERE upload_date = '".$update_date."'";
    	$check_date = $this->db->query($search_date);
    	if (isset($check_date) && $check_date->row() != null) {
    		if ($check_date->row()->upload_date === $update_date && $check_date->row()->is_active == 1) {
	    		return -1;
	    	} else {
	    		//call that function
	    		return self::databaseOperations($data, $update_date);
	    	}
    	} else {
    		//call that function 
    		return self::databaseOperations($data, $update_date);
    	}
    }
    public function databaseOperations($data, $update_date) {
    	//$ci =& get_instance();
    	$some1 = "";
    	$some2 = "";
    	$some3 = "";
    	$some4 = "";
    	$some5 = "";
    	$some6 = "";
    	for ($i=2; $i <= $data->rowcount(); $i++) { 
    		$some1 = explode(' ', $data->val($i,5))[0];
	    	$some2 = explode(' ', $data->val($i,5))[1];
	    	$some3 = explode(' ', $data->val($i,12))[1];
	    	$some4 = explode(' ', $data->val($i,12))[0];
	    	$some5 = $data->val($i,6) == 'DONE' ? 1 : 0;
	    	$some6 = $data->val($i,10) == 'DONE' ? 1 : 0 ;
			$sql = "INSERT INTO file_upload_log (update_date, name, bmh_user_id, fb_account_url, date_of_1st_ph, first_ph_amount, task_one,task_one_details, task_one_id, modarator_comment_task_one,task_two, task_two_details, task_two_id,modarator_comment_task_two, current_ph, date_of_ph_2nd) VALUES ('".$update_date."','".$data->val($i,2)."','".$data->val($i,3)."','".$data->val($i,4)."','".$some1."','".$some2."','".$some5."','".$data->val($i,7)."','".$data->val($i,8)."','".$data->val($i,9)."','".$some6."','".$data->val($i,11)."','".$data->val($i,13)."','".$data->val($i,14)."','".$some3."','".$some4."')";
			$query = $this->db->query($sql);
		}
		$insert_date = "INSERT INTO file_on_date(upload_date,is_active) VALUES ('".$update_date."', 1)";
		$run_query = $this->db->query($insert_date);
        unlink('../file_uploads/'.$update_date.'.xls');
		if ($run_query == 1) {
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