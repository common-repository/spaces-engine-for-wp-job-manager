<div id="wpe-wps-jobs-dashboard">

	<?php
	if ( wpe_wps_can_manage() ) {
			echo do_shortcode( '[job_dashboard]' );
	}
	?>

</div>
