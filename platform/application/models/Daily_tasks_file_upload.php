<?php
 /**
 * 
 */
 class Daily_tasks_file_upload extends CI_Model
 {
 	public function __construct()
    {
            // Call the CI_Model constructor
            parent::__construct();
    }
    public function index($data) {
    	print_r($data->val(2,1));
    	exit();
    	/*$data_to_push = array();
    	for ($i=2; $i <= $data->rowcount(); $i++) { 
				//for ($j=2; $j <= $data->colcount() ; $j++) { 
					$sql = INSERT INTO `file_upload_log`(`update_date`, `timestamp`, `name`, `bmh_user_id`, `email`, `fb_account_url`, `date_of_1st_ph`, `first_ph_amount`, `task_one`, `task_one_details`, `task_one_id`, `modarator_comment_task_one`, `task_two`, `task_two_details`, `task_two_id`, `modarator_comment_task_two`, `current_ph`, `id`) VALUES ("somedata",$data->val($i,1),[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12],[value-13],[value-14],[value-15],[value-16],[value-17],[value-18])
					print_r($data->val($i,$j));
					echo "<br/>";
				//}
				echo "<br>";
			}
        	unlink('../file_uploads/'.$name);*/
    }
 }