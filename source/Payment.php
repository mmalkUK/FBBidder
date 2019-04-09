<?php

/**
 * Description of Payment
 *
 * This class will handle payment link or form generation for paypal
 * 
 * @author mmalicki
 */
class Payment {
        //https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/
    
    //if sandbox is set to true than connection will be used only for test purposes
    public $sandbox;
    
    /*  Types of buttons and they descriptions:
     *  _xclick                 The button that the person clicked was a Buy Now button.
     *  _oe-gift-certificate 	The button that the person clicked was a Buy Gift Certificate button.
     *  _xclick-subscriptions 	The button that the person clicked was a Subscribe button.
     *  _xclick-auto-billing 	The button that the person clicked was an Automatic Billing button.
     *  _xclick-payment-plan 	The button that the person clicked was an Installment Plan button.
     *  _donations              The button that the person clicked was a Donate button. 
     * 
     */
    public $cmd = "_xclick";
    
    /*
     * Set based on sandbox mode. If sandbox = yes than use sandbox address for payments (test) or
     * if is false use live address
     * This variable is set in constructor
     */
    private $connectionAddress;
    
    /*
     * Set via constructor. This is email address or code under which payment will be made
     */
    private $business;
    
    /*
     * The locale of the login or sign-up page, which may have the specific country's language available, depending on 
     * localization. If unspecified, PayPal determines the locale by using a cookie in the subscriber's browser. 
     * If there is no PayPal cookie, the default locale is US.
     * The following two-character country codes are supported by PayPal:
     * AU – Australia
     * AT – Austria
     * BE – Belgium
     * BR – Brazil
     * CA – Canada
     * CH – Switzerland
     * CN – China
     * DE – Germany
     * ES – Spain
     * GB – United Kingdom
     * FR – France
     * IT – Italy
     * NL – Netherlands
     * PL – Poland
     * PT – Portugal
     * RU – Russia
     * US – United States
     * The following 5-character codes are also supported for languages in specific countries:
     * da_DK – Danish (for Denmark only)
     * he_IL – Hebrew (all)
     * id_ID – Indonesian (for Indonesia only)
     * ja_JP – Japanese (for Japan only)
     * no_NO – Norwegian (for Norway only)
     * pt_BR – Brazilian Portuguese (for Portugal and Brazil only)
     * ru_RU – Russian (for Lithuania, Latvia, and Ukraine only)
     * sv_SE – Swedish (for Sweden only)
     * th_TH – Thai (for Thailand only)
     * tr_TR – Turkish (for Turkey only)
     * zh_CN – Simplified Chinese (for China only)
     * zh_HK – Traditional Chinese (for Hong Kong only)
     * zh_TW – Traditional Chinese (for Taiwan only)
     */
    private $locale;
    
    /*
     * The currency of the payment. The default is USD. 
     */ 
    private $currency_code;
    
    
    /*
     * Do not prompt buyers for a shipping address. Allowable values are:
     * 0 – prompt for an address, but do not require one
     * 1 – do not prompt for an address
     * 2 – prompt for an address, and require one
     */
    private $no_shipping = "2";
    
    /*
     * return
     * The URL to which PayPal redirects buyers' browser after they complete their payments. For example, 
     * specify a URL on your site that displays a "Thank you for your payment" page.
     * Default – PayPal redirects the browser to a PayPal webpage
     */
    private $return_URL = "";
    
    
    /*
     * cancel_return
     * A URL to which PayPal redirects the buyers' browsers if they cancel checkout before completing 
     * their payments. For example, specify a URL on your website that displays a "Payment Canceled" page.
     * Default – PayPal redirects the browser to a PayPal webpage.
     */
    private $cancel_return = "";
    
    /*
     * Transaction-based tax override variable. Set this variable to a percentage that applies to the amount 
     * multiplied by the quantity selected during checkout. This value overrides any tax settings set in your account 
     * profile. Allowable values are numbers 0.001 through 100. Valid only for Buy Now and Add to Cart buttons. 
     * Default – Profile tax settings, if any, apply. 
     */
    private $tax_rate = 0;
    
    /*
     * The URL to which PayPal posts information about the payment, in the form of Instant Payment Notification messages.
     */
    private $notify_url = "";
    
    
    public function __construct($sandbox, $business, $locale, $currency_code, $return_URL = "", $cancel_return = "", $tax_rate = 0, $notify_url = ""){
        if($sandbox == "yes"){
            $this->sandbox = true;
            $this->connectionAddress = "https://www.sandbox.paypal.com";
        }else{
            $this->sandbox = false;
            $this->connectionAddress = "https://www.paypal.com";
        } 
        $this->business = $business;
        $this->locale = $locale;
        $this->currency_code = $currency_code;
        
        if($return_URL != ""){
            $this->return_URL = $return_URL;
        }
        if($cancel_return != ""){
            $this->cancel_return = $cancel_return;
        }
        
        if($tax_rate != 0){
            $this->tax_rate = $tax_rate;
        }
        
        if($notify_url != ""){
            $this->notify_url = $notify_url;
        }
    }
    
    /*
     * This function will generate paypal payment button as a URL link 
     */
    public function getLinkBuyNowButton($item_name, $item_number, $qty, $amount, $pp, $custom = ""){
            $code = $this->connectionAddress . "/cgi-bin/webscr?cmd=_xclick&business=" . urlencode($this->business) . 
                    "&lc=" . urlencode($this->locale) . 
                    "&item_name=". urlencode($item_name) . 
                    "&item_number=" . urlencode($item_number) . 
                    "&amount=" . urlencode($amount) . 
                    "&shipping=" . urlencode($pp) . 
                    "&quantity=" . urlencode($qty) . 
                    "&currency_code=" . urlencode($this->currency_code) . 
                    "&no_note=1&no_shipping=" . urlencode($this->no_shipping) . 
                    "&rm=2&return=" . urlencode($this->return_URL) . 
                    "&cancel_return=" . urlencode($this->cancel_return) . 
                    "&bn=PP%2dBuyNowBF%3abtn_buynowCC_LG%2egif%3aNonHosted&custom=" . urlencode($custom) . 
                    "&tax_rate=" . urlencode($this->tax_rate) . 
                    "&notify_url=" . urlencode($this->notify_url);            
            return $code;
    }
}
