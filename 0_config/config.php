<?php

/*
 * VM Bidder configuration file
 */

//database connection information
$db = array();
$db['address'] = "tcp:e46m6jeaqx.database.windows.net,1433"; //tcp:rhwcela4lq.database.windows.net,1433
$db['username'] = ""; //vmbidder_live //
$db['password'] = ""; //myF&yhLblH1F+Sv
$db['database'] = "vmbidder";
$db['type'] = 'mssql';


//this is only diferentiation between development and live
$developerMode = true; 
$showMessages = false;

//server url for main page accesor
$globalServerUrl = 'alpha-vmt.cloudapp.net';
$globalFolder = '/FBA/development/';
$globalServerUrlMobile = 'm.alpha-vmt.cloudapp.net';

