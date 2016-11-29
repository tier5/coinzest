<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Registrations extends CI_Controller {

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
		Library_Auth_Common::set_post_allowed_request_callback(NULL);
		Library_Auth_Common::check_allowed_request();
		print BackEnd_Registration_View::build_html();

		//Library_Auth_Common::check_allowed_request();

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

	public function get_photo_id($id) {
		Library_Auth_Common::check_allowed_request();
		if (LoginAuth::is_user_have_manager_access()) {
			if ($id == "") {
				$user_id = LoginAuth::get_session_user_id();
				$id = LoginAuth::get_session_user_id();
			}
		} else {
			$user_id = LoginAuth::get_session_user_id();
			$id = LoginAuth::get_session_user_id();
		}
		if ($id > 0) {
			$data = DB_Users::get($id);
			if (count($data) > 0) {

				$is_access = true;
				if ($is_access) {
					$uploads_dir = Registry::get("uploads_dir")."/personal_ids";
					if (!is_dir($uploads_dir)) {
						@mkdir($uploads_dir);
					}
					$destination = $uploads_dir."/".$id.".png";
					if (is_file($destination)) {
						//print "in here\n";
						header("Content-type: image/png");
						readfile("$destination");
						exit;
					} 
				}
			}
		}
		$destination = $_SERVER['DOCUMENT_ROOT']."/images/no-image-box.png";
		header("Content-type: image/png");
		readfile("$destination");
		exit;
	}

	public function update_registration() {
		Library_Auth_Common::set_post_allowed_request_callback(NULL);
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();

		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		$data = null;
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data)) {
				$data = $obj_request_data->data->data;
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;
		$obj_result->is_error = false;

		$user_id = LoginAuth::get_session_user_id();



		if (!is_null($data)) {
			$db_model = new DB_Users();

			
			$temp_data = $db_model->get($user_id);
			if (count($temp_data) > 0) {
				if ($temp_data['is_done_registration_process'] == 'N' && $temp_data['is_have_photo_id'] == "Y") {
					//print_r($temp_data);
					//print_r($data);

					if ($data->change_password == "") {
						$obj_result->is_error = true;
					} else if ($data->change_password != $data->change_password2) {
						$obj_result->is_error = true;
					}

					if ($data->date_of_birth == "") {
						$obj_result->is_error = true;
					}

					if ($data->country == "") {
						$obj_result->is_error = true;
					}

					if ($data->complete_street_address == "") {
						$obj_result->is_error = true;
					}
					if ($data->id_number == "") {
						$obj_result->is_error = true;
					}
					if ($data->id_number == "") {
						$obj_result->is_error = true;
					}

					if (!$obj_result->is_error) {
						// race condition on is done registration process
						$bcrypt = new Bcrypt(5);
						$user_data = Array();
						$user_data['password'] = $bcrypt->hash($data->change_password);
						$user_data['complete_street_address'] = $data->complete_street_address;
						$user_data['photo_id_number'] = $data->id_number;
						$user_data['date_of_birth'] = $data->date_of_birth;
						$user_data['country'] = $data->country;

						// give user 7 days before expiring account after account is done with registration process
						$now_gm_date_time = Library_DB_Util::time_to_gm_db_time($db_model->db, strtotime("+7 days"));
						$user_data['expiration_before_new_ph_gm_date_time'] = $now_gm_date_time;
						$user_data['is_done_registration_process'] = "Y";
						$user_data['merged_unique_id'] = preg_replace("/[^0-9]/","",$user_data['date_of_birth'])."_". $user_data['photo_id_number']."_".preg_replace("/[^0-9]/","",$user_data['complete_street_address']);
						$result = $db_model->save($user_id, $user_data);
						//print "in here\n";
						if ($result == 1) {
							$obj_result->is_success = true;
						}
					}
				}
			}
		}
		print json_encode($obj_result);
	}

	/* upload image receipt */
	// could just pass in get with id
	public function upload_photo_id() {
		Library_Auth_Common::set_post_allowed_request_callback(NULL);
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$matchedrequest_id = 0;
		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->matchedrequest_id)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_submit_image = false;
		
		// verify that we are the ph
		$db_model = new DB_Users();

		
		$temp_data = $db_model->get($user_id);
		if (count($temp_data) > 0) {
			if ($temp_data['is_done_registration_process'] == 'N') {
				$is_submit_image = true;
			}
		}

		//$is_verified = false;
		$is_update_db = false;

		

		if ($is_submit_image && $user_id > 0) {
			$filename = $_FILES['file']['tmp_name'];
			$tmp_file = $filename;
			//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/". $filename;
			$uploads_dir = Registry::get("uploads_dir")."/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}
			$filename = $_FILES['file']['name'];


			$uploads_dir = Registry::get("uploads_dir")."/personal_ids";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}
			$destination = $uploads_dir."/".$user_id.".png";
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
				//print "no errror\n";
			}
			*/
			imagepng(imagecreatefromstring(file_get_contents($tmp_file)), $destination);
			$is_update_db = true;
		}
		// race condition between uploading and updating db


		if ($is_update_db) {
			// upload
			$data = Array();
			$data['is_have_photo_id'] = "Y";
			$result = $db_model->save($user_id, $data);
			if ($result == 1) {
				$obj_result->is_success = true;
			}
		}


		//$this->image_lib->resize();
		unlink($_FILES['file']['tmp_name']);

		print json_encode($obj_result);
	}

	public function upload_government_id() {
		Library_Auth_Common::set_post_allowed_request_callback(NULL);
		Library_Auth_Common::check_allowed_request();
		$obj_request_data = Common::load_request_data();
		$matchedrequest_id = 0;
		//print_r($obj_request_data);
		//print_r($_POST);
		// need to check if this will work on remote server
		if (is_object($obj_request_data) && isset($obj_request_data->data)) {
			if (isset($obj_request_data->data->matchedrequest_id)) {
			}
		}
		$obj_result = new Stdclass();
		$obj_result->is_success = false;

		$user_id = LoginAuth::get_session_user_id();

		$is_submit_image = false;
		
		// verify that we are the ph
		$db_model = new DB_Users();

		
		$temp_data = $db_model->get($user_id);
		if (count($temp_data) > 0) {
			if ($temp_data['is_done_registration_process'] == 'N') {
				$is_submit_image = true;
			}
		}

		//$is_verified = false;
		$is_update_db = false;

		

		if ($is_submit_image && $user_id > 0) {
			$filename = $_FILES['file']['tmp_name'];
			$tmp_file = $filename;
			//$destination = $_SERVER['DOCUMENT_ROOT']."/../../image_receipts/". $filename;
			$filename = $_FILES['file']['name'];

			$uploads_dir = Registry::get("uploads_dir")."/";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}

			$uploads_dir = Registry::get("uploads_dir")."/government_ids";
			if (!is_dir($uploads_dir)) {
				@mkdir($uploads_dir);
			}
			$destination = $uploads_dir."/".$user_id.".png";
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
			imagepng(imagecreatefromstring(file_get_contents($tmp_file)), $destination);
			$is_update_db = true;
		}
		// race condition between uploading and updating db


		if ($is_update_db) {
			// upload
			$data = Array();
			$data['is_have_government_id'] = "Y";
			$result = $db_model->save($user_id, $data);
			if ($result == 1) {
				$obj_result->is_success = true;
			}
		}


		//$this->image_lib->resize();
		unlink($_FILES['file']['tmp_name']);

		print json_encode($obj_result);
	}


}
?>
