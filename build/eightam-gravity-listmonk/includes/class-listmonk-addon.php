<?php
/**
 * Gravity Forms Listmonk add-on.
 *
 * @package EightAM\GravityFormsListmonk
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GFForms' ) ) {
	return;
}

/**
 * Gravity Forms feed add-on for Listmonk.
 */
class EAGF_Listmonk_AddOn extends GFFeedAddOn {

	/**
	 * Add-on version.
	 *
	 * @var string
	 */
	protected $_version = EAGF_LISTMONK_VERSION;

	/**
	 * Minimum Gravity Forms version.
	 *
	 * @var string
	 */
	protected $_min_gravityforms_version = '2.5';

	/**
	 * Add-on slug.
	 *
	 * @var string
	 */
	protected $_slug = EAGF_LISTMONK_SLUG;

	/**
	 * Add-on title.
	 *
	 * @var string
	 */
	protected $_title = 'Gravity Forms Listmonk';

	/**
	 * Add-on short title.
	 *
	 * @var string
	 */
	protected $_short_title = 'Listmonk';

	/** @var string */
	protected $_path = 'eightam-gravity-listmonk/listmonk-gravityforms.php';

	/** @var string */
	protected $_full_path = EAGF_LISTMONK_PLUGIN_FILE;

	/** @var string */
	protected $_capabilities_settings_page = 'gravityforms_listmonk';

	/** @var string */
	protected $_capabilities_form_settings = 'gravityforms_listmonk';

	/** @var string */
	protected $_capabilities_uninstall = 'gravityforms_listmonk_uninstall';

	/** @var string */
	protected $_menu_icon = EAGF_LISTMONK_PLUGIN_URL . 'assets/img/listmonk-icon.svg';

	/** @var EAGF_Listmonk_AddOn */
	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return EAGF_Listmonk_AddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		parent::init();

