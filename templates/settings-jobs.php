<?php

/**
 * This template holds all fields for the 'Jobs' page of the Space settings section.
 */

$permalinks   = spaces_wpjm_get_raw_permalink_settings();
$email_url    = get_post_meta( get_the_ID(), 'wpe_wps_jobs_email', true );
$video_url    = get_post_meta( get_the_ID(), 'wpe_wps_jobs_video', true );
$twitter      = get_post_meta( get_the_ID(), 'wpe_wps_jobs_twitter', true );
$show_filters = get_post_meta( get_the_ID(), 'wpe_wps_jobs_show_filters', true );
?>

<div id="wpe-wps-jobs" class="wpe-wps-settings-section">
	<h2><?php esc_html_e( 'Job Settings', 'spaces-wpjm' ); ?></h2>
	<form method="post" id="wpe-wps-settings-jobs" name="settings-jobs"  enctype='multipart/form-data'>

		<h3 class="wpe-wps-settings-section-preamble">
			<?php
			printf(
			/* translators: %s: The singular label for a Space */
				esc_html__( 'Your site administrator may have enabled job management (%1$s) for this %2$s. If you wish to manage jobs, please enable the menu tab via \'Tabs and Buttons\' on the left. If the site administrator has enabled additional settings, you will see them below.', 'spaces-wpjm' ),
				esc_html( $permalinks['jobs_archive'] ),
				esc_html( wpe_wps_get_singular_label() ),
			);
			?>
		</h3>

		<fieldset class="wpe-wps-material has-description">
			<input placeholder=" " required="required" type="text" id="jobs[email]" name="jobs[email]" value="<?php echo esc_attr( $email_url ); ?>">
			<span class="highlight"></span>
			<span class="bar"></span>
			<label for="jobs[email]"><?php esc_html_e( 'Application email/URL for job applications', 'spaces-wpjm' ); ?></label>
		</fieldset>
		<p class="wpe-wps-settings-section-description">
			<?php esc_html_e( 'Enter an email or URL to pre-fill the box on the form. Useful if all applications go to the same place.', 'spaces-wpjm' ); ?>
		</p>

		<fieldset class="wpe-wps-material has-description">
			<input placeholder=" " required="required" type="text" id="jobs[video]" name="jobs[video]" value="<?php echo esc_url_raw( $video_url ); ?>">
			<span class="highlight"></span>
			<span class="bar"></span>
			<label for="jobs[video]"><?php esc_html_e( 'Optional link to a video (will be pre-filled on all forms)', 'spaces-wpjm' ); ?></label>
		</fieldset>

		<fieldset class="wpe-wps-material has-description">
			<input placeholder=" " required="required" type="text" id="jobs[twitter]" name="jobs[twitter]" value="<?php echo esc_attr( $twitter ); ?>">
			<span class="highlight"></span>
			<span class="bar"></span>
			<label for="jobs[twitter]"><?php esc_html_e( 'Optional Twitter username (will be pre-filled on all forms)', 'spaces-wpjm' ); ?></label>
		</fieldset>

		<fieldset class="wpe-wps-slider has-description">
			<span class="checkbox-label"><?php esc_html_e( 'Enable filters?', 'spaces-wpjm' ); ?></span>
			<label class="switch">
				<input id="jobs[show_filters]" type="checkbox" name="jobs[show_filters]"
					<?php checked( 'on', $show_filters, true ); ?>>
				<span class="slider round"></span>
			</label>
		</fieldset>
		<p class="wpe-wps-settings-section-description">
			<?php esc_html_e( 'If your Space has lots of Jobs, enable filters to make them easier to sort.', 'spaces-wpjm' ); ?>
		</p>

		<?php wp_nonce_field( 'update-space-settings' ); ?>
		<div class="wpe-wps-settings-action-wrapper">
			<button class="wpe-wps-settings-submit button primary" id="wpe-wps-jobs-submit" type="submit" data-section="jobs">
				<?php
				printf(
				/* translators: %s: The singular label for a Space */
					esc_html__( 'Save %s Settings', 'spaces-wpjm' ),
					esc_html( wpe_wps_get_singular_label() ),
				);
				?>
				<div class="wpe-wps-ajax-spinner"></div>
			</button>
			<div class="wpe-wps-result-box wpe-wps-settings-result-box" id="wps-jobs-result"></div>
		</div>
	</form>
</div>
