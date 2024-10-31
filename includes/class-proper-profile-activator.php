<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Proper_Profile
 * @subpackage Proper_Profile/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Proper_Profile
 * @subpackage Proper_Profile/includes
 * @author     Your Name <email@example.com>
 */
class Proper_Profile_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::update_database();
		add_option('proper_profile_activation_redirect',true);
	}

	public static function update_db_check() {
		if( is_super_admin() ){ // only super admin entry to admin page will allow this
			self::update_database();
		}
	}

	public static function update_database(){
		global $wpdb;

		$installed_version = get_option('proper_profile_db_version');

		if($installed_version != PROPER_PROFILE_DB_VERSION){

			// update the shops table (one per network hence the use of $wpdb->base_prefix)
			// this may be called in loop for each blog but will have effect (if any) only on the 1st call
			$table_name = $wpdb->prefix . 'proper_profile_persons';

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				email varchar(100) NOT NULL,
				data text NOT NULL,
				cached_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY idx_email (email)
			) $charset_collate;";

			// wp_die($sql);

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$ret = dbDelta( $sql );
			// wp_die(var_export($ret));

			update_option( 'proper_profile_db_version', PROPER_PROFILE_DB_VERSION );

		}

	}


}
