<?php
class Firebase_push {

    function firebaseSendPushNotif ($server_api_key, $body, $title, $deviceIDs) {
        $msg = array (
            'body' 	=> $body,
            'title'	=> $title,
        );

        $fields = array (
            'to'            => json_encode($deviceIDs),
            'notification'  => $msg,
        );

        $headers = array (
            'Authorization: key=' . $server_api_key,
            'Content-Type: application/json'
        );

        //send firebase!.
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields) );
        $result = curl_exec($ch );
        curl_close( $ch );

        //result from firebase server.
        return $result;
    }
}

?>
