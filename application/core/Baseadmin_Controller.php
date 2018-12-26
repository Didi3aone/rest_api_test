<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Baseadmin_Controller extends Basemain_Controller {

	/**
	 * Base controller constructor.
	 * this parent is used for every admin controllers.
	 */
    function __construct() {
        parent::__construct();
        
        //check user must be admin level before can get inside this.
        if (!isset($this->currentUser['id_level']) || $this->currentUser['id_level'] != 1) {
            //then he is not an admin, kick him out!.
            //redirect to login.
            redirect("/logout");
        } 
    }
    
    
}
