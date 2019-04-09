<?php

    /*
     * Project:			VM Bidder
	 * Version:			4.0
	 * File:			index.php
	 * Location:		./index.php
	 * Author:			Marcin Malicki
	 * Contact:			mmalicki@vmtrading.co.uk
	 * Web:				http://vmtrading.co.uk
	 * Description:		Main front end file
	 * Creation date:	24/12/2014
	 * Changes:			none
     */

    include_once '0_config/config.php';
    
    
    if(!function_exists("__autoload")) {
        function __autoload($class_name) {
            include_once 'source/' . $class_name . '.php';
        }
    }
    
    $is_gob_main = false;
    
    $get_ownerDB = filter_input(INPUT_GET, 'gob');
    if(!isset($get_ownerDB)){
        $is_gob_main = true;
    }
    
    if(strlen($get_ownerDB) != 44 && $is_gob_main == false){
        $is_gob_main = true;
    }
    
    if($is_gob_main == true){
        if($developerMode){
            $get_ownerDB = 'i-PgGbzHZFA7M8hMtUXTRycUNrzPx9tEbk4OkcPsC5E,';  // for development enviroment
        }else{
			//new gob for main needs to be generated
            $get_ownerDB ='csfS0bJ3mCDUhN5AdijH67QfzvQ_Mx_XLu743qCkFHs,'; // for live enviroment
        }     
    }
    
    $ownerDB = Helper::decryptStringArray($get_ownerDB);
     
    if(!$ownerDB){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=gob_usrl'</script>";
        }else{
           echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>"; 
        }    
        die();
    }
    
    $database = new MDatabase($db['address'], $db['username'], $db['password'], $db['database'], $ownerDB, $db['type']);
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
   
	//check if we have valid instance
	if($application->fbObject == "" && $application->planName == ""){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=GOBdoesntexist&message=" . urlencode($database->getError()) . "'</script>";
        }else{
            echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
        }
       die();		
	}
	
    //set default timezone
    date_default_timezone_set($application->timeZone);
    
    //check if app has been paid. If not redirect int FB main page
    if(strtotime($application->expiryDate) < time()){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=expired&date=" . $application->expiryDate . "&dbe=" . $database->getError() . "'</script>";
        }else{
            echo "<script> top.location.href='https://apps.facebook.com/1396660107221314/'</script>";
        }
        die();
    }
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
    $helperRedirect = null;

    $accesor = filter_input(INPUT_SERVER, 'HTTP_HOST');
    

    if($accesor == $globalServerUrl){
        $helperRedirect = new FacebookRedirectLoginHelper($application->fbUrl . '?gob=' . $get_ownerDB);
    }else{
        $helperRedirect = new FacebookRedirectLoginHelper('https://' . $globalServerUrlMobile . '?gob=' . $get_ownerDB);
    }
    
    try {
        $session = $helperRedirect->getSessionFromRedirect();
    } catch(FacebookRequestException $ex) {
        // When Facebook returns an error
		$database->mainLog($ex->getMessage());
    } catch(\Exception $ex) {
        // When validation fails or other local issues
		$database->mainLog($ex->getMessage());
    }
    
    if(!isset($session) && $accesor == $globalServerUrl){
        $helper = new FacebookCanvasLoginHelper();

        try {
            $session = $helper->getSession();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        }        
    }        

    if(!isset($session) && $accesor != $globalServerUrl){
        $session = FacebookSession::newAppSession();

        // To validate the session:
        try {
          $session->validate();
        } catch (FacebookRequestException $ex) {
			// Session not valid, Graph API returned an exception with the reason.
			$database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
			// Graph API returned info, but it may mismatch the current app or have expired.
			$database->mainLog($ex->getMessage());
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
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
        } 
        // get response
        try{
            $profile = $response->getGraphObject()->asArray();
        } catch (FacebookRequestException $ex) {
            $database->mainLog($ex->getMessage());
        } catch (\Exception $ex) {
            $database->mainLog($ex->getMessage());
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
        if($usr == null){
			if(isset($profile['birthday'])){
				$profile['birthday'] = Helper::convertDate($profile['birthday']);
			}
            $usr = new User($profile);
			
            $insertUser = $usr->buildData(array('Id', 'firstUseDate', 'instanceId'));
			
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
        
        if($application->debugMode == 1 && $showMessages == true){
           echo '<pre>User</pre>';
           echo '<pre>' . print_r($usr) . '</pre>';
        }
        
        //check if we have correct token
        $newToken = $session->getLongLivedSession()->getToken(); 
        if($usr->token != $newToken){
            $database->update('users', array('token' => $newToken), array('Id' => $usr->Id));
            $usr->token = $newToken;
        }
        
        //check if email exist
        if($usr->fbEmail == "" && isset($profile['email'])){
            $database->update('users', array('fbEmail' => $profile['email']), array('Id' => $usr->Id));
        }

        //get all active auctions details to be shown on main page
        
        //check if app come from only bookmark
        //if no follow as it is
        //if yes check if user is assigned in one or more app
        //if one app move to correct app
        //if more app show list of apps
        
        $get_fb_source = filter_input(INPUT_GET, 'fb_source');
        $get_ref = filter_input(INPUT_GET, 'ref');
        
        
        //check if user access app from bookmark. If no just follow thru
        if(isset($get_fb_source) && isset($get_ref)){
           //user access app from bookmark - this will use main database
           //check if we have development or live instance
           
           $where_array = null;
           if($developerMode){
               $where_array = array('fb_id' => $usr->fbId, 'development' => 'Y');
           }else{
               $where_array = array('fb_id' => $usr->fbId, 'development' => 'N');
           }          
           $global_users = $database->selectArrayClass('globalUsers', 'GlobalUser', $where_array, null, "AND folder <> 'main'");
           
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
           }else{
               //there is more than one shop to user so list needs to be shown
                    if($developerMode){
                        echo "<script> top.location.href='https://apps.facebook.com/bidderdevelopment/auctionsList'</script>";
                    }else{
                        echo "<script> top.location.href='https://apps.facebook.com/vmbidder/auctionsList'</script>";
                    }    
                    die();                
           }
        }
        $cmd = filter_input(INPUT_GET, 'cmd');
		if(!isset($cmd)){
			if($developerMode){
				echo "<script> top.location.href='https://apps.facebook.com/bidderdevelopment/?gob=" . $get_ownerDB . "'</script>";
                }else{
                    echo "<script> top.location.href='https://apps.facebook.com/vmbidder/?gob=" . $get_ownerDB . "'</script>";
                }    
            die(); 			
		}
		
		if($cmd != 'aboutus' && $cmd != 'delivery' && $cmd != 'tandc'){
			if($developerMode){
				echo "<script> top.location.href='https://apps.facebook.com/bidderdevelopment/?gob=" . $get_ownerDB . "'</script>";
                }else{
                    echo "<script> top.location.href='https://apps.facebook.com/vmbidder/?gob=" . $get_ownerDB . "'</script>";
                }    
            die(); 				
		}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include('head.php'); ?>
</head>

<body style="background-color:white;" class="<?php echo $application->theme_colour; ?>">
<div class="page-container" id="wrapperTop">
    <?php include('header.php'); ?>
  <div class="container">
    <div class="row">
      
    </div>
          <div class="clearfix"></div>
          <div class="row" style="margin-top:20px;">
			<div class="col-md-12">			
                  <div class="newsletter clearfix">
            <div class="col-md-10 col-sm-10 pull-left">
      			<h1>
					<?php
						if($cmd == 'aboutus'){
							echo $language->f_aboutus;	
						}else if($cmd == 'delivery'){
							echo $language->f_delivery;
						}else if($cmd == 'tandc'){
							echo $language->f_tandc;
						}				
					?>				
				</h1>
			</div>
			<div class="col-md-2 col-sm-2 pull-right">
      			<button type="button" class="btn btn-default btn-lg pull-right" onclick="top.location.href='<?php echo $application->fbUrl . 'index?gob=' . $get_ownerDB; ?>'"><?php echo $language->go_back; ?></button> 
			</div>
			<div class="clearfix"></div>					
					<div class="col-md-12 col-sm-12">
					<?php
						if($cmd == 'aboutus'){
							echo $database->getVar('info_aboutus');	
						}else if($cmd == 'delivery'){
							echo $database->getVar('info_delivery');
						}else if($cmd == 'tandc'){
							echo $database->getVar('info_tandc');
						}				
					?>
					</div>
  			<div class="col-md-12 col-sm-12 pull-right">
      			<button type="button" class="btn btn-default btn-lg pull-right" onclick="top.location.href='<?php echo $application->fbUrl . 'index?gob=' . $get_ownerDB; ?>'"><?php echo $language->go_back; ?></button> 
			</div>					
                  </div>
              </div>
        
			</div>
       
          
      </div>	
	<?php include('footer.php'); ?>

</div>
<!--<div id="fb-root"></div>-->
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
<?php echo $application->googleTracking; ?>
</body>
</html>
<?php }else{ ?>
<?php echo '<!doctype html><html><head></head><body>'. $language->aouth_message .'</body></html>'; ?>

<?php }  

