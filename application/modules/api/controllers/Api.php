<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
	 * Param : Format (opt)  , nama, email, foto
	 */
    public function add_post() {

        //get parameter
        $nama  = $this->post("username");
        $email = $this->post("email");

        //check if api_key is not empty
        if ($nama == "" || $email == "") {
            //return NG , api_key , nama , email must not empty
            $err_message = str_replace("%FIELD%", "Name , Email" ,ERROR_CODE_13);
            $this->response($this->_result_NG ($err_message, 13), REST_Controller::HTTP_OK);
        }

        //begin trans
        $this->db->trans_begin();

        $arrayToDB = array(
            'username'  => $nama,
            'email'     => $email,
        );

        if(count(array_count_values(str_split($nama))) == 1) {
           $arrayToDB['note']  = "true";
        } else {
            $arrayToDB['note'] = "false";
        }
  //       if (!preg_match('/^[A-Za-z0-9]+$/', $nama))
		// {
		//     $arrayToDB['note'] = "true";
		// } else {
		// 	$arrayToDB['note'] = "false";
		// }

         if (isset($_FILES['foto']['size']) && $_FILES['foto']['size'] > 0) {
         	// print_r($this->input->post());
            //upload file image
            //prepare config.
            $config = array(
                "allowed_types"     =>  "png|gif|jpg",
                "file_ext_tolower"  =>  true,
                "overwrite"         =>  false,
                "max_size"          =>  "102400",
                "file_target"       =>  array(
                    "upload_path"   =>  "uploads/",
                    "filename"      =>  "files",
                ),
            );

            //load the uploader library.
            $this->load->library('Uploader');

            //try to upload the image.
            $upload_result = $this->uploader->upload_files("foto", false, $config);

            if ($upload_result['is_error'] == true) {
                if ($upload_result['result'][0]['error_code'] == 1)  {
                    //return NG , error messages
                    $err_message = str_replace("%ERROR%", $upload_result['result'][0]['error_msg'] ,ERROR_CODE_17);
                    $this->response($this->_result_NG ($err_message, 17), REST_Controller::HTTP_OK);
                }
            } else {
                $arrayToDB['image'] = $upload_result['result'][0]['uploaded_path'];
            }
        }
        // print_r($$upload_result['result'][0]['uploaded_path']);exit();
        //update user data
        $result = $this->db->insert("user",$arrayToDB);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $this->response($this->_result_NG (ERROR_CODE_16, 16), REST_Controller::HTTP_OK);

        } else {
            $this->db->trans_commit();

            //return user data and user reports
            $this->response($this->_result_OK(array(
                "response" => $result)) 
            , REST_Controller::HTTP_OK);
        }
    }
}