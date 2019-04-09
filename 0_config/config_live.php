<?php

/*
 * VM Bidder configuration file
 */

//database connection information
$db = array();
$db['address'] = "tcp:rhwcela4lq.database.windows.net,1433";
$db['username'] = ";
$db['password'] = ""; 
$db['database'] = "vmbidder_live";
$db['type'] = 'mssql';


//this is only diferentiation between development and live
$developerMode = false; 
$showMessages = false;

//server url for main page accesor
$globalServerUrl = 'alpha-vmt.co.uk';
$globalFolder = '/FBA/main/';
$globalServerUrlMobile = 'm.alpha-vmt.co.uk';

