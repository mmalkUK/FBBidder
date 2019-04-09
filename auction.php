<?php
/**
 * Front end details for bidding application
 * 
 * @author Marcin Malicki <mmalicki@vmtrading.co.uk>
 * @version 3.0
 */

    include_once '0_config/config.php';
?>

<?php

    //autoload classes when needed
    if(!function_exists("__autoload")) {
        function __autoload($class_name) {
            include_once 'source/' . $class_name . '.php';
        }
    }
    
    //don't stop as default. will be changed if app needs to be stopped
    $stop = false;
    
    //get database name from ur;
    $get_ownerDB = filter_input(INPUT_GET, 'gob');
    
    $is_gob_main = false;
    
    //check if database name has been provided with url
    if(!isset($get_ownerDB)){
        $is_gob_main = true;
    }
    
    //check if gob has correct length
    if(strlen($get_ownerDB) != 44 && $is_gob_main == false){
        $is_gob_main = true;
    }    
    
    if($is_gob_main== true){
        if($developerMode){
            $get_ownerDB = 'Himj-WkH9GDs0pkmbFuHoovgYrknvp-b-Z6OUvvp1hE,';  // for development enviroment
        }else{
            $get_ownerDB ='csfS0bJ3mCDUhN5AdijH67QfzvQ_Mx_XLu743qCkFHs,'; // for live enviroment
        }              
    }
    
    //decode database name from url
    $ownerDB = Helper::decryptStringArray($get_ownerDB);    

    //check if unserialize worked fine
    if(!$ownerDB){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=gob_usrl'</script>";
        }else{
            echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
        }
        die();
    }    
    
    //create instance of database class which will be used for all db requests
    $database = new MDatabase($db['address'], $db['username'], $db['password'], $ownerDB);
    
    //check if we have correct database and connection has been establish
    if($database->getError()){
        if($application->debugMode == 1){
            echo "<script> top.location.href='http://vmbidder.com?error=database&message=" . urlencode($database->getError()) . "'</script>";
            die();
        }else{
            echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
            die();
        }
    }
    
    //create instance of translation class which holds all front end translation
    $language = new Translation($database);
    
    //create instance of Application class which holds all app variables
    $application = new Application($database, $ownerDB);     
    
    //get auction id from url
    $get_uid = filter_input(INPUT_GET, 'uid'); 
    
    //check if auction id has been privided via url
    if(!isset($get_uid)){
        echo "<script> top.location.href='" . $application->fbUrl . "'</script>";
        $stop = true;
        die();
    }
    
    //get auction from database
    $auction = $database->selectSingleClass('auctions', 'Auction', array('id' => Helper::decryptStringArray($get_uid, $application->secret)));
    
    //check if auction has been loaded/exist
    if($auction == null){
        $stop = true;
        echo "<script> top.location.href='" . $application->fbUrl . "'</script>";
        die();
    }
    
    //generate Facebook object tags for this auction
?>
<html>
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php echo $application->fbObject; ?>: http://ogp.me/ns/fb/<?php echo $application->fbObject; ?>#">
    <title><?php echo $application->pageTitle; ?></title>
    <meta property="og:type"   content="<?php echo $application->fbObject; ?>:auction" /> 
    <meta property="fb:app_id" content="<?php echo $application->appId; ?>" /> 
    <meta property="og:url"    content="<?php echo $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB; ?>" /> 
    <meta property="og:title"  content="<?php echo $auction->productTitle; ?>" /> 
    <meta property="og:image"  content="<?php echo $auction->picturePath; ?>" />
    <meta property="og:description" content="<?php echo $auction->shortDesc; ?>" />

