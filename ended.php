  
<div class="clearfix"></div>
  <div class="container">
    <div class="col-md-12 cat_header">
      <h2 style="text-align:left;"><?php echo $language->our_latest_finished_auctions; ?></h2>
    </div>
		<div class="row" style="margin-top:20px;">
        <?php 
		$i = 0; 	
		foreach ($endedAuctions as $row) { 
        if($row->active <> '0' && $i < 4) { 
        	$finalPrice = $row->getEndPrice($database);
			?>
			<div class="col-md-4 col-sm-4 ">
				<div class="three fa-border lineleft">
					<div class="pull-left"> <img src="<?php echo $row->picture; ?>" width="50" height="50"> </div>
					<div> 
						<span class="txt ended" style="font-size:12px;"><?php echo $row->productTitle; ?></span> <br>
						<span class="price">For <span class="red strong"><?php echo $application->currencyPreffix . number_format($finalPrice, 2) . $application->currencySuffix; ?></span></span> 
					</div>
				</div>				
			</div>
		<?php } $i++; } ?>
		</div>
	<!--</div>-->
  </div>



