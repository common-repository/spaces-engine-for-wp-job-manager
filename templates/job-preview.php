<?php
/**
 * Job listing preview when submitting job listings.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-preview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.32.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<form method="post" id="job_preview" action="<?php echo esc_url( $form->get_action() ); ?>">
	<?php
	/**
	 * Fires at the top of the preview job form.
	 *
	 * @since 1.32.2
	 */
	do_action( 'preview_job_form_start' );
	?>
	<div class="job_listing_preview_title">
		<h2><?php esc_html_e( 'Preview', 'wp-job-manager' ); ?></h2>
		<div class="job_listing_preview_actions">
			<input type="submit" name="continue" id="job_preview_submit_button" class="button job-manager-button-submit-listing" value="<?php echo esc_attr( apply_filters( 'submit_job_step_preview_submit_text', __( 'Submit Listing', 'wp-job-manager' ) ) ); ?>" />
			<input type="submit" name="edit_job" class="button job-manager-button-edit-listing" value="<?php esc_attr_e( 'Edit listing', 'wp-job-manager' ); ?>" />
		</div>
	</div>
	<div class="job_listing_preview single_job_listing">
		<ul class="job_listings">
			<li <?php job_listing_class( 'open' ); ?> data-longitude="" data-latitude="">
				<a class="wpe-wps-job-header">
					<?php the_company_logo(); ?>
					<div class="listing-title">
						<div class="position">
							<h3><?php wpjm_the_job_title(); ?></h3>
						</div>
						<div class="icons">
							<?php if ( get_the_job_salary() ) : ?>
								<div class="salary" data-salary="<?php echo esc_attr( get_the_job_salary() ); ?>" data-currency="<?php echo esc_attr( get_the_job_salary_currency() ); ?>" data-locale="<?php echo esc_attr( get_locale() ); ?>" data-unit="<?php echo esc_attr( get_the_job_salary_unit_display_text() ); ?>"></div>
							<?php endif; ?>

							<div class="location">
								<?php the_job_location( false ); ?>
							</div>

							<?php
							if ( wpe_wps_can_manage() ) {
								if ( function_exists( 'get_job_application_count' ) ) {
									$count = 0;
									if ( 0 !== $count ) {
										/* translators: 1: Singular number of applications, 2: plural number of applications. */
										$str = sprintf( _n( '%d Application', '%d Applications', $count, 'spaces-wpjm' ), $count );
										echo '<div class="applications">' . esc_html( $str ) . '</div>';
									}
								}

								echo '<div class="posted">' . esc_html( wp_date( get_option( 'date_format' ), get_post_datetime()->getTimestamp() ) ) . '</div>';
							}
							?>
						</div>

					</div>

					<ul class="meta">
						<?php do_action( 'job_listing_meta_start' ); ?>

						<?php if ( get_option( 'job_manager_enable_types' ) ) { ?>
							<?php $types = wpjm_get_the_job_types(); ?>
							<?php
							if ( ! empty( $types ) ) :
								foreach ( $types as $type ) :
									?>
									<li class="job-type <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></li>
									<?php
								endforeach;
							endif;
							?>
						<?php } ?>

						<?php do_action( 'job_listing_meta_end' ); ?>
					</ul>
					<div class="chevron"><i class="fas fa-chevron-right"></i></div>
				</a>
				<div class="single_job_listing">
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
								<div class="job_description">
									<?php wpjm_the_job_description(); ?>
								</div>
								<div class="job-data">


							</div>


							<div class="job_application application">

								<input style="cursor: not-allowed;" type="button" class="application_button button" value="<?php esc_attr_e( 'Apply for job', 'spaces-wpjm' ); ?>" />
								<input style="cursor: not-allowed;" type="button" class="application_button button" value="<?php esc_attr_e( 'Ask us a question', 'spaces-wpjm' ); ?>" />

							</div>

							<?php
							/**
							 * single_job_listing_end hook
							 */
							do_action( 'single_job_listing_end' );
							?>
							</div>
					<?php if ( get_the_job_location() ) : ?>
						<div class="spaces-wpjm-location">
							<?php spaces_wpjm_map(); ?>
						</div>
					<?php endif; ?>
					</div>
			</li>
		</ul>

		<input type="hidden" name="job_id" value="<?php echo esc_attr( $form->get_job_id() ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( $form->get_step() ); ?>" />
		<input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form->get_form_name() ); ?>" />
	</div>
	<?php
	/**
	 * Fires at the bottom of the preview job form.
	 *
	 * @since 1.32.2
	 */
	do_action( 'preview_job_form_end' );
	?>
</form>
