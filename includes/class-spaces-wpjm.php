<?php
/**
 * File containing the class Spaces_WPJM.
 *
 * @package spaces-wpjm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles core plugin hooks and action setup.
 */
class Spaces_WPJM {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Main Spaces For WP Job Manager Instance.
	 *
	 * Ensures only one instance of Spaces For WP Job Manager is loaded or can be loaded.
	 *
	 * @static
	 * @see Spaces_WPJM()
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Actions.
		add_action( 'init', array( $this, 'elimination' ) );
		add_action( 'init', array( $this, 'rewrites' ) );
		add_action( 'query_vars', array( $this, 'query_vars' ), 99 );
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'do_scripts' ) );
		add_action( 'wp_print_scripts', array( $this, 'do_not_do_scripts' ), 9999 );
		add_action( 'wp_print_styles', array( $this, 'do_not_do_scripts' ), 9999 );
		add_action( 'wpe_wps_frontend_settings_section_html', array( $this, 'add_frontend_setting_section_html' ), 10, 2 );
		add_action( 'job_manager_update_job_data', array( $this, 'link_job_with_space' ), 10, 2 );
		add_action( 'job_manager_job_dashboard_column_applications', array( $this, 'applications_column' ) );
		add_action( 'wp_head', array( $this, 'output_colors' ) );
		add_action( 'job_manager_applications_new_job_application', array( $this, 'redirect_after_application_submission' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'create_activity' ), 10, 3 );

		// Filters.
		add_filter( 'wpe_wps_add_settings_sections', array( $this, 'add_frontend_setting_section' ) );
		add_filter( 'has_wpjm_shortcode', array( $this, 'load_wpjm_frontend_styles' ) );
		add_filter( 'wpe_wps_primary_nav', array( $this, 'setup_nav' ) );
		add_filter( 'get_job_listings_query_args', array( $this, 'job_query' ), 10, 2 );
		add_filter( 'job_manager_locate_template', array( $this, 'locate_templates' ), 11, 3 );
		add_filter( 'wpe_wps_settings_action', array( $this, 'process_frontend_settings' ) );
		add_filter( 'submit_job_form_fields', array( $this, 'add_form_fields' ) );
		add_filter( 'job_manager_update_job_listings_message', array( $this, 'job_saved_message' ), 10, 3 );
		add_filter( 'job_manager_should_run_shortcode_action_handler', array( $this, 'enable_dashboard_shortcode_handler' ) );
		add_filter( 'job_manager_settings', array( $this, 'job_manager_settings' ) );
		add_filter( 'job_manager_get_dashboard_jobs_args', array( $this, 'job_dashboard_query' ) );
		add_filter( 'body_class', array( $this, 'add_body_classes' ) );
		add_filter( 'job_manager_user_can_edit_job', array( $this, 'allow_admins_to_edit_jobs' ), 10, 2 );

		// Admin
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'colorpickers' ) );
			add_action( 'admin_footer', array( $this, 'colorpickersjs' ) );
		}
	}

	/**
	 * Unhook the 'Applications' column output, as it is really unintuitive.
	 *
	 * @return void
	 */
	public function elimination() {
		if ( function_exists( 'wpe_wps_remove_class_action' ) ) {
			wpe_wps_remove_class_action( 'job_manager_job_dashboard_column_applications', 'WP_Job_Manager_Applications_Dashboard', 'applications_column', 10 );
		}
	}

	/**
	 * Add query vars.
	 *
	 * @param $vars
	 *
	 * @return mixed
	 */
	public function query_vars( $vars ) {
		$vars[] = 'all';
		$vars[] = 'create';
		$vars[] = 'dashboard';

		return $vars;
	}

	/**
	 * Create rewrites so we can hold our Jobs subpages.
	 *
	 * @return void
	 */
	public function rewrites() {
		$permalinks     = spaces_wpjm_get_raw_permalink_settings();
		$jobs_permalink = untrailingslashit( empty( $permalinks['jobs_archive'] ) ? 'jobs' : $permalinks['jobs_archive'] );

		add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/all', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&all=active-job-tab', 'top' );
		add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/dashboard', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&dashboard=active-job-tab', 'top' );
		add_rewrite_rule( '^spaces/([^/]*)/([^/]*)/create', 'index.php?wpe_wpspace=$matches[1]&' . esc_html( $jobs_permalink ) . '=active-space-tab&create=active-job-tab', 'top' );

	}

	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'spaces-wpjm', false, SPACES_WPJM_PLUGIN_DIR . '/languages/' );
	}

	/**
	 * Load the required scripts for the plugin's frontend.
	 */
	public function do_scripts() {
		if ( is_singular( 'wpe_wpspace' ) ) {
			wp_enqueue_style( 'spaces-wpjm', SPACES_WPJM_PLUGIN_URL . '/assets/css/main.css', array(), SPACES_WPJM_VERSION );
			wp_enqueue_script( 'spaces-wpjm', SPACES_WPJM_PLUGIN_URL . '/assets/js/main.js', array( 'jquery' ), SPACES_WPJM_VERSION, true );
		}
	}

	/**
	 * Get rid of BuddyBoss's horrific modal.
	 *
	 * @return void
	 */
	public function do_not_do_scripts() {
		if ( is_singular( 'wpe_wpspace' ) ) {
			wp_dequeue_script( 'buddyboss-theme-wpjobmanager-js' );
		}
	}

	/**
	 * Add a new 'jobs' item to our frontend settings menu.
	 *
	 * @param $sections
	 *
	 * @return mixed
	 */
	public function add_frontend_setting_section( $sections ) {
		$sections['jobs'] = array(
			'label'    => __( 'Job Settings', 'spaces-wpjm' ),
			'priority' => 5,
		);

		return $sections;
	}

	/**
	 * Display the 'Jobs' frontend settings page template.
	 *
	 * @param $section_id
	 * @param $post_id
	 *
	 * @return void
	 */
	public function add_frontend_setting_section_html( $section_id, $post_id ) {
		if ( 'jobs' === $section_id ) {
			require SPACES_WPJM_PLUGIN_DIR . '/templates/settings-jobs.php';
		}
	}

	/**
	 * Add information to the Job, when saved.
	 *
	 * @param $job_id
	 * @param $values
	 *
	 * @return void
	 */
	public function link_job_with_space( $job_id, $values ) {
		$space_id = $values['company']['company_space'];

		if ( ! $space_id ) {
			return;
		}

		update_post_meta( $job_id, '_space_id', intval( $space_id ) );
	}

	/**
	 * Creates a new Space activity, as soon as a new post is published.
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 *
	 * @return void
	 */
	public function create_activity( $new_status, $old_status, $post ) {
		if ( 'job_listing' != $post->post_type ) {
			return;
		}

		if ( 'publish' == $new_status  && 'preview' == $old_status ) {
			$job_id = $post->ID;

			$space_id = get_post_meta( $job_id, '_space_id', true );

			if ( ! $space_id ) {
				return;
			}

			// We only create an activity when a job is linked, not whenever it is updated
			if ( ! get_post_meta( $job_id, '_activity_id', true ) && 'publish' == $post->post_status ) {
				$job_link = add_query_arg( 'job_id', $job_id, spaces_wpjm_get_jobs_permalink() );

				// Add a BuddyPress activity for the Space that has created the job
				$args = array(
					'component'         => 'wpe_wpspace',
					'user_id'           => 0,
					'item_id'           => $space_id,
					'type'              => 'new_job_space',
					'action'            => '<a href="' . get_permalink( $space_id ) . '">' . get_the_title( $space_id ) . ' </a>posted a new <a href="' . esc_url_raw( $job_link ) . '">' . spaces_wpjm_get_job_base_slug() . '</a>',
					'content'           => '<a href="' . esc_url_raw( $job_link ) . '">' . get_the_title( $job_id ) . '</a>',
					'secondary_item_id' => get_post( $job_id )->post_author,
					'primary_link'      => spaces_wpjm_get_jobs_permalink(),
				);
				$activity_id = bp_activity_add( $args );

				update_post_meta( $job_id, '_activity_id', intval( $activity_id ) );
			}
		}
	}

	/**
	 * WP Job Manager looks for a shortcode in post content to load its stylesheet. We don't do it that way, so must filter it manually.
	 *
	 * @return bool
	 */
	public function load_wpjm_frontend_styles( $has_wpjm_shortcode ) {
		if ( wpe_wps_is_space() ) {
			return true;
		}

		return $has_wpjm_shortcode;
	}

	/**
	 * Setup nav item via hook.
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	public function setup_nav( $items ) {
		$permalinks        = spaces_wpjm_get_raw_permalink_settings();
		$archive_permalink = untrailingslashit( empty( $permalinks['jobs_archive'] ) ? 'job-listings' : $permalinks['jobs_archive'] );

		$items[ $archive_permalink ] = array(
			'id'       => $archive_permalink,
			'title'    => apply_filters( 'spaces_wpjm_tab_title', ucfirst( $archive_permalink ) ),
			'callback' => 'spaces_wpjm_setup_jobs_page',
		);

		return $items;
	}


	/**
	 * Checks to see if this jobs screen is on a Space, and if so, only displays Jobs posted by that Space.
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return array
	 */
	public function job_query( $query_args, $args ) {
		if ( wp_doing_ajax() && $_REQUEST['form_data'] ) {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Handled outside of filter @see class-wp-job-manager-ajax.php
			parse_str( $_REQUEST['form_data'], $form );

			if ( isset( $form['space_id'] ) && wpe_wps_is_space_by_id( $form['space_id'] ) ) {
				$query_args['meta_query'][] = array(
					array(
						'key'   => '_space_id',
						'value' => $form['space_id'],
					),
				);
			}
		} elseif ( wpe_wps_is_space() ) {
			$query_args['meta_query'][] = array(
				array(
					'key'   => '_space_id',
					'value' => wpe_wps_get_id(),
				),
			);
		}

		return $query_args;
	}

	/**
	 * If we are on a single Space, override WP Job Manager templates.
	 *
	 * @param $template
	 * @param $template_name
	 * @param $template_path
	 *
	 * @return mixed|string
	 */
	public function locate_templates( $template, $template_name, $template_path ) {
		if ( wpe_wps_is_space() || wpe_wps_is_space_by_ajax() ) {
			if ( 'content-job_listing.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/content-job_listing.php';
			}

			if ( 'job-filters.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-filters.php';
			}

			if ( 'job-submit.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-submit.php';
			}

			if ( 'job-application.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-application.php';
			}

			if ( 'job-dashboard.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-dashboard.php';
			}

			if ( 'job-preview.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-preview.php';
			}

			if ( 'job-submitted.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/job-submitted.php';
			}

			if ( 'application-form.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/wp-job-manager-applications/application-form.php';
			}

			if ( 'job-applications.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/wp-job-manager-applications/job-applications.php';
			}

			if ( 'job-application-footer.php' === $template_name ) {
				return SPACES_WPJM_PLUGIN_DIR . '/templates/wp-job-manager-applications/job-application-footer.php';
			}
		}

		return $template;
	}

	/**
	 * Process our Jobs page of the frontend settings.
	 *
	 * @param $params
	 *
	 * @return array|mixed
	 */
	public function process_frontend_settings( $params ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Handled outside of filter @see class-wpe-wps-frontend-settings.php
		if ( isset( $params['jobs'] ) ) {
			if ( ! empty( $params['jobs']['email'] ) ) {
				update_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_email', sanitize_text_field( $params['jobs']['email'] ) );
			} else {
				delete_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_email' );
			}

			if ( ! empty( $params['jobs']['video'] ) ) {
				update_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_video', sanitize_text_field( $params['jobs']['video'] ) );
			} else {
				delete_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_video' );
			}

			if ( ! empty( $params['jobs']['twitter'] ) ) {
				update_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_twitter', sanitize_text_field( $params['jobs']['twitter'] ) );
			} else {
				delete_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_twitter' );
			}

			if ( ! empty( $params['jobs']['show_filters'] ) ) {
				update_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_show_filters', sanitize_text_field( $params['jobs']['show_filters'] ) );
			} else {
				delete_post_meta( $_POST['spaceid'], 'wpe_wps_jobs_show_filters' );
			}

			return array(
				'message' => sprintf(
				/* translators: %s: The singular label for a Space */
					esc_html__( '%s settings updated successfully', 'spaces-wpjm' ),
					esc_html( wpe_wps_get_singular_label() )
				),
				'refresh' => true,
			);
		} else {
			return $params;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * If using a Space to submit a job, add a field to hold the Space ID. It will be auto-populated via the template.
	 *
	 * @param $fields
	 *
	 * @return array|mixed
	 */
	public function add_form_fields( $fields ) {
		if ( ! wpe_wps_is_space() ) {
			return $fields;
		}

		$fields['company']['company_space'] = array(
			'label'       => esc_html__( 'Space ID', 'spaces-wpjm' ),
			'type'        => 'text',
			'required'    => false,
			'priority'    => 999,
			'placeholder' => esc_html__( 'Enter a Space ID', 'spaces-wpjm' ),
		);

		return $fields;
	}

	/**
	 * Change the URL when a job is saved, so we redirect back to our Spaces job page.
	 *
	 * @param $save_message
	 * @param $job_id
	 * @param $values
	 *
	 * @return string
	 */
	public function job_saved_message( $save_message, $job_id, $values ) {
		$unlinked = wp_strip_all_tags( $save_message );

		$linked = '<a href="' . esc_url_raw( add_query_arg( 'job_id', $job_id, spaces_wpjm_get_jobs_permalink() ) ) . '">' . $unlinked . '</a>';

		return $linked;
	}

	/**
	 * Enables shortcodes on our implementation of the Jobs Dashboard.
	 *
	 * @param $should_run_handler
	 *
	 * @return bool|mixed
	 */
	public function enable_dashboard_shortcode_handler( $should_run_handler ) {
		if ( wpe_wps_is_space() ) {
			$should_run_handler = true;
		}

		return $should_run_handler;
	}

	/**
	 * Adds the ability to set a color for a Job Type.
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function job_manager_settings( $settings ) {
		$settings['spaces_job_colors'] = array(
			__( 'Job Colors for Spaces', 'spaces-wpjm' ),
			$this->create_options(),
		);

		return $settings;
	}

	/**
	 * Saves our Job Type color.
	 *
	 * @return array
	 */
	private function create_options() {
		$terms   = get_terms( 'job_listing_type', array( 'hide_empty' => false ) );
		$options = array();

		if ( isset( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_slug = '';
				$term_name = '';
				if ( isset( $term->slug ) ) {
					$term_slug = $term->slug;
				}
				if ( isset( $term->name ) ) {
					$term_name = $term->name;
				}
				$options[] = array(
					'name'        => 'spaces_wpjm_job_type_' . $term_slug . '_color',
					'std'         => '',
					'placeholder' => '#',
					'label'       => $term_name,
					'desc'        => __( 'Hex value for the color of this job type.', 'spaces_wpjm' ),
					'attributes'  => array(
						'data-default-color' => '#fff',
						'data-type'          => 'colorpicker',
					),
				);
			}
		}

		return $options;
	}

	/**
	 * Show the count of applications in the job dashboard
	 *
	 * @param  WP_Post Job
	 */
	public function applications_column( $job ) {
		global $wp;

		$count = get_job_application_count( $job->ID );

		$url = add_query_arg(
			array(
				'action' => 'show_applications',
				'job_id' => $job->ID,
			),
			home_url( $wp->request )
		);

		echo $count ? '<a href="' . esc_url_raw( $url ) . '">' . esc_html__( 'View', 'spaces-wpjm' ) . ' (' . esc_html( $count ) . ')</a>' : '&ndash;';
	}

	/**
	 * Adds styles to our Space based on Job Colors set viw settings.
	 *
	 * @return void
	 */
	public function output_colors() {
		if ( ! wpe_wps_is_space() ) {
			return;
		}

		$terms = get_terms( 'job_listing_type', array( 'hide_empty' => false ) );

		echo "<style id='spaces_job_colors'>\n";

		if ( isset( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$color = get_option( 'spaces_wpjm_job_type_' . $term->slug . '_color', '#fff' );

				if ( function_exists( 'color2rgba' ) ) {
					$background = color2rgba( $color, 0.07 );
				} else {
					$background = '#fff';
				}

				printf( "#spaces-wpjm ul.job_listings .job_listing.job_listing_type-%s { border-color: var(--bb-content-border-color) var(--bb-content-border-color) var(--bb-content-border-color) %s; } \n", esc_attr( $term->slug ), esc_attr( $color ) );
				printf( "#spaces-wpjm ul.job_listings ul.meta li.job-type.%s { border-color: %s; color: %s; background-color: %s; } \n", esc_attr( $term->slug ), esc_attr( $color ), esc_attr( $color ), esc_attr( $background ) );

			}
		}

		echo "</style>\n";
	}

	public function redirect_after_application_submission( $application_id, $job_id ) {
		$space_id = get_post_meta( $job_id, '_space_id', true );

		if ( ! $space_id ) {
			return;
		}

		// Redirect to show the success message and prevent duplicate submissions.
		if ( wp_safe_redirect(
			add_query_arg(
				array(
					'job_id'              => $job_id,
					'application_success' => '1',

				),
				get_the_permalink( $space_id ) . spaces_wpjm_get_slug()
			)
		) ) {
			exit;
		}
	}

	/**
	 * Enqueue a Hex Color Picker inside the WP Job Manager settings screens.
	 *
	 * @param $hook
	 *
	 * @return void
	 */
	public function colorpickers( $hook ) {
		$screen = get_current_screen();

		if ( 'job_listing_page_job-manager-settings' !== $screen->id ) {
			return;
		}

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Initiate our color pickers.
	 *
	 * @return void
	 */
	public function colorpickersjs() {
		$screen = get_current_screen();

		if ( 'job_listing_page_job-manager-settings' !== $screen->id ) {
			return;
		}
		?>
		<script>
			jQuery(document).ready(function($){
				$( 'input[data-type="colorpicker"]' ).wpColorPicker();
			});
		</script>
		<?php
	}

	/**
	 * Alters the WPJM Dashboard Query. Would normally show only current user's jobs.
	 * We want all user's jobs, but only for the current Space.
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return mixed
	 */
	public function job_dashboard_query( $job_dashboard_args ) {
		if ( wpe_wps_is_space() ) {
			$job_dashboard_args['post_status'] = array( 'publish', 'expired', 'pending' );
			$job_dashboard_args['meta_query']  = array(
				array(
					'key'   => '_space_id',
					'value' => wpe_wps_get_id(),
				),
			);
			unset( $job_dashboard_args['author'] );
		} elseif ( wpe_wps_is_space_by_ajax() ) {
			$job_dashboard_args['post_status'] = array( 'publish', 'expired', 'pending' );
			$job_dashboard_args['meta_query']  = array(
				array(
					'key'   => '_space_id',
					'value' => wpe_wps_get_id_by_ajax(),
				),
			);
			unset( $job_dashboard_args['author'] );
		}

		return $job_dashboard_args;
	}

	/**
	 * Add a class to the body of a Space Jobs page, to assist with styling.
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function add_body_classes( $classes ) {
		if ( is_singular( 'wpe_wpspace' ) ) {

			global $wp_query;

			if ( array_key_exists( spaces_wpjm_get_slug(), $wp_query->query_vars ) ) {
				$classes[] = 'wpe-wps-jobs';
			}
		}

		return $classes;
	}

	/**
	 * Allows Space admins to also edit jobs.
	 *
	 * @param $can_edit
	 * @param $job_id
	 *
	 * @return bool|mixed
	 */
	public function allow_admins_to_edit_jobs( $can_edit, $job_id ) {
		if ( ! $job_id ) {
			$job_id = isset( $_GET['job_id'] ) ? esc_html( $_GET['job_id'] ) : '';
		}

		if ( $job_id ) {
			$space_id = get_post_meta( $job_id, '_space_id', true );

			if ( ! $space_id ) {
				return $can_edit;
			}

			if ( wpe_wps_can_manage( get_current_user_id(), get_post( $space_id ) ) ) {
				return true;
			}
		}

		return $can_edit;
	}
}
