<div id="wpe-wps-jobs-list">

<?php

if ( ! get_post_meta( get_the_ID(), 'wpe_wps_jobs_show_filters', true ) ) {
	echo '<style>.job_filters {display: none}</style>';
}

if ( shortcode_exists( 'jobs' ) ) {
	echo do_shortcode( '[jobs]' );
}
?>

</div>
