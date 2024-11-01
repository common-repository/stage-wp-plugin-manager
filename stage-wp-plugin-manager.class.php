<?php
/**
 * Stage_WP_Plugin_Manager
 *
 * Manage which plugins must be active for a particular stage only
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
class Stage_WP_Plugin_Manager {

	private static $instance;

	private $stages                    = array();
	private $default_stage             = 'production';
	private $current_stage             = 'production';
	private $managed_plugins           = array();
	private $managed_network_plugins   = array();
	private $stage_plugins             = array();
	private $stage_network_plugins     = array();
	private $non_stage_plugins         = array();
	private $non_stage_network_plugins = array();
	private $original_setup            = array();
	private $network_original_setup    = array();
	private $add_key                   = 'wp_stage_plugins_add';
	private $delete_key                = 'wp_stage_plugins_delete';
	private $wp_option                 = 'wp_stage_managed_plugins';
	private $wp_network_option         = 'wp_stage_managed_network_plugins';
	private $stages_txt                = array();
	private $text_domain               = 'stage-wp-plugin-manager';
	private $mu_plugin                 = false;

	private function __construct() {
		do_action( 'stage_wp_plugin_manager_before_init' );
		$this->set_stages();
		$this->set_default_stage();
		$this->original_setup            = get_option( 'active_plugins' );
		$this->network_original_setup    = $this->reformat( get_site_option( 'active_sitewide_plugins' ) );
		$this->stages_txt                = apply_filters( 'stage_wp_plugin_manager_stages_txt', $this->stages_txt );
		$this->current_stage             = $this->get_current_stage();
		$this->mu_plugin                 = $this->is_mu_plugin();
		$this->managed_plugins           = $this->get_managed_plugins();
		$this->managed_network_plugins   = $this->get_managed_network_plugins();
		$this->stage_plugins             = $this->get_stage_plugins();
		$this->stage_network_plugins     = $this->get_stage_network_plugins();
		$this->non_stage_plugins         = $this->get_non_stage_plugins();
		$this->non_stage_network_plugins = $this->get_non_stage_network_plugins();
		do_action( 'stage_wp_plugin_manager_init' );
	}

	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}

	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			$this->$property = $value;
		}
	}

	/**
	 * Prevent clone() for an instance of this class.
	 */
	public function __clone() {
		trigger_error( __( 'Invalid operation: you cannot clone an instance of ', $this->text_domain ) . get_class( $this ), E_USER_ERROR );
	}

	/**
	 * Prevent unserialize() for an instance of this class.
	 */
	public function __wakeup() {
		trigger_error( __( 'Invalid operation: you cannot unserialize an instance of ', $this->text_domain ) . get_class( $this ), E_USER_ERROR );
	}

	/**
	 * Obtain self instance of this class.
	 *
	 * @return object Self instance of this class.
	 */
	public static function get_instance() {
		if ( !self::$instance instanceof self ) {
			self::$instance = new self;
		}
	    return self::$instance;
	}

	/**
	 * Set stages for this plugin.
	 */
	private function set_stages() {
		$this->register_stage( 'local', __( 'Local', $this->text_domain ) );
		$this->register_stage( 'staging', __( 'Staging', $this->text_domain ) );
		$this->register_stage( 'production', __( 'Production', $this->text_domain ) );
		$stages = $this->stages;
		$stages = apply_filters( 'stage_wp_plugin_manager_stages', $stages );
		$this->stages = $stages;
	}

	/**
	 * Get default stage to be used by the plugin.
	 *
	 * @param  string  $stage Name for the default stage.
	 * @return string         Filtered name for the default stage.
	 */
	private function set_default_stage( $stage = 'production' ) {
		$stage = apply_filters( 'stage_wp_plugin_manager_default_stage', $stage );
		$this->default_stage = $stage;
	}

	/**
	 * Get the current stage.
	 *
	 * @return string Name of the current stage.
	 */
	private function get_current_stage() {
		$stage = $this->default_stage;
		if ( $this->isset_stage() ) {
			$stage = WP_STAGE;
		}
		return $stage;
	}

	/**
	 * Get array of plugins managed by this plugin.
	 *
	 * @param  string $option wp_option name to get list of plugins from.
	 * @param  string $filter Name of filter to be applied to result.
	 * @return array          Filtered array containing plugins.
	 */
	private function get_managed_plugins( $network = false, $filter = '' ) {
		$option = $network ? get_site_option( $this->wp_network_option ) : get_option( $this->wp_option );
		$managed_plugins = $option ? $option : array();
		foreach ( $managed_plugins as $key => $plugins ) {
			foreach ( $plugins as $_key => $plugin ) {
				if ( plugin_basename( dirname( __FILE__ ) . '/stage-wp-plugin-manager.php' ) == $plugin ) {
					unset( $managed_plugins[$key][$_key] );
				}
			}
		}
		$filter = $filter ? $filter : 'stage_wp_plugin_manager_managed_plugins';
		$managed_plugins = apply_filters( $filter, $managed_plugins );
		return $managed_plugins;
	}

	/**
	 * Get array of network plugins managed by this plugin.
	 *
	 * @return array Filtered array containing plugins.
	 */
	private function get_managed_network_plugins() {
		return $this->get_managed_plugins(
			$network = true,
			'stage_wp_plugin_manager_network_managed_plugins'
		);
	}

	/**
	 * Get filtered array of plugins.
	 *
	 * @param  array  $managed_plugins Array containing plugins.
	 * @param  string $filter          Name of the filter to be applied to the result.
	 * @return array                   Filtered array containing plugins.
	 */
	private function get_filtered_stage_plugins( $managed_plugins = array(), $filter = '' ) {
		$stage_plugins = isset( $managed_plugins[$this->current_stage] )
		                 ? $managed_plugins[$this->current_stage] : array();
		if ( $filter ) {
			$stage_plugins = apply_filters( $filter, $stage_plugins );
		}
		return $stage_plugins;
	}

	/**
	 * Get array of non-network plugins for the current stage.
	 *
	 * @return array Filtered array containing plugins.
	 */
	private function get_stage_plugins() {
		return $this->get_filtered_stage_plugins(
			$this->managed_plugins,
			'stage_wp_plugin_manager_stage_plugins'
		);
	}

	/**
	 * Get array of network plugins for the current stage.
	 *
	 * @return array Filtered array containing plugins.
	 */
	private function get_stage_network_plugins() {
		return $this->get_filtered_stage_plugins(
			$this->managed_network_plugins,
			'stage_wp_plugin_manager_stage_network_plugins'
		);
	}

	/**
	 * Get array of plugins that don't belong to the current stage.
	 *
	 * @param  array  $managed_plugins Array containing managed plugins.
	 * @param  string $filter          Name of the filter to be applied to the result.
	 * @return array                   Filtered array containing plugins.
	 */
	private function get_non_stage_plugins( $managed_plugins = array(), $filter = '' ) {
		$managed_plugins = $managed_plugins ? $managed_plugins : $this->managed_plugins;
		$filter = $filter ? $filter : 'stage_wp_plugin_manager_stage_plugins';
		$non_stage_plugins = $managed_plugins;
		$list = array();
		if ( isset( $non_stage_plugins[$this->current_stage] ) ) {
			unset( $non_stage_plugins[$this->current_stage] );
		}
		if ( is_array( $non_stage_plugins ) && !empty( $non_stage_plugins ) ) {
			foreach ( $non_stage_plugins as $stage => $plugins ) {
				if ( is_array( $plugins ) && !empty( $plugins ) ) {
					foreach ( $plugins as $plugin ) {
						$list[] = $plugin;
					}
				}
			}
		}
		$non_stage_plugins = $list;
		$non_stage_plugins = apply_filters( $filter, $non_stage_plugins );
		return $non_stage_plugins;
	}

	/**
	 * Get array of network plugins that don't belong to the current stage.
	 *
	 * @return array Filtered array containing plugins.
	 */
	private function get_non_stage_network_plugins() {
		return $this->get_non_stage_plugins(
			$this->managed_network_plugins,
			'stage_wp_plugin_manager_non_stage_network_plugins'
		);
	}

	/**
	 * Reformat list of plugins from network format to standard format.
	 *
	 * @param  array $plugins Array containing plugins.
	 * @return array          Formatted array containing plugins.
	 */
	public function reformat( $plugins = array() ) {
		$reformatted_plugins = array();

		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $key => $value ) {
				$reformatted_plugins[] = $key;
			}
		}
		return $reformatted_plugins;
	}

	/**
	 * Register a new stage.
	 *
	 * @param  string $stage The name of a stage.
	 * @param  string $txt   The translatable text to be assigned to a stage.
	 */
	private function register_stage( $stage, $txt ) {
		if ( !in_array( $stage, $this->stages ) ) {
			$this->stages[] = $stage;
		}
		$this->register_stage_txt( $stage, $txt );
	}

	/**
	 * Register a translatable text for a given stage.
	 *
	 * @param  string $stage The name of a stage.
	 * @param  string $txt   The translatable text to be assigned to a stage.
	 */
	private function register_stage_txt( $stage, $txt ) {
		if ( $this->stage_is_valid( $stage ) ) {
			$this->stages_txt[$stage] = $txt;
		}
	}

	/**
	 * Obtain the translatable text for a given stage.
	 *
	 * @param  string $stage The name of a stage.
	 */
	public function get_stage_txt( $stage) {
		$txt = isset( $this->stages_txt[$stage] ) ? $this->stages_txt[$stage] : $stage;
		return $txt;
	}

	/**
	 * Check if a stage is set.
	 *
	 * @return boolean Wether a stage is set or not.
	 */
	public function isset_stage() {
		if (   defined( 'WP_STAGE' )
			&& ( '' != WP_STAGE )
			&& $this->stage_is_valid( WP_STAGE )
		) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the current stage is valid.
	 *
	 * @param  string  $stage The name of a stage.
	 * @return boolean        Wether the current given is valid or not.
	 */
	public function stage_is_valid( $stage = '' ) {
		$stage = $stage ? $stage : $this->current_stage;
		$valid = ( in_array( $stage, $this->stages ) );
		return $valid;
	}

	public function is_really_active( $plugin, $network = false ) {
		$plugins = $network ? $this->network_original_setup : $this->original_setup;
		$active = in_array( $plugin, $plugins ) ? true : false;
		return $active;
	}

	/**
	 * Resets the plugin configuration for a given stage.
	 *
	 * @param  array   $plugins A list of active plugins.
	 * @return boolean          Wether the reset process was performed or not.
	 */
	public function do_reset( $plugins, $network = false ) {
		// Decide if we are doing a network reset or a default one.
		$non_stage_plugins = $network ? $this->get_non_stage_network_plugins() : $this->get_non_stage_plugins();
		$stage_plugins = $network ? $this->get_stage_network_plugins() : $this->get_stage_plugins();
		if ( $network ) {
			$plugins = $this->reformat( $plugins );
		}
		// Remove non-stage plugins.
		if (   is_array( $non_stage_plugins )
			&& !empty( $non_stage_plugins )
		) {
			foreach( $non_stage_plugins as $plugin ) {
				if( ( $key = array_search( $plugin, $plugins ) ) !== false ) {
				    unset( $plugins[$key] );
				}
			}
		}
		// Add stage plugins.
		if (   is_array( $stage_plugins )
			&& !empty( $stage_plugins )
		) {
			foreach( $stage_plugins as $plugin ) {
				if ( !in_array( $plugin, $plugins ) ) {
					$plugins[] = $plugin;
				}
			}
		}
		// Remove all possible non-existent plugins.
		$plugins = $this->clean( $plugins );
		if ( $network ) {
			$reformatted_plugins = array();
			foreach ( $plugins as $key => $value ) {
				$reformatted_plugins[$value] = rand( 1000000000, 9999999999 );
			}
			$plugins = $reformatted_plugins;
		}
		return $plugins;
	}

	/**
	 * Add a plugin to the list of a given stage.
	 *
	 * @param string $value The basename of the plugin that must be added to the list.
	 */
	public function add( $plugin, $stage = false, $network = false ) {
		$stage = $stage ? $stage : $this->current_stage;
		// Decide if we are adding to network or not.
		$plugins = $network ? $this->managed_network_plugins : $this->managed_plugins;
		// If the given plugin is not on the list of plugins, then we add it.
		if(    !isset( $plugins[$stage] )
			|| !in_array( $plugin, $plugins[$stage] )
		) {
			$plugins[$stage][] = $plugin;
		}
		// Remove all possible non-existent plugins.
		if ( isset( $plugins[$stage] ) ) {
			$plugins[$stage] = $this->clean( $plugins[$stage] );
		}
		// The modified array is the new list of plugins.
		$this->update( $plugins, $network );
	}

	/**
	 * Updates the list of managed plugins.
	 *
	 * @param array $value The updated value for the list.
	 */
	public function update( $value, $network = false ) {
		// Decide if we are updating network or not.
		$option = $network ? $this->wp_network_option : $this->wp_option;
		if ( $network ) {
			update_site_option( $option, $value );
			$this->managed_network_plugins = $this->get_managed_network_plugins();
		} else {
			update_option( $option, $value );
			$this->managed_plugins = $this->get_managed_plugins();
		}
	}

	/**
	 * Removes a plugin from the list for a given stage.
	 *
	 * @param string $value The basename of the plugin that must be removed from the list.
	 */
	public function delete( $value, $stage = false, $network = false ) {
		// Decide if we are deleting from network or not.
		$stage = $stage ? $stage : $this->current_stage;
		$plugins = $network ? $this->managed_network_plugins : $this->managed_plugins;
		// If the given plugin is on the list, then remove it.
		if (   isset( $plugins[$stage] )
			&& ( $key = array_search( $value, $plugins[$stage] ) ) !== false
		) {
		    unset( $plugins[$stage][$key] );
		}
		// Remove all possible non-existent plugins.
		if ( isset( $plugins[$stage] ) ) {
			$plugins[$stage] = $this->clean( $plugins[$stage] );
		}
		// The modified array is the new list of plugins.
		$this->update( $plugins, $network );
	}

	/**
	 * Clean a given list of plugins, checking for non-existent files.
	 *
	 * @param  array $plugins An array containing relative paths to plugins from the plugin directory.
	 * @return array          A clean list of plugins, without non-existent files.
	 */
	public function clean( $plugins ) {
		$clean_plugins = array();
		foreach( $plugins as $plugin ) {
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
					$clean_plugins[] = $plugin;
			}
		}
		return $clean_plugins;
	}

	/**
	 * Check if this plugin is a must-use one.
	 *
	 * @return boolean Wether this plugin is a must-use one or not.
	 */
	public function is_mu_plugin() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$mu_plugins = get_mu_plugins();
		$is_mu = !empty( $mu_plugins[basename( __FILE__ )] );
		return $is_mu;
	}

}
