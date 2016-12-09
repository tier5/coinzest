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
		if ($id != null) {
			$this->load->model('daily_tasks_file_upload');
			$query_res = $this->daily_tasks_file_upload->getEmail($id);
			if ($query_res != null) {
				$query_res_sec =  $this->daily_tasks_file_upload->getTableData($query_res);
				if (count($query_res_sec) > 0) {
					echo json_encode($query_res_sec);
				} else {
					//spread sheet there is no data
					echo "No Data in spread sheet";
				}
			} else {
				//no data related to user id
				echo "No Data Related To user id";
			}
		} else {
			echo "No Id";
		}
	}

}