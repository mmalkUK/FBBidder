  <div class="container">
    <div class="row">
      <div class="col-md-12 slideshow">
        <div>
          <ul class="slides">
			<?php
			$banners = $database->selectArrayClass('banners', 'Banner', null, "WHERE instanceId='" . $ownerDB . "'", 'ORDER BY sortOrder ASC');
			foreach($banners as $row){
				echo '<li> <a target="_new" href="' . $row->link . '"> <img src="' . $row->path . '" alt="' . $row->alt . '" /> </a> </li>';
			}
			?>
          </ul>
        </div>
      </div>
    </div>
  </div>


