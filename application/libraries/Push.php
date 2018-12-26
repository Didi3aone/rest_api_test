<?php
class Push {
	/**
	 * Method to send push notif to ios devices.
	 * must have : PEM key file, PEM passphrase.
	 @param $deviceTokens array of device tokens.
	 @param $notifMessage is the title of the push notif.
	 @param $content is the content message of the push notif.
	 @param $pathToPem is path to PEM key FILE.
	 @param $isSandBox default is true, send to sendbox APNS.
	 @param $PEMpassphrase is the password of the PEM key.
	 */
	function simpleiOSPushNotif ($deviceTokens = null, $notifMessage = null, $content = null, $pathToPEM = null, $isSandBox = true, $PEMpassphrase = null, $info = null) {

		//
		//validations.
		//
		//PEM passphrase.
		if ($PEMpassphrase == null) {
			return "PEM passphrase cannot null.\n";
		} else {
			//private key's passphrase here:
			$passphrase = $PEMpassphrase;
		}
		//PEM file.
		if ($pathToPEM == null) {
			return "must provide path to PEM key.\n";
		} else {
			//check file exists.
			if (!file_exists($pathToPEM)) {
				return "PEM file not exists or path is wrong.\n ".$pathToPEM ;
			}
		}
		//device tokens.
		if ($deviceTokens == null || count($deviceTokens) < 1) {
			return "device token array must not null or empty.\n";
		}

		//construct the content.
		$contentBody = array("title"   => $notifMessage,
							 "body" => $content,
							);

		//create stream.
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $pathToPEM);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		// Open a connection to the APNS server
		$fp = null;
		if ($isSandBox) {
			$fp = stream_socket_client(
				'ssl://gateway.sandbox.push.apple.com:2195', $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		} else {
			$fp = stream_socket_client(
				'ssl://gateway.push.apple.com:2195', $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		}

		if (!$fp)
			return("Failed to connect: $err $errstr".PHP_EOL."\n");

		// Create the payload body
		$body['aps'] = array(
			'alert' => $contentBody,
			'sound' => 'default',
			'badge' => 0,
			);
		
		if ($info != null) {
			$body['aps']['info'] = $info;
		}

		// Encode the payload as JSON
		$payload = json_encode($body);

		//send to multiple device in single connection
		foreach($deviceTokens as $deviceToken) {

			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
		}

		// Close the connection to the server
		fclose($fp);

		//success.


        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
	}


	/**
	 * Method to send push notif to android devices.
	 * must have : Google PUSH API key.
	 @param $deviceTokens array of device tokens.
	 @param $title is the title of the push notif.
	 @param $message is the content message of the push notif.
	 @param $googlePushAPIkey is the the google API KEY for push.
	 */
	function simpleAndroidPushNotif ($deviceTokens = null, $title = null, $message = null, $googlePushAPIkey = null) {

		//
		//validations.
		//
		//googlePushAPIkey.
		if ($googlePushAPIkey == null) {
			return "googlePushAPIkey cannot null.\n";
		}
		//device tokens.
		if ($deviceTokens == null || count($deviceTokens) < 1) {
			return "device token array must not null or empty.\n";
		}

		//construct the content.
		$content = array("title"   => $title,
						 "message" => $message
						);

		//push google api key.
		$GOOGLE_API_KEY = $googlePushAPIkey;

		//Set POST variables
		$url = 'https://android.googleapis.com/gcm/send';

		$fields = array(
			'registration_ids' => $deviceTokens,
			'data' => $content,
		);

		$headers = array(
			'Authorization: key=' . $GOOGLE_API_KEY,
			'Content-Type: application/json'
		);

		//Open connection
		$ch = curl_init();

		//Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		//Execute post
		$result = curl_exec($ch);
		// print_r($result); exit;
		if ($result === FALSE) {
			return "Curl failed: ".curl_error($ch)."\n";
		}

		//Close connection
		curl_close($ch);

		//success.
		return "Push Success\n";
	}
}

?>