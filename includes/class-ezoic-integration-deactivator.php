<?php
namespace Bloomly_Namespace;

/**
 * Fired during plugin deactivation
 *
 * @link       https://ezoic.com
 * @since      1.0.0
 *
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ezoic-integration-cache-identifier.php';
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/includes
 * @author     Ezoic Inc. <support@ezoic.com>
 */
class Ezoic_Integration_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		//Lets figure out if any caching is going on
		$cacheIndetifier = new Ezoic_Integration_Cache_Identifier();

		//Lets determine what kind of caching is going on
		if( $cacheIndetifier->GetCacheType() == Ezoic_Cache_Type::HTACCESS_CACHE ) {
			//modify htaccess files
			$cacheIndetifier->RemoveHTACCESSFile();
			//modify php files
			$cacheIndetifier->RestoreAdvancedCache();
		} elseif ( $cacheIndetifier->GetCacheType() == Ezoic_Cache_Type::PHP_CACHE ) {
			//modify htaccess files
			$cacheIndetifier->RemoveHTACCESSFile();
			//modify php files
			$cacheIndetifier->RestoreAdvancedCache();
		}
	}

}
