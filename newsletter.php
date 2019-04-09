          <div class="clearfix"></div>
		  <div class="container">
			  <div class="row" style="margin-top:20px;">
				  <div class="col-md-12">
					  <div class="newsletter clearfix">
						<div class="pull-left paddingRight5"><span><a target="_top" href="<?php echo $application->facebookLink; ?>"><img src="image/ico-Facebook.png"/></a></span></div>
						<div class="pull-left paddingRight5"><span><a target="_top" href="<?php echo $application->twitterLink; ?>"><img src="image/ico-Twitter.png"/></a></span></div>
						<div class="pull-left paddingRight5"><span><a target="_top" href="rss.xml.php?gob=<?php echo $get_ownerDB; ?>"><img src="image/ico-Rss.png"/></a></span></div>				
						  <h3><span class="pull-right" style="font-size:18px;">Newsletter</span></h3>
						  <div>
							  <form name="newsletterFrm" method="post" action="">
								<input type="text" name="newsletterEmail" id="newsletterEmail" class="email" value="<?php echo $language->enter_your_email; ?>">
								<input type="hidden" name="signed_request" value="<?php echo $_REQUEST["signed_request"]; ?>" />
								<input type="submit" value="Subscribe" class="btn btn-primary" id="newsletterSubmit">
							  </form>
						  </div>
					  </div>
				  </div>
				</div>
          </div>
		  
<!-- messages -->
<div class="modal fade" id="emailAdded" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php echo $language->confirmation; ?></h4>
      </div>
      <div class="modal-body">
        <?php echo $language->newsletter_confirmation; ?>
      </div>
      <div class="modal-footer">
       
        <button type="button" class="btn btn-primary">OK</button>
      </div>
    </div>
  </div>
</div>		
<!-- messages end --> 
