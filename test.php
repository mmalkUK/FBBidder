//<?php
//
///* 
// * To change this license header, choose License Headers in Project Properties.
// * To change this template file, choose Tools | Templates
// * and open the template in the editor.
// */
//
//if(!function_exists("__autoload")) {
//    function __autoload($class_name) {
//        include_once 'source/' . $class_name . '.php';
//    }
//}
//
//include_once('0_config/config.php');
//
//$database = new MDatabase($db['address'], $db['username'], $db['password'], 'fba_development');
//
//
////$application = new Application($database, 'fba_development');
//
////echo $dba->insertRow('newsletter', array('email' => 'mmalkbham@googlemail.com'));
//
////echo $dba->checkIfExist('newsletter', array('email' => 'mmalkbham@googlemail.com'));
//
////$u = new User();
//
////$u = $dba->selectArrayClass('auctions', 'Auction', null, "WHERE endDate > now() AND startDate < now() AND active = '1'");
//
////print_r($u);
//
////echo $dba->update('users', array('fbSEX' => 'male'), array('fbSEX' => 'dupa'));
//
////$u = $dba->selectSingleClass('users', 'User', array('id' => '2'));
//
////print_r($u);
//
////$bid = new Bid();
////$bid = $dba->selectArrayClass('bids', 'Bid', array('auction' => '99'), null, 'ORDER BY bid DESC LIMIT 1');
////echo 'test';
////print_r($bid);
////
////if($bid == null) {echo 'null';}
//
////echo $dba->getField('language', 'translation', 'variable', 'ok');
//
////                 $auction = $database->selectSingleClass('auctions', 'Auction', array('id' => '5'), $application->secret ) ;                 $auction->getHighestBid($database);
////                 $auction->getEndPrice($database);
////                 echo $auction->endPrice;
////                 $emailClass = new Email($auction, $application, 'win');
////                 
////                 echo $emailClass->sendEmail();
////                 echo $emailClass->body;
////                 echo $emailClass->toEmail;
////                 print_r($auction->winner);
//
////$dashboard= new Dashboard();
////print_r($dashboard->buildAll($database));
////
////
////$x = Helper::encryptStringArray('12');
////echo $x;
////echo "<br>";
////echo Helper::decryptStringArray($x);
//
////$cat = $database->selectArrayClass('category', 'Category');
////print_r($cat);
//
////$auction = $database->selectSingleClass('auctions', 'Auction', array('id' => '7'));
////print_r($auction);
////echo "UPDATE auctions SET endDate=DATE_ADD(endDate, INTERVAL $application->popcornExtendTime SECOND) WHERE id = '" . $auction->id . "'";
////
////$database->runQuery("UPDATE auctions SET endDate=DATE_ADD(endDate, INTERVAL $application->popcornExtendTime SECOND) WHERE id = $auction->id", true);
////
////$auction = $database->selectSingleClass('auctions', 'Auction', array('id' => '7'));
////print_r($auction);
////
////echo 'dupa:' . $auction->endDate;
//echo "start<br>";
////echo $database->getError() . '1' . $database->error;
////if ($database->getError()){
////    //echo "<script> top.location.href='http://fbbidder.com?error=database&message=" . urlencode($database->getError()) . "'</script>";
////}
////
////$x = Helper::encryptStringArray("fba_development");
////echo $x;
//
//date_default_timezone_set('UTC');
//
//$auction = $database->selectSingleClass('auctions', 'Auction', array('id' => '2'));
//
//$end = new DateTime($auction->endDate);
//$start = new DateTime($auction->startDate);
//$diff2now = $start->diff(new DateTime(date('Y-m-d H:i:s')))->days;
//$diff2start = $end->diff($start)->days;
//
//
//echo '<br>star date:' . $start->format('Y-m-d H:i:s');
//$start->add(new DateInterval('P' . $diff2now . 'D'));
//echo '<br>star date:' . $start->format('Y-m-d H:i:s');
//$int =  $diff2start + $diff2now;
//echo '<br>end date:' . $end->format('Y-m-d H:i:s');
//$end->add(new DateInterval('P' . $int . 'D'));
//echo '<br>end date:' . $end->format('Y-m-d H:i:s');
//
//

$query = "SELECT dupa FROM cialo order by cos LIMIT 212331a";
$newQuery = $query;
$posLimit = strpos($query, "LIMIT ");
if($posLimit > 1){
	$queryLen = strlen($query);

	$cut = substr($query, 7, $posLimit - 7);

	$limit = substr($query, $posLimit+6);

	$top = "TOP $limit";

	$newQuery = "SELECT $top $cut";
}else{
	echo 'nolimit';
}

echo $newQuery;