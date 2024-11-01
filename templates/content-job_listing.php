<?php
/**
 * Job listing in the loop.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-job_listing.php.
 *
 * @see https://wpjobmanager.com/document/template-overrides/
 * @authorAutomattic
 * @package wp-job-manager
 * @categoryTemplate
 * @since 1.0.0
 * @version 1.34.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$class = '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No data being processed.
if ( ! empty( $_GET['job_id'] ) ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No data being processed.
	if ( $post->ID == $_GET['job_id'] ) {
		$class = 'open';
	}
}

if ( wp_doing_ajax() ) {
	$space_id = wpe_wps_get_id_by_ajax();
} else {
	$space_id = get_queried_object_id();
}


	wpe_wps_remove_class_action( 'job_listing_meta_end', 'WP_Job_Manager_Application_Deadline',
		'display_the_deadline', 10 );

?>
<li <?php job_listing_class( $class ); ?> data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>">
	<a class="wpe-wps-job-header">
		<?php the_company_logo(); ?>
		<div class="listing-title">
			<div class="listing-title-upper">
				<div class="position">
					<h3>
						<?php
						wpjm_the_job_title();
						?>
					</h3>

				</div>
				<span class="wpe-wps-expired-message"><?php esc_html_e( 'Expired', 'spaces-wpjm' ); ?></span>
			</div>
			<div class="icons">
				<?php
				if ( get_the_job_salary( $post ) ) :
					?>
					<div class="salary"
						data-salary="<?php echo esc_attr( get_the_job_salary( $post ) ); ?>"
						data-currency="<?php echo esc_attr( get_the_job_salary_currency( $post ) ); ?>"
						data-locale="<?php echo esc_attr( get_locale() ); ?>"
						data-unit="<?php echo esc_attr( get_the_job_salary_unit_display_text( $post ) ); ?>">
					</div>
					<?php
				endif;
				?>

				<div class="location">
					<?php
					the_job_location( false );
					?>
				</div>

				<?php
				if ( wpe_wps_can_manage() ) :
					?>

					<div class="icons-manager-only">

						<?php
						if ( function_exists( 'get_job_application_count' ) ) {
							$count = get_job_application_count( $post->ID );
							if ( 0 !== $count ) {
								/* translators: 1: Singular number of applications, 2: plural number of applications. */
								$str = sprintf(
									_n( '%d Application', '%d Applications', $count, 'spaces-wpjm' ),
									$count
								);
								echo '<div class="applications">' . esc_html( $str ) . '</div>';
							}
						}

						?>
					</div>
					<?php endif; ?>
			</div>

		</div>

		<ul class="meta">
			<?php
			do_action( 'job_listing_meta_start' );
			?>

			<?php
			if ( get_option( 'job_manager_enable_types' ) ) {
				?>
				<?php
				$types = wpjm_get_the_job_types();
				?>
				<?php
				if ( ! empty( $types ) ) :
					foreach ( $types as $type ) :
						?>
						<li class="job-type
						<?php echo esc_attr( sanitize_title( $type->slug ) ); ?>">
							<?php
							echo esc_html( $type->name );
							?>
						</li>
						<?php
					endforeach;
				endif;
				?>
				<?php
			}
			?>

			<?php
			do_action( 'job_listing_meta_end' );
			?>
		</ul>
		<div class="chevron"><i class="fas fa-chevron-down"></i></div>
	</a>
	<?php
	/**
	 * Single job listing.
	 */

	if ( job_manager_user_can_view_job_listing( $post->ID ) ) :
		?>
		<div class="single_job_listing" style="display: none">
			<?php
			if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) :
				?>
				<div class="job-manager-info">
					<?php esc_html_e( 'This listing has expired.', 'wp-job-manager' ); ?>
				</div>
				<?php else : ?>
					<?php
					/**
					 * single_job_listing_start hook
					 *
					 * @unhooked job_listing_meta_display - 20
					 * @unhooked job_listing_company_display - 30
					 */
					remove_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );
					remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
					do_action( 'single_job_listing_start' );
					?>

				<div class="job-details">
					<div class="job_dates">
						<div class="posted">
							<?php
							echo esc_html(
								wp_date(
									get_option( 'date_format' ),
									get_post_datetime( $post )->getTimestamp()
								)
							);
							?>
						</div>
						<?php spaces_wpjm_display_the_deadline( $post->ID ) ?>
					</div>
					<div class="job_description">
						<?php wpjm_the_job_description(); ?>
					</div>
					<div class="job-data">
						<?php if ( wpe_wps_can_manage() ) : ?>

							<?php
							if ( function_exists( 'get_job_application_count' ) ) :
								$count = get_job_application_count( $post->ID );
								if ( $count > 0 ) :
									$str = sprintf(
									/* translators: 1: Singular number of applications, 2: plural number of applications. */
										_n(
											'%d application submitted',
											'%d applications submitted',
											$count,
											'spaces-wpjm'
										),
										$count
									);

									$space_url = add_query_arg(
										array(
											'action' => 'show_applications',
											'job_id' => $post->ID,
										),
										spaces_wpjm_get_jobs_permalink( get_post( $space_id ) ) . '/dashboard'
									);

									?>
								<div class="spaces-wpjm-applications">
									<?php echo esc_html( $str ); ?>
									<a href="<?php echo esc_url_raw( $space_url ); ?>"> (<?php esc_html_e( 'view all', 'spaces-wpjm' ); ?>)</a>
								</div>
								<?php endif; ?>
							<?php endif; ?>

						<?php elseif ( candidates_can_apply() ) : ?>

							<?php if ( get_option( 'job_application_prevent_multiple_applications' ) && user_has_applied_for_job( get_current_user_id(), $post->ID ) ) : ?>

								<p><?php esc_html_e( 'You have already applied for this job', 'spaces-wpjm' ); ?></p>

							<?php else : ?>

								<?php get_job_manager_template( 'job-application.php' ); ?>

							<?php endif; ?>

						<?php endif; ?>


					</div>
				</div>
			<div class="spaces-wpjm-job-sidebar">
				<?php
				/**
				 * single_job_listing_sidebar_start hook
				 */
				do_action( 'single_job_listing_sidebar_start' );
				?>
				<?php if ( get_the_job_location() ) : ?>
					<div class="spaces-wpjm-location">
						<?php spaces_wpjm_map(); ?>
					</div>
				<?php endif; ?>
				<?php if ( is_user_logged_in() ) : ?>

					<a href="<?php echo esc_url_raw( add_query_arg( 'sendto', wpe_wps_get_id_by_ajax(), wpe_wps_get_user_inbox_url() ) ); ?>" class="contact_button button primary"><?php esc_attr_e( 'Ask us a question', 'spaces-wpjm' ); ?></a>

				<?php endif; ?>
				<?php
				/**
				 * single_job_listing_sidebar_end hook
				 */
				do_action( 'single_job_listing_sidebar_end' );
				?>
			</div>




					<?php
					/**
					 * single_job_listing_end hook
					 */
					do_action( 'single_job_listing_end' );
					?>
				<?php endif; ?>
		</div>
		<?php endif; ?>
</li>
