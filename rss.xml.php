<?php

    include_once '0_config/config.php';
    
    
    if(!function_exists("__autoload")) {
        function __autoload($class_name) {
            include_once 'source/' . $class_name . '.php';
        }
    }
    
    $get_ownerDB = filter_input(INPUT_GET, 'gob');
    if(!isset($get_ownerDB)){
        echo "<script> top.location.href='http://vmbidder.com?error=gob'</script>";
        die();
    }
    
    if(strlen($get_ownerDB) != 44){
        echo "<script> top.location.href='http://vmbidder.com?error=gob_len'</script>";
        die();
    }
    $ownerDB = Helper::decryptStringArray($get_ownerDB);
     
    if(!$ownerDB){
        echo "<script> top.location.href='http://vmbidder.com?error=gob_usrl'</script>";
        die();
    }

	$database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], $ownerDB, $db['type']);
    //check if we have correct database and connection has been establish
    $dbError = $database->getError();
    if($dbError != null){
       echo "<script> top.location.href='http://vmbidder.com?error=database&message=" . urlencode($database->getError()) . "'</script>";
       die();
    }

    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	$auctions = $database->selectArrayClass('auctions', 'Auction', null, "WHERE endDate > getdate() AND startDate < getdate() AND active = 1");
    $secret = $database->getVar('secret');
?>
<rss version="2.0">
    <channel>
        <title><?php echo $database->getVar('rssTitle'); ?></title>
        <link>https://apps.facebook.com/vmbidder/</link>
        <description><?php echo $database->getVar('rssDesc'); ?></description>

        <?php
            foreach($auctions as $row){
        ?>
            <item>
                <title><?php echo $row->productTitle; ?></title>
                <link><?php echo $row->productTitle; ?></link>
                <guid><?php echo 'https://apps.facebook.com/' . $database->getVar('appId') . '/details?uid=' . Helper::encryptStringArray($row->Id, $secret) . '&amp;gob=' . $get_ownerDB; ?></guid>
                <pubDate><?php echo $row->startDate; ?></pubDate>
                <description><?php echo $row->shortDesc; ?></description>
            </item>
        <?php
            }
        ?>
     
    </channel>
</rss>