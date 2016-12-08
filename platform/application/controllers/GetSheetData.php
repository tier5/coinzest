<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class GetSheetData extends CI_Controller { 
	function __construct() {
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(array('user_agent', 'read_excel/Spreadsheet_Excel_Reader', 'session'));
	}

	public function index() { 
		$id = $this->input->post('userid');
		$this->load->model('daily_tasks_file_upload');
		$query_res = $this->daily_tasks_file_upload->getEmail($id);
		//print_r($query_res);
		if ($query_res != null) {
			$query_res_sec =  $this->daily_tasks_file_upload->getTableData($query_res);
			echo json_encode($query_res_sec);
			/*foreach ($query_res_sec as $key => $value) {
				print_r($value);
			}*/
		} else {
			//no data related to user id
			echo -1;
		}
	}

}