<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Proper_Profile
 * @subpackage Proper_Profile/includes
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
 * @package    Proper_Profile
 * @subpackage Proper_Profile/includes
 * @author     Your Name <email@example.com>
 */
class Proper_Profile {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Proper_Profile_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $proper_profile    The string used to uniquely identify this plugin.
	 */
	protected $proper_profile;

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
		if ( defined( 'PROPER_PROFILE_VERSION' ) ) {
			$this->version = PROPER_PROFILE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->proper_profile = 'crmagic';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Proper_Profile_Loader. Orchestrates the hooks of the plugin.
	 * - Proper_Profile_i18n. Defines internationalization functionality.
	 * - Proper_Profile_Admin. Defines all hooks for the admin area.
	 * - Proper_Profile_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-proper-profile-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-proper-profile-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-proper-profile-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-proper-profile-public.php';

		$this->loader = new Proper_Profile_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Proper_Profile_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Proper_Profile_i18n();

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

		$plugin_admin = new Proper_Profile_Admin( $this->get_proper_profile(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// useless in v1.1, to be depracted soon
		// $this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'add_order_proper_profile_column_header', 20 );
		// $this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'add_order_proper_profile_column_content' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'redirect_after_activation' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'proper_profile_options_page' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'plugins_loaded' );

		// useless in v1.1, to be depracted soon
		// $this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $plugin_admin, 'single_order_widget' );

		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'add_action_links', 10, 2);

		$this->loader->add_action('wp_loaded', $plugin_admin, 'buffer_start');
		$this->loader->add_action('shutdown', $plugin_admin, 'buffer_end');

		$this->loader->add_action( 'wp_ajax_get_proper_profile', $plugin_admin, 'get_proper_profile_ajax' );

		$this->loader->add_action( 'admin_post_proper_profile_get_api_key', $plugin_admin, 'get_api_key' );


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Proper_Profile_Public( $this->get_proper_profile(), $this->get_version() );

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	public function get_proper_profile() {
		return $this->proper_profile;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Proper_Profile_Loader    Orchestrates the hooks of the plugin.
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
