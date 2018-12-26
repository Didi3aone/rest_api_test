<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Basemain_Controller extends MX_Controller {

	//protected var for benchmark.
	protected $benchmarkData;

    //protected var for current user login.
    protected $currentUser;
    protected $_dm;


	/**
	 * Base controller constructor.
	 * this will be called first at anytime for any controllers.
	 */
    function __construct() {
        parent::__construct();

        //array for benchmarking.
        $this->benchmarkData = array();
        $this->benchmarkData['time'] = $this->benchmark->elapsed_time();
        $this->benchmarkData['memory_usage'] = $this->benchmark->memory_usage();

        //get controller.
        $controller = $this->router->fetch_class();

        //get method.
        $method = $this->router->fetch_method();

        //save current user if he is already logged in.
        if ($this->session->has_userdata('sess_login_user')) {
            $this->currentUser = $this->session->sess_login_user;
        }

		//check if user is already loged in or not.
        if ($controller == "user" && ($method == "login" || $method == "forgot_password" || $method == "reset_password")) {
            //if user already login, then cannot go to this login page.
            if (isset($this->currentUser) && $method != "reset_password") {
                //redirect to home directly.
                redirect("/home");
            }
        } else {
            //check if user not login (no session) then redirect to login.
            if (!isset($this->currentUser)) {
                redirect("/login");
            }
        }

        $this->load->model('Dynamic_model');
        $this->_dm = new Dynamic_model();
    }


    /**
     * This method is for sending notification to correct users when a report is created or updated or deleted.
     * $mode is between "insert", "update", or "delete".
     */
    protected function send_alert_notification_from_report($report_id = null, $report_item = null, $classname = null, $mode = null) {

        //validates.
        if ($report_id === null || $report_item === null) return false;
        if ($mode === null) $mode = "insert";

        //load model.
        $this->load->model('admin/Alert_model');

        //get email targets.
        $targets = $this->Alert_model->get_notif_destination_for_a_report($report_item['id']);

        //create the message content.
        $content = "Kepada YTH.<br/><br/>";

		$content_mobile = "";

        //depend of mode.
        switch ($mode) {
            case "insert": {
                $content .= "<p>Telah terjadi penambahan data.<br/><br/>";
                $content_mobile .= "Telah terjadi penambahan data pada report ";
                break;
            }
            case "update": {
                $content .= "<p>Telah terjadi pengubahan data.<br/><br/>";
                $content_mobile .= "Telah terjadi pengubahan data pada report ";
                break;
            }
            case "delete": {
                $content .= "<p>Telah terjadi penghapusan data.<br/><br/>";
                $content_mobile .= "Telah terjadi penghapusan data pada report ";
                break;
            }
            default: {
                break;
            }
        }

        //the information.
        $content .= "Report : ".$report_item['nama_report']."<br/>".
                    "Waktu  : ".dateformat(strtotime("now"))."<br/>".
                    "</p>".
                    "<p>Terimakasih atas perhatiannya.</p>".
                    "<br/>".
                    "BPOM Administrator.";

		$content_mobile .= $report_item['nama_report'];

        //the subject.
        $subject = "Notifikasi BPOM";

        //destination emails.
        $destination = array();
        $gcms = array();

        $this->load->model('Dynamic_model');
        $arrayToDB = [];
        foreach ($targets as $target) {
            if (!empty($target['email'])) {
                $destination[] = $target['email'];
            }
            if (!empty($target['gcm_key'])) {
                $gcms[] = $target['gcm_key'];
            }
            //insert to dtb_notifications for saving.
            $arrayToDB[] = array(
                "id" => uniqid("", true),
                "title" => $subject,
                "message" => $content_mobile,
                "user_id" => $target['id'],
                "registered_datetime" => date("Y-m-d H:i:s"),
            );
        }
        if (!empty($arrayToDB)) {
            //insert to notification table.
            $this->Dynamic_model->set_model("dtb_notifications", "dn", "id")->insert($arrayToDB, ["is_batch" => true, "is_direct" => true]);
        }

        //no email destination, we should not continue email sending.
        if (empty($destination)) {
            return false;
        }

        //send emails!.
        //in a bulk.
        $mail = sendmail(array(
            'subject'   =>  $subject,
            'message'   =>  $content,
            'to'        =>  $destination,
        ), "html");

        //if any error, we can log it.
        if ($mail['is_error']) {
            log_message('error', $mail['error_message']);
        }

        //push notifications
		if (!empty($gcms)) {
			//send push notif with firebase.
			if (FIREBASE_PUSH_API_KEY != "") {
				$this->load->library("Firebase_push");
				$push = new Firebase_push();
				$res = $push->firebaseSendPushNotif (FIREBASE_PUSH_API_KEY, $content_mobile, $subject, $gcms);
                //print_r($res);exit;
				log_message('error', $res);
			}
		}

		return true;
    }
}
