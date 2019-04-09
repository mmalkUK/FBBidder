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
            $get_ownerDB ='AIoCLcaWXU-7F-MWCdYC3BHXQjOjhjjOCXxIgbcLB0k,'; // for live enviroment
        }     
    }
    
    $ownerDB = Helper::decryptStringArray(urldecode($get_ownerDB));
     
    if(!$ownerDB){
        if($developerMode){
            echo "<script> top.location.href='http://vmbidder.com?error=gob_usrl'</script>";
        }else{
           echo "<script> top.location.href='https://apps.facebook.com/vmbidder/'</script>"; 
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
            echo "<script> top.location.href='https://apps.facebook.com/vmbidder/'</script>";
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
            echo "<script> top.location.href='https://apps.facebook.com/vmbidder/'</script>";
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
            echo "<script> top.location.href='https://apps.facebook.com/vmbidder/'</script>";
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
        $helperRedirect = new FacebookRedirectLoginHelper($application->fbUrl . '?gob=' . $get_ownerDB . '');
    }else{
        $helperRedirect = new FacebookRedirectLoginHelper('https://' . $globalServerUrlMobile . '?gob=' . $get_ownerDB );
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
        $emailAdded = 0;

        //get newsletter email from POST
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
        
        $limitAuctions = null;
        
        if($application->planLiveAuctions > 1){
            $limitAuctions = "DESC LIMIT $application->planLiveAuctions";
        }
       
        $auctions = $database->selectArrayClass('auctions', 'Auction', null, "WHERE endDate > getdate() AND startDate < getdate() AND active = '1' AND instanceId='" . $ownerDB . "'" , $limitAuctions);
        $categories = $database->selectArrayClass('category', 'Category', null, "WHERE instanceId='" . $ownerDB . "'", 'ORDER BY sortOrder ASC');

        //get list of categories with auctions;
        $list = array();

        foreach($categories as $row){
            $list[$row->Id] = 0;
        }
                
        //prepare list of categories which has auctions in it
        foreach($auctions as $row){
            $list[$row->category] = 1;
        }
	
        //get ended auctions to show on main page
        $endedAuctions = $database->selectArrayClass('auctions', 'Auction', null, "WHERE endDate < getdate() AND active <> '0' AND instanceId='" . $ownerDB . "' ORDER BY endDate DESC LIMIT 3");
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
        
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include('head.php'); ?>
</head>

<body class="<?php echo $application->theme_colour; ?>">
<div class="page-container" id="wrapperTop">
    <?php include('header.php'); ?>
	<?php include('banner.php'); ?>

	<?php 
		if($application->oneCategory == "no") {
		foreach($categories as $row){
			if($list[$row->Id] == 1){ ?>
	<div class="wrapper" style="padding-bottom:0px !important;">
		<div class="cat_header">
			<h2 style="text-align:left;"><?php echo $row->name; ?></h2>
		</div>
		<div class="jcarousel-wrapper">
			<div class="jcarousel">
				<ul>
				<?php
                  foreach($auctions as $_row){
                        if($_row->category == $row->Id){ 
                            $price = $_row->getHighestBid($database);
                 ?>
					  <li>
						<div class="product">
						  <a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><img alt="<?php echo $row->name; ?>" src="<?php echo $_row->picture; ?>"></a>
						  <div class="name"> <a href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><?php echo $_row->productTitle; ?></a> 
						  </div>
						  <div class="price">
							<p>
								<?php 
									echo $application->currencyPreffix . number_format($price, 2) . $application->currencySuffix;
									if(($_row->buyItNowPrice != 0.00) && ($_row->buyItNowPrice >= $price)){
										echo ' / ' . $application->currencyPreffix . number_format($_row->buyItNowPrice, 2) . $application->currencySuffix;
									}
									
								?>
							</p>
						  </div>
						</div>
						<div class="btn-group btn-group-justified" role="group" aria-label="...">
						  <div class="btn-group" role="group">
							<a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><button type="button" class="btn btn-default"><i class="fa fa-shopping-cart"></i> Bid Now</button></a>
						  </div>
						  <div <?php if(($_row->buyItNowPrice == 0.00) || ($_row->buyItNowPrice <= $price)){ echo 'style="display:none;"'; }?> class="btn-group" role="group">
							<a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><button type="button" class="btn btn-default"><i class="fa fa-gavel"></i> Buy Now</button></a>
						  </div>
						</div>
					  </li>				
				
				<?php }} ?>				
				</ul>
			</div>
			<a href="#" class="jcarousel-control-prev">&lsaquo;</a> <a href="#" class="jcarousel-control-next">&rsaquo;</a>
			<p class="jcarousel-pagination"></p>
		</div>
	</div>

	<?php }}}else{
    foreach($categories as $row){
		$tRow = 0;
        if($list[$row->Id] == 1){ ?>		
	<div class="wrapper" style="padding-bottom:0px !important;">
		<div class="cat_header">
			<h2 style="text-align:left;"><?php echo $row->name; ?></h2>
		</div>
		<div class="jcarousel-wrapper">
			<div class="jcarousel2">
				<?php
				  if($tRow % 3 == 0){ 
					echo '<ul>'; 
				  }
                  foreach($auctions as $_row){
                        if($_row->category == $row->Id){ 
                            $price = $_row->getHighestBid($database);
                 ?>
					  <li>
						<div class="product">
						  <a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><img alt="<?php echo $row->name; ?>" src="<?php echo $_row->picture; ?>"></a>
						  <div class="name"> <a href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><?php echo $_row->productTitle; ?></a> 
						  </div>
						  <div class="price">
							<p>
								<?php 
									echo $application->currencyPreffix . number_format($price, 2) . $application->currencySuffix;
									if(($_row->buyItNowPrice != 0.00) && ($_row->buyItNowPrice >= $price)){
										echo ' / ' . $application->currencyPreffix . number_format($_row->buyItNowPrice, 2) . $application->currencySuffix;
									}
									
								?>
							</p>
						  </div>
						</div>
						<div class="btn-group btn-group-justified" role="group" aria-label="...">
						  <div class="btn-group" role="group">
							<a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><button type="button" class="btn btn-default"><i class="fa fa-shopping-cart"></i> Bid Now</button></a>
						  </div>
						  <div <?php if(($_row->buyItNowPrice == 0.00) || ($_row->buyItNowPrice <= $price)){ echo 'style="display:none;"'; }?> class="btn-group" role="group">
							<a target="_top" href="<?php echo $application->fbUrl . "details?uid=" . Helper::encryptStringArray($_row->Id, $application->secret); ?>&gob=<?php echo $get_ownerDB?>"><button type="button" class="btn btn-default"><i class="fa fa-gavel"></i> Buy Now</button></a>
						  </div>
						</div>
					  </li>				
				
				<?php }} 
					$tRow++;
					if($tRow % 3 == 0){
						echo '</ul>';
					}
				?>				
				
			</div>
		</div>
	</div>
	<?php }}} ?>

	<?php include('newsletter.php'); ?>
	<?php include('ended.php'); ?>  
	<?php include('footer.php'); ?>

<script>
$(document).ready( function(){	
	
		$('.slideshow > div').flexslider({
			animation:"slide",
			easing:"",
			direction:"horizontal",
			startAt:0,
			initDelay:0,
			slideshowSpeed:7000,
			animationSpeed:600,
			prevText:"Previous",
			nextText:"Next",
			pauseText:"Pause",
			playText:"Play",
			pausePlay:false,
			controlNav:true,
			slideshow:true,
			animationLoop:true,
			randomize:false,
			smoothHeight:false,
			useCSS:true,
			pauseOnHover:true,
			pauseOnAction:true,
			touch:true,
			video:false,
			mousewheel:false,
			keyboard:false
		});

		$('.item-carousel').carousel('pause');
});	
</script>
<!-- Newsletter -->
<script>
$(document).ready(function() {
    $('#newsletterSubmit').click(function(e){
        e.preventDefault();
        var re = new RegExp(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
        if(re.test($('#newsletterEmail').val())){
            newsletterFrm.submit();
        } else {
			document.getElementById("wrapperTop").scrollIntoView();
			$('#myModalLabelP').text('Warning');
			$('#messagePContent').text('Please input correct email');
			$('#messageEmail').modal({show: true});
            return false;       
        }
    });

    $('#newsletterEmail').focusout(function(){
        if($(this).val() == ''){
            $(this).val('<?php echo $language->enter_your_email; ?>');
        }

    });
    $('#newsletterEmail').focusin(function(){
        if($(this).val() == '<?php echo $language->enter_your_email; ?>'){
            $(this).val('');
        } 
    });

var emailEnded = <?php echo $emailAdded; ?>;
if(emailEnded == 1){
	document.getElementById("wrapperTop").scrollIntoView();
	$('#messagePContent').text('Email Added');
	$('#myModalLabelP').text('Confirmation');
	$('#messageEmail').modal({show: true});
} 

});  
</script>
<!-- Newsletter end -->
 <!-- messages Email-->
<div class="modal fade" id="messageEmail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span id="myModalLabelP"></span></h4>
			</div>
			<div class="modal-body">
				<p id="messagePContent"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>		
<!-- messages end Email-->  
  
</div>
<div id="fb-root"></div>
<!--<script src="https://connect.facebook.net/en_US/all.js"></script>-->
<script>
    window.fbAsyncInit = function() {
      	FB.init({
       	appId : '<?php echo $application->appId; ?>',
       	status : true, <?php // check login status?>
       	cookie : true, <?php // enable cookies to allow the server to access the session?>
       	xfbml : true <?php // parse XFBML?>
      	});
        
        FB.Canvas.setAutoGrow(true);
    };
  // Load the SDK asynchronously
  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/all.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));	
</script> 
<?php echo $application->googleTracking; ?>
</body>
</html>
<?php }else{ ?>
<?php echo '<!doctype html><html><head></head><body>'. $language->aouth_message .'</body></html>'; ?>

<?php }  

