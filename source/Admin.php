<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Admin
 *
 * @author mmalicki
 */
class Admin {
    public $Id;
    public $name;
    public $email;
    private $password;
    public $instanceId;
	
	public function getPassword(){
        return $this->password;
    }
	
	public function clearPassword(){
		$this->password = null;
	}
}
