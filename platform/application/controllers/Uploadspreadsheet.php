<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Uploadspreadsheet extends CI_Controller { 
	
	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->library(array('user_agent', 'read_excel/Spreadsheet_Excel_Reader'));
	}

	public function index() {
		$this->load->model('daily_tasks_file_upload');
		$file_name = array();
		//getting the file name as $file_name[0]
		$file_name = explode(".", $_FILES['daily_task_sheet']['name']);
		if ($file_name[1] == 'xls') {
        	//move the file to get data
        	$tmp_name = $_FILES["daily_task_sheet"]["tmp_name"];
        	$name = $_FILES["daily_task_sheet"]["name"];
        	move_uploaded_file($tmp_name, "../file_uploads/$name");
        	error_reporting(E_ALL ^ E_NOTICE);
			$data = new Spreadsheet_Excel_Reader("../file_uploads/".$name);
			$this->daily_tasks_file_upload->index($data);
		} else {
			//bad input here xlsx allowed
			redirect($this->agent->referrer());
		}
		//print_r($file_name[0]);
	}
}