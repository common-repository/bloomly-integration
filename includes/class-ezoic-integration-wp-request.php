<?php
namespace Bloomly_Namespace;

require_once( dirname( __FILE__ ) . '/class-ezoic-integration-request-utils.php');
require_once( dirname( __FILE__ ) . '/interface-ezoic-integration-request.php');

class Ezoic_Integration_WP_Request implements iEzoic_Integration_Request {
    private $request_data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
    public function __construct() {
        $this->request_data = Ezoic_Integration_Request_Utils::GetRequestBaseData();
	}

    public function GetContentResponseFromEzoic( $final_content ) {
        $cache_key = md5($final_content);
        //Create proper request data structure
        $request = $this->getEzoicRequest($cache_key);

        //Attempt to retrieve cached content
        $response = $this->getCachedContentEzoicResponse( $request );

		//Only upload non cached data on bad cache response and no wordpress error
        if( !is_wp_error($response) && $this->nonValidCachedContent($response) ) {
            //Send content to ezoic and return back altered content
            $response = $this->getNonCachedContentEzoicResponse($final_content, $request);
        }

        return $response;
    }

    private function getCachedContentEzoicResponse( $request ) {
        $request['body']['request_type'] = 'cache_only';
		$result = wp_remote_post(Ezoic_Integration_Request_Utils::GetEzoicServerAddress(), $request);

		return $result;
	}

	private function getNonCachedContentEzoicResponse( $final, $request ) {
		//Set content for non cached response
		$request['body']['content'] = $final;
		$request['body']['request_type'] = 'with_content';
		$result = wp_remote_post(Ezoic_Integration_Request_Utils::GetEzoicServerAddress(), $request);

		return $result;
    }
    
    private function nonValidCachedContent( $result ) {
		return ($result['response']['code'] == 404 || $result['response']['code'] == 400);
	}
    
    private function getEzoicRequest( $cache_key ) {
        global $wp;
        //Form current url 
		$current_url = add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
        $request_params = array(
            'cache_key' => $cache_key,
            'action' => 'get-index-series',
            'content_url' => $current_url,
            'request_headers' => $this->request_data["request_headers"],
            'response_headers' => $this->request_data["response_headers"],
            'http_method' => $this->request_data["http_method"],
            'ezoic_api_version' => $this->request_data["ezoic_api_version"],
            'ezoic_wp_integration_version' => $this->request_data["ezoic_wp_plugin_version"],
        );

		if(!empty($_GET)){
		    $request_params = array_merge($request_params, $_GET);
		}

		unset($this->request_data["request_headers"]["Content-Length"]);
        $this->request_data["request_headers"]['X-Wordpress-Integration'] = 'true';

        //Get IP for X-Forwarded-For
        $ip = $this->request_data["client_ip"];

	    $request = array(
	    	'timeout' => 5,
	        'body' => $request_params,
            'headers' => array('X-Wordpress-Integration' => 'true', 'X-Forwarded-For' => $ip, 'Expect' => ''),
            'cookies' => $this->buildCookiesForRequest(),
        );

	    return $request;
    }

    private function buildCookiesForRequest() {
		//Build proper cookies for WP remote post
		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			$cookies[] = new \WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
        }

        return $cookies;
    }

}