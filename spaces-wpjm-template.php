<?php
/**
 * Functions
 *
 * @package     spaces-wpjm
 */

/**
 * Set up the mainJobs view inside a Space.
 *
 * @return false|string
 */
function spaces_wpjm_setup_jobs_page() {
	global $wp_query;

	if ( in_array( 'active-job-tab', $wp_query->query_vars, true ) ) {
		// We are on a jobs page, let's dig deeper
		$active_tab = array_search( 'active-job-tab', $wp_query->query_vars, true );
	} else {
		$active_tab = 'all';
	}

	ob_start(); ?>
	<div id="spaces-wpjm">

	<?php if ( wpe_wps_can_manage() ) : ?>
	<nav class="wpe-wps-jobs-nav main-navs bp-navs dir-navs bp-subnavs" role="navigation" aria-label="Directory menu">
		<ul class="component-navigation spaces-nav">
			<li id="wpe-wps-index-all" class="wpe-wps-index-scope-link <?php echo 'all' === $active_tab ? 'selected' : ''; ?>">
				<a href="<?php echo esc_url_raw( spaces_wpjm_get_jobs_permalink() ); ?>" class="wpe-wps-jobs-nav-button" id="wpe-wps-show-jobs" data-scope="all">
					<div class="bb-component-nav-item-point">
							<?php esc_html_e( 'All Jobs', 'spaces-wpjm' ); ?>
					</div>
				</a>
			</li>
			<li id="" class="wpe-wps-index-scope-link <?php echo 'dashboard' === $active_tab ? 'selected' : ''; ?>">
				<a href="<?php echo esc_url_raw( spaces_wpjm_get_jobs_permalink() . '/dashboard' ); ?>" class="wpe-wps-jobs-nav-button" id="wpe-wps-show-dashboard" data-scope="all">
					<div class="bb-component-nav-item-point">
							<?php esc_html_e( 'Dashboard', 'spaces-wpjm' ); ?>
					</div>
				</a>
			</li>
			<li class="no-ajax space-create create-button <?php echo 'create' === $active_tab ? 'selected' : ''; ?>">
				<a href="<?php echo esc_url_raw( spaces_wpjm_get_jobs_permalink() . '/create' ); ?>" class="wpe-wps-jobs-nav-button" id="wpe-wps-create-job">
					<div class="bb-component-nav-item-point">
							<?php esc_html_e( 'Submit a Job', 'spaces-wpjm' ); ?>
					</div>
				</a>
			</li>
		</ul><!-- .component-navigation -->
	</nav>
		<?php endif; ?>

		<?php require_once SPACES_WPJM_PLUGIN_DIR . '/templates/' . $active_tab . '.php'; ?>

	</div>
	<?php
	return ob_get_flush();
}

/**
 * Get the jobs page for a Space.
 *
 * @return string
 */
function spaces_wpjm_get_jobs_permalink( $post = null ) {
	if ( ! $post ) {
		$post = get_post();
	}

	return get_the_permalink( $post ) . spaces_wpjm_get_slug();
}

/**
 * Get the current permalinks for WP Job Manager.
 *
 * @return mixed|null
 */
function spaces_wpjm_get_raw_permalink_settings() {
	return json_decode( get_option( 'job_manager_permalinks' ), true );
}

/**
 * Get the current job base slug for WP Job Manager.
 *
 * @return mixed|null
 */
function spaces_wpjm_get_job_base_slug() {
	$permalinks = spaces_wpjm_get_raw_permalink_settings();

	return $permalinks['job_base'];
}

/**
 * Gets the slug used for jobs, which can be changed under Settings > Permalinks.
 *
 * @return string
 */
function spaces_wpjm_get_slug() {
	$permalinks = spaces_wpjm_get_raw_permalink_settings();

	return untrailingslashit( empty( $permalinks['jobs_archive'] ) ? 'job-listings' : $permalinks['jobs_archive'] );
}

/**
 * Creates a map for our job listings
 *
 * @return false|string
 */
function spaces_wpjm_map() {
	$api_key = wpe_wps_get_option( 'google_api' );

	// Find an API key. Spaces Engine takes priority.
	if ( ! empty( wpe_wps_get_option( 'google_api' ) ) ) {
		$api_key = wpe_wps_get_option( 'google_api' );
	} elseif ( ! empty( get_option( 'job_manager_google_maps_api_key' ) ) ) {
		$api_key = get_option( 'job_manager_google_maps_api_key' );
	} else {
		$error = new WP_Error( 013, 'There was a problem getting some Space information (013).' );
		return $error->get_error_message();
	}

	$address = get_the_job_location();

	if ( false === $address ) {
		$error = new WP_Error( 014, 'Please enter a valid address.' );
		return $error->get_error_message();
	}

	ob_start();
	?>

	<iframe
			width="300"
			height="300"
			style="border:0;border-radius: 10px;"
			loading="lazy"
			allowfullscreen
			src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr( $api_key ); ?>
			&q=<?php echo esc_attr( rawurlencode( $address ) ); ?>">
	</iframe>

	<?php
	return ob_get_flush();
}

/**
 * Show deadline on job pages
 */
function spaces_wpjm_display_the_deadline( $post_id ) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	if ( ! is_plugin_active( 'wp-job-manager-application-deadline/wp-job-manager-application-deadline' ) ) {
		return;
	}

	$deadline = get_post_meta( $post_id, '_application_deadline', true );
	$expiring = false;
	$expired  = false;
	$date_str = null;

	if ( $deadline ) {
		$expiring_days = apply_filters( 'job_manager_application_deadline_expiring_days', 2 );
		$expiring      = ( floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= -$expiring_days );
		$expired       = ( floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) > 0 );
		$date_str      = date_i18n( get_option( 'date_format' ), strtotime( $deadline ) );
	}

	// Do not display anything if listing is already expired.
	if ( is_singular( 'job_listing' ) && $expired ) {
		return;
	}

	$timestamp = strtotime( $deadline );

	/**
	 * Filters the display string for the application closing date.
	 *
	 * @since 1.2.1
	 *
	 * @param string $date_str  The default date string to be displayed.
	 * @param string $timestamp The timestamp of the closing date.
	 */
	$date_str = apply_filters( 'job_manager_application_deadline_closing_date_display', $date_str, $timestamp );

	if ( $date_str ) {
		echo '<div class="application-deadline ' . ( $expiring ? 'expiring' : '' ) . ' ' . ( $expired ? 'expired' : '' ) . '"><label>' . ( $expired ? __( 'Closed', 'wp-job-manager-application-deadline' ) : __( 'Closes', 'wp-job-manager-application-deadline' ) ) . ':</label> ' . $date_str . '</div>';
	}
}

