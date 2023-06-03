<?php
/**
 * Plugin Name:       Strive Content Calendar
 * Plugin URI:        https://strivecalendar.com/
 * Description:       Strive Content Calendar helps you manage and master your publishing schedule.
 * Version:           1.32.1
 * Requires at least: 5.2
 * Tested up to:      6.0.2
 * Requires PHP:      7.3.29
 * Author:            Strive
 * Author URI:        https://strivecalendar.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       strive
 * Domain Path:       /languages
 */
if ( ! function_exists( 'scc_freemius' ) ) {
	// Create a helper function for easy SDK access.
	function scc_freemius() {
		global $scc_freemius;

//        if (!isset($scc_freemius)) {
//            // Include Freemius SDK.
//            require_once dirname(__FILE__) . '/freemius/start.php';
//
//            $scc_freemius = fs_dynamic_init([
//                'id'                  => '8271',
//                'slug'                => 'strive-content-calendar',
//                'premium_slug'        => 'strive-content-calendar',
//                'type'                => 'plugin',
//                'public_key'          => 'pk_a122ce1e2a72d4a7a1885401d73ab',
//                'is_premium'          => true,
//                'is_premium_only'     => true,
//                'has_addons'          => false,
//                'has_paid_plans'      => true,
//                'is_org_compliant'    => false,
//                'trial'               => [
//                    'days'               => 14,
//                    'is_require_payment' => false,
//                ],
//                'has_affiliation'     => 'selected',
//                'menu'                => [
//                    'slug'           => 'strive-content-calendar',
//                    'first-path'     => 'edit.php?page=strive-content-calendar-welcome',
//                    'contact'        => true,
//                    'pricing'        => false,
//                    'support'        => false,
//                    'parent'         => [
//                        'slug' => 'edit.php',
//                    ],
//                ],
//            ]);
//        }

		return $scc_freemius;
	}

	// Init Freemius.
	scc_freemius();
	// Signal that SDK was initiated.
	do_action( 'scc_freemius_loaded' );

	// Rename "Affiliation" submenu
//    fs_override_i18n([
//        'affiliation' => __('Affiliate Program', 'strive'),
//    ], 'strive-content-calendar');
}
//
//// Redirect the pricing page straight to checkout
//function scc_redirect_freemius_pricing_to_checkout($url)
//{
//    return scc_freemius()->checkout_url();
//}
//scc_freemius()->add_filter('pricing_url', 'scc_redirect_freemius_pricing_to_checkout', 10, 1);
//
//// Hide the admin notice when free trial is started b/c Strive has its own Welcome page for this
//function scc_hide_freemius_admin_notices($show, $msg)
//{
//    if ('trial_started' == $msg['id']) {
//        // Don't show the trial promotional admin notice.
//        return false;
//    } elseif ('plan_upgraded' == $msg['id']) {
//        // Don't show the license activated admin notice.
//        return false;
//    }
//
//    return $show;
//}
//scc_freemius()->add_filter('show_admin_notice', 'scc_hide_freemius_admin_notices', 10, 2);
//
//// Hide the affiliate program notice
//scc_freemius()->add_filter('show_affiliate_program_notice', '__return_false');


define( 'STRIVEC_WEBSITE_URL', esc_url( 'https://strivecalendar.com' ) );