		add_action( 'admin_post_eagf_listmonk_retry', array( $this, 'handle_retry_request' ) );
		add_action( 'gform_entry_detail_meta_boxes', array( $this, 'add_entry_meta_box' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'maybe_show_credentials_notice' ) );
	}

	/**
	 * Get the menu icon for the add-on.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		return file_get_contents( EAGF_LISTMONK_PLUGIN_DIR . 'assets/img/listmonk-icon.svg' );
	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @return string
	 */
	public function get_short_title() {
		return $this->_short_title;
	}

	/**
	 * Define custom capabilities and map them to Gravity Forms capabilities.
	 *
	 * @param string $capability Optional capability to check.
	 * @return array|string
	 */
	public function get_capabilities( $capability = '' ) {
		$capabilities = array(
			'gravityforms_listmonk'           => 'gravityforms_edit_settings',
			'gravityforms_listmonk_uninstall' => 'gravityforms_uninstall',
		);

		if ( $capability ) {
			return isset( $capabilities[ $capability ] ) ? $capabilities[ $capability ] : $capability;
		}

		return $capabilities;
	}

	/**
	 * Enqueue admin assets.
	 */
	public function scripts() {
		return array();
	}

	/**
	 * Enqueue admin styles.
	 */
	public function styles() {
		return array();
	}

	/**
	 * Plugin settings.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'       => __( 'Listmonk Credentials', 'eightam-gf-listmonk' ),
				'description' => sprintf(
					/* translators: %s: URL to Listmonk API documentation */
					__( 'Provide API details with access to create subscribers. %s', 'eightam-gf-listmonk' ),
					'<a href="https://listmonk.app/docs/apis/apis/" target="_blank" rel="noopener noreferrer">' . __( 'Learn more about Listmonk API credentials', 'eightam-gf-listmonk' ) . '</a>'
				),
				'fields'      => array(
					array(
						'name'        => 'base_url',
						'label'       => __( 'Base URL', 'eightam-gf-listmonk' ),
						'type'        => 'text',
						'required'    => true,
						'class'       => 'large',
						'placeholder' => 'https://listmonk.example.com/',
						'tooltip'     => __( 'The full URL to your Listmonk installation, including https://', 'eightam-gf-listmonk' ),
					),
					array(
						'name'     => 'username',
						'label'    => __( 'API Username', 'eightam-gf-listmonk' ),
						'type'     => 'text',
						'required' => true,
						'tooltip'  => __( 'The username for HTTP basic authentication to the Listmonk API.', 'eightam-gf-listmonk' ),
					),
					array(
						'name'     => 'api_key',
						'label'    => __( 'API Key / Token', 'eightam-gf-listmonk' ),
						'type'     => 'text',
						'required' => true,
						'tooltip'  => __( 'The password/token for HTTP basic authentication to the Listmonk API.', 'eightam-gf-listmonk' ),
					),
					array(
						'name'    => 'preconfirm_default',
						'label'   => __( 'Preconfirm Subscriptions by Default', 'eightam-gf-listmonk' ),
						'type'    => 'checkbox',
						'choices' => array(
							array(
								'label' => __( 'Skip double opt-in for all feeds unless overridden.', 'eightam-gf-listmonk' ),
								'name'  => 'enabled',
							),
						),
						'tooltip' => __( 'When enabled, subscribers will be automatically confirmed without requiring email confirmation.', 'eightam-gf-listmonk' ),
					),
				),
			),
		);
	}

	/**
	 * Feed settings.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		$form = $this->get_current_form();

		return array(
			array(
				'title'  => __( 'Feed Settings', 'eightam-gf-listmonk' ),
				'fields' => array(
					array(
						'label'    => __( 'Feed Name', 'eightam-gf-listmonk' ),
						'type'     => 'text',
						'name'     => 'feedName',
						'class'    => 'medium',
						'required' => true,
						'tooltip'  => __( 'Enter a name to identify this feed.', 'eightam-gf-listmonk' ),
					),
				),
			),
			array(
				'title'  => __( 'Subscriber Details', 'eightam-gf-listmonk' ),
				'fields' => array(
					array(
						'label'      => __( 'Field Mapping', 'eightam-gf-listmonk' ),
						'type'       => 'field_map',
						'name'       => 'field_map',
						'field_map'  => array(
							array(
								'name'     => 'email',
								'label'    => __( 'Email Address', 'eightam-gf-listmonk' ),
								'required' => true,
							),
							array(
								'name'  => 'name',
								'label' => __( 'Name', 'eightam-gf-listmonk' ),
							),
						),
					),
					array(
						'label'                => __( 'Attributes JSON', 'eightam-gf-listmonk' ),
						'name'                 => 'attribs_json',
						'type'                 => 'textarea',
						'class'                => 'medium merge-tag-support mt-position-right',
						'description'          => __( 'Optional JSON object for subscriber attributes. Example: {"city":"Berlin","source":"website"}. Supports merge tags.', 'eightam-gf-listmonk' ),
						'validation_callback'  => array( $this, 'validate_attribs_json' ),
						'save_callback'        => array( $this, 'save_attribs_json' ),
					),
				),
			),
			array(
				'title'  => __( 'List Assignment', 'eightam-gf-listmonk' ),
				'fields' => array(
					array(
						'label'           => __( 'List Presets (Optional)', 'eightam-gf-listmonk' ),
						'type'            => 'select',
						'name'            => 'list_presets',
						'required'        => false,
						'multiple'        => 'multiple',
						'choices'         => $this->get_list_choices_for_select(),
						'description'     => sprintf(
							/* translators: %s: URL to Listmonk lists documentation */
							__( 'Hold Ctrl/Cmd to select multiple lists that will always receive the subscriber. %s', 'eightam-gf-listmonk' ),
							'<a href="https://listmonk.app/docs/concepts/#lists" target="_blank" rel="noopener noreferrer">' . __( 'Learn about Listmonk lists', 'eightam-gf-listmonk' ) . '</a>'
						),
						'save_callback'   => array( $this, 'save_list_presets' ),
					),
					array(
						'label'       => __( 'Dynamic List Field', 'eightam-gf-listmonk' ),
						'type'        => 'select',
						'name'        => 'dynamic_list_field',
						'choices'     => $this->get_dynamic_field_choices( $form ),
						'description' => __( 'Optional field whose submitted values resolve to List IDs (comma-separated, JSON, or checkbox selections). The List ID can be found in your Listmonk admin panel.', 'eightam-gf-listmonk' ),
					),
					array(
						'label'   => __( 'Preconfirm Subscriptions', 'eightam-gf-listmonk' ),
						'name'    => 'preconfirm_mode',
						'type'    => 'select',
						'choices' => array(
							array(
								'label' => __( 'Inherit global setting', 'eightam-gf-listmonk' ),
								'value' => 'inherit',
							),
							array(
								'label' => __( 'Always preconfirm this feed', 'eightam-gf-listmonk' ),
								'value' => 'yes',
							),
							array(
								'label' => __( 'Require confirmation (double opt-in)', 'eightam-gf-listmonk' ),
								'value' => 'no',
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Save callback for list_presets multi-select.
	 *
	 * @param array        $field Field config.
	 * @param string|array $value Submitted value.
	 * @return string Comma-separated list IDs.
	 */
	public function save_list_presets( $field, $value ) {
		if ( is_array( $value ) ) {
			// Filter out empty values and join with commas
			return implode( ',', array_filter( $value ) );
		}
		return (string) $value;
	}

	/**
	 * Save callback for attribs_json textarea.
	 *
	 * @param array        $field Field config.
	 * @param string|array $value Submitted value.
	 * @return string JSON string.
	 */
	public function save_attribs_json( $field, $value ) {
		// GF sometimes passes arrays, get the actual string value
		if ( is_array( $value ) ) {
			// Get from POST directly
			$field_name = rgar( $field, 'name' );
			$value = rgpost( '_gform_setting_' . $field_name );
		}
		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * Validate JSON attributes field.
	 *
	 * @param array  $field Field config.
	 * @param string $value Submitted value.
	 * @return void
	 */
	public function validate_attribs_json( $field, $value ) {
		// Handle array or string input
		if ( is_array( $value ) ) {
			$value = '';
		}
		
		// Empty is valid
		if ( empty( trim( (string) $value ) ) ) {
			return;
		}

		// Check if it's valid JSON
		json_decode( $value );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->set_field_error( $field, __( 'Invalid JSON format. Please provide a valid JSON object.', 'eightam-gf-listmonk' ) );
		}
	}

	/**
	 * Process feed submission.
	 *
	 * @param array $feed  Feed configuration.
	 * @param array $entry Entry data.
	 * @param array $form  Form.
	 * @return void
	 */
	public function process_feed( $feed, $entry, $form ) {
		$client = $this->get_client();

		if ( is_wp_error( $client ) ) {
			$this->log_error( __METHOD__ . '(): Client unavailable. ' . $client->get_error_message() );
			$this->add_entry_note( $entry['id'], sprintf( __( 'Listmonk feed skipped: %s', 'eightam-gf-listmonk' ), $client->get_error_message() ) );
			return;
		}

		$field_map = $this->get_field_map_fields( $feed, 'field_map' );
		
		$email = $this->get_field_value( $form, $entry, rgar( $field_map, 'email' ) );

		if ( empty( $email ) ) {
			$this->add_entry_note( $entry['id'], __( 'Listmonk feed aborted: email address missing.', 'eightam-gf-listmonk' ) );
			return;
		}

		$name = $this->get_field_value( $form, $entry, rgar( $field_map, 'name' ) );

		$lists = $this->resolve_lists( $feed, $entry, $form );

		if ( empty( $lists ) ) {
			$this->add_entry_note( $entry['id'], __( 'Listmonk feed skipped: no lists were selected.', 'eightam-gf-listmonk' ) );
			return;
		}

		$attribs = $this->build_attributes_payload( $feed, $entry, $form );
		$payload = array(
			'email'                    => $email,
			'name'                     => $name,
			'lists'                    => array_values( $lists ),
			'attribs'                  => $attribs,
			'preconfirm_subscriptions' => $this->is_preconfirm_enabled( $feed ),
			'status'                   => 'enabled',
		);

		$this->log_debug( __METHOD__ . '(): Sending payload => ' . print_r( $payload, true ) );

		$response = $client->upsert_subscriber( $payload );

		if ( $response['success'] ) {
			$this->add_entry_note( $entry['id'], sprintf( __( 'Listmonk sync successful (HTTP %d).', 'eightam-gf-listmonk' ), $response['code'] ) );
			return;
		}

		$message = sprintf( __( 'Listmonk sync failed: %s', 'eightam-gf-listmonk' ), $response['message'] );
		$this->add_entry_note( $entry['id'], $message );
		$this->log_error( __METHOD__ . '(): ' . $message );
	}

	/**
	 * Add entry note helper.
	 *
	 * @param int    $entry_id Entry ID.
	 * @param string $content  Message.
	 * @return void
	 */
	protected function add_entry_note( $entry_id, $content ) {
		GFAPI::add_note( $entry_id, 0, __( 'Listmonk', 'eightam-gf-listmonk' ), $content );
	}

	/**
	 * Build attributes payload from feed settings.
	 *
	 * @param array $feed Feed.
	 * @param array $entry Entry.
	 * @param array $form Form.
	 * @return array
	 */
	protected function build_attributes_payload( $feed, $entry, $form ) {
		$attribs = new stdClass(); // Use object for JSON encoding
		
		$json = trim( rgar( $feed['meta'], 'attribs_json' ) );
		if ( $json ) {
			// Replace merge tags
			$json = GFCommon::replace_variables( $json, $form, $entry, false, false, false, 'text' );
			$decoded = json_decode( $json );
			
			if ( is_object( $decoded ) ) {
				$attribs = $decoded;
			} elseif ( json_last_error() !== JSON_ERROR_NONE ) {
				$this->log_error( __METHOD__ . '(): Invalid JSON in attribs_json after merge tag replacement: ' . json_last_error_msg() );
			}
		}

		return $attribs;
	}

	/**
	 * Determine preconfirm flag.
	 *
	 * @param array $feed Feed.
	 * @return bool
	 */
	protected function is_preconfirm_enabled( $feed ) {
		$mode = rgar( $feed['meta'], 'preconfirm_mode', 'inherit' );

		if ( 'yes' === $mode ) {
			return true;
		}

		if ( 'no' === $mode ) {
			return false;
		}

		$default = rgars( $this->get_plugin_settings(), 'preconfirm_default/enabled' );

		return (bool) $default;
	}

	/**
	 * Feed list columns.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'  => __( 'Feed Name', 'eightam-gf-listmonk' ),
			'listCount' => __( 'Lists', 'eightam-gf-listmonk' ),
		);
	}

	/**
	 * Get value for feedName column.
	 *
	 * @param array $feed Feed.
	 * @return string
	 */
	public function get_column_value_feedName( $feed ) {
		return rgars( $feed, 'meta/feedName', __( 'Unnamed Feed', 'eightam-gf-listmonk' ) );
	}

	/**
	 * Get value for listCount column.
	 *
	 * @param array $feed Feed.
	 * @return string
	 */
	public function get_column_value_listCount( $feed ) {
		$presets = rgar( $feed['meta'], 'list_presets' );
		$count   = 0;
		
		if ( ! empty( $presets ) ) {
			// Handle comma-separated string
			$preset_ids = is_array( $presets ) ? $presets : explode( ',', $presets );
			$count = count( array_filter( $preset_ids ) );
		}
		
		$dynamic = rgar( $feed['meta'], 'dynamic_list_field' );

		if ( $count && $dynamic ) {
			return sprintf( __( '%d preset + dynamic', 'eightam-gf-listmonk' ), $count );
		} elseif ( $count ) {
			return sprintf( _n( '%d list', '%d lists', $count, 'eightam-gf-listmonk' ), $count );
		} elseif ( $dynamic ) {
			return __( 'Dynamic only', 'eightam-gf-listmonk' );
		}

		return __( 'None', 'eightam-gf-listmonk' );
	}

	/**
	 * Resolve lists from static presets and dynamic field.
	 *
	 * @param array $feed Feed.
	 * @param array $entry Entry.
	 * @param array $form Form.
	 * @return int[]
	 */
	protected function resolve_lists( $feed, $entry, $form ) {
		$lists = array();

		// Get preset lists from multi-select (stored as comma-separated string)
		$presets = rgar( $feed['meta'], 'list_presets' );
		
		if ( ! empty( $presets ) ) {
			// Convert comma-separated string to array
			$preset_ids = is_array( $presets ) ? $presets : explode( ',', $presets );
			foreach ( $preset_ids as $list_id ) {
				$list_id = absint( trim( $list_id ) );
				if ( $list_id ) {
					$lists[] = $list_id;
				}
			}
		}

		// Get dynamic lists from form field
		$field_id = rgar( $feed['meta'], 'dynamic_list_field' );
		if ( $field_id ) {
			$dynamic_lists = $this->parse_dynamic_lists( $field_id, $entry, $form );
			$lists = array_merge( $lists, $dynamic_lists );
		}

		return array_values( array_unique( array_filter( array_map( 'absint', $lists ) ) ) );
	}

	/**
	 * Parse dynamic list values.
	 *
	 * @param string|int $field_id Field ID.
	 * @param array      $entry Entry.
	 * @param array      $form Form.
	 * @return int[]
	 */
	protected function parse_dynamic_lists( $field_id, $entry, $form ) {
		$field = GFFormsModel::get_field( $form, $field_id );
		
		if ( ! $field ) {
			return array();
		}

		// For checkbox fields, get the actual selected values (not the 0/1 flags)
		if ( $field->type === 'checkbox' ) {
			$raw_list = array();
			foreach ( $field->inputs as $input ) {
				$input_id = (string) $input['id'];
				$input_value = rgar( $entry, $input_id );
				// Checkbox stores the choice value when checked, empty when unchecked
				if ( ! empty( $input_value ) ) {
					$raw_list[] = $input_value;
				}
			}
			return array_map( 'absint', array_filter( array_map( 'trim', $raw_list ) ) );
		}

		// For other field types
		$value = $field->get_value_export( $entry, $field_id, true );
		$raw_list = array();

		if ( is_array( $value ) ) {
			$raw_list = $value;
		} elseif ( is_string( $value ) ) {
			$string = trim( $value );
			if ( ! $string ) {
				return array();
			}

			if ( $string[0] === '[' ) {
				$decoded = json_decode( $string, true );
				if ( is_array( $decoded ) ) {
					$raw_list = $decoded;
				}
			} else {
				$raw_list = preg_split( '/[,\|]/', $string );
			}
		}

		return array_map( 'absint', array_filter( array_map( 'trim', (array) $raw_list ) ) );
	}

	/**
	 * Retrieve dynamic field choices.
	 *
	 * @param array $form Form.
	 * @return array
	 */
	protected function get_dynamic_field_choices( $form ) {
		$choices = array(
			array(
				'label' => __( '— None —', 'eightam-gf-listmonk' ),
				'value' => '',
			),
		);

		if ( ! is_array( $form ) || empty( $form['fields'] ) ) {
			return $choices;
		}

		foreach ( $form['fields'] as $field ) {
			if ( ! $field instanceof GF_Field ) {
				continue;
			}

			if ( $field->allowsMultipleSelections || $field->type === 'checkbox' || $field->type === 'select' || $field->type === 'text' ) {
				$choices[] = array(
					'label' => sprintf( '%s (ID %s)', $field->get_field_label( false, null ), $field->id ),
					'value' => $field->id,
				);
			}
		}

		return $choices;
	}

	/**
	 * Build select choices from Listmonk lists.
	 *
	 * @return array
	 */
	protected function get_list_choices_for_select() {
		$lists = $this->get_cached_lists();

		$choices = array(
			array(
				'label' => __( '— Select Lists —', 'eightam-gf-listmonk' ),
				'value' => '',
			),
		);

		if ( is_wp_error( $lists ) ) {
			$choices[] = array(
				'label' => sprintf( __( 'Error: %s', 'eightam-gf-listmonk' ), $lists->get_error_message() ),
				'value' => '',
			);
			return $choices;
		}

		if ( empty( $lists ) ) {
			$choices[] = array(
				'label' => __( 'No lists found', 'eightam-gf-listmonk' ),
				'value' => '',
			);
			return $choices;
		}

		foreach ( $lists as $list ) {
			$choices[] = array(
				'label' => sprintf( '%s (ID %d)', $list['name'], $list['id'] ),
				'value' => $list['id'],
			);
		}

		return $choices;
	}

	/**
	 * Fetch and cache lists.
	 *
	 * @param bool $force Force refresh.
	 * @return array|WP_Error
	 */
	public function get_cached_lists( $force = false ) {
		$client = $this->get_client();
		
		if ( is_wp_error( $client ) ) {
			return $client;
		}

		// Use a stable cache key based on the base URL
		$settings = $this->get_plugin_settings();
		$base_url = rgar( $settings, 'base_url', '' );
		$key = 'eagf_listmonk_lists_' . md5( $base_url );
		
		$lists = $force ? false : get_transient( $key );

		// Treat empty array as no cache (could be stale empty result)
		if ( false === $lists || ( is_array( $lists ) && empty( $lists ) ) ) {
			$response = $client->get_lists();
			
			if ( ! $response['success'] ) {
				return new WP_Error( 'listmonk_lists_failed', $response['message'] );
			}

			// Listmonk wraps results in data.data.results
			$lists = rgars( $response, 'data/data/results', array() );
			
			// Only cache if we got actual results
			if ( ! empty( $lists ) ) {
				set_transient( $key, $lists, MINUTE_IN_SECONDS * 10 );
			}
		}

		return $lists;
	}

	/**
	 * Instantiate API client.
	 *
	 * @return EAGF_Listmonk_Client|WP_Error
	 */
	protected function get_client() {
		$settings = $this->get_plugin_settings();
		$base_url = rgar( $settings, 'base_url' );
		$username = rgar( $settings, 'username' );
		$api_key  = rgar( $settings, 'api_key' );

		if ( ! $base_url || ! $username || ! $api_key ) {
			return new WP_Error( 'missing_settings', __( 'Listmonk credentials are not configured.', 'eightam-gf-listmonk' ) );
		}

		return new EAGF_Listmonk_Client( $base_url, $username, $api_key );
	}

	/**
	 * Add entry sidebar box for retry.
	 *
	 * @param array $meta_boxes Existing boxes.
	 * @param array $entry Entry.
	 * @param array $form Form.
	 * @return array
	 */
	public function add_entry_meta_box( $meta_boxes, $entry, $form ) {
		if ( ! current_user_can( 'gravityforms_listmonk' ) ) {
			return $meta_boxes;
		}

		$meta_boxes['eagf_listmonk'] = array(
			'title'    => __( 'Listmonk Actions', 'eightam-gf-listmonk' ),
			'callback' => array( $this, 'render_entry_meta_box' ),
			'context'  => 'side',
		);

		return $meta_boxes;
	}

	/**
	 * Render retry meta box.
	 *
	 * @param array $args Args from GF.
	 * @param array $box Box data.
	 * @return void
	 */
	public function render_entry_meta_box( $args, $box ) {
		$entry = rgar( $args, 'entry' );
		$form  = rgar( $args, 'form' );

		?>
		<p><?php esc_html_e( 'Re-run the Listmonk feed if you updated subscriber data or resolved API issues.', 'eightam-gf-listmonk' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'eagf_listmonk_retry', '_eagf_retry_nonce' ); ?>
			<input type="hidden" name="action" value="eagf_listmonk_retry" />
			<input type="hidden" name="entry_id" value="<?php echo esc_attr( rgar( $entry, 'id' ) ); ?>" />
			<input type="hidden" name="form_id" value="<?php echo esc_attr( rgar( $form, 'id' ) ); ?>" />
			<?php submit_button( __( 'Retry Feed', 'eightam-gf-listmonk' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
	}

	/**
	 * Handle retry action.
	 *
	 * @return void
	 */
	public function handle_retry_request() {
		if ( ! current_user_can( 'gravityforms_listmonk' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'eightam-gf-listmonk' ) );
		}

		check_admin_referer( 'eagf_listmonk_retry', '_eagf_retry_nonce' );

		$entry_id = absint( rgar( $_POST, 'entry_id' ) );
		$form_id  = absint( rgar( $_POST, 'form_id' ) );

		if ( ! $entry_id || ! $form_id ) {
			wp_safe_redirect( wp_get_referer() );
			return;
		}

		$entry = GFAPI::get_entry( $entry_id );
		$form  = GFAPI::get_form( $form_id );

		if ( is_wp_error( $entry ) || empty( $form ) ) {
			wp_safe_redirect( wp_get_referer() );
			return;
		}

		$feeds = GFAPI::get_feeds( null, $form_id, $this->_slug );

		if ( is_wp_error( $feeds ) ) {
			wp_safe_redirect( wp_get_referer() );
			return;
		}

		foreach ( $feeds as $feed ) {
			if ( ! rgar( $feed, 'is_active' ) ) {
				continue;
			}

			$this->process_feed( $feed, $entry, $form );
		}

		wp_safe_redirect( wp_get_referer() );
	}

	/**
	 * Show admin notice if credentials are missing and feeds exist.
	 *
	 * @return void
	 */
	public function maybe_show_credentials_notice() {
		// Only show on admin pages
		if ( ! is_admin() ) {
			return;
		}

		// Only show to users who can manage settings
		if ( ! current_user_can( 'gravityforms_listmonk' ) ) {
			return;
		}

		// Check if credentials are configured
		$client = $this->get_client();
		if ( ! is_wp_error( $client ) ) {
			return;
		}

		// Only show if the error is missing settings
		if ( 'missing_settings' !== $client->get_error_code() ) {
			return;
		}

		// Check if any active feeds exist
		$feeds = $this->get_feeds();
		$has_active_feed = false;

		if ( ! empty( $feeds ) && is_array( $feeds ) ) {
			foreach ( $feeds as $feed ) {
				if ( rgar( $feed, 'is_active' ) ) {
					$has_active_feed = true;
					break;
				}
			}
		}

		if ( ! $has_active_feed ) {
			return;
		}

		// Show the notice
		$settings_url = admin_url( 'admin.php?page=gf_settings&subview=' . $this->_slug );
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Listmonk Integration:', 'eightam-gf-listmonk' ); ?></strong>
				<?php
				printf(
					/* translators: %s: URL to settings page */
					esc_html__( 'You have active Listmonk feeds but credentials are not configured. Please configure your API credentials in the %s.', 'eightam-gf-listmonk' ),
					'<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Listmonk settings', 'eightam-gf-listmonk' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}
