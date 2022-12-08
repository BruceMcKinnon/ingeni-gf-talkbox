<?php

GFForms::include_feed_addon_framework();

class GFTBFeedAddOn extends GFFeedAddOn {

	protected $_version = GF_TALKBOX_FEED_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9.16';
	protected $_slug = 'tbfeedaddon';
	protected $_path = 'ingeni-gf-talkbox/ingeni-gf-talkbox.php';
	protected $_full_path = __FILE__;
	protected $_title = 'TalkBox Add-On';
	protected $_short_title = 'TalkBox Feed Add-On';

	private static $_instance = null;

	private static $ingeniTbApi = null;


	/**
	 * Get an instance of this class.
	 *
	 * @return GFTBFeedAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFTBFeedAddOn();
		}

		return self::$_instance;
	}



	private function ingeni_load_tb() {
		// Init auto-update from GitHub repo
		require 'plugin-update-checker/plugin-update-checker.php';
		$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/BruceMcKinnon/ingeni-gf-talkbox',
			__FILE__,
			'ingeni-gf-talkbox'
		);
	}


	/**
	 * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
	 */
	public function init() {

		parent::init();

		// Include the Talkbox class
		require_once('ingeni-gf-talkbox-api-class.php');

		// Check for updates from GitHib repo
		$this->ingeni_load_tb();
	}


	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed e.g. subscribe the user to a list.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 *
	 * @return bool|void
	 */
	public function process_feed( $feed, $entry, $form ) {
		$feedName  = $feed['meta']['tbFeedName'];
		$enabled  = $feed['meta']['tbFeedEnabled'];

		// Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
		$field_map = $this->get_field_map_fields( $feed, 'tbMappedFields' );

		// Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
		$merge_vars = array();
		foreach ( $field_map as $name => $field_id ) {
			// Get the field value for the specified field id
			$merge_vars[ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		// Send the values to the third-party service.
		global $IngeniTbApi;

		if ( ( !$IngeniTbApi ) && ( $enabled ) ) {

			$settings = $this->get_plugin_settings();

			// Access a specific setting e.g. an api key
			$url = rgar( $settings, 'talkbox_url' );
			$username = rgar( $settings, 'talkbox_username' );
			$password = rgar( $settings, 'talkbox_password' );

			$debug = rgar( $settings, 'talkbox_debug' );

			if ($debug) {
				error_log( 'settings: '.print_r( $settings, true ) );
				error_log( 'mergevars: '.print_r( $merge_vars, true ) );
			}

			if (strlen($username) > 0) {
				$ingeniTbApi = new IngeniTbApi( $url, $username, $password, $debug );

				$errMsg = '';
				$retJson = $ingeniTbApi->ingeni_tb_create_update_email( $errMsg, $merge_vars['email'], $merge_vars['first_name'], $merge_vars['last_name'], $merge_vars['phone'] );

				if ($debug) {
					error_log( 'ret: '.print_r( $retJson, true ) );
				}
			}
		}
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {

		return parent::scripts();
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => 'ingeni-tb-css',
				'src'     => $this->get_base_url() . '/css/ingeni-gf-talkbox-admin.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Talkbox Add-On Settings', 'tbfeedaddon' ),
				'fields' => array(
					array(
						'name'    => 'talkbox_url',
						'tooltip' => esc_html__( 'URL of TalkBox API server', 'tbfeedaddon' ),
						'label'   => esc_html__( 'TalkBox API URL', 'tbfeedaddon' ),
						'type'    => 'text',
						'class'   => 'small',
						'default_value' => INGENI_DEFAULT_TALKBOX_FEED_URL,
					),
					array(
						'name'    => 'talkbox_username',
						'tooltip' => esc_html__( 'Find this in the TalkBox account Tools > Developers page', 'tbfeedaddon' ),
						'label'   => esc_html__( 'TalkBox API Username', 'tbfeedaddon' ),
						'type'    => 'text',
						'class'   => 'small',

					),
					array(
						'name'    => 'talkbox_password',
						'tooltip' => esc_html__( 'Find this in the TalkBox account Tools > Developers page', 'tbfeedaddon' ),
						'label'   => esc_html__( 'TalkBox API password', 'tbfeedaddon' ),
						'type'    => 'text',
						'class'   => 'small',
					),
					array(
						'type'    => 'checkbox',
						'name'    => 'talkbox_debugging',
						'label'   => esc_html__( 'Debug mode', 'tbfeedaddon' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Debug mode', 'tbfeedaddon' ),
								'name' => 'talkbox_debug',
								'tooltip' => esc_html__( 'Debug mode', 'tbfeedaddon' ),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page in the Form Settings > Talkbox Feed Add-On area.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Talkbox Feed Settings', 'tbfeedaddon' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Feed name', 'tbfeedaddon' ),
						'type'    => 'text',
						'name'    => 'tbFeedName',
						'tooltip' => esc_html__( 'Name this indiviual feed', 'tbfeedaddon' ),
						'class'   => 'small',
					),
					array(
						'label'   => esc_html__( 'Enable this Feed', 'tbfeedaddon' ),
						'type'    => 'checkbox',
						'name'    => 'tbFeedEnabled',
						'tooltip' => esc_html__( 'Check to send new entries to TalkBox', 'tbfeedaddon' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Check to send new entries to TalkBox', 'tbfeedaddon' ),
								'name'  => 'tbFeedEnabled',
							),
						),
					),
					array(
						'name'      => 'tbMappedFields',
						'label'     => esc_html__( 'Map Talkbox values to GF Fields', 'tbfeedaddon' ),
						'type'      => 'field_map',
						'field_map' => array(
							array(
								'name'       => 'email',
								'label'      => esc_html__( 'Email', 'tbfeedaddon' ),
								'required'   => 0,
								'field_type' => array( 'email', 'hidden' ),
							),
							array(
								'name'     => 'first_name',
								'label'    => esc_html__( 'First name', 'tbfeedaddon' ),
								'required' => 0,
							),
							array(
								'name'     => 'last_name',
								'label'    => esc_html__( 'Last name', 'tbfeedaddon' ),
								'required' => 0,
							),
							array(
								'name'       => 'phone',
								'label'      => esc_html__( 'Phone', 'tbfeedaddon' ),
								'required'   => 0,
								'field_type' => 'phone',
							),
						),
					),
					array(
						'name'           => 'condition',
						'label'          => esc_html__( 'Condition', 'tbfeedaddon' ),
						'type'           => 'feed_condition',
						'checkbox_label' => esc_html__( 'Enable Condition', 'tbfeedaddon' ),
						'instructions'   => esc_html__( 'Process this TalkBox feed if', 'tbfeedaddon' ),
					),
				),
			),
		);
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'tbFeedName'  => esc_html__( 'Feed Name', 'tbfeedaddon' ),
		);
	}


	/**
	 * Prevent feeds being listed or created if an api key isn't valid.
	 *
	 * @return bool
	 */
	public function can_create_feed() {

		// Get the plugin settings.
		//$settings = $this->get_plugin_settings();

		// Access a specific setting e.g. an api key
		//$key = rgar( $settings, 'apiKey' );

		return true;
	}

}
