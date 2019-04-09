<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Translation
 *
 * @author Marcin
 */
class Translation {
    public $actual_bid;
    public $auction_end;
    public $aouth_message;
    public $bid_now;
    public $buy_it_now;
    public $brand;
    public $bidding_time;
    public $bidding_date;
    public $bid;
    public $confirmation;
    public $description;
    public $enter_your_email;
    public $newsletter_confirmation;
    public $e_correct_bid;
    public $e_minimum_bid;
    public $e_auction_ended;
    public $go_back;
    public $min;
    public $name;
    public $p_p;
    public $please_enter_your_bid;
    public $our_latest_finished_auctions;
    public $t_days;
    public $t_hours;
    public $t_minutes;
    public $t_seconds;
    public $buy_it_message;
    public $buy_it_now_title;
    public $buy_it_now_button_confirm;
    public $buy_it_now_button_cancel;
    public $buy_it_message_after_ok;
    public $buy_it_message_after_error;
    public $ok;
    public $reserve_not_meet;
	public $wall_outbid;
	public $wall_win;
	public $photo;
	public $e_not_allowed;
	public $e_fan;
	public $f_aboutus;
	public $f_delivery;
	public $f_tandc;
	public $missing_email;

    public function __construct(&$db, $original = false) {
        //get all objects from class and fill data from database
        foreach (get_object_vars($this) as $key => $value) {
            //check if there is no value already assigned to the class objects
            if($value == null && $original == false){
                //fill data from database
				$this->{$key} = $db->getField('dbo.language', 'translation', 'variable', $key);
            }
            if($value == null && $original == true){
                //fill data from database
				$this->{$key} = $db->getField('dbo.language', 'translation', 'variable', 't_org_' . $key, true);
            }            
        }
    }
    
}
