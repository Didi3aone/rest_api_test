<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Basemanager_Controller extends MX_Controller {
    
    public $data_login_admin;
    
    function __construct() {
        parent::__construct();
        //get session login_admin
        $login_admin = $this->session->userdata('login_admin');
		
        $menu_minified_manager = $this->session->userdata('menu_minified_manager');
        
        if (!isset($login_admin['admin_id'])) {
            redirect("tripman/login");
        } else {
            if (!isset ($menu_minified_manager)) {
                $menu_minified_manager = 2;
            }
            $data = array(
                'login_admin' => $login_admin,
                'menu_minified_manager' => $menu_minified_manager,
            );
            $this->data_login_admin = $login_admin;
            $this->data_menu_minified_manager = $menu_minified_manager;
            $this->load->vars($data);
        }
        
        //get controller
        $controller = $this->router->fetch_class();
        
        // $allowed = array("module","navigation","menu","page","dashboard","login");
        
        // //guest
        // if ($login_admin['type'] == 2) {
            // if (array_search($controller,$allowed) === FALSE) {
                // show_404('not found');
            // }
        // }
		
		//load library form
		$this->load->library('form_validation');
    }
}