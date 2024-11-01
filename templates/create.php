<div id="wpe-wps-jobs-create-screen">

	<?php
	if ( wpe_wps_can_manage() ) {
		echo do_shortcode( '[submit_job_form]' );
	}
	?>

</div>