// Creat the Strive class
if ( ! class_exists( 'Strive_Content_Calendar' ) ) {
	class Strive_Content_Calendar {
		private static $__instance;

		public static function instance() {
			if ( ! isset( self::$__instance ) && ! ( self::$__instance instanceof self ) ) {
				self::$__instance = new self;
				self::$__instance->__setup_constants();
				self::$__instance->__includes();

				// Restrict functionaliy if license invalid

				self::$__instance->calendar       = new SCC_Calendar();
				self::$__instance->cal_settings   = new SCC_Calendar_Settings();
				self::$__instance->checklists     = new SCC_Checklists();
				self::$__instance->check_settings = new SCC_Checklists_Settings();
				self::$__instance->pipeline       = new SCC_Pipeline();
				self::$__instance->pipe_settings  = new SCC_Pipeline_Settings();
				self::$__instance->statuses       = new SCC_Statuses();
				self::$__instance->revisions      = new SCC_Revisions();
				self::$__instance->settings       = new SCC_Settings();
				self::$__instance->filters        = new SCC_Filters();
				self::$__instance->welcome        = new SCC_Welcome();
				self::$__instance->live_updates   = new SCC_Live_Updates();


				// Always allow notes to work
				self::$__instance->notes = new SCC_Notes();
			}

			return self::$__instance;
		}

		private function __setup_constants() {
			// Plugin Folder Path
			if ( ! defined( 'STRIVE_CC_DIR' ) ) {
				define( 'STRIVE_CC_DIR', plugin_dir_path( __FILE__ ) );
			}
			// Plugin Folder URL
			if ( ! defined( 'STRIVE_CC_URL' ) ) {
				define( 'STRIVE_CC_URL', plugin_dir_url( __FILE__ ) );
			}
			// Plugin Root File
			if ( ! defined( 'STRIVE_CC_FILE' ) ) {
				define( 'STRIVE_CC_FILE', __FILE__ );
			}
			// Plugin version
			if ( ! defined( 'STRIVE_CC_VERSION' ) ) {
				define( 'STRIVE_CC_VERSION', 1.32 );
			}
		}

		private function __includes() {
			require_once STRIVE_CC_DIR . 'inc/calendar/calendar.php';
			require_once STRIVE_CC_DIR . 'inc/calendar/settings.php';
			require_once STRIVE_CC_DIR . 'inc/calendar/live-updates.php';
			require_once STRIVE_CC_DIR . 'inc/checklists/checklists.php';
			require_once STRIVE_CC_DIR . 'inc/checklists/settings.php';
			require_once STRIVE_CC_DIR . 'inc/pipeline/pipeline.php';
			require_once STRIVE_CC_DIR . 'inc/pipeline/settings.php';
			require_once STRIVE_CC_DIR . 'inc/statuses.php';
			require_once STRIVE_CC_DIR . 'inc/revisions.php';
			require_once STRIVE_CC_DIR . 'inc/settings.php';
			require_once STRIVE_CC_DIR . 'inc/notes.php';
			require_once STRIVE_CC_DIR . 'inc/filters.php';
			require_once STRIVE_CC_DIR . 'inc/welcome.php';
		}

		public function __construct() {
			// Restrict functionality based on active license

			add_action( 'init', [ $this, 'register_post_meta' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_scripts' ] );
			add_action( 'plugins_loaded', [ $this, 'update_db_version' ], 99 );


			// Always allow the menu links to show up
			add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
			add_filter( 'submenu_file', [ $this, 'hide_welcome_menu' ] );
			add_filter( 'plugin_action_links_strive-content-calendar/strive.php', [ $this, 'plugin_settings_link' ] );
			// Load textdomain
			add_action( 'init', [ $this, 'load_textdomain' ] );
			// Always keep their Notes so they aren't held for "ransom" after license expiration
			add_action( 'init', [ $this, 'register_notes_post_meta' ] );
			add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_post_notes_scripts' ] );

			// Always enqueue my styles on the license activation page
			add_action( 'admin_enqueue_scripts', [ $this, 'license_activation_styles' ] );
		}


		// Save their current plugin version to the database
		public function update_db_version() {
			if ( get_option( 'strive_db_version' ) != STRIVE_CC_VERSION ) {
				update_option( 'strive_db_version', STRIVE_CC_VERSION );
			}
		}

		public function load_textdomain() {
			load_plugin_textdomain( 'strive', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		public function license_activation_styles( $hook ) {
			wp_register_style( 'strive-license-styles', STRIVE_CC_URL . 'css/strive/license.css', [], STRIVE_CC_VERSION );
			wp_enqueue_style( 'strive-license-styles' );
		}

		// Register editorial status, revision, and checklists post meta
		public function register_post_meta() {
			// Editorial status
			register_post_meta( '', '_strive_editorial_status', [
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => 'not-started',
				'sanitize_callback' => [ $this, 'sanitize_post_status' ],
				'auth_callback'     => function () {
					return self::permission_manager( 'contributor' );
				},
			] );

			// ID of parent post if revision
			// Empty string for 1st argument makes it available for all post types
			register_post_meta( '', '_strive_copy_of', [
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'number',
				'sanitize_callback' => [ $this, 'sanitize_post_revision' ],
				'auth_callback'     => function () {
					return self::permission_manager( 'contributor' );
				},
			] );

			// Post checklists
			$checklist_post_types = self::strive_get_option( 'strive_checklist_post_types', [ 'post' ] );
			foreach ( $checklist_post_types as $post_type ) {
				register_post_meta( $post_type, '_strive_checklists', [
					'single'            => true,
					'type'              => 'string',
					'sanitize_callback' => [ $this, 'sanitize_post_checklists' ],
					'show_in_rest'      => [
						'prepare_callback' => function ( $value ) {
							if ( is_string( $value ) && $value !== '' ) {
								return $value;
							} else {
								return wp_json_encode( $value );
							}
						},
					],
					'auth_callback'     => function () {
						return self::permission_manager( 'contributor' );
					},
				] );
				// ID of active checklist
				if ( get_bloginfo( 'version' ) >= 5.5 ) {
					// The only difference is the DEFAULT field. Not sure how else to do this...
					register_post_meta( $post_type, '_strive_active_checklist', [
						'show_in_rest'      => true,
						'single'            => true,
						'type'              => 'string',
						'default'           => self::strive_get_option( 'strive_default_checklist', '' ),
						'sanitize_callback' => 'sanitize_text_field',
						'auth_callback'     => function () {
							return self::permission_manager( 'contributor' );
						},
					] );
				} else {
					register_post_meta( $post_type, '_strive_active_checklist', [
						'show_in_rest'      => true,
						'single'            => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'auth_callback'     => function () {
							return self::permission_manager( 'contributor' );
						},
					] );
				}
			}

			// Have to register index.js for Gutenberg on this hook
			wp_register_script( 'strive-checklists-editor-js', STRIVE_CC_URL . 'gutenberg/checklists.js', [ 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-core-data', 'wp-editor', 'wp-data', 'wp-components', 'wp-compose', 'wp-i18n' ], STRIVE_CC_VERSION );
			wp_register_script( 'strive-statuses-js', STRIVE_CC_URL . 'gutenberg/statuses.js', [ 'wp-element', 'wp-plugins', 'wp-edit-post', 'wp-core-data', 'wp-data', 'wp-components', 'wp-compose' ], STRIVE_CC_VERSION );
		}

		// Register the Notes post meta
		public function register_notes_post_meta() {
			register_post_meta( 'post', '_strive_post_notes', [
				'single'            => true,
				'show_in_rest'      => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => function () {
					return self::permission_manager( 'contributor' );
				},
			] );

			// Have to register post-notes.js for Gutenberg on this hook
			wp_register_script( 'strive-post-notes-js', STRIVE_CC_URL . 'gutenberg/post-notes.js', [ 'wp-plugins', 'wp-element', 'wp-edit-post', 'wp-data', 'wp-components', 'wp-compose', 'wp-i18n' ], STRIVE_CC_VERSION );

			// Register hidePermalink.js for Gutenberg on this hook
			wp_register_script( 'strive-hide-permalink-js', STRIVE_CC_URL . 'gutenberg/hide-permalink.js', [ 'wp-data', 'wp-edit-post' ], STRIVE_CC_VERSION );
		}

		// Sanitize the array of checklist IDs saved to _strive_checklists encoded as a string
		public function sanitize_post_checklists( $input ) {
			// Example $input:  "['fjf03nfe8', '983nf93j']"
			return sanitize_text_field( $input );
		}

		// Sanitize the editorial status when post is saved
		public function sanitize_post_status( $input ) {
			// Only allow one of 4 post status values
			if ( array_key_exists( $input, self::post_statuses() ) ) {
				return $input;
			} else {
				return false;
			}
		}

		// Sanitize the parent ID of revisions
		public function sanitize_post_revision( $input ) {
			return absint( $input );
		}

		// Enqueue all the CSS & JS
		public function enqueue_scripts( $hook ) {
			// Limit to Strive's menu
			if ( $hook == 'posts_page_strive-content-calendar' ) {
				$default_tab = null;
				$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;

				if ( $tab == null ) {
					wp_register_style( 'strive-pickr-style', STRIVE_CC_URL . 'css/third-party/pickr.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-pickr-style' );
					wp_register_script( 'strive-pickr-js', STRIVE_CC_URL . 'js/third-party/pickr.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-pickr-js' );

					// Calendar JS
					wp_register_script( 'strive-calendar-js', STRIVE_CC_URL . 'js/strive/calendar.js', [ 'jquery', 'strive-dragula-js', 'strive-select2-js', 'strive-moment-js', 'strive-pickr-js' ], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-calendar-js' );

					// Calendar styles
					wp_register_style( 'strive-calendar-style', STRIVE_CC_URL . 'css/strive/style.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-calendar-style' );

					// Add nonces for Ajax callbacks
					wp_add_inline_script( 'strive-calendar-js', 'const STRIVE_AJAX = ' . json_encode( [
							'open_post_details_modal_nonce'  => wp_create_nonce( 'open_post_details_modal' ),
							'update_post_nonce'              => wp_create_nonce( 'update_post' ),
							'drag_drop_update_nonce'         => wp_create_nonce( 'drag_drop_update' ),
							'delete_post_nonce'              => wp_create_nonce( 'delete_post' ),
							'post_draft_select_nonce'        => wp_create_nonce( 'post_draft_select' ),
							'insert_post_draft_nonce'        => wp_create_nonce( 'insert_post_draft' ),
							'search_unscheduled_posts_nonce' => wp_create_nonce( 'search_unscheduled_posts' ),
							'reload_calendar_nonce'          => wp_create_nonce( 'reload_calendar' ),
							'save_settings_nonce'            => wp_create_nonce( 'save_settings' ),
							'save_sidebar_display_nonce'     => wp_create_nonce( 'save_sidebar_display' ),
							'save_filter_nonce'              => wp_create_nonce( 'save_filter' ),
							'delete_filter_nonce'            => wp_create_nonce( 'delete_filter' ),
							'live_update_calendar_nonce'     => wp_create_nonce( 'live_update_calendar' ),
							'live_updates_nonce'             => wp_create_nonce( 'live_updates' ),
							'locale'                         => get_locale(),
						] ), 'before' );

					// Select2.js
					wp_register_script( 'strive-select2-js', STRIVE_CC_URL . 'js/third-party/select2.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-select2-js' );
					wp_register_style( 'strive-select2-css', STRIVE_CC_URL . 'css/third-party/select2.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-select2-css' );

					// Datepicker and Moment.js dependency for it
					wp_register_script( 'strive-moment-js', STRIVE_CC_URL . 'js/third-party/moment.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-moment-js' );
					wp_register_script( 'strive-datepicker-js', STRIVE_CC_URL . 'js/third-party/daterangepicker.js', [ 'strive-moment-js' ], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-datepicker-js' );
					wp_register_style( 'strive-datepicker-css', STRIVE_CC_URL . 'css/third-party/daterangepicker.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-datepicker-css' );
				} elseif ( $tab == 'pipeline' ) {
					// Pipline JS
					wp_register_script( 'strive-pipeline-js', STRIVE_CC_URL . 'js/strive/pipeline.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-pipeline-js' );

					// Pipline styles
					wp_register_style( 'strive-pipeline-style', STRIVE_CC_URL . 'css/strive/pipeline.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-pipeline-style' );

					wp_add_inline_script( 'strive-pipeline-js', 'const STRIVE_AJAX = ' . json_encode( [
							'save_pipeline_settings_nonce' => wp_create_nonce( 'save_pipeline_settings' ),
							'reload_pipeline_nonce'        => wp_create_nonce( 'reload_pipeline' ),
						] ), 'before' );
				} elseif ( $tab == 'checklists' ) {
					// Checklist JS
					wp_register_script( 'strive-checklists-js', STRIVE_CC_URL . 'js/strive/checklists.js', [ 'strive-dragula-js' ], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-checklists-js' );

					// Make plugin URL and Ajax nonces available to checklist.js file
					wp_add_inline_script( 'strive-checklists-js', 'const STRIVE_DATA = ' . json_encode( [
							'get_the_checklist_nonce'       => wp_create_nonce( 'get_the_checklist' ),
							'export_checklist_nonce'        => wp_create_nonce( 'export_checklist' ),
							'import_checklist_nonce'        => wp_create_nonce( 'import_checklist' ),
							'save_checklist_settings_nonce' => wp_create_nonce( 'save_checklist_settings' ),
							'plugin_url'                    => STRIVE_CC_URL,
						] ), 'before' );

					// Checklist styles
					wp_register_style( 'strive-checklist-style', STRIVE_CC_URL . 'css/strive/checklists.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-checklist-style' );
				} elseif ( $tab == 'account' || $tab == 'affiliation' || $tab == 'contact' ) {
					// Settings styles
					wp_register_style( 'strive-freemius-style', STRIVE_CC_URL . 'css/strive/freemius.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-freemius-style' );

					wp_register_script( 'strive-freemius-js', STRIVE_CC_URL . 'js/strive/freemius.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-freemius-js' );
				} elseif ( $tab == 'settings' ) {
					wp_register_style( 'strive-pickr-style', STRIVE_CC_URL . 'css/third-party/pickr.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-pickr-style' );
					wp_register_script( 'strive-pickr-js', STRIVE_CC_URL . 'js/third-party/pickr.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-pickr-js' );

					wp_register_style( 'strive-settings-style', STRIVE_CC_URL . 'css/strive/settings.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-settings-style' );

					wp_register_script( 'strive-settings-js', STRIVE_CC_URL . 'js/strive/settings.js', [ 'jquery', 'strive-pickr-js' ], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-settings-js' );

					wp_add_inline_script( 'strive-settings-js', 'const STRIVE_AJAX = ' . json_encode( [
							'save_settings_nonce'           => wp_create_nonce( 'save_settings' ),
							'save_checklist_settings_nonce' => wp_create_nonce( 'save_checklist_settings' ),
							'save_pipeline_settings_nonce'  => wp_create_nonce( 'save_pipeline_settings' ),
						] ), 'before' );
				}
				// Load Dragula for calendar and checklists
				if ( $tab == null || $tab == 'checklists' ) {
					// Dragula
					wp_register_script( 'strive-dragula-js', STRIVE_CC_URL . 'js/third-party/dragula.js', [], STRIVE_CC_VERSION );
					wp_enqueue_script( 'strive-dragula-js' );
					wp_register_style( 'strive-dragula-css', STRIVE_CC_URL . 'css/third-party/dragula.css', [], STRIVE_CC_VERSION );
					wp_enqueue_style( 'strive-dragula-css' );
				}
			}

			// Load small stylesheet for welcome screen after activation
			if ( $hook == 'posts_page_strive-content-calendar-welcome' ) {
				wp_register_style( 'strive-welcome-style', STRIVE_CC_URL . 'css/strive/welcome.css', [], STRIVE_CC_VERSION );
				wp_enqueue_style( 'strive-welcome-style' );

				wp_register_script( 'strive-welcome-js', STRIVE_CC_URL . 'js/strive/welcome.js', [], STRIVE_CC_VERSION );
				wp_enqueue_script( 'strive-welcome-js' );

				// Make Ajax nonces available to welcome.js file
				wp_add_inline_script( 'strive-welcome-js', 'const STRIVE_DATA = ' . json_encode( [
						'save_onboarding_settings_nonce' => wp_create_nonce( 'save_onboarding_settings' ),
						'import_starter_checklist_nonce' => wp_create_nonce( 'import_starter_checklist' ),
					] ), 'before' );
			}

			// Post editor
			if ( $hook == 'post.php' || $hook == 'post-new.php' ) {
				// Styling for revision label and post meta boxes
				wp_register_style( 'strive-editor-style', STRIVE_CC_URL . 'css/strive/editor.css', [], STRIVE_CC_VERSION );
				wp_enqueue_style( 'strive-editor-style' );

				// Javascript for checklists in Classic Editor
				if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
					global $typenow;
					// Only load for post types that support checklists
					$checklist_post_types = SCC()->strive_get_option( 'strive_checklist_post_types', [ 'post' ] );
					if ( in_array( $typenow, $checklist_post_types ) ) {
						// And double-check they have custom field support
						if ( post_type_supports( $typenow, 'custom-fields' ) ) {
							wp_register_script( 'strive-editor-js', STRIVE_CC_URL . 'js/strive/editor.js', [], STRIVE_CC_VERSION );
							wp_enqueue_script( 'strive-editor-js' );
						}
					}
				}
			}
		}

		// Load index.js and give it access to the checklists array
		public function enqueue_block_editor_scripts() {
			if ( is_customize_preview() ) {
				return;
			}

			$checklist_default = self::$__instance->checklists->default_checklists( esc_html__( 'Primary', 'strive' ) );
			global $post;

			// Only load for post types with checklists enabled
			$checklist_post_types = SCC()->strive_get_option( 'strive_checklist_post_types', [ 'post' ] );
			if ( in_array( get_post_type(), $checklist_post_types ) ) {
				// And double-check they have custom field support
				if ( post_type_supports( $post->post_type, 'custom-fields' ) ) {
					// Had to be registered on 'init' in 'register_post_meta'
					wp_enqueue_script( 'strive-checklists-editor-js' );

					$url = add_query_arg( [
						'page' => 'strive-content-calendar',
						'tab'  => 'checklists',
					], admin_url( 'edit.php' ) );

					// Add global variable with checklist array
					wp_add_inline_script( 'strive-checklists-editor-js', 'const STRIVE = ' . json_encode( [
							'checklists'                 => self::strive_get_option( 'strive_post_checklists', false ),
							'checklists_post_saved'      => get_post_meta( $post->ID, '_strive_active_checklist', true ),
							'checklists_default_global'  => self::strive_get_option( 'strive_default_checklist', '' ),
							'checklists_completed_tasks' => get_post_meta( $post->ID, '_strive_checklists', true ),
							'checklist_create_url'       => $url,
						] ), 'before' );
				}
			}

			// Add Editorial Status to posts, pages, and any CPT with custom field support
			if ( in_array( get_post_type(), self::post_types_with_custom_fields() ) ) {
				wp_enqueue_script( 'strive-statuses-js' );
			}

			// Only load for revisions (all post types). Registered on 'init' hook.
			if ( get_post_meta( $post->ID, '_strive_copy_of', true ) ) {
				wp_enqueue_script( 'strive-hide-permalink-js' );
			}
		}

		// Load post notes script for Gutenberg
		public function enqueue_post_notes_scripts() {
			// Only load for posts
			if ( get_post_type() == 'post' ) {
				// This is post-notes.js. It had to be registered on 'init'
				wp_enqueue_script( 'strive-post-notes-js' );
			}
		}

		// Add the settings page
		public function add_settings_page() {
			// Add the Content Calendar menu
			add_submenu_page(
				'edit.php',
				'Strive Content Calendar',
				esc_html__( 'Content Calendar', 'strive' ),
				'publish_posts',
				'strive-content-calendar',
				[ $this, 'settings_page_markup' ]
			);

			// Add hidden menu for Welcome screen
			add_submenu_page(
				'edit.php',
				esc_html__( 'Welcome to Strive!', 'strive' ),
				esc_html__( 'Welcome', 'strive' ),
				'manage_options',
				'strive-content-calendar-welcome',
				[ $this, 'welcome_page_markup' ]
			);
		}

		// Remove the "Welcome" and "Pricing" submenus from the sidebar
		public function hide_welcome_menu( $submenus ) {
			// Remove Welcome page
			remove_submenu_page( 'edit.php', 'strive-content-calendar-welcome' );
			// Remove Pricing page
			remove_submenu_page( 'edit.php', 'strive-content-calendar-pricing' );
			// Remove Account page
			remove_submenu_page( 'edit.php', 'strive-content-calendar-account' );
			// Remove Contact Us page
			remove_submenu_page( 'edit.php', 'strive-content-calendar-contact' );
			// Remove Affiliate program page
			remove_submenu_page( 'edit.php', 'strive-content-calendar-affiliation' );

			return $submenus;
		}

		// Add the link to the plugin in the Plugins menu
		public function plugin_settings_link( $links ) {
			// Build the URL
			$url = add_query_arg( 'page', 'strive-content-calendar', admin_url( 'edit.php' ) );

			// Create the link
			$settings_link = '<a class="calendar-link" href="' . esc_url( $url ) . '">' . esc_html__( 'Content Calendar', 'strive' ) . '</a>';

			// Add the link to the array
			array_push( $links, $settings_link );

			return $links;
		}

		public function settings_page_markup() {

			// check user capabilities
			if ( ! self::permission_manager( 'author' ) ) {
				return;
			}

			// Get the active tab from the $_GET param
			$default_tab = null;
			$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;
			$classes     = 'strive-content-calendar-parent';
			if ( get_option( 'strive_color_blind_mode', false ) ) {
				$classes .= ' color-blind';
			}
			$sidebar_open = SCC()->strive_get_option( 'strive_unscheduled_drafts_open', false );
			if ( $sidebar_open && $tab == null ) {
				$classes .= ' unscheduled-sidebar-open';
			} ?>
            <div id="strive-content-calendar-parent" class="<?php echo esc_attr( $classes ); ?>">
                <nav class="nav-tab-wrapper">
                    <a href="?page=strive-content-calendar" class="nav-tab <?php if ( $tab === null ): ?>nav-tab-active<?php endif; ?>">
						<?php esc_html_e( 'Calendar', 'strive' ); ?>
                    </a>
                    <a href="?page=strive-content-calendar&tab=pipeline" class="nav-tab <?php if ( $tab === 'pipeline' ): ?>nav-tab-active<?php endif; ?>">
						<?php esc_html_e( 'Pipeline', 'strive' ); ?>
                    </a>
					<?php if ( self::permission_manager( 'admin' ) ) : ?>
                        <a href="?page=strive-content-calendar&tab=checklists" class="nav-tab <?php if ( $tab === 'checklists' ): ?>nav-tab-active<?php endif; ?>">
							<?php esc_html_e( 'Checklists', 'strive' ); ?>
                        </a>
                        <a href="?page=strive-content-calendar&tab=settings" class="nav-tab <?php if ( $tab === 'settings' ): ?>nav-tab-active<?php endif; ?>">
							<?php esc_html_e( 'Settings', 'strive' ); ?>
                        </a>
					<?php endif; ?>
					<?php
					// Add video tutorial link
					if ( $tab === null ) {
						echo self::$__instance->return_wistia_video( 'calendar' );
					} elseif ( $tab === 'pipeline' ) {
						echo self::$__instance->return_wistia_video( 'pipeline' );
					} elseif ( $tab === 'checklists' ) {
						echo self::$__instance->return_wistia_video( 'checklists' );
					} ?>
                </nav>
                <div class="tab-content"><?php
					switch ( $tab ):
						case 'pipeline':
//							if ( self::permission_manager( 'contributor' ) ) {
//								echo self::$__instance->pipeline->build_pipeline();
//								if ( self::permission_manager( 'admin' ) ) {
//									echo self::$__instance->pipe_settings->output_settings_fields();
//								}
//							}

							echo $this->display_pro_notice();
							break;
						case 'checklists':
//							if ( self::permission_manager( 'admin' ) ) {
//								echo self::$__instance->checklists->build_checklists();
//								echo self::$__instance->check_settings->output_checklist_settings();
//							}
							echo $this->display_pro_notice();
							break;
						case 'settings':
							if ( self::permission_manager( 'admin' ) ) {
								echo self::$__instance->settings->build_settings();
							}
							break;
						case 'account':
							if ( self::permission_manager( 'admin' ) ) {
								scc_freemius()->_account_page_load();
								scc_freemius()->_account_page_render();
							}
							break;
						case 'affiliation':
							if ( self::permission_manager( 'admin' ) ) {
//								scc_freemius()->_affiliation_page_render();
							}
							break;
						case 'contact':
							if ( self::permission_manager( 'admin' ) ) {
								Freemius::_clean_admin_content_section();
								scc_freemius()->_contact_page_render();
							}
							break;
						default:
							$this->live_updates->empty_list();
							echo self::$__instance->calendar->build_calendar();
							if ( self::permission_manager( 'admin' ) ) {
								echo self::$__instance->cal_settings->output_settings_fields();
							}
							break;
					endswitch; ?>
                </div>
            </div>
			<?php
		}

		public function welcome_page_markup() {
			if ( ! scc_freemius()->is_pending_activation() ) {
				self::$__instance->welcome->welcome_page_markup();
			}
		}

		// Post status array used everywhere
		public function post_statuses() {
			return [
				'not-started' => esc_html__( 'Not Started', 'strive' ),
				'writing'     => esc_html__( 'Writing', 'strive' ),
				'editing'     => esc_html__( 'Editing', 'strive' ),
				'complete'    => esc_html__( 'Complete', 'strive' ),
			];
		}

		// Used to output tutorial link next to tabs
		public function return_wistia_video( $tab ) {
			// Always load popover script
			$html = '<script src="https://fast.wistia.com/assets/external/E-v1.js" async></script>';

			// Customize video ID and text based on active tab
			if ( $tab == 'calendar' ) {
				$vid_id = 'p1v7rf2p61';
				$text   = esc_html__( 'Play the Calendar Tutorial', 'strive' );
			} elseif ( $tab == 'pipeline' ) {
				$vid_id = 'ucf699emh5';
				$text   = esc_html__( 'Play the Pipeline Tutorial', 'strive' );
			} elseif ( $tab == 'checklists' ) {
				$vid_id = 'l3x8xzku7h';
				$text   = esc_html__( 'Play the Checklists Tutorial', 'strive' );
			}

			// Build the HTML for the video link
			$html .= '<script src="https://fast.wistia.com/embed/medias/' . esc_attr( $vid_id ) . '.jsonp" async></script>';
			$html .= '<span class="wistia_embed wistia_async_' . esc_attr( $vid_id ) . ' popover=true popoverAnimateThumbnail=true popoverContent=link" style="display:inline;position:relative">';
			$html .= '<a class="video-tut-link" href="#"><span class="dashicons dashicons-controls-play"></span>' . esc_html( $text ) . '</a>';
			$html .= '</span>';

			return $html;
		}

		// Prevent empty strings from every being used
		public function strive_get_option( $name, $default ) {
			$option = get_option( $name, $default );

			return $option === '' ? $default : $option;
		}

		public function post_types_with_custom_fields() {
			$post_types = [ 'post', 'page' ];
			$user_cpts  = get_post_types( [
				'public'   => true,
				'_builtin' => false,
			] );
			foreach ( $user_cpts as $cpt ) {
				if ( post_type_supports( $cpt, 'custom-fields' ) ) {
					$post_types[] = $cpt;
				}
			}

			return $post_types;
		}

		public function permission_manager( $role = 'subscriber' ) {
			if ( $role == 'contributor' ) {
				return current_user_can( 'edit_posts' );
			} elseif ( $role == 'author' ) {
				return current_user_can( 'publish_posts' );
			} elseif ( $role == 'editor' ) {
				return current_user_can( 'edit_others_posts' );
			} elseif ( $role == 'admin' ) {
				return current_user_can( 'manage_options' );
			} else {
				return false;
			}
		}

		public function can_edit_this_post( $post_id = 0 ) {
			if ( SCC()->permission_manager( 'editor' ) ) {
				return true;
			} else {
				$post = get_post( $post_id );

				return $post->post_author == get_current_user_id();
			}
		}

		public function get_post_type_icon( $post_type ) {
			$icon = get_post_type_object( $post_type )->menu_icon;
			$html = '<div class="post-type-icon">';
			if ( esc_url_raw( $icon ) === $icon ) {
				$html .= '<span><img src="' . esc_url( $icon ) . '" width="20px" height="20px" /></span>';
			} else {
				$html .= '<span class="dashicons ' . esc_attr( $icon ) . '"></span>';
			}
			$html .= '</div>';

			return $html;
		}

		// Only return post types that are enabled and currently have custom fields
		public function get_supported_post_types( $option ) {
			$post_types      = SCC()->strive_get_option( $option, [ 'post' ] );
			$supported_types = [];
			foreach ( $post_types as $post_type ) {
				if ( post_type_supports( $post_type, 'custom-fields' ) ) {
					$supported_types[] = $post_type;
				}
			}

			return $supported_types;
		}

		public function get_all_post_types() {
			$post_types = [ 'post', 'page' ];
			$cpts       = get_post_types( [
				'public'   => true,
				'_builtin' => false,
			] );
			foreach ( $cpts as $cpt ) {
				$post_types[] = $cpt;
			}

			return $post_types;
		}

		public function get_users_can_write() {
			$roles = [];
			foreach ( wp_roles()->roles as $role_name => $role_obj ) {
				if ( ! empty( $role_obj['capabilities']['edit_posts'] ) ) {
					$roles[] = $role_name;
				}
			}

			$users = get_users( [ 'role__in' => $roles ] );

			return $users;
		}

		public function pretty_var_dump( $var ) {
			echo '<pre>';
			var_dump( $var );
			echo '</pre>';
		}

		public function display_pro_notice() {

			ob_start();

			echo '<div class="strive-pro-notice">';
			echo '<p>' . esc_html__( 'This section is for pro version only.', 'strive-calendar' ) . '<a target="_blank" href="' . STRIVEC_WEBSITE_URL . '">' . esc_html__( 'Try Pro Version', 'strive-calendar' ) . '</a></p>';
			echo '</div>';
			echo '<style>';
			echo '
                .strive-pro-notice {
                    background: #ffe28b;
                    display: inline-block;
                    margin: 20px 0;
                    padding: 18px 10px 18px 10px;
                }
                
                .strive-pro-notice p {
                    margin: 0;
                    font-size: 14px;
                    line-height: 21px;
                }
                
                .strive-pro-notice a,
                .strive-pro-notice a:focus,
                .strive-pro-notice a:active{
                    background: #000;
                    color: #f1f1f1;
                    margin-left: 10px;
                    padding: 8px 12px;
                    border-radius: 3px;
                    font-size: 13px;
                    line-height: 20px;
                    text-decoration: none;
                    outline: none;
                    box-shadow: none;
                }
            ';
			echo '</style>';

			return ob_get_clean();
		}
	}
}

// Boot it up
function SCC() {
	return Strive_Content_Calendar::instance();
}

SCC();
