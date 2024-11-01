<?php
/**
 * Lists the job applications for a particular job listing.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/job-applications.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-applications
 * @category    Template
 * @version     2.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="job-manager-job-applications">
	<a href="<?php echo esc_url( add_query_arg( 'download-csv', true ) ); ?>" class="job-applications-download-csv"><?php esc_html_e( 'Download CSV', 'wp-job-manager-applications' ); ?></a>

	<p>
	<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No data being processed.
		printf( esc_html__( 'The job applications for "%s" are listed below.', 'wp-job-manager-applications' ), '<a href="' . esc_url_raw( add_query_arg( 'job_id', absint( $_GET['job_id'] ), spaces_wpjm_get_jobs_permalink() ) ) . '">' . esc_html( get_the_title( $job_id ) ) . '</a>' );
	?>
			<a href="<?php echo esc_url_raw( spaces_wpjm_get_jobs_permalink() . '/dashboard' ); ?>"><?php esc_html_e( '&#8629; Return to Dashboard', 'spaces-wpjm' ); ?></a></p>
	<div class="job-applications">
		<form class="filter-job-applications" method="GET">
			<p>
				<select name="application_status">
					<option value=""><?php esc_html_e( 'Filter by status', 'wp-job-manager-applications' ); ?>...</option>
					<?php foreach ( get_job_application_statuses() as $name => $label ) : ?>
						<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $application_status, $name ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<select name="application_orderby">
					<option value=""><?php esc_html_e( 'Newest first', 'wp-job-manager-applications' ); ?></option>
					<option value="name" <?php selected( $application_orderby, 'name' ); ?>><?php esc_html_e( 'Sort by name', 'wp-job-manager-applications' ); ?></option>
					<option value="rating" <?php selected( $application_orderby, 'rating' ); ?>><?php esc_html_e( 'Sort by rating', 'wp-job-manager-applications' ); ?></option>
				</select>
				<input type="hidden" name="action" value="show_applications" />
				<input type="hidden" name="job_id" value="<?php echo absint( $_GET['job_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Handled elsewhere (template). ?>" />
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Handled elsewhere (template).
				if ( ! empty( $_GET['page_id'] ) ) :
					?>
					<input type="hidden" name="page_id" value="<?php echo absint( $_GET['page_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Handled elsewhere (template). ?>" />
				<?php endif; ?>
			</p>
		</form>
		<ul class="job-applications">
			<?php foreach ( $applications as $application ) : ?>
				<li class="job-application" id="application-<?php esc_attr_e( $application->ID ); ?>">
					<header>
						<?php job_application_header( $application ); ?>
					</header>

					<?php do_action( 'job_application_content_start' ); ?>

					<section class="job-application-content">
						<?php job_application_meta( $application ); ?>
						<?php job_application_content( $application ); ?>
					</section>
					<section class="job-application-edit">
						<?php job_application_edit( $application ); ?>
					</section>
					<section class="job-application-notes">
						<?php job_application_notes( $application ); ?>
					</section>

					<?php do_action( 'job_application_content_end' ); ?>

					<footer>
						<?php job_application_footer( $application ); ?>
					</footer>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
	</div>
</div>
