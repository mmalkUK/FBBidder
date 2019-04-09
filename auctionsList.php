<?php

    /*
     * FBA auctionLists.php file
     */

    include_once '0_config/config.php';
    $global_users = null;
    
    if(!function_exists("__autoload")) {
        function __autoload($class_name) {
            include_once 'source/' . $class_name . '.php';
        }
    }
    
    $is_gob_main = true;
    
    if($is_gob_main == true){
        if($developerMode){
            $get_ownerDB = 'Himj-WkH9GDs0pkmbFuHoovgYrknvp-b-Z6OUvvp1hE,';  // for development enviroment
        }else{
			$get_ownerDB ='AIoCLcaWXU-7F-MWCdYC3BHXQjOjhjjOCXxIgbcLB0k,'; // for live enviroment
        }     
    }
    
    $ownerDB = Helper::decryptStringArray($get_ownerDB);
         
	$database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], null, $db['type']);
    //check if we have correct database and connection has been establish
    $dbError = $database->getError();
    
    if($dbError != null){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=database&message=" . urlencode($database->getError()) . "'</script>";
        }else{
            echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
        }
       die();
    }   
    $language = new Translation($database);
    $application = new Application($database, $ownerDB);    
        
    session_start();
    
///////////////////////////////SDK 4
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
    
        
        
    FacebookSession::setDefaultApplication($application->appId,$application->secret);
    $helperRedirect = new FacebookRedirectLoginHelper($application->fbUrl . '?gob=' . $get_ownerDB);
    
    try {
        $session = $helperRedirect->getSessionFromRedirect();
    } catch(FacebookRequestException $ex) {
        // When Facebook returns an error
        Helper::_log($ex->getMessage());
    } catch(\Exception $ex) {
        // When validation fails or other local issues
        Helper::_log($ex->getMessage());
    }
    

    
    if(!isset($session)){
        $helper = new FacebookCanvasLoginHelper();

        try {
            $session = $helper->getSession();
        } catch (FacebookRequestException $ex) {
            Helper::_log($ex->getMessage());
        } catch (\Exception $ex) {
            Helper::_log($ex->getMessage());
        }        
    }        

    if(!isset($session)){
        // login helper with redirect_uri when user didn't authorise app        
        echo "<script> top.location.href='" . $helperRedirect->getLoginUrl( array( 'email', 'publish_actions' ) ) . "'</script>"; 
        die();
    }
    
	if($application->debugMode == 1 && $showMessages == true){
        echo '<pre>User Profile</pre>';
        echo '<pre>' . print_r($session) . '</pre>';        
    }
    
    //if session exist it means person aurhorised app
    if ($session) {

        $request = new FacebookRequest( $session, 'GET', '/me' );
        try{
            $response = $request->execute();
        } catch (FacebookRequestException $ex) {
            Helper::_log($ex->getMessage());
        } catch (\Exception $ex) {
            Helper::_log($ex->getMessage());
        } 
        // get response
        try{
            $profile = $response->getGraphObject()->asArray();
        } catch (FacebookRequestException $ex) {
            Helper::_log($ex->getMessage());
        } catch (\Exception $ex) {
            Helper::_log($ex->getMessage());
        }  
        
		if($application->debugMode == 1 && $showMessages == true){
            echo '<pre>User Profile</pre>';
            echo '<pre>' . print_r($profile) . '</pre>';
        }

    
///////////////////////////////SDK 4 END    
        
        
        $stop = false;
  
        //create user instance          
        $usr = new User();
        $usr = $database->selectSingleClass('users', 'User', array('fbId' => $profile['id']));
        
        //check if user exist (as maybe registered under different instance and add to database
        
		if($application->debugMode == 1 && $showMessages == true){
           echo '<pre>User</pre>';
           echo '<pre>' . print_r($usr) . '</pre>';
        }
        
           //user access app from bookmark - this will use main database
           //check if we have development or live instance           
           $where_array = null;
           if($developerMode){
               $where_array = array('fb_id' => $usr->fbId, 'development' => 'Y');
           }else{
               $where_array = array('fb_id' => $usr->fbId, 'development' => 'N');
           }          
		$global_users = $database->selectArrayClass('globalUsers', 'GlobalUser', $where_array);
           
           
           
           
           //check if we have one instane or more
           if(count($global_users) == 1){
               if($global_users[0]->gob != $get_ownerDB){
                    if($developerMode){
                        echo "<script> top.location.href='https://apps.facebook.com/bidderdevelopment/?gob=" . $global_users[0]->gob . "'</script>";
                    }else{
                        echo "<script> top.location.href='https://apps.facebook.com/vmbidder/?gob=" . $global_users[0]->gob . "'</script>";
                    }    
                    die();                   
               }
           }elseif(count($global_users) == 0){
                    if($developerMode){
                        echo "<script> top.location.href='https://apps.facebook.com/bidderdevelopment/'</script>";
                    }else{
                        echo "<script> top.location.href='https://apps.facebook.com/vmbidder/'</script>";
                    }    
                    die();                 
           }else{
         
           }
        }
        
?>
<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap-select.css">
		<link href='css/font-awesome.min.css' rel='stylesheet' type='text/css'/>
		<link rel="stylesheet" type="text/css" href="css/flexslider.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<title>VM Bidder</title>
	</head>
	<body style="background-color:white;">
		<div class="page-container">
			<div class="container">
				<div class="row">
				</div>
				<div class="clearfix"></div>
				<div class="row" style="margin-top:20px;">
					<div class="newsletter clearfix no-align">
						<?php
							if($global_users != null){
								foreach($global_users as $row){
    								$database->setInstance($row->instanceId);
    								$link = $database->getVar('logoPic');
						?>
						<div class="col-md-12">
							<a href="<?php echo "https://apps.facebook.com/" . $application->fbObject . "/?gob=" . $row->gob; ?>" target="_top"><img src="<?php echo $link; ?>" alt="" height="60px"></a>
						</div>                   
						<?php }} ?>               
					</div>
				</div>
			</div>
			<div class="clearfix" style="padding-top:20px;"></div>
			<div class="footer black">
				<div class="container">
					<div class="row">
						<div class="col-md-4">
						</div>
						<div class="col-md-4">
						</div>
						<div class="col-md-4">
						</div>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script> 
		<script type="text/javascript" src="js/bootstrap.min.js"></script> 
		<script type="text/javascript" src="js/bootstrap-select.min.js"></script> 
		<script type="text/javascript" src="js/jquery.flexslider-min.js"></script> 
		<script type="text/javascript" src="js/jquery.easing.1.3.js"></script> 

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
	</body>
</html>