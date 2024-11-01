<?php
/**
 * Plugin Name: Spaces Engine For WP Job Manager
 * Plugin URI: https://spacesengine.com/
 * Description: Allow your Spaces to post jobs directly from their page.
 * Version: 1.0.2
 * Author: Spaces Engine
 * Requires at least: 5.8
 * Tested up to: 6.2
 * Requires PHP: 7.2
 * Text Domain: spaces-wpjm
 * Domain Path: /languages/
 * License: GPL2+
 *
 * @package spaces-wpjm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
define( 'SPACES_WPJM_VERSION', '1.0.2' );
define( 'SPACES_WPJM_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SPACES_WPJM_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'SPACES_WPJM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

function spaces_wpjm_dependency_check() {
	require_once dirname( __FILE__ ) . '/includes/class-spaces-wpjm-dependency-checker.php';
	if ( ! Spaces_WPJM_Dependency_Checker::check_dependencies() ) {
		return;
	} else {
		require_once dirname( __FILE__ ) . '/includes/class-spaces-wpjm.php';
	}
}
add_action( 'admin_init', 'spaces_wpjm_dependency_check' );

require_once dirname( __FILE__ ) . '/includes/class-spaces-wpjm.php';
require_once SPACES_WPJM_PLUGIN_DIR . '/spaces-wpjm-template.php';
function spaces_wpjm_activate() {
	$permalinks     = spaces_wpjm_get_raw_permalink_settings();
	$jobs_permalink = untrailingslashit( empty( $permalinks['jobs_archive'] ) ? 'jobs' : $permalinks['jobs_archive'] );

	add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/all', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&all=active-job-tab', 'top' );
	add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/dashboard', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&dashboard=active-job-tab', 'top' );
	add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/create', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&create=active-job-tab', 'top' );

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'spaces_wpjm_activate' );

/**
 * Main instance of Spaces For WP Job Manager.
 *
 * Returns the main instance of Spaces For WP Job Manager to prevent the need to use globals.
 *
 * @return Spaces_WPJM
 */
function spaces_wpjm_init() {
	if ( class_exists( 'Spaces_WPJM' ) ) {
		return Spaces_WPJM::instance();
	} else {
		return null;
	}

}

$GLOBALS['spaces_wpjm'] = spaces_wpjm_init();
