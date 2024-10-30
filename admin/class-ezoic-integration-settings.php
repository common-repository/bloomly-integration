<?php

namespace Bloomly_Namespace;

/**
 * The settings of the plugin.
 *
 * @link       https://ezoic.com
 * @since      1.0.0
 *
 * @package    Ezoic_Integration
 * @subpackage Ezoic_Integration/admin
 */

/**
 * Class Ezoic_Integration_Admin_Settings
 * @package Bloomly_Namespace
 */
class Ezoic_Integration_Admin_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $cacheType;

	private $cacheIdentity;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * This function introduces the theme options into the 'Appearance' menu and into a top-level menu.
	 */
	public function setup_plugin_options_menu() {

		// Add the menu to the Plugins set of menu items
		add_options_page(
			EZOIC__PLUGIN_NAME,
			EZOIC__PLUGIN_NAME,
			'manage_options',
			EZOIC__PLUGIN_SLUG,
			array(
				$this,
				'render_settings_page_content',
			)
		);

	}

	/**
	 * Provides default values for the Display Options.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'is_integrated' => false,
			'check_time'    => '',
		);

		return $defaults;

	}

	/**
	 * Provide default values for the Social Options.
	 *
	 * @return array
	 */
	public function default_advanced_options() {

		$defaults = array(
			'verify_ssl' => true,
		);

		return $defaults;

	}

	/**
	 * Renders a settings page
	 *
	 * @param string $active_tab
	 */
	public function render_settings_page_content( $active_tab = '' ) {
		?>
        <div class="wrap" id="ez_integration">

            <h2><?php _e( EZOIC__PLUGIN_NAME, 'ezoic' ); ?></h2>

			<?php if ( isset( $_GET['tab'] ) ) {
				$active_tab = $_GET['tab'];
			} elseif ( $active_tab == 'advanced_options' ) {
				$active_tab = 'advanced_options';
			} else {
				$active_tab = 'integration_status';
			} // end if/else ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=<?php echo EZOIC__PLUGIN_SLUG; ?>&tab=integration_status"
                   class="nav-tab <?php echo $active_tab == 'integration_status' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Integration Status',
						'ezoic' ); ?></a>
                <a href="?page=<?php echo EZOIC__PLUGIN_SLUG; ?>&tab=advanced_options"
                   class="nav-tab <?php echo $active_tab == 'advanced_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Advanced Settings',
						'ezoic' ); ?></a>
				<?php if ( 'Ezoic' === EZOIC__SITE_NAME ) { ?>
                    <a href="https://support.ezoic.com/" target="_blank" class="nav-tab" id="help-tab">
						<?php _e( 'Help Center', 'ezoic' ); ?>
                    </a>
				<?php } ?>
            </h2>

            <form method="post" action="options.php" id="ezoic_settings">
				<?php

				if ( $active_tab == 'advanced_options' ) {

					settings_fields( 'ezoic_integration_options' );
					do_settings_sections( 'ezoic_integration_settings' );
					submit_button( 'Save Settings' );

				} else {

					settings_fields( 'ezoic_integration_status' );
					do_settings_sections( 'ezoic_integration_status' );

				} // end if/else

				?>
            </form>

        </div><!-- /.wrap -->
		<?php
	}

	public function general_options_callback() {
		$options = get_option( 'ezoic_integration_status' );

		echo '<hr/>';
		self::display_notice( $options );

	} // end general_options_callback

	public function advanced_options_callback() {
		$options = get_option( 'ezoic_integration_options' );

		echo '<p>' . __( 'Only use if you\'re having issue with our default integration!', 'ezoic' ) . '</p>';
		echo '<hr/>';
	} // end advanced_options_callback

	/**
	 * Initializes options page by registering the Sections, Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_display_options() {

		// If the plugin options don't exist, create them.
		if ( false == get_option( 'ezoic_integration_status' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'ezoic_integration_status', $default_array );
		}

		add_settings_section(
			'general_settings_section',
			__( 'Integration Status', 'ezoic' ),
			array( $this, 'general_options_callback' ),
			'ezoic_integration_status'
		);

		add_settings_field(
			'is_integrated',
			__( 'Integration Status', 'ezoic' ),
			array( $this, 'is_integrated_callback' ),
			'ezoic_integration_status',
			'general_settings_section',
			array(
				//__( 'Activate this setting to display the header.', 'ezoic' ),
				//'class' => 'hidden',
			)
		);

		add_settings_field(
			'check_time',
			__( 'Last Checked', 'ezoic' ),
			array( $this, 'check_time_callback' ),
			'ezoic_integration_status',
			'general_settings_section',
			array(//'class' => 'last_checked'
			)
		);

		register_setting(
			'ezoic_integration_status',
			'ezoic_integration_status'
		);

	} // end initialize_display_options

	/**
	 * Initializes the advanced options by registering the Sections, Fields, and Settings.
	 *
	 * This function is registered with the 'admin_init' hook.
	 */
	public function initialize_advanced_options() {

		//delete_option( 'ezoic_integration_options' );
		if ( false == get_option( 'ezoic_integration_options' ) ) {
			$default_array = $this->default_advanced_options();
			update_option( 'ezoic_integration_options', $default_array );
		} // end if

		add_settings_section(
			'advanced_settings_section',
			__( 'Advanced Settings', 'ezoic' ),
			array( $this, 'advanced_options_callback' ),
			'ezoic_integration_settings'
		);

		add_settings_field(
			'verify_ssl',
			'Verify SSL',
			array( $this, 'verify_ssl_callback' ),
			'ezoic_integration_settings',
			'advanced_settings_section',
			array(
				__( 'Turn off SSL verification', 'ezoic' ),
			)
		);

		register_setting(
			'ezoic_integration_options',
			'ezoic_integration_options'
		);

	}

	public function is_integrated_callback( $args ) {

		$options = get_option( 'ezoic_integration_status' );

		$html = '<input type="hidden" id="is_integrated" name="ezoic_integration_status[is_integrated]" value="1" ' . checked( 1,
				isset( $options['is_integrated'] ) ? $options['is_integrated'] : 0, false ) . '/>';

		$html .= '<div>';
		if ( $options['is_integrated'] ) {
			$html .= '<span class="text-success">Active</span>';
		} else {
			$html .= '<span class="danger">Inactive</span>';
		}
		$html .= '</div>';

		echo $html;

	} // end is_integrated_callback

	public function check_time_callback() {

		$options = get_option( 'ezoic_integration_status' );

		$html = '<input type="hidden" id="check_time" name="ezoic_integration_status[check_time]" value="' . $options['check_time'] . '"/>';
		$html .= '<div>' . date( 'm/d/Y H:i:s',
				$options['check_time'] ) . ' &nbsp; [<a href="?page=' . EZOIC__PLUGIN_SLUG . '&tab=integration_status&recheck=1">recheck</a>]</div>';

		echo $html;

	} // end check_time_callback


	public function verify_ssl_callback( $args ) {

		$options = get_option( 'ezoic_integration_options' );

		$html = '<select id="verify_ssl" name="ezoic_integration_options[verify_ssl]">';
		$html .= '<option value="1" ' . selected( $options['verify_ssl'], 1, false ) . '>' . __( 'Yes',
				'ezoic' ) . '</option>';
		$html .= '<option value="0" ' . selected( $options['verify_ssl'], 0, false ) . '>' . __( 'No',
				'ezoic' ) . '</option>';
		$html .= '</select>';
		$html .= '<label for="verify_ssl">&nbsp;&nbsp;' . $args[0] . '</label>';

		echo $html;

	} // end verify_ssl_callback

	public function display_notice( $options ) {

		$type = '';

		$cacheIndetifier     = new Ezoic_Integration_Cache_Identifier();
		$this->cacheIdentity = $cacheIndetifier->GetCacheIdentity();
		$this->cacheType     = $cacheIndetifier->GetCacheType();

		$time_check = current_time( 'timestamp' ) - 21600; // 6 hours
		if ( $options['is_integrated'] == '' || $options['check_time'] <= $time_check || ( isset( $_GET['recheck'] ) && $_GET['recheck'] ) ) {

			$results = $this->getIntegrationCheckEzoicResponse();

			$update                  = array();
			$update['is_integrated'] = $results['result'];
			$update['check_time']    = current_time( 'timestamp' );
			update_option( 'ezoic_integration_status', $update );

			if ( false === $results['result'] ) {

				if ( ! empty( $results['error'] ) ) {
					$args = apply_filters(
						'ezoic_view_arguments',
						array( 'type' => 'integration_error' ),
						'ezoic-integration-admin'
					);
				} else {
					$args = apply_filters(
						'ezoic_view_arguments',
						array( 'type' => 'not_integrated' ),
						'ezoic-integration-admin'
					);
				}

				foreach ( $args as $key => $val ) {
					$$key = $val;
				}

			}
			$is_integrated = $results['result'];

			$file = EZOIC__PLUGIN_DIR . 'admin/partials/' . 'ezoic-integration-admin-display' . '.php';
			include( $file );

		} else {
			$is_integrated = $options['is_integrated'];
		}


	}

	private function getIntegrationCheckEzoicResponse() {

		$content  = 'ezoic integration test';
		$response = $this->requestDataFromEzoic( $content );

		return $response;

	}

	private function requestDataFromEzoic( $final_content ) {

		$timeout = 5;

		$cache_key = md5( $final_content );

		$request_data = Ezoic_Integration_Request_Utils::GetRequestBaseData();

		$request_params = array(
			'cache_key'                    => $cache_key,
			'action'                       => 'get-index-series',
			'content_url'                  => get_home_url() . '?ezoic_domain_verify=1',
			'request_headers'              => $request_data["request_headers"],
			'response_headers'             => $request_data["response_headers"],
			'http_method'                  => $request_data["http_method"],
			'ezoic_api_version'            => $request_data["ezoic_api_version"],
			'ezoic_wp_integration_version' => $request_data["ezoic_wp_plugin_version"],
			'content'                      => $final_content,
			'request_type'                 => 'with_content',
		);

		$ezoic_options = get_option( 'ezoic_integration_options' );

		if ( $this->cacheType != Ezoic_Cache_Type::NO_CACHE ) {

			$settings = array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL            => $request_data["ezoic_request_url"],
				CURLOPT_TIMEOUT        => $timeout,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER     => array(
					'X-Wordpress-Integration: true',
					'X-Forwarded-For: ' . $request_data["client_ip"],
					'Content-Type: application/x-www-form-urlencoded',
					'Expect:',
				),
				CURLOPT_POST           => true,
				CURLOPT_HEADER         => true,
				CURLOPT_POSTFIELDS     => http_build_query( $request_params ),
				CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
			);

			if ( $ezoic_options['verify_ssl'] == false ) {
				$settings[ CURLOPT_SSL_VERIFYPEER ] = false;
				$settings[ CURLOPT_SSL_VERIFYHOST ] = false;
			}

			$result = Ezoic_Integration_Request_Utils::MakeCurlRequest( $settings );

			if ( ! empty( $result['error'] ) ) {
				return array( "result" => false, "error" => $result['error'] );
			}

		} else {

			unset( $request_data["request_headers"]["Content-Length"] );
			$request_data["request_headers"]['X-Wordpress-Integration'] = 'true';

			$settings = array(
				'timeout' => $timeout,
				'body'    => $request_params,
				'headers' => array(
					'X-Wordpress-Integration' => 'true',
					'X-Forwarded-For'         => $request_data["client_ip"],
					'Expect'                  => ''
				),
			);

			if ( $ezoic_options['verify_ssl'] == false ) {
				$settings['sslverify'] = false;
			}

			$result = wp_remote_post( $request_data["ezoic_request_url"], $settings );

			if ( is_wp_error( $result ) ) {
				return array( "result" => false, "error" => $result->get_error_message() );
			}

		}

		if ( is_array( $result ) && isset( $result['body'] ) ) {
			$final = $result['body'];
		} else {
			$final = $result;
		}

		return array( "result" => $this->ParsePageContents( $final ) );

	}

	private function ParsePageContents( $contents ) {
		if ( strpos( $contents, 'This site is operated by Ezoic and Wordpress Integrated' ) !== false ) {
			return true;
		}

		return false;
	}
}