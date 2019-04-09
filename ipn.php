<?php
	include 'source/class.phpmailer.php';
	include 'source/class.smtp.php';    
    include_once '0_config/config.php';

    if(!function_exists("__autoload")) {
        function __autoload($class_name) {
            include_once 'source/' . $class_name . '.php';
        }
    }    
    $raw_post_data = file_get_contents('php://input');

    $get_ownerDB = filter_input(INPUT_GET, 'gob');
    if(!isset($get_ownerDB)){
        header("HTTP/1.0 404 Not Found"); 
        die();
    }
    
    if(strlen($get_ownerDB) != 44){
        header("HTTP/1.0 404 Not Found");
        die();
    }
    $ownerDB = Helper::decryptStringArray($get_ownerDB);    

    if(!$ownerDB){
        header("HTTP/1.0 404 Not Found");
        die();
    }    
    
	$database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], $ownerDB, $db['type']);
	date_default_timezone_set($database->getVar('timeZone'));
    // STEP 1: Read POST data
 
    // reading posted data from directly from $_POST causes serialization 
    // issues with array data in POST
    // reading raw POST data from input stream instead. 
    $raw_post_array = explode('&', $raw_post_data);
    $myPost = array();
    foreach ($raw_post_array as $keyval) {
        $keyval = explode ('=', $keyval);
        if(count($keyval) == 2){
            $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
    }
    
    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-validate';
    if(function_exists('get_magic_quotes_gpc')) {
        $get_magic_quotes_exists = true;
    } 
    foreach ($myPost as $key => $value) {        
        if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
            $value = urlencode(stripslashes($value)); 
        } else {
            $value = urlencode($value);
        }
        $req .= "&$key=$value";
    }
 
    
    // STEP 2: Post IPN data back to paypal to validate
 
    //based on database settings switch from sandbox to live ipn connections
    //based on database this is synchronised with payment button creation
    $curl_address = "https://www.paypal.com/cgi-bin/webscr";
	if($database->getVar("paypalSandbox") == "yes") {
        $curl_address = "https://www.sandbox.paypal.com/cgi-bin/webscr";		
    }

    $ch = curl_init($curl_address);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
    // In wamp like environments that do not come bundled with root authority certificates,
    // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
    // of the certificate as shown below.
    // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
    if( !($res = curl_exec($ch)) ) {
		$database->mainLog("Got " . curl_error($ch) . " when processing IPN data");
        curl_close($ch);
        exit;
    }
    curl_close($ch);
 

    // STEP 3: Inspect IPN validation result and act accordingly

    if (strcmp ($res, "VERIFIED") == 0) {
        // check whether the payment_status is Completed
        // check that txn_id has not been previously processed
        // check that receiver_email is your Primary PayPal email
        // check that payment_amount/payment_currency are correct
        // process payment
 
        $error = false;

        //1. Get auction for which payment is made
		$a = $database->selectSingleClass('auctions', 'Auction', array('Id' => $_POST['item_number']));

        if($a->status == "00"){
            $a->status = "01";
        }
        
		$database->update('auctions', array('status' => $a->status), array('Id' => $a->Id));
                
        $message = "PLEASE ENSURE YOU DOUBLE CHECK WITH PAYPAL IF PAYMENT IS CORRECT \r\n<br>";
        $message .= "Amount: " . $_POST['mc_gross'] . "\r\n<br>";
        $message .= "Item: " . $_POST['item_name'] . "\r\n<br>";
        $message .= "Auction id: " . $_POST['item_number'] . "\r\n<br>";
        $message .= "Name: " . $_POST['first_name'] . " " . $_POST['last_name'] . "\r\n<br>";
        $message .= "Paypal email: " . $_POST['payer_email'] . "\r\n<br>";
        $message .= "Payment date: " . $_POST['payment_date'] . "\r\n<br>";
        
        $message .= "Address recipient: " . $_POST['address_name'] . "\r\n<br>";
        $message .= "Address: " . $_POST['address_street'] . "\r\n<br>";
        $message .= "Postcode: " . $_POST['address_zip'] . "\r\n<br>";
        $message .= "City: " . $_POST['address_city'] . "\r\n<br>";
        $message .= "State: " . $_POST['address_state'] . "\r\n<br>";
        $message .= "Country: " . $_POST['address_country'] . "\r\n<br>";
        
            
        $email = $database->getVar("paypalConfirmationEmail");
        
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = '127.0.0.1';
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'notification@vmbidder.com';                 // SMTP username
		$mail->Password = 'dupamaryny';                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to			
		$mail->From = 'notification@vmbidder.com';
		$mail->Subject = 'VM Bidder payment notification';			
		$mail->FromName = 'VM Bidder';
		$mail->addReplyTo('notification@vmbidder.com');
		$mail->addAddress($email);     // Add a recipient					
		$mail->Body = html_entity_decode($message);	
		$mail->isHTML(true); 
		if(!$mail->send()) {
			$error = 'Mailer Error: ' . $mail->ErrorInfo . print_r($_POST);
			$database->mainLog($error);
		} 	

    } else if (strcmp ($res, "INVALID") == 0) {
        $message = "WARNING!!! INVALID PAYPAL PAYMENT CONFIRMATION \r\n<br>";
        $message .= "Amount: " . $_POST['mc_gross'] . "\r\n<br>";
        $message .= "Item: " . $_POST['item_name'] . "\r\n<br>";
        $message .= "Auction id: " . $_POST['item_number'] . "\r\n<br>";
        $message .= "Name: " . $_POST['first_name'] . " " . $_POST['last_name'] . "\r\n<br>";
        $message .= "Paypal email: " . $_POST['payer_email'] . "\r\n<br>";
        $message .= "Payment date: " . $_POST['payment_date'] . "\r\n<br>";
        
        $message .= "Address recipient: " . $_POST['address_name'] . "\r\n<br>";
        $message .= "Address: " . $_POST['address_street'] . "\r\n<br>";
        $message .= "Postcode: " . $_POST['address_zip'] . "\r\n<br>";
        $message .= "City: " . $_POST['address_city'] . "\r\n<br>";
        $message .= "State: " . $_POST['address_state'] . "\r\n<br>";
        $message .= "Country: " . $_POST['address_country'] . "\r\n<br>";
        
            
		$email = $database->getVar("paypalConfirmationEmail");
        
        $mailheader = "From: " . $email . "\r\n";
        $mailheader .= "Reply-To: " . $email . "\r\n";
        $mailheader .= "Content-type: text/html; charset=iso-8859-1\r\n";
    
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = '127.0.0.1';
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'notification@vmbidder.com';                 // SMTP username
		$mail->Password = 'dupamaryny';                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to			
		$mail->From = 'notification@vmbidder.com';
		$mail->Subject = 'VM Bidder payment notification';			
		$mail->FromName = 'VM Bidder';
		$mail->addReplyTo('notification@vmbidder.com');
		$mail->addAddress($email);     // Add a recipient					
		$mail->Body = html_entity_decode($message);	
		$mail->isHTML(true); 
		if(!$mail->send()) {
			$error = 'Mailer Error: ' . $mail->ErrorInfo . print_r($_POST);
			$database->mainLog($error);
		} 	
	
    }

