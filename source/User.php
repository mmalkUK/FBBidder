<?php

/**
 * Description of User
 *
 * @author mmalicki
 */
class User {
    public $Id;
    public $fbId;
    public $fbName;
    public $fbEmail;
    public $fbDOB;
    public $active;
    public $banned;
    public $fbSEX;
    public $isAdmin;
    public $firstUseDate;
    public $token;
	public $instanceId;
    
    public function __construct($profile = null) {
        if($profile != null){
            $this->fbId = $profile['id'];
            $this->fbName = $profile['name'];
            if(isset($profile['email'])){
                $this->fbEmail = $profile['email'];
            }else{
                $this->fbEmail = "";
            }
            if(isset($profile['birthday'])){
                $this->fbDOB = $profile['birthday'];
            }else{
                $this->fbDOB = '1901-01-01';
            }
            $this->active = 1;
            $this->banned = 0;
            $this->fbSEX = $profile['gender'];
            $this->isAdmin = 0;
            $this->token = '';
        }
    }
    
    public function buildData($ommit = null){
        $data = get_object_vars($this);
        if($data != null){
            foreach($ommit as $row){
                unset($data[$row]);
            }
        }
        return $data;
    }  
	  
}
