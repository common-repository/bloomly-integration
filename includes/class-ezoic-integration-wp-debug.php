<?php
namespace Bloomly_Namespace;

require_once( dirname( __FILE__ ) . '/interface-ezoic-integration-debug.php');

class Ezoic_Integration_WP_Debug implements iEzoic_Integration_Debug {

	private $ezHeaders;
	private $cache_identity;

	/**
	 * Ezoic_Integration_WP_Debug constructor.
	 *
	 * @param $cache_identity
	 */
	public function __construct( $cache_identity ) {
		$this->cache_identity = $cache_identity;
	}

    public function GetDebugInformation() {
		global $wp;
		$home_url = home_url( $wp->request );
        $current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );

		$data = array();

		if(function_exists('get_plugins')) {
			$data['Plugins'] = get_plugins();
		}

		if(function_exists('phpversion')) {
			$data['PHP'] = phpversion();
		}

        $debug_content = array("Home URL" => $home_url, "Current URL" => $current_url, "Cache Type" => $this->cache_identity);
        $debug_content = array_merge($debug_content, $data);
        return "<!-- " . print_r($debug_content, true) . "-->";
    }
	
	public function WeShouldDebug() {
		if( isset($_GET["ez_wp_debug"]) && $_GET["ez_wp_debug"] == "1" ) {
			return true;
		}

        return false;
    }
}