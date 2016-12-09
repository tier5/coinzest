<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Uploadspreadsheet extends CI_Controller { 
	
	function __construct() {
		parent::__construct();
		$this->load->helper(array('url'));
		$this->load->library(array('user_agent', 'read_excel/Spreadsheet_Excel_Reader', 'session'));
		$testing = 1;
	}

	public function index() {
		/*print_r($_COOKIE);
		exit();*/
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
			$query_res = $this->daily_tasks_file_upload->index($data, $file_name[0]);
			/*print_r($query_res);
			exit();*/
			session_start();
			switch ($query_res) {
				case 1:
					//success
					//$_SESSION["response"] = 1;
					$this->session->set_flashdata('response', 1);
					break;
				case -1:
					//file already uploaded
					//$_SESSION["response"] = -1;
					$this->session->set_flashdata('response', -1);
					//$this->session->set_userdata('response',-1);
					break;
				case 0:
					//fail to insert 2nd rec
					//$_SESSION["response"] = 0;
					$this->session->set_flashdata('response', 0);
					//$this->session->set_userdata('response',0);
					break;
				default:
					// internal server error
					//$_SESSION["response"] = 500;
					$this->session->set_flashdata('response', 500);
					//$this->session->set_userdata('response',500);
					break;
			}
			redirect($this->agent->referrer());
			//$this->session->unset_userdata('response');
			/*print_r($this->session->userdata());
			exit();*/
		} else {
			//bad input here xls allowed
			//$_SESSION["response"] = 2;
			$this->session->set_flashdata('response', 2);
			//$this->session->set_userdata('response',2);
			redirect($this->agent->referrer());
		}
	}
	public function showError() {
		//1->ok, 2->bad file extension, -1 file already uploaded, 0->failed to insert second rec ,500 internal server error
		print_r($_SESSION['response']);
		//unset($_SESSION['response']);
	}
}