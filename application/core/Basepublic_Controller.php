<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Basepublic_Controller extends CI_Controller {
    
    public $data_login_user;
    public $user_choice;
    
    function __construct() {
        parent::__construct();
		
		//get user_agent 
		$this->load->library('user_agent');
		
		$is_iphone = $this->agent->is_mobile('iphone');
		
        //get session login_admin
        $login_user = $this->session->userdata('login_user');
        $user_choice = $this->session->userdata('user_choice');
		
		//check faq
		$faq = $this->Faq_model->check_faq();
        
        $data = array(
            'login_user' => $login_user,
            'user_choice' => $user_choice,
            'is_iphone' => $is_iphone,
            'total_faq' => $faq,
        );
        $this->data_login_user = $login_user;
        $this->user_choice = $user_choice;
        $this->load->vars($data);
        
        //get controller
        $controller = $this->router->fetch_class();
        $function = $this->router->fetch_method();
        
        if ($controller != "store") {
            $this->session->unset_userdata('uv_store_search');
        }
		
		if ($controller != "offices") {
            $this->session->unset_userdata('uv_office_search');
        }
		
		if ($controller != "colours") {
            $this->session->unset_userdata('color_search');
        } else if ($controller == "colours") {
			if ($function == "index") {
				$this->session->unset_userdata('color_search');
			}
		}
		
		if ($controller != "palette") {
            $this->session->unset_userdata('pallete_search');
            $this->session->unset_userdata('pallete_filter');
            $this->session->unset_userdata('palette_detail_search');
        } else if ($controller == "palette") {
			if ($function == "detail") {
				$this->session->unset_userdata('pallete_search');
				$this->session->unset_userdata('pallete_filter');
			}
			
			if ($function == "lists") {
				$this->session->unset_userdata('palette_detail_search');
			}
		}
        
        $allowed = array("logout","likebox","account");
        $logined = array("login","register","forgot_password");
        
        //guest
        if (!isset($login_user['id'])) {
            if (array_search($controller,$allowed) !== FALSE) {
                show_404('page');
            }
        } else if (isset($login_user['id'])) {
			if (array_search($controller,$logined) !== FALSE) {
                show_404('page');
            }
		}
    }
}