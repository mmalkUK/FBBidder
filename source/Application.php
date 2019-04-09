<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application
 *
 * @author mmalicki
 */
class Application {
    public $appId;
    public $secret;
    
    public $canvasUrl;
    public $fbUrl = "https://apps.facebook.com/";
    
    public $language = "EN";
    public $currencyPreffix;
    public $currencySuffix;
    public $timeZone;
    
    public $planName;
    public $planLiveAuctions;
    public $planAdminAccounts;
    public $planCSS;
    public $expiryDate;
    public $plan_pro;
    
    public $logoLink;
    public $logoAlt;
	public $logoPic;
	
    public $oneCategory;
    public $twitterLink;
    public $facebookLink;
    public $googleTracking;
    public $likedNS;
    
    public $winEmailFrom;
    public $winEmailSubject;
    public $winEmailBody;
    
    public $outbidEmailFrom;
    public $outbidEmailSubject;
    public $outbidEmailBody;

    public $paypalemail;
    public $currency;
    public $vat;
    public $location;
    public $paypalSandbox;

    public $ownerDB;
    
    public $popcornAuction;
    public $popcornExtendTime;
    
    public $debugMode;
    public $pageTitle;
    public $fbObject;
	
	public $notification_outbid;
	public $theme_colour;

    
    public function __construct(&$db, $ownerDB = null) {        
        if($ownerDB != null){
            $this->ownerDB = $ownerDB;
        }else{
            $this->ownerDB = "error";
        }
        //get all objects from class and fill data from database
        foreach (get_object_vars($this) as $key => $value) {
            //check if there is no value already assigned to the class objects
            if($value == null){
                //fill data from database
                $this->{$key} = $db->getVar($key);
            }
        }
        
        //buld facebook Url
		$this->fbUrl .= $this->fbObject . '/';
        //$this->fbUrl .= $this->appId . '/';
    }
    
}
