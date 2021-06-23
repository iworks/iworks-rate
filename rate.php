<?php
/**
 * iWorks_Rate - Dashboard Notification module.
 *
 * @version 2.0.0
 * @author  iworks (Marcin Pietrzak)
 *
 */
if ( ! class_exists( 'iworks_rate' ) ) {
	class iworks_rate {

		/**
		 * This class version.
		 *
		 * @since 1.0.1
		 * @var   string
		 */
		private $version = '2.0.0';

		/**
		 * $wpdb->options field name.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		protected $option_name = 'iworks_rates';

		/**
		 * List of all registered plugins.
		 *
		 * @since 1.0.0
		 * @var   array
		 */
		protected $plugins = array();

		/**
		 * Module options that are stored in database.
		 * Timestamps are stored here.
		 *
		 * Note that this option is stored in site-meta for multisite installs.
		 *
		 * @since 1.0.0
		 * @var   array
		 */
		protected $stored = array();

		/**
		 * Initializes and returns the singleton instance.
		 *
		 * @since  1.0.0
		 */
		static public function instance() {
			static $Inst = null;
			if ( null === $Inst ) {
				$Inst = new iworks_rate();
			}
			return $Inst;
		}

		/**
		 * Set up the iworks_rate module. Private singleton constructor.
		 *
		 * @since  1.0.0
		 */
		private function __construct() {
			/**
			 * settings
			 */
			$this->stored = wp_parse_args(
				get_site_option( $this->option_name, false, false ),
				array()
			);
			/**
			 * actions
			 */
			add_action( 'load-index.php', array( $this, 'load' ) );
			add_action( 'iworks-register-plugin', array( $this, 'register' ), 5, 3 );
			add_action( 'wp_ajax_iworks_rate_button', array( $this, 'ajax_button' ) );
		}

		public function load() {
			$plugin_id = $this->choose_plugin();
			if ( empty( $plugin_id ) ) {
				return;
			}
			$this->plugin_id = $plugin_id;
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'admin_notices', array( $this, 'show' ) );
		}

		/**
		 * Save persistent module-data to the WP database.
		 *
		 * @since  1.0.0
		 */
		protected function store_data() {
			update_site_option( $this->option_name, $this->stored );
		}

		/**
		 * Action handler for 'iworks-register-plugin'
		 * Register an active plugin.
		 *
		 * @since  1.0.0
		 * @param  string $plugin_id WordPress plugin-ID (see: plugin_basename).
		 * @param  string $title Plugin name for display.
		 * @param  string $slug the plugin slug on wp.org
		 */
		public function register( $plugin_id, $title, $slug ) {
			// Ignore incorrectly registered plugins to avoid errors later.
			if ( empty( $plugin_id ) || empty( $title ) || empty( $slug ) ) {
				return;
			}
			$data                        = array(
				'title' => $title,
				'slug'  => $slug,
			);
			$this->plugins[ $plugin_id ] = $data;
			/*
			 * When the plugin is registered the first time we store some infos
			 * in the persistent module-data that help us later to find out
			 * if/which message should be displayed.
			 */
			if ( empty( $this->stored[ $plugin_id ] ) ) {
				$this->stored[ $plugin_id ] = wp_parse_args(
					array(
						'registered' => time(),
						'show_at'    => time() + rand( 7, 14 ) * DAY_IN_SECONDS,
						'rated'      => 0,
						'hide'       => 0,
					),
					$data
				);
				// Finally save the details.
				$this->store_data();
			}
		}

		/**
		 * Ajax handler called when the user chooses the CTA button.
		 *
		 * @since  1.0.0
		 */
		public function ajax_button() {
			$plugin_id = filter_input( INPUT_POST, 'plugin_id', FILTER_SANITIZE_STRING );
			if ( empty( $plugin_id ) ) {
				wp_send_json_error();
			}
			if ( ! isset( $this->plugins[ $plugin_id ] ) ) {
				wp_send_json_error();
			}
			switch ( filter_input( INPUT_POST, 'button', FILTER_SANITIZE_STRING ) ) {
				case '':
				case 'add-review':
					$this->add_weeks( $plugin_id );
					wp_send_json_success();
				case 'hide':
					$this->add_weeks( $plugin_id );
					$this->hide( $plugin_id );
					wp_send_json_success();
				case 'donate':
					$this->add_months( $plugin_id );
					wp_send_json_success();
			}

			wp_send_json_success();
		}

		public function hide( $plugin_id ) {
			if ( ! isset( $this->stored[ $plugin_id ] ) ) {
				return;
			}
			$this->stored[ $plugin_id ]['rated'] = time();
			$this->store_data();
		}

		private function add_weeks( $plugin_id ) {
			if ( ! isset( $this->stored[ $plugin_id ] ) ) {
				return;
			}
			$this->stored[ $plugin_id ]['show_at'] = time() + rand( 2, 3 ) * WEEK_IN_SECONDS + rand( 0, 3 ) * DAY_IN_SECONDS;
			$this->store_data();
		}

		private function add_months( $plugin_id ) {
			if ( ! isset( $this->stored[ $plugin_id ] ) ) {
				return;
			}
			$this->stored[ $plugin_id ]['show_at'] = time() + rand( 10, 15 ) * WEEK_IN_SECONDS + rand( 0, 7 ) * DAY_IN_SECONDS;
			$this->store_data();
		}

		/**
		 * Ajax handler called when the user chooses the dismiss button.
		 *
		 * @since  1.0.0
		 */
		public function dismiss() {
			$plugin = $this->get_plugin_from_post();
			if ( is_wp_error( $plugin ) ) {
				wp_send_json_error();
			}
			wp_send_json_success();
		}

		/**
		 * Action handler for 'load-index.php'
		 * Set-up the Dashboard notification.
		 *
		 * @since  1.0.0
		 */
		public function enqueue() {
			wp_enqueue_style(
				__CLASS__,
				plugin_dir_url( __FILE__ ) . 'admin.css',
				array(),
				$this->version
			);
			wp_enqueue_script(
				__CLASS__,
				plugin_dir_url( __FILE__ ) . 'admin.js',
				array(),
				$this->version,
				true
			);
		}

		/**
		 * Action handler for 'admin_notices'
		 * Display the Dashboard notification.
		 *
		 * @since  1.0.0
		 */
		public function show() {
			$this->render_message( $this->plugin_id );
		}

		/**
		 * Check to see if there is a pending message to display and returns
		 * the message details if there is.
		 *
		 * Note that this function is only called on the main Dashboard screen
		 * and only when logged in as super-admin.
		 *
		 * @since  1.0.0
		 * @return object|false
		 *         string $plugin WordPress plugin ID?
		 */
		protected function choose_plugin() {
			$choosen_plugin_id = false;
			if ( wp_is_mobile() ) {
				return $choosen_plugin_id;
			}
			/**
			 * change time by filter
			 */
			$now = apply_filters( 'iworks_rate_set_custom_time', time() );
			foreach ( $this->stored as $plugin_id => $item ) {
				if ( ! isset( $this->plugins[ $plugin_id ] ) ) {
					if ( isset( $this->stored[ $plugin_id ] ) ) {
						unset( $this->stored[ $plugin_id ] );
						$this->store_data();
					}
					continue;
				}
				if ( intval( $item['show_at'] ) > $now ) {
					continue;
				}
				if ( false === $choosen_plugin_id ) {
					$choosen_plugin_id = $plugin_id;
				}
			}
			return $choosen_plugin_id;
		}


		/**
		 * Renders the actual Notification message.
		 *
		 * @since  1.0.0
		 */
		protected function render_message( $plugin_id ) {
			$file                = sprintf(
				'%s/templates/%s.php',
				dirname( __FILE__ ),
				'thanks'
			);
			$plugin              = wp_parse_args(
				$this->plugins[ $plugin_id ],
				$this->stored[ $plugin_id ]
			);
			$plugin['plugin_id'] = $plugin_id;
			$plugin['logo']      = apply_filters( 'iworks_rate_notice_logo_style', '', $plugin );
			$plugin['ajax_url']  = admin_url( 'admin-ajax.php' );
			$plugin['classes']   = array(
				'iworks-rate',
				'iworks-rate-' . $plugin['slug'],
				'iworks-rate-notice',
			);
			if ( ! empty( $plugin['logo'] ) ) {
				$plugin['classes'][] = 'has-logo';
			}
			load_template( $file, true, $plugin );
		}

	}

	// Initialize the module.
	iworks_rate::instance();
}
