<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ufmedia.co.uk
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 * @author     John Thompson <john@ufmedia.co.uk>
 */
class Cloudfront_Cache_Clear {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cloudfront_Cache_Clear_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CLOUDFRONT_CACHE_CLEAR_VERSION' ) ) {
			$this->version = CLOUDFRONT_CACHE_CLEAR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cloudfront-cache-clear';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cloudfront_Cache_Clear_Loader. Orchestrates the hooks of the plugin.
	 * - Cloudfront_Cache_Clear_i18n. Defines internationalization functionality.
	 * - Cloudfront_Cache_Clear_Admin. Defines all hooks for the admin area.
	 * - Cloudfront_Cache_Clear_Aws. Defines all AWS related functionality.
	 * - Cloudfront_Cache_Clear_Scheduler. Defines all cache clearing scheduling functionality.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-cloudfront-cache-clear-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-cloudfront-cache-clear-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-cloudfront-cache-clear-admin.php';

		/**
		 * The class responsible for interacting with AWS and invalidating the CloudFront cache.
		 */

		require_once plugin_dir_path( __DIR__ ) . 'includes/class-cloudfront-cache-clear-aws.php';

		/**
		 * The class responsible for scheduling the invalidation of the CloudFront cache.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-cloudfront-cache-clear-scheduler.php';

		$this->loader = new Cloudfront_Cache_Clear_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cloudfront_Cache_Clear_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cloudfront_Cache_Clear_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		/**
		 * Plugin/admin hooks.
		 */
		$plugin_admin = new Cloudfront_Cache_Clear_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'cloudfront_settings_init' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_cloudfront_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_clear_cache_button_click' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'cf_show_admin_notice' );

		/**
		 * CloudFront hooks.
		 */
		$cloud_front = new Cloudfront_Cache_Clear_Aws();
		$this->loader->add_action( 'queue_cache_clear', $cloud_front, 'clear_cloudfront_cache' );

		/**
		 * The following hooks are for cache plugins.
		 *
		 * These create an imidiate invalidation when the cache is cleared.
		 */
		$this->loader->add_action( 'w3tc_flush_all', $cloud_front, 'clear_cloudfront_cache' ); // W3 Total Cache support.
		$this->loader->add_action( 'wp_cache_cleared', $cloud_front, 'clear_cloudfront_cache' ); // WP Super Cache support.

		/**
		 * Scheduler hooks.
		 */
		$scheduler = new Cloudfront_Cache_Clear_Scheduler();

		/**
		 * Attachment related hooks.
		 *
		 * These hooks are for when an attachment is added, updated or deleted.
		 * We need to schedule a cache clear when this happens to prevent multiple invalidations on bulk updates.
		 */
		$this->loader->add_action( 'delete_attachment', $scheduler, 'schedule_cache_clear' );
		$this->loader->add_action( 'edit_attachment', $scheduler, 'schedule_cache_clear' );
		$this->loader->add_action( 'wp_update_attachment_metadata', $scheduler, 'schedule_cache_clear' );
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cloudfront_Cache_Clear_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
