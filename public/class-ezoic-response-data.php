<?php
namespace Bloomly_Namespace;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ezoic.com
 * @since      1.0.0
 *
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/public
 * @author     Ezoic Inc. <support@ezoic.com>
 */
class Ezoic_Response_Data {

    private $ezHeaders;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {
    }
 
    public function HandleEzoicResponse( $final , $response ) {
        if( !is_wp_error($response) && isset($response['response']) && 
		isset($response['response']['code']) && 
		$response['response']['code'] == 200 ) {
                //Get headers and add them to the response
            $this->ezHeaders = wp_remote_retrieve_headers( $response );
            $this->alterResponseHeaders();

            //Replace final content with ezoic content
            if( is_array($response) && isset($response['body']) ) {
                $final = $response['body'];
            } else {
                $final = $response;
            }
            
        } else {
			if( is_wp_error($response) ) {
				$final = $final . "<!-- " . $response->get_error_message() . " -->";
			} elseif (isset($response['response']) && 
						isset($response['response']['code']) && 
						$response['response']['code'] != 200) {
				$final = $final . "<!-- " . $response['response']['code'] . " -->";
			}
        }

        return $final;
    }

    private function alterResponseHeaders() {
		if( !is_null($this->ezHeaders) && !headers_sent() ){

			$headers = array();

			if( is_array($this->ezHeaders) ) {
				$headers = $this->ezHeaders;
			} else {
				$headers = $this->ezHeaders->getAll();
			}

			foreach( $headers as $key => $header ) {
				//Avoid content encoding as this will cause rendering problems
				if( !$this->isBadHeader($key) ) {
					$this->handleHeaderObject($key, $header);
				}
			}
		}
	}
	
	private function isBadHeader($key) {
		return ($key == 'Content-Encoding' 
		    || $key == 'content-encoding'
			|| $key == 'Transfer-Encoding' 
			|| $key == 'transfer-encoding'
			|| $key == 'Content-Length' 
			|| $key == 'content-length'
			|| $key == 'Accept-Ranges' 
			|| $key == 'accept-ranges'
			|| $key == 'Status'
			|| $key == 'status');
	}

	private function handleHeaderObject($key, $header) {
		if( is_array($header) ) {
			foreach( $header as $subheader) {
				header($key . ': ' . $subheader, false);
			}
		} else {
			header($key . ': ' . $header);
		}
	}

}
