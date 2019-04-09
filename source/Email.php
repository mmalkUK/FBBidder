<?php



/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Email
 *
 * @author Marcin
 */
class Email {
    public $type;
    //to be changed to private
    private $toEmail;
    private $fromEmail;
    private $subject;
    private $body;
    private $mailHeader;
    
    private $auction;
    private $application;
    private $gob;
	
	private $usePHPMailer;
	private $mail;



	public function __construct($auction, $application, $type , $gob, $usePHPMailer = false, $fromEmail = true) {
		$this->type = $type;        
        $this->gob = $gob;
		$this->usePHPMailer = $usePHPMailer;
        
		if($this->type == 'win'){
            $this->fromEmail = $application->winEmailFrom;
            $this->subject = $application->winEmailSubject;
            $this->body = $application->winEmailBody;
            $this->toEmail = $auction->winner->fbEmail;
        }else if($this->type == 'outbid'){
            $this->fromEmail = $application->outbidEmailFrom;
            $this->subject = $application->outbidEmailSubject;
            $this->body = $application->outbidEmailBody;
            if($auction->looser != null){
                $this->toEmail = $auction->looser->fbEmail;            
            }
        }
        
        $this->auction = $auction;
        $this->application = $application;
		if($fromEmail == true){
			$this->fromEmail = 'notification@vmbidder.com';	
		}
        $this->payment = new Payment($application->paypalSandbox, $application->paypalemail, $application->location, $application->currency, "", "", $application->vat, $application->canvasUrl . "ipn.php?gob=" . $gob);
		$this->buildHeader();
		if($usePHPMailer){
			$this->mail = new PHPMailer;			
			//$this->mail->SMTPDebug = 3; 
			$this->mail->isSMTP();
			$this->mail->Host = '127.0.0.1';
			$this->mail->SMTPAuth = true;                               // Enable SMTP authentication
			$this->mail->Username = 'notification@vmbidder.com';                 // SMTP username
			$this->mail->Password = 'dupamaryny';                           // SMTP password
			//$this->mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$this->mail->Port = 587;                                    // TCP port to connect to			
			$this->mail->From = $this->fromEmail;
			$this->mail->Subject = $this->subject;			
			$this->mail->FromName = $this->fromEmail;
			$this->mail->addReplyTo($this->fromEmail);
			if($auction->looser != null && $this->type == 'outbid'){
				$this->mail->addAddress($this->toEmail);     // Add a recipient					
			}else{
				$this->mail->addAddress($this->toEmail);
			}
		}
    }
    
    private function buildHeader(){
	$this->mailHeader = "From: " . $this->fromEmail . "\r\n";
	$this->mailHeader .= "Reply-To: " . $this->fromEmail . "\r\n";
	$this->mailHeader .= "Content-type: text/html; charset=iso-8859-1\r\n";        
    }
    
    public function sendEmail(){
        if($this->type == "outbid"){
            if($this->auction->looser == null){
                return false;
            }
        }
        $this->convertTags();
        return $this->send();
    }   
    
    private function send(){
		if($this->usePHPMailer){
			$this->mail->Body = html_entity_decode($this->body);	
			$this->mail->isHTML(true);   
			
			if(!$this->mail->send()) {
				return 'Mailer Error: ' . $this->mail->ErrorInfo;
			} else {
				return 'Message has been sent';
			}						
		}else{
			return mail($this->toEmail, $this->subject, $this->body, $this->mailHeader) or die ("Failure");
		}
    }

    private function convertTags(){
        
        if($this->type == 'win'){
            $firstName = explode(" ", $this->auction->winner->fbName);
			$payButton = $this->payment->getLinkBuyNowButton($this->auction->productTitle, $this->auction->Id, 1, number_format( ($this->auction->finalPrice/(1 + number_format($this->application->vat,2)/100)),2), $this->auction->postage);   
            $this->body = str_replace("[(payButton)]", $payButton , $this->body);
            $this->body = str_replace("[(name)]", $this->auction->winner->fbName, $this->body);
            $this->body = str_replace("[(firstName)]", $firstName[0], $this->body);
	
            $this->body = str_replace("[(newPrice)]", $this->application->currencyPreffix . $this->auction->finalPrice . $this->application->currencySuffix, $this->body);
        }else{
            $firstName = explode(" ", $this->auction->looser->fbName);
            $this->body = str_replace("[(name)]", $this->auction->looser->fbName, $this->body);
            $this->body = str_replace("[(firstName)]", $firstName[0], $this->body);
            //$this->body = str_replace("[(payButton)]", $payButton , $this->body);
	
            $this->body = str_replace("[(newPrice)]", $this->application->currencyPreffix . $this->auction->highestBid . $this->application->currencySuffix, $this->body);            
        }
        
        $this->body = str_replace("[(auctionTitle)]", $this->auction->productTitle, $this->body);
		$this->body = str_replace("[(link)]", $this->application->fbUrl . "details?uid=" . urlencode(Helper::encryptStringArray($this->auction->Id, $this->application->secret)) . "&gob=" . urlencode($this->gob) , $this->body);	        
        $this->body = str_replace("[(shortDesc)]", $this->auction->shortDesc, $this->body);
    }
}
