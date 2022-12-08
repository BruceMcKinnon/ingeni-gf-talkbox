<?php

//
// IngeniTalkboxApi - Class to connect and retrive info from the TalkBox API
//

class IngeniTbApi {
	private $tb_api_username;
	private $tb_api_password;
	private $tb_api_url;
	private $debug;

	public function __construct( $url, $username, $password, $debug_mode = 0 ) {
		$this->tb_api_username = $username;
		$this->tb_api_password = $password;
		$this->tb_api_url = $url;
		$this->debug = $debug_mode;
	}

	private function is_local() {
		$local_install = false;
		if ( ($_SERVER['SERVER_NAME']=='localhost') || ( stripos($_SERVER['SERVER_NAME'],'dev.local') !== false ) ) {
			$local_install = true;
		}
		return $local_install;
	}

	public function fb_log($msg, $filename = "", $overwrite = false) {
		$upload_dir = wp_upload_dir();
		$outFile = $upload_dir['basedir'];
	
		if ( $this->is_local() ) {
			$outFile .= DIRECTORY_SEPARATOR;
		} else {
			$outFile .= DIRECTORY_SEPARATOR;
		}

		if ($filename == "") {
			$filename = basename(__DIR__);
		}
		$outFile .= $filename.'.txt';
		
		date_default_timezone_set("Australia/Sydney");

		$write_mode = "a";
		if ($overwrite) {
			$write_mode = "w+";
		}

		// Now write out to the file
		$log_handle = fopen($outFile, $write_mode);
		if ($log_handle !== false) {
			fwrite($log_handle, date("Y-m-d H:i:s").": ".$msg."\r\n");
			fclose($log_handle);
		}
	}	

	// Perform a test connection to the TalkBox server
	public function ingeni_tb_test( &$errMsg, $testUrl = '' ) {
		$this->debug = 1;

		$test_url = $this->tb_api_url.'/account';
		if ( strlen($testUrl) > 0 ) {
			$test_url = $testUrl;
		}

		$retJson = $this->ingeni_tb_connect( $test_url, $errMsg, false );

		return $retJson;
	}

	// Find an existing contact
	private function ingeni_tb_find_email( &$errMsg, $email, $testUrl = '' ) {
		$test_url = $this->tb_api_url;
		if ( strlen($testUrl) > 0 ) {
			$test_url = $testUrl;
		}
		if ( stripos($test_url,'/contact/find_first?email=') === false ) {
			$test_url .= '/contact/find_first?email=';
		}

		$retJson = $this->ingeni_tb_connect( $test_url.$email, $errMsg );

		return $retJson;
	}


	// Update and existing Contact, or create a new one if none matches the supplied email address
	public function ingeni_tb_create_update_email( &$errMsg, $email, $first_name, $last_name, $phone = '', $testUrl = '' ) {
		$test_url = $this->tb_api_url;
		if ( strlen($testUrl) > 0 ) {
			$test_url = $testUrl;
		}
		if ( stripos($test_url,'/contact/create_or_update?email=') === false ) {
			$test_url .= '/contact/create_or_update?email=';
		}

		$put_data = array( 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'unsubscribed_email' => 0);
		
		$phone = str_replace(' ','',$phone);
		if ( strlen( $phone ) > 0 ) {
			$put_data = array_merge( $put_data, array('mobile_number' => $phone) );
		}
		
		$put_data_json = json_encode( $put_data );

		$retJson = $this->ingeni_tb_connect( $test_url.$email, $errMsg, true, $put_data_json );

		return $retJson;
	}



	// Connect to the Talkback server
	private function ingeni_tb_connect( $url, &$errMsg, $is_json = false, $put_json = '' ) {
		try {
			$return_json = "";

			$request_headers = [
				'Content-Type:application/json'
			];

			// Remove any double forward slashes, with the exception of the protocol marker
			$url = preg_replace('/(?<!:)\/\/+/', '/', $url);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $this->tb_api_username . ":" . $this->tb_api_password);

			// We're sending JSON back to the Talkbox server
			if ( $put_json ) {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $put_json);
				$is_json = true;
			}

			if ( $is_json ) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
			}


			$this->fb_log('data '. $put_json);

			if (substr($url, 0, 5) == "https") {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			}

			if ($this->debug) {
				$this->fb_log('Connecting to '.$url);
			}
			
			$return_data = curl_exec($ch);

			if (curl_errno($ch)) {
				$errMsg = curl_error($ch);
			} else {
				$return_json = json_decode($return_data, true);
			}

			// Show me the result
			curl_close($ch);

		} catch (Exception $ex) {
			$errMsg = $ex->Message;
		}

		if ($this->debug) {
			$this->fb_log('Received '.print_r($return_json,true));

			if ( $errMsg ) {
				$this->fb_log('Error '.$errMsg);
			}
		}

		return $return_json;
	}
} ?>