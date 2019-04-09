<?php
/**
 * Front end details for bidding application
 * 
 * @author Marcin Malicki <mmalicki@vmtrading.co.uk>
 * @version 3.0
 */
	include 'source/class.phpmailer.php';
	include 'source/class.smtp.php';
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
			$get_ownerDB = 'i-PgGbzHZFA7M8hMtUXTRycUNrzPx9tEbk4OkcPsC5E,';  // for development enviroment
        }else{
			$get_ownerDB ='AIoCLcaWXU-7F-MWCdYC3BHXQjOjhjjOCXxIgbcLB0k,'; // for live enviroment
        }              
    }
    
    //decode database name from url
	$ownerDB = Helper::decryptStringArray(urldecode($get_ownerDB));    

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
	$database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], $ownerDB, $db['type']);
    
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

	//check if we have valid instance
	if($application->fbObject == "" && $application->planName == ""){
		if($developerMode){
			echo "<script> top.location.href='http://vmbidder.com?error=GOBdoesntexist&message=" . urlencode($database->getError()) . "'</script>";
		}else{
			echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
		}
		die();		
	} 
   
    //get auction id from url
    $get_uid = filter_input(INPUT_GET, 'uid'); 
    
    //check if auction id has been privided via url
    if(!isset($get_uid)){
        echo "<script> top.location.href='" . $application->fbUrl . "'</script>";
        $stop = true;
        die();
    }
    
    //get auction from database
	$auction = $database->selectSingleClass('auctions', 'Auction', array('Id' => Helper::decryptStringArray($get_uid, $application->secret)));
	$endedAuctions = $database->selectArrayClass('auctions', 'Auction', null, "WHERE endDate < getdate() AND active <> '0' AND instanceId='" . $ownerDB . "' ORDER BY endDate DESC LIMIT 3");    
    //check if auction has been loaded/exist
    if($auction == null){
        $stop = true;
        echo "<script> top.location.href='" . $application->fbUrl . "'</script>";
        die();
    }
    
    //generate Facebook object tags for this auction
?>
<!DOCTYPE HTML>
<html>
    <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# <?php echo $application->fbObject; ?>: http://ogp.me/ns/fb/<?php echo $application->fbObject; ?>#">
    <meta property="og:type"   content="<?php echo $application->fbObject; ?>:auction" /> 
    <meta property="fb:app_id" content="<?php echo $application->appId; ?>" /> 
    <meta property="og:url"    content="<?php echo $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB; ?>" /> 
    <meta property="og:title"  content="<?php echo $auction->productTitle; ?>" /> 
    <meta property="og:image"  content="<?php echo 'https://' . $globalServerUrl . $globalFolder . $auction->picture; ?>" />
    <meta property="og:description" content="<?php echo $auction->shortDesc; ?>" />
	<?php include('head.php'); ?>
<?php    
    //display "debug" mode if app is in debug mode    
    if($application->debugMode == 1 && $showMessages == true){
        echo '<pre>DEBUG MODE</pre>';
    }
    
    //set time zone
    date_default_timezone_set($application->timeZone);
    
    //check if application been paid for
    if(strtotime($application->expiryDate) < time()){
        if($application->debugMode == 1){
            echo "<script> top.location.href='http://vmbidder.com?error=expiry_date&message=" . $application->expiryDate . "'</script>"; 
            die();
        }else{
            echo "<script> top.location.href='http://vmbidder.com'</script>";             
            die();
        }
    }
    
    //start session
    session_start();

    //include all Facebook PHP SDK 4
    require_once( 'Facebook/FacebookSession.php' );
    require_once( 'Facebook/FacebookRedirectLoginHelper.php' );
    require_once( 'Facebook/FacebookCanvasLoginHelper.php' );
    require_once( 'Facebook/FacebookRequest.php' );
    require_once( 'Facebook/FacebookResponse.php' );
    require_once( 'Facebook/FacebookSDKException.php' );
    require_once( 'Facebook/FacebookRequestException.php' );
    require_once( 'Facebook/FacebookOtherException.php' );
    require_once( 'Facebook/FacebookAuthorizationException.php' );
    require_once( 'Facebook/GraphObject.php' );
    require_once( 'Facebook/GraphSessionInfo.php' );
    require_once( 'Facebook/FacebookHttpable.php' );
    require_once( 'Facebook/FacebookCurl.php' );
    require_once( 'Facebook/FacebookCurlHttpClient.php' );
    require_once( 'Facebook/FacebookServerException.php' );
	//require_once( 'Facebook/FacebookPermissionException.php' );
    
    //set namespaces for Facebook PHP SDK 4
    use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookCanvasLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\FacebookResponse;
    use Facebook\FacebookSDKException;
    use Facebook\FacebookRequestException;
    use Facebook\FacebookOtherException;
    use Facebook\FacebookAuthorizationException;
    use Facebook\GraphObject;
    use Facebook\GraphSessionInfo;
    use Facebook\FacebookHttpable;
    use Facebook\FacebookCurl;
    use Facebook\FacebookCurlHttpClient;
    use Facebook\FacebookServerException;
	//use Facebook\FacebookPermissionException;
    
    //set bidding application as default application
    FacebookSession::setDefaultApplication($application->appId, $application->secret);
    
    //create instance of class responsible for login redirection
    $helperRedirect = new FacebookRedirectLoginHelper($application->fbUrl . '?gob=' . $get_ownerDB);
    
    //try to establish session from redirection
    try {
        $session = $helperRedirect->getSessionFromRedirect();
    } catch(FacebookRequestException $ex) {
        // When Facebook returns an error
        $database->mainLog($ex->getMessage());
    } catch(\Exception $ex) {
        // When validation fails or other local issues
        $database->mainLog($ex->getMessage());
    }    
    
    //check if session has been created from redirection. If not try to get session from canvas login
    if(!isset($session)){
        $helper = new FacebookCanvasLoginHelper();
        try {
            $session = $helper->getSession();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        }        
    }        

    //if there is still not session redirect user to login url. Also set basic html page for facebook when they try to get
    //story object information
    if(!isset($session)){
        // login helper with redirect_uri
        echo "</head><body>";
        echo "<script> top.location.href='" . $helperRedirect->getLoginUrl( array( 'email', 'publish_actions' ) ) . "'</script>"; 
        echo "</body></html>";
        die();
    }
    
    //if session has been created continue with page if not display default error page
    if ($session) {
        
        //get actual user derails from facebook
        $request = new FacebookRequest( $session, 'GET', '/me' );
        try{
            $response = $request->execute();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        } 
        
        //get user profile
        try{
            $profile = $response->getGraphObject()->asArray();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        }  
        
        //if app is in debug mode display actual user profile
        if($application->debugMode == 1 && $showMessages == true){
            echo '<pre>FB User Profile</pre>';
            echo '<pre>' . print_r( $profile, 1 ) . '</pre>';
        }        
}
    
    $emailAdded = 0;

    //Newsletter form submit handler
    $post_newsletterEmail = filter_input(INPUT_POST, 'newsletterEmail');
    //Newsletter form submit handler
    if(isset($post_newsletterEmail)){
        //check if email is already there and if not add email to database
        if(!$database->checkIfExist('newsletter', array('email' => $post_newsletterEmail))){
            $database->insertRow('newsletter', array('email' => $post_newsletterEmail));
        }
        //if email added show confirmation message
        $emailAdded = true;
    }    

     if(!$stop){


        //create user instance          
        $usr = $database->selectSingleClass('users', 'User', array('fbId' => $profile['id']));
            


        // get response
        try{
            $profile = $response->getGraphObject()->asArray();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        }         

        //check if user exist (as maybe registered under different instance and add to database
        if($usr == null){
			if(isset($profile['birthday'])){
				$profile['birthday'] = Helper::convertDate($profile['birthday']);
			}			
            $usr = new User($profile);
            $insertUser = $usr->buildData(array('id', 'firstUseDate', 'instanceId'));
            $database->insertRow('users', $insertUser);
            $usr = $database->selectSingleClass('users', 'User', array('fbId' => $profile['id']));
            //add user to global database
            //check if we have development or live instance
           
            $where_array = null;
            if($developerMode){
                $where_array = array('fb_id' => $usr->fbId, 'development' => 'Y', 'gob' => $get_ownerDB);
            }else{
                $where_array = array('fb_id' => $usr->fbId, 'development' => 'N', 'gob' => $get_ownerDB);
            }          
            $global_users = $database->selectArrayClass('globalUsers', 'GlobalUser', $where_array);
            if(count($global_users) == 0){
                //user don't exist so add him in
                $data_array = null;
                if($developerMode){
                    $data_array = array('fb_id' => $usr->fbId, 'development' => 'Y', 'gob' => $get_ownerDB);
                }else{
                    $data_array = array('fb_id' => $usr->fbId, 'development' => 'N', 'gob' => $get_ownerDB);
                }          
                $database->insertRow('globalUsers', $data_array);
            }else{
                //user already exist for this instance so do nothing
            }			
            
        }        
        
        $newToken = $session->getLongLivedSession()->getToken();
        //check if we have correct token
        if($usr->token != $newToken){
            $database->update('users', array('token' => $newToken), array('Id' => $usr->Id));
            $usr->token = $newToken;
        }
        
        //check if email exist
        if($usr->fbEmail == "" && $profile['email']){
            $database->update('users', array('fbEmail' => $profile['email']), array('Id' => $usr->Id));
        }
        
         $ended = 0;
         
         if(strtotime($auction->endDate) < time()){
            $ended = 1;
         }
         
         if($auction->active != 1){
             $ended = 1;
         }
         
         $post_bidValue = filter_input(INPUT_POST, 'bidValue'); 
         if(isset($post_bidValue)){
             if(strtotime($auction->endDate) > time()){
                 $database->insertRow('bids', array('userId' => $usr->Id, 'fbId' => $usr->fbId, 'auction' => $auction->Id, 'bid' => number_format($post_bidValue, 2)));                
                 if($application->popcornAuction == 1){
                    $database->runQuery("UPDATE auctions SET endDate=DATE_ADD(endDate, INTERVAL $application->popcornExtendTime SECOND) WHERE Id = " . $auction->Id . "AND instanceId='$application->instanceId'", true);
                 }
                 $auction = $database->selectSingleClass('auctions', 'Auction', array('Id' => Helper::decryptStringArray($get_uid, $application->secret)));
                 $auction->getHighestBid($database);

                 $emailClass = new Email($auction, $application, 'outbid', $get_ownerDB, true);
                if($application->debugMode == 1 && $showMessages == true){
                    echo '<pre>Sending email has been switched off</pre>';
                }else{
                    $emailClass->sendEmail();        
					
                }
				if($auction->looser != null && $auction->looser != $auction->winner){
					//publish story
					 $requestStory = new FacebookRequest( $session, 'POST', '/me/' . $application->fbObject . ':bid_on',
						 array(
						   'auction' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB,
							'fb:explicitly_shared' => 'true'
						 )                

					 );  

                 
					 try{
						 $responseStory = $requestStory->execute();
						 if($application->debugMode == 1 && $showMessages == true){
							echo '<pre>Story Response</pre>';
							echo '<pre>' . print_r($responseStory) . '</pre>';
						 }
					 } catch (FacebookRequestException $ex) {
						 $database->mainLog($ex->getMessage());
					 } catch (\Exception $ex) {
						 $database->mainLog($ex->getMessage());
					 }

					$appToken = null; 
					$appTokenRequest = new FacebookRequest($session, 'GET', '/oauth/access_token', array('client_secret' => $application->secret, 'client_id' => $application->appId, 'grant_type' => 'client_credentials'));
					 try{
						 $appTokenResponse = $appTokenRequest->execute()->getGraphObject()->asArray();
						 $appToken = $appTokenResponse['access_token'] ;
						 if($application->debugMode == 1 && $showMessages == true){
							echo '<pre>Access Token</pre>';
							echo '<pre>token' . $appTokenResponse['access_token'] . '</pre>';
						 }
					 } catch (FacebookRequestException $ex) {
						 $database->mainLog($ex->getMessage());
					 } catch (\Exception $ex) {
						 $database->mainLog($ex->getMessage());
					 }	


                 
					$notificationRequest = new FacebookRequest($session, 'POST', '/' . $auction->looser->fbId . '/notifications', array('access_token' => $appToken, 'href' => "details?uid=" . $get_uid . "&gob=" . $get_ownerDB , 'template' => "$application->notification_outbid $auction->productTitle"));
					 try{
						 $notificationResponse = $notificationRequest->execute();
						 if($application->debugMode == 1 && $showMessages == true){
							echo '<pre>Notification Response</pre>';
							echo '<pre>' . print_r($notificationResponse) . '</pre>';
						 }
					
					 } catch (FacebookRequestException $ex) {
						 $database->mainLog($ex->getMessage());
					 } catch (\Exception $ex) {
						 $database->mainLog($ex->getMessage());
					 }	
				
				
							
					 //post on looser wall when looser is different than winner only for pro_subscription
					 if($auction->looser != null && $application->plan_pro == 1){
						 if($auction->looser->id != $auction->winner->id){
							$requestOutbidMessageArray = array('access_token' => $auction->looser->token,    'message' => $language->wall_outbid . " " . $auction->productTitle, 'picture' => $auction->picturePath, 
											   'caption' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB, 'link' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB );            
							$requestOutbidMessage = new FacebookRequest($session, 'POST', '/' . $auction->looser->fbId . '/feed', $requestOutbidMessageArray);
							try{
							   $responseOutbidMessage = $requestOutbidMessage->execute();
							   if($application->debugMode == 1){
								   echo '<pre>Outbid Message Response</pre>';
								   echo '<pre>' . print_r($responseOutbidMessage) . '</pre>';
							   }
							} catch (FacebookRequestException $ex) {
							   $database->mainLog($ex->getMessage());
							} catch (\Exception $ex) {
							   $database->mainLog($ex->getMessage());
							}                    
						 }
					 }
				}
              }else{
                 $ended = 1;
              }
         }
         
         $bids = $database->selectArrayClass('bids', 'Bid', array('auction' => $auction->Id), null, 'ORDER BY bid DESC');
         
         //get actual price of item
         $price = $auction->getHighestBid($database);
        
         
         //check if emaill address has been provided in case it is missing and update database
         if(filter_input(INPUT_POST, 'missingEmailAddress')){
             $database->update('users', array('fbEmail' => filter_input(INPUT_POST, 'missingEmailAddress')), array('Id' => $usr->id));
         }
         
         $BuyItNow = filter_input(INPUT_POST, 'BuyItNow');
         //check if user wants to buy it now this item
         $BuyItNowSold = -1;
         $BuyItNowError = false;
         if(isset($BuyItNow)){             
             $auction->active = 3;
             $auction->winningUser = $usr->Id; 
             $auction->endPrice = $auction->buyItNowPrice;
             $auction->endDate = date("Y-m-d H:i:s");
             
             $saveResponse = $auction->saveBIN($database);
             if($application->debugMode == 1){
                 echo '<pre>Save BIN response</pre>';
                 echo '<pre>' . $saveResponse . '</pre>';
             }
             if(!$saveResponse){
                 $BuyItNowSold = 3; //3 - means error
             }else{
                 
                 //if everything is ok payment request will be send
                 $auction->getEndPrice($database);              
                 $emailClass = new Email($auction, $application, 'win', $get_ownerDB, true);
                 $emailClass->sendEmail();
                 if($application->debugMode == 1){
                     echo '<pre>Buy it know should be succesfull</pre>';
                 }
                 $BuyItNowSold = 1;
                 
                 //post on winner wall about this purchase only if plan_pro
                 if($auction->winner != null){
                        if($application->plan_pro == 1){
                            $requestWinMessageArray = array('access_token' => $auction->winner->token,    'message' => $language->wall_win . " " . $auction->productTitle, 'picture' => $auction->picturePath, 
                                               'caption' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB, 'link' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB );            
                            $requestWinMessage = new FacebookRequest($session, 'POST', '/' . $auction->winner->fbId . '/feed', $requestWinMessageArray);
                            try{
                               $responseWinMessage = $requestWinMessage->execute();
                               if($application->debugMode == 1 && $showMessages == true){
                                   echo '<pre>Win Message Response</pre>';
                                   echo '<pre>' . print_r($responseWinMessage) . '</pre>';
                               }
                            } catch (FacebookRequestException $ex) {
                               $database->mainLog($ex->getMessage());
                            } catch (\Exception $ex) {
                               $database->mainLog($ex->getMessage());
                            }
                        }
                        //create won story on winner page
                        $requestStory = new FacebookRequest( $session, 'POST', '/me/' . $application->fbObject . ':win',
                            array(
                              'auction' => $application->fbUrl . "details?uid=" . $get_uid . "&gob=" . $get_ownerDB,
                                'fb:explicitly_shared' => 'true'
                            )                
                        );  

                        try{
                            $responseStory = $requestStory->execute();
                            if($application->debugMode == 1 && $showMessages == true){
                               echo '<pre>Win Story Response</pre>';
                               echo '<pre>' . print_r($responseStory) . '</pre>';
                            }
                        } catch (FacebookRequestException $ex) {
                            $database->mainLog($ex->getMessage());
                        } catch (\Exception $ex) {
                            $database->mainLog($ex->getMessage());
                        }
                        
                 }                 
             }
             $ended = 1;
         }else{
             $BuyItNowSold = 0;
         }
         
?>
</head>
<body class="<?php echo $application->theme_colour; ?>">
<div class="page-container" id="wrapper">
    <?php include('header.php'); ?>
    <div class="container">
		<div class="row product-info">
			<div class="col-md-12">
				<div class="col-md-10"> 
					<h1>
						<strong>
							<?php echo $auction->productTitle?>
							</strong>
							</h1>
							</div>
							<div class="col-md-2" style="padding-top:15px">
							<button type="button" class="btn btn-default btn-lg" onclick="top.location.href='<?php echo $application->fbUrl . 'index?gob=' . $get_ownerDB; ?>'">
						<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span><a href="<?php echo $application->fbUrl . 'index?gob=' . $get_ownerDB; ?>" target="_top"><?php echo $language->go_back; ?></a>
					</button>
				</div>
				<div class="clearfix"></div>
				<div class="line"></div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6">	
				<div class="image fa-border">
					<img src="<?php echo $auction->picture; ?>" title="<?php echo $auction->productTitle; ?>" alt="<?php echo $auction->shortDesc; ?>" id="image" />
				</div>	
  			</div>
			<div class="col-md-6 col-sm-6 col-xs-6">
				<ul>
					<li><p><span><?php echo $language->brand; ?>:</span><?php echo $auction->brand; ?></p></li>
					<li><p><span><?php echo $language->p_p; ?>:</span><?php echo $application->currencyPreffix . number_format($auction->postage, 2) . $application->currencySuffix; ?></p></li>
					<li><p><span><?php echo $language->auction_end; ?>: </span><div id="clock"></div></p></li>
				</ul>
				<div class="alert alert-danger custom message error" role="alert" style="display: none;"></div>
				<?php
				if(number_format($auction->reservePrice, 2) > $price){
				?>
				<div class="alert alert-warning custom" role="alert"><p></pack><?php echo $language->reserve_not_meet; ?></p></div>
				<?php } ?>
				<div class="jumbotron price noTopMargin" style="text-align:center">
					<strong><?php echo $language->actual_bid; ?><br/><span class="priceBig" style=""><?php echo $application->currencyPreffix . number_format($price, 2) . $application->currencySuffix;?></span></strong>
				</div>
				<div class="line"></div>
				<p><?php echo $language->please_enter_your_bid; ?> <span class="">[<?php echo $language->min; ?>: <?php echo $application->currencyPreffix . number_format($price + $auction->bidStep, 2) . $application->currencySuffix;?>]</span> </p>
				<form class="form-inline" action="" method="POST" name="frmBid">
					<input type="text" value="<?php if(!$ended) { echo number_format($price + $auction->bidStep, 2); } else { echo "0.00"; } ?>" style=" width:100%;" name="bidValue" id="bidValue" class="enterBid"/><br/>
					<input type="hidden" name="signed_request" value="<?php echo $_REQUEST['signed_request']; ?>" />
					<input type="hidden" name="uid" value="<?php echo filter_input(INPUT_GET, 'uid'); ?>" />
					<input type="hidden" name="fbuid" value="<?php echo $usr->fbId; ?>" />
					<div class="btn-group btn-group-justified enterBid" name="bidValue" id="bidValue" value="<?php if(!$ended) { echo number_format($price + $auction->bidStep, 2); } else { echo "0.00"; } ?>" role="group" aria-label="...">
						<div class="btn-group" role="group">
							<button type="submit" class="btn btn-default add_cart" id="placeBid"><i class="fa fa-shopping-cart"></i> <?php echo $language->bid_now; ?></button>
						</div>
					<?php 
					if($auction->buyItNowPrice != 0.00){
						if($price < $auction->buyItNowPrice){
					?>    

						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default add_cartBIN" id="buy" ><i class="fa fa-gavel"></i> <?php echo $language->buy_it_now . " - " . $application->currencyPreffix . number_format($auction->buyItNowPrice, 2) . $application->currencySuffix; ?></button>
						</div>
					<?php
					}}
					?>
							
					</div>
				</form>
			<?php 
			if($auction->buyItNowPrice != 0.00){
				if($price < $auction->buyItNowPrice){
			?>    

				<div style="display:none;">
					<form action="" method="POST" name="frmBiN" id="frmBiNID">
						<input type="hidden" name="BuyItNow" id="BiN" value="yes"/> 
						<input type="hidden" name="signed_request" value="<?php echo $_REQUEST['signed_request']; ?>" />
						<input type="hidden" name="uid" value="<?php echo filter_input(INPUT_GET, 'uid'); ?>" />
						<input type="hidden" name="fbuid" value="<?php echo $usr->fbId; ?>" />
					</form>
				</div>
			<?php
			}}
			?>
				<div class="clearfix"></div>
			</div>
               
			<div class="col-md-12">
				<div class="tabs">
					<ul class="nav nav-tabs" id="myTab">
						<li class="active"><?php echo $language->description; ?></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="home"><?php echo $auction->longDesc; ?></div>					
					</div>
				</div>
                    
			</div><!--Ends Description div -->
		</div>
		<div class="row product-info">
      		<div class="biddingTable">
				<table class="table specs">
					<thead>
						<tr>
							<th><span><?php echo $language->photo; ?></span></th>
							<th><span><?php echo $language->name; ?></span></th>
							<th><span><?php echo $language->bid; ?></span></th>
							<th><span><?php echo $language->bidding_time; ?></span></th>
							<th><span><?php echo $language->bidding_date; ?></span></th>
					  </tr>
					</thead>
					<tbody>          		
						<tr class="padding">
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php foreach($bids as $row){ ?>
						<tr>
							<td>
								<div class="imgb">
									<a class="fadeable" href="">
										<img src="https://graph.facebook.com/<?php echo $row->fbId; ?>/picture?type=normal" alt="blank" width="78" style="opacity: 1;">
									</a>
								</div>
							</td>
							<td class="prod-col">
								<div>
									<p><?php echo $database->selectSingleClass('users', 'User', array('fbId' => $row->fbId))->fbName; ?></p>
								</div>
							</td>
							<td><div><?php echo $application->currencyPreffix . number_format($row->bid, 2) . $application->currencySuffix; ?></div></td>
							<td><div><?php echo date("G:i:s", strtotime($row->bidDate)); ?></div></td>
							<td><div><?php echo date("Y-m-d", strtotime($row->bidDate)); ?></div></td>
						</tr>
						<tr class="padding bot">
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		
		
		</div>

		<div class="clearfix"></div>
		<div class="col-md-12 cat_header">
			<h2><?php echo $language->our_latest_finished_auctions; ?></h2>
		</div>
		<div class="row" style="margin-top:20px;">
        <?php 
		$i = 0; 	
		foreach ($endedAuctions as $row) { 
        if($row->active <> '0' && $i < 4) { ?>
			<div class="col-md-4 col-sm-4 ">
				<div class="three fa-border lineleft" >
					<div class="pull-left"> <img src="<?php echo $row->picture; ?>" width="50" height="50"> </div>
					<div> 
						<span class="txt ended" style="font-size:12px;"><?php echo $row->productTitle; ?></span> <br>
						<span class="price">For <span class="red strong"><?php echo $application->currencyPreffix . number_format($row->actualPrice, 2) . $application->currencySuffix; ?></span></span> 
					</div>
				</div>
			</div>
		<?php } $i++; } ?>
		</div>
	</div>
	<?php include('footer.php'); ?>





<div class="modal fade" id="messageBINBefore" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $language->buy_it_now_button_cancel; ?></span></button>
				<h4 class="modal-title"><span><?php echo $language->buy_it_now_title; ?></span></h4>
			</div>
			<div class="modal-body">
				<p><?php echo $language->buy_it_message; ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" onclick="$('#frmBiNID').submit();"><?php echo $language->buy_it_now_button_confirm; ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language->buy_it_now_button_cancel; ?></button>
			</div>
		</div>
	</div>
</div>	

<div class="modal fade" id="messageBINAfter" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $language->buy_it_now_button_cancel; ?></span></button>
				<h4 class="modal-title"><span><?php echo $language->buy_it_now_title; ?></span></h4>
			</div>
			<div class="modal-body">
				<p><?php if($BuyItNowSold == 1){ echo $language->buy_it_message_after_ok; } else { echo $language->buy_it_message_after_error; } ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language->ok; ?></button>
			</div>
		</div>
	</div>
</div>	

<div class="modal fade" id="missingEmail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $language->buy_it_now_button_cancel; ?></span></button>
				<h4 class="modal-title"><span><?php echo $language->enter_your_email; ?></span></h4>
			</div>
			<div class="modal-body">
				<p><?php echo $language->missing_email; ?></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $language->ok; ?></button>
			</div>
		</div>
	</div>
</div>	

<script>
var ended = <?php echo $ended; ?>;
var emailEnded = <?php echo $emailAdded; ?>;
var buyItNowSuccess = <?php echo $BuyItNowSold; ?>;
$(function() {
   if(ended == 1){
        $('.message.error').html('<p><i class="fa fa-exclamation"></i><?php echo $language->e_auction_ended; ?></p>');
        $('.message.error').show();
        ban = 1;
        $("#bidValue").attr("disabled", "disabled");
        $("#placeBid").attr("disabled", "disabled");
        $("#buy").attr("disabled", "disabled");
        return;	      
   }
});

    function dateError(){
        $('.message.error').html('<p><i class="fa fa-exclamation"></i><?php echo $language->e_auction_ended; ?></p>');
        $('.message.error').show();
        return;
    }
	
    function isNumber(n){
        return !isNaN(parseFloat(n)) && isFinite(n);
    }	

</script>
<script type="text/javascript" src="js/jquery.countdown.js" charset="utf-8"></script>
<script>
	$(function() {
	  var d, h, m, s;
	  $('div#clock').countdown(new Date(
		  <?php echo date("Y",strtotime($auction->endDate)) . ","; 
				echo date("n",strtotime($auction->endDate)) . "-1,"; 
				echo date("j,G,i,s,u",strtotime($auction->endDate)); 
		  ?>), function(event) {
		var timeFormat = "%d <?php echo $language->t_days; ?> %h <?php echo $language->t_hours; ?> %m <?php echo $language->t_minutes;?> %s <?php echo $language->t_seconds; ?>",
			$this = $(this);
		switch(event.type) {
		  case "days":
			d = event.value;
			break;
		  case "hours":
			h = event.value;
			break;
		  case "minutes":
			m = event.value;
			break;
		  case "seconds":
			s = event.value;
			break;
		  case "finished":
			$this.fadeTo('slow', 0.5);
			break;
		}
		// Assemble time format
		if(d > 0) {
		  timeFormat = timeFormat.replace(/\%d/, d);
		  timeFormat = timeFormat.replace(/\(s\)/, Number(d) == 1 ? '' : 's');
		} else {
		  timeFormat = timeFormat.replace(/\%d day\(s\)/, '');
		}
		timeFormat = timeFormat.replace(/\%h/, h);
		timeFormat = timeFormat.replace(/\%m/, m);
		timeFormat = timeFormat.replace(/\%s/, s);
		// Display
		$this.html(timeFormat);
	  });
	});

</script>
<script>
var ban = 0;
var like = 1;
$(document).ready(function() {
    $('#bidValue').focus(function(){
       if($(this).val() == <?php echo $price + $auction->bidStep; ?>){
           $(this).val('');
       } 
    });
    $('#bidValue').blur(function(){
       if($(this).val() == ''){
           $(this).val('<?php echo number_format($price + $auction->bidStep, 2); ?>');
       } 
    });


    $('#placeBid').click(function(e){
        e.preventDefault();
        if(ban == 1){
            $('.message.error').html('<p><?php echo $language->e_not_allowed; ?></p>');
            $('.message.error').show();
            return;		
        }	
        if(like == 0){
            $('.message.error').html('<p>E<?php echo $language->e_fan; ?></p>');
            $('.message.error').show();
            return;		
        }
        if(!isNumber($('#bidValue').val())){
            $('.message.error').html('<p><?php echo $language->e_correct_bid; ?></p>');
            $('.message.error').show();
            return;
        }
        if($('#bidValue').val() < <?php echo $price + $auction->bidStep; ?>){
            $('.message.error').html('<p><?php echo $language->e_minimum_bid; echo $application->currencyPreffix . number_format(($price + $auction->bidStep), 2) . $application->currencySuffix; ?></p>');
            $('.message.error').show();
            return;
        }
        $(this).prop('disabled', true);
        <?php
            if($auction->buyItNowPrice != 0.00){
                if($price < $auction->buyItNowPrice){
                    echo "$('#buy').prop('disabled',  true);";
                }
            }
        ?>
        
        frmBid.submit();
        return true;
    });
    $('#buy').click(function(e){
        e.preventDefault();
        document.getElementById("wrapper").scrollIntoView();
		$('#messageBINBefore').modal({show: true});
		
    });	
	
	 if(buyItNowSuccess != 0){
        document.getElementById("wrapper").scrollIntoView();
		$('#messageBINAfter').modal({show: true});
		
    }
<?php if($usr->fbEmail != ""){ ?>
	$('#missingEmail').modal({show: true});
<?php } ?>

});


</script>
<div id="fb-root"></div>
<script src="https://connect.facebook.net/en_US/all.js"></script>
<script>
    window.fbAsyncInit = function() {
      	FB.init({
       	appId : '<?php echo $application->appId; ?>',
       	status : true, <?php // check login status?>
       	cookie : true, <?php // enable cookies to allow the server to access the session?>
       	xfbml : true <?php // parse XFBML?>
      	});
        
        FB.Canvas.setAutoGrow(true);
    }
</script> 
</div> 
<?php echo $application->googleTracking; ?>
</body>
</html>

<?php }else{ ?>
<!doctype html>
<html>
    <head>
    </head>
    <body>
        <?php echo $language->aouth_message; ?>
    </body>
</html>

<?php } 

