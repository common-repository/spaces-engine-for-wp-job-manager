<?php
/**
 * File containing the class Spaces_WPJM_Dependency_Checker.
 *
 * @package spaces-wpjm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles checking for Spaces For WP Job Manager's dependencies.
 */
class Spaces_WPJM_Dependency_Checker {
	const MINIMUM_PHP_VERSION = '5.6.20';
	const MINIMUM_WP_VERSION  = '4.9.0';

	/**
	 * Check if Spaces For WP Job Manager's dependencies have been met.
	 *
	 * @return bool True if we should continue to load the plugin.
	 */
	public static function check_dependencies() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! self::check_php() ) {
			add_action( 'admin_notices', array( 'Spaces_WPJM_Dependency_Checker', 'add_php_notice' ) );
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );

			return false;
		}

		if ( ! self::check_wp() ) {
			add_action( 'admin_notices', array( 'Spaces_WPJM_Dependency_Checker', 'add_wp_notice' ) );
			add_filter( 'plugin_action_links_' . SPACES_WPJM_PLUGIN_BASENAME, array( 'Spaces_WPJM_Dependency_Checker', 'wp_version_plugin_action_notice' ) );
		}

		if ( ! self::check_spaces() ) {
			add_action( 'admin_notices', array( 'Spaces_WPJM_Dependency_Checker', 'add_spaces_notice' ) );
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );

			return false;
		}

		if ( ! self::check_wpjm() ) {
			add_action( 'admin_notices', array( 'Spaces_WPJM_Dependency_Checker', 'add_wpjm_notice' ) );
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );

			return false;
		}

		return true;
	}

	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	private static function check_php() {
		return version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @access private
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is version of PHP that WP Job Manager requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( '<strong>Spaces For WP Job Manager</strong> requires a minimum PHP version of %1$s, but you are running %2$s.', 'spaces-wpjm' ), self::MINIMUM_PHP_VERSION, phpversion() );

		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'spaces-wpjm' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'spaces-wpjm' )
		);
		echo '</p></div>';
	}

	/**
	 * Checks for our Spaces Engine requirement.
	 *
	 * @return bool
	 */
	private static function check_spaces() {
		$version = get_option( 'WPE_WPS_PLUGIN_VERSION' );

		if ( is_plugin_active( 'spaces/wpe-wps.php' ) && version_compare( $version, '1.2.1', '>=' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Adds notice in WP Admin that Spaces Engine is required.
	 *
	 * @access private
	 */
	public static function add_spaces_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$message = __( '<strong>Spaces For WP Job Manager</strong> requires Spaces Engine (version 1.2.1 or later) to be installed and activated.', 'spaces-wpjm' );

		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$spaces_url = 'https://spacesengine.com';
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $spaces_url ),
			esc_html__( 'Learn about Spaces Engine', 'spaces-wpjm' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'spaces-wpjm' )
		);
		echo '</p></div>';
	}

	/**
	 * Checks for our WP Job Manager requirement.
	 *
	 * @return bool
	 */
	private static function check_wpjm() {
		return is_plugin_active( 'wp-job-manager/wp-job-manager.php' );
	}

	/**
	 * Adds notice in WP Admin that WP Job Manager is required.
	 *
	 * @access private
	 */
	public static function add_wpjm_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$message = __( '<strong>Spaces For WP Job Manager</strong> requires WP Job Manager to be installed and activated.', 'spaces-wpjm' );

		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$spaces_url = 'https://wordpress.org/plugins/wp-job-manager/';
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $spaces_url ),
			esc_html__( 'Download WP Job Manager', 'spaces-wpjm' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'spaces-wpjm' )
		);
		echo '</p></div>';
	}

	/**
	 * Deactivate self.
	 */
	public static function deactivate_self() {
		deactivate_plugins( SPACES_WPJM_PLUGIN_BASENAME );
	}

	/**
	 * Checks for our WordPress version requirement.
	 *
	 * @return bool
	 */
	private static function check_wp() {
		global $wp_version;

		return version_compare( $wp_version, self::MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of WordPress is not met.
	 *
	 * @access private
	 */
	public static function add_wp_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$update_action_link = '';
		if ( current_user_can( 'update_core' ) ) {
			// translators: %s is the URL for the page where users can go to update WordPress.
			$update_action_link = ' ' . sprintf( __( 'Please <a href="%s">update WordPress</a> to avoid issues.', 'spaces-wpjm' ), esc_url( self_admin_url( 'update-core.php' ) ) );
		}

		echo '<div class="error">';
		echo '<p>' . wp_kses_post( __( '<strong>Spaces For WP Job Manager</strong> requires a more recent version of WordPress.', 'spaces-wpjm' ) . $update_action_link ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add admin notice when WP upgrade is required.
	 *
	 * @access private
	 *
	 * @param array $actions Actions to show in WordPress admin's plugin list.
	 * @return array
	 */
	public static function wp_version_plugin_action_notice( $actions ) {
		if ( ! current_user_can( 'update_core' ) ) {
			$actions[] = '<strong style="color: red">' . esc_html__( 'WordPress Update Required', 'spaces-wpjm' ) . '</strong>';
		} else {
			$actions[] = '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" style="color: red">' . esc_html__( 'WordPress Update Required', 'spaces-wpjm' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Returns the screen IDs where dependency notices should be displayed.
	 *
	 * @return array
	 */
	private static function get_critical_screen_ids() {
		return array( 'dashboard', 'plugins', 'plugins-network' );
	}
}
