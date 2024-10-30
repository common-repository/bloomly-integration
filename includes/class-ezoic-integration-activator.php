<?php
namespace Bloomly_Namespace;

/**
 * Fired during plugin activation
 *
 * @link       https://ezoic.com
 * @since      1.0.0
 *
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/includes
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ezoic-integration-wp-endpoints.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ezoic-integration-cache-identifier.php';
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/includes
 * @author     Ezoic Inc. <support@ezoic.com>
 */
class Ezoic_Integration_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// check plugin compatibility
		self::checkCompatibility();

		//Create endpoints db table
		$ez_endpoints  = new Ezoic_Integration_WP_Endpoints();
		$sql = $ez_endpoints->GetTableCreateStatement();
		$current_version = $ez_endpoints->GetTableVersion();
		$installed_version = get_option('ezoic_db_option');

		if( $installed_version !== $current_version ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			update_option('ezoic_db_version', $current_version);
		}

		//Lets figure out if any caching is going on
		$cacheIndetifier = new Ezoic_Integration_Cache_Identifier();

		//Lets determine what kind of caching is going on
		if( $cacheIndetifier->GetCacheType() == Ezoic_Cache_Type::HTACCESS_CACHE ) {
			//modify htaccess files
			$cacheIndetifier->GenerateHTACCESSFile();
			//modify php files
			$cacheIndetifier->ModifyAdvancedCache();
		} elseif ( $cacheIndetifier->GetCacheType() == Ezoic_Cache_Type::PHP_CACHE ) {
			//modify htaccess files
			$cacheIndetifier->GenerateHTACCESSFile();
			//modify php files
			$cacheIndetifier->ModifyAdvancedCache();
		}

		//Generate our config so we know where our possible HTACCESS files will be located
		$cacheIndetifier->GenerateConfig();

	}

	/**
	 * Check plugin compatibility
	 */
	private static function checkCompatibility() {

		// Check if running on WPEngine (not supported)
		if ( function_exists( 'is_wpe' ) ) {
			if ( is_wpe() ) {
				deactivate_plugins( EZOIC__PLUGIN_FILE );
				wp_die( 'Unfortunately, the <strong>' . EZOIC__PLUGIN_NAME . ' plugin</strong> is not compatible with <strong>WPEngine</strong> and cannot be activated!<br />
                        <a href="' . EZOIC__SITE_LOGIN . '" target="_blank">Click here to explore other integration options</a>.
                        <br /><br /><br /><a href="' . get_admin_url( null, 'plugins.php' ) . '">&#171; Back to the WordPress Plugins page</a>' );

			}
		}

	}
}
