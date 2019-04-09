<?php

include "source/frontEndFunctions.php";
include "source/commonFunctions.php";

echo "<html lang=\"en\"><head></head><body>";



$separator = "</br>---------------------</br>";


echo "Test Unit </br>";
echo $_GET['cmd'];
echo $separator;



$business = "paypal-developer@vmtrading.co.uk";
$sandbox = true;
$locale = "GB";
$currency_code = "GBP";

$p = new Payment($sandbox, $business, $locale, $currency_code, "", "", 20, "http://alpha-vmt.co.uk/FBA_2_0/development/ipn.php");


$code =  $p->getLinkBuyNowButton("Test Product Item Name", "9991212", 1, 9.99, 1.99);

echo "<a href=\"" . $code ."\">" . $code . "</a>"; 


echo $separator;
echo "</body></html>";