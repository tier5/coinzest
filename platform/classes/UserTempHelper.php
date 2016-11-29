<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserTempHelper extends CI_Controller {

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


	public function get_proof_image($id, $folder_name) {
		Library_Auth_Common::check_allowed_request();
		if ($id > 0) {
			$user_id = LoginAuth::get_session_user_id();

			$is_access = false;
			if ($user_id == $id || LoginAuth::is_user_have_admin_access()) {
				$is_access = true;
			}
			if ($is_access) {
				$uploads_dir = Registry::get("uploads_dir")."/";
				if (!is_dir($uploads_dir)) {
					@mkdir($uploads_dir);
				}

				//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/".$id.".png";
				$uploads_dir = Registry::get("uploads_dir")."/$folder_name/";
				if (!is_dir($uploads_dir)) {
					@mkdir($uploads_dir);
				}
				$destination = Registry::get("uploads_dir")."/$folder_name/".$id.".png";
				if (is_file($destination)) {
					//print "in here\n";
					header("Content-type: image/png");
					readfile("$destination");
					exit;
				} 
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}


	// set that the iamge receipt upload has been sent
	function set_proof_image_upload($user_id, $field_name) {
		$db_ut_model = new DB_UserTemp();

		$arr_ut_data = $db_ut_model->get_by_user_id($user_id);
		$obj_result = new stdclass();
		$obj_result->is_success = false;

		$now_gm_date_time = Library_DB_Util::time_to_gm_db_time();
		
		$data = Array();

		$data[$field_name] = "Y";
		$data['user_id'] = $user_id;
		$data['gm_modified'] = $now_gm_date_time;

		//print "in here\n";
		$db_ut_model->save(0, $data);
		$number_affected = $db_ut_model->db->affected_rows();
		if ($number_affected > 0) {
			$obj_result->is_success = true;
		} else {
			$arr_criteria = Array();
			$arr_criteria['user_id'] = $user_id;
			$is_dev_server = Registry::get("is_dev_server");
			if (!$is_dev_server) {
				$arr_criteria['is_fields_locked'] = "N";
			}
			$arr_criteria[$field_name] = "N";
			//print_r($arr_criteria);
			$db_ut_model->update($data, $arr_criteria);
			$number_affected = $db_ut_model->db->affected_rows();
			if ($number_affected > 0) {
				$obj_result->is_success = true;
			}
		}
		return $obj_result;
	}

	function accept_and_set_proof_image($id, $field_name) {
		$obj_request_data = Common::load_request_data();

		if (is_object($obj_request_data)) {
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_verified = false;
		$is_submit_image = false;
		
		// verify that we are the ph
		$db_model = new DB_UserTemp();
		$temp_data = $db_model->get_by_user_id($id);
		//print_r($temp_data);
		$is_verified = true;
		if (count($temp_data) > 0) {
			if ($user_id == $temp_data['user_id'] || LoginAuth::is_user_have_admin_access()) {
				if ($temp_data[$field_name] == "Y") {
					//$is_verified = false;
				}
			}
		}

		if ($is_verified) {
			// upload
			$result = UserTempHelper::set_proof_image_upload($id, $field_name);
			if ($result->is_success) {
				$obj_result->is_success = true;
			}
		}

		//$this->image_lib->resize();
		return $obj_result;

	}


	function upload_proof_image($id, $arr_files, $field_name, $folder_name) {
		$obj_request_data = Common::load_request_data();

		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_verified = false;
		$is_submit_image = false;
		
		$is_submit_image = true;
		// verify that we are the ph
		$db_ut_model = new DB_UserTemp();
		$temp_data = $db_ut_model->get_by_user_id($id);
		if (count($temp_data) > 0) {
			if ($user_id == $temp_data['user_id']) {
				//print "field name is $field_name<br>\n";
				//print_r($temp_data);
				if ($temp_data[$field_name] == "Y") {
					$is_submit_image = false;
				}
			}
		}

		if ($is_submit_image) {
			$filename = $arr_files['file']['tmp_name'];
			$tmp_file = $filename;
			//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/". $filename;
			$filename = $arr_files['file']['name'];
			

			$uploads_dir = Registry::get("uploads_dir")."/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}

			$destination = Registry::get("uploads_dir")."/$folder_name/".$id.".png";


			$uploads_dir = Registry::get("uploads_dir")."/$folder_name/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}
			$destination = Registry::get("uploads_dir")."/$folder_name/".$id.".png";
			//print "destination is $destination\n";

			//move_uploaded_file($tmp_file, $destination);
			/*
			$this->load->library('image_lib');
			$settings['maintain_ratio'] = TRUE;
			$settings['image_library'] = 'gd2';
			$settings['create_thumb'] = TRUE;
			$settings['quality'] = '100%';
			$settings['width'] = 400;
			$settings['height'] = 600;
			$settings['new_image'] = $destination;
			$settings['source_image'] = $tmp_file;
			$this->load->library('image_lib',$settings); 
			if ( !$this->image_lib->resize()){
				// if got fail.
				$error = $this->image_lib->display_errors();	
				//print_r($error);
				//print "in here error\n";
			} else {
			}
			*/
				//print "no errror\n";
			if (is_file($tmp_file)) {
				imagepng(imagecreatefromstring(file_get_contents($tmp_file)), $destination);
			}
			$obj_result->is_success = true;
		}


		//$this->image_lib->resize();
		unlink($arr_files['file']['tmp_name']);

		return $obj_result;
	}



}
?>
