<?php
/**
 * Content for job submission (`[submit_job_form]`) shortcode.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-submit.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.34.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$job_fields['application']['value']         = get_post_meta( get_the_ID(), 'wpe_wps_jobs_email', true ) ?? '';
$company_fields['company_name']['value']    = wpe_wps_get_title();
$company_fields['company_website']['value'] = get_the_permalink();
$company_fields['company_tagline']['value'] = wpe_wps_get_description();
$company_fields['company_video']['value']   = get_post_meta( get_the_ID(), 'wpe_wps_jobs_video', true ) ?? '';
$company_fields['company_twitter']['value'] = get_post_meta( get_the_ID(), 'wpe_wps_jobs_twitter', true ) ?? '';
$company_fields['company_space']['value']   = get_the_ID();

$avatar = get_post_meta( get_the_ID(), 'avatar_id', true );
if ( $avatar ) {
	$company_fields['company_logo']['value'] = wp_get_attachment_url( $avatar );
} else {
	$avatar_url                              = get_post_meta( get_the_ID(), 'avatar', true );
	$company_fields['company_logo']['value'] = esc_url_raw( $avatar_url );
}

global $job_manager;
?>
<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-job-form" class="job-manager-form" enctype="multipart/form-data">

	<?php
	if ( isset( $resume_edit ) && $resume_edit ) {
		printf( '<p><strong>' . esc_html__( 'You are editing an existing job. %s', 'wp-job-manager' ) . '</strong></p>', '<a href="?job_manager_form=submit-job&new=1&key=' . esc_attr( $resume_edit ) . '">' . esc_html__( 'Create A New Job', 'wp-job-manager' ) . '</a>' );
	}
	?>

	<?php do_action( 'submit_job_form_start' ); ?>

	<?php if ( job_manager_user_can_post_job() || job_manager_user_can_edit_job( $job_id ) ) : ?>

		<!-- Job Information Fields -->
		<?php do_action( 'submit_job_form_job_fields_start' ); ?>

		<?php foreach ( $job_fields as $key => $field ) : ?>
			<fieldset class="fieldset-<?php echo esc_attr( $key ); ?> fieldset-type-<?php echo esc_attr( $field['type'] ); ?>">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . wp_kses_post( apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-job-manager' ) . '</small>', $field ) ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php
					get_job_manager_template(
						'form-fields/' . $field['type'] . '-field.php',
						array(
							'key'   => $key,
							'field' => $field,
						)
					);
					?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<?php do_action( 'submit_job_form_job_fields_end' ); ?>

		<!-- Company Information Fields -->
		<?php if ( $company_fields ) : ?>

		<div style="display: none">
			<h2><?php esc_html_e( 'Company Details', 'wp-job-manager' ); ?></h2>

			<?php do_action( 'submit_job_form_company_fields_start' ); ?>

			<?php foreach ( $company_fields as $key => $field ) : ?>
				<fieldset class="fieldset-<?php echo esc_attr( $key ); ?> fieldset-type-<?php echo esc_attr( $field['type'] ); ?>">
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . wp_kses_post( apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . __( '(optional)', 'wp-job-manager' ) . '</small>', $field ) ); ?></label>
					<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
						<?php
						get_job_manager_template(
							'form-fields/' . $field['type'] . '-field.php',
							array(
								'key'   => $key,
								'field' => $field,
							)
						);
						?>
					</div>
				</fieldset>
			<?php endforeach; ?>

			<?php do_action( 'submit_job_form_company_fields_end' ); ?>

		</div>

		<?php endif; ?>

		<?php do_action( 'submit_job_form_end' ); ?>

		<p>
			<input type="hidden" name="job_manager_form" value="<?php echo esc_attr( $form ); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<input type="submit" name="submit_job" class="button" value="<?php echo esc_attr( $submit_button_text ); ?>" />
			<span class="spinner" style="background-image: url(<?php echo esc_url( includes_url( 'images/spinner.gif' ) ); ?>);"></span>
		</p>

	<?php else : ?>

		<?php do_action( 'submit_job_form_disabled' ); ?>

	<?php endif; ?>
</form>
