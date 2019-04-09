<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(!function_exists("__autoload")) {
    function __autoload($class_name) {
        include_once 'source/' . $class_name . '.php';
    }
}

include_once('0_config/config.php');

$database = new MDatabase($db['address'], $db['username'], $db['password'], 'fba_development');


$application = new Application($database, 'fba_development');

$database->update('auctions', array());