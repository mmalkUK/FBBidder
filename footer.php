<?php
// Footer
// Include closing tag for body and html
?>



<div class="clearfix" style="padding-top:20px;"></div>
  <div class="footer black">
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-4">
          <ul>
            <li><a target="_top" href="<?php echo $application->fbUrl . "info?cmd=aboutus&gob=$get_ownerDB"; ?>"><?php echo $language->f_aboutus; ?></a></li>
          </ul>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-4">
          <ul>
            <li><a target="_top" href="<?php echo $application->fbUrl . "info?cmd=delivery&gob=$get_ownerDB"; ?>"><?php echo $language->f_delivery; ?></a></li>
          </ul>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-4">
          <ul>
            <li><a target="_top" href="<?php echo $application->fbUrl . "info?cmd=tandc&gob=$get_ownerDB"; ?>"><?php echo $language->f_tandc; ?></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
