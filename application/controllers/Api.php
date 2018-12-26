<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
    }

    public function add_post() {

        //get parameter
        $nama  = $this->post("nama");
        $email = $this->post("email");

        //check if api_key is not empty
        if (trim($nama) == "" || trim($email) == "") {
            //return NG , api_key , nama , email must not empty
            $err_message = str_replace("%FIELD%", "Nama , Email" ,ERROR_CODE_13);
            $this->response($this->_result_NG ($err_message, 13), REST_Controller::HTTP_OK);
        }

        //begin trans
        $this->db->trans_begin();

        $arrayToDB = array(
            'username'  => $nama,
            'email'     => $email,
        );

         if (isset($_FILES['foto']['size']) && $_FILES['foto']['size'] > 0) {
            //upload file image
            //prepare config.
            $config = array(
                "allowed_types"     =>  "png|gif|jpg",
                "file_ext_tolower"  =>  true,
                "overwrite"         =>  false,
                "max_size"          =>  "102400",
                "file_target"       =>  array(
                    "upload_path"   =>  "upload/user",
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

        //update user data
        $this->db->insert($arrayToDB);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            $this->response($this->_result_NG (ERROR_CODE_16, 16), REST_Controller::HTTP_OK);

        } else {
            $this->db->trans_commit();

            //clear cache DB
            $this->clear_cache();

            //return user data and user reports
            $this->response($this->_result_OK(array("user" => $user)) , REST_Controller::HTTP_OK);
        }
    }

    public function _upload($file='')
    {

        $this->load->library("upload");

        if($_FILES['files']['size'] > 0) {
            $config['upload_path'] = "uploads/";
            $config['allowed_types'] = "gif|png|jpeg|jpg";

            $this->upload->initialize($config);

            if(!$this->upload->do_upload("file")) {
                $error = $this->upload->display_erros();
                $this->response(array('status' => 'failed upload file', 502));
            } else {
                $this->response(array(
                    "status" => "success"
                ),200);
            }
        }

        // $config['upload_path'] = './uploads/';
        // $config['allowed_types'] = 'gif|jpg|png';
        // $config['max_size']  = '100';
        // $config['max_width']  = '1024';
        // $config['max_height']  = '768';
        
        // $this->load->library('upload', $config);
        
        // if ( ! $this->upload->do_upload()){
        //     $error = array('error' => $this->upload->display_errors());
        // }
        // else{
        //     $data = array('upload_data' => $this->upload->data());
        //     echo "success";
        // }
    }
}