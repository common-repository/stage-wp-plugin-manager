<?php
/**
 * Stage_WP_Plugin_Manager_Loader
 *
 * Load Stage_WP_Plugin_Manager.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
class Stage_WP_Plugin_Manager_Loader {

	private $instance;
	private $nonce_key;

	public function __construct() {
		// Instance Stage_WP_Plugin_Manager.
		$this->instance = Stage_WP_Plugin_Manager::get_instance();
		// Init plugin.
		$this->init();
	}

	public function __get( $property ) {
		if ( property_exists( $this, $property ) ) {
			return $this->$property;
		}
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		// Set nonce key for data validation.
		$this->set_nonce_key();
		// Get plugin basename.
		$plugin = plugin_basename( dirname( __FILE__ ) . '/stage-wp-plugin-manager.php' );
		// Check if a stage was set.
		if ( $this->instance->isset_stage() ) {
			// Filter active plugins.
			add_filter( 'option_active_plugins', array( $this, 'process_reset' ) );
			// Filter active plugins for network.
			add_filter( 'site_option_active_sitewide_plugins', array( $this, 'process_reset' ) );
			// Add links to Plugins menu.
			add_action( 'admin_init', array( $this, 'add_links' ) );
			// Add Javascript to process AJAX requests.
			add_action( 'admin_footer', array( $this, 'add_script' ) );
			// Process AJAX requests.
			add_action( 'wp_ajax_stage_wp_plugin_manager_process_ajax', array( $this, 'process_ajax' ) );
			// Add footer message.
			add_filter ('admin_footer_text', array( $this, 'add_footer_message' ), 999 );
			// Add a link to settings page in Plugins page.
			add_filter( 'plugin_action_links_' . $plugin, array( $this, 'add_settings_link' ) );
		}
		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'add_admin_menu' ) );
		// Add warning in admin area.
		add_action( 'admin_notices', array( $this, 'add_admin_warning' ) );
		add_action( 'network_admin_notices', array( $this, 'add_admin_warning' ) );
		// Load text domain.
		add_action( 'init', array( $this, 'load_text_domain' ), 99 );
	}

	/**
	 * Set nonce key for data validation.
	 */
	public function set_nonce_key() {
		$nonce_key = 'stage-wp-plugin-manager-update-managed-plugins';
		$nonce_key = apply_filters( 'stage_wp_plugin_manager_nonce_key', $nonce_key );
		$this->nonce_key = $nonce_key;
	}

	/**
	 * Load text domain.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( $this->instance->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Sets up configuration for current stage.
	 *
	 * @param  array $plugins The current list of active plugins.
	 * @return array          The new list of active plugins.
	 */
	public function process_reset( $plugins ) {
		$network = 'site_option_active_sitewide_plugins' == current_filter() ? true : false;
		$this->process_request( $this->instance->current_stage, $network );
		$plugins = $this->instance->do_reset( $plugins, $network );
		return $plugins;
	}

	/**
	 * Process the request for a plugin (or an array of plugins) in order to be
	 * added or removed from the list of managed plugins.
	 */
	private function process_request( $stage = false, $network = false ) {
		// Process bulk request.
		require_once( ABSPATH . '/wp-includes/pluggable.php' );
		$instance = $this->instance;
		$option = $network ? $instance->wp_network_option : $instance->wp_option;
		if (   ( isset( $_REQUEST[$option] ) && $this->request_is_valid() )
			|| ( isset( $_REQUEST['page'] ) && 'stage-wp-plugin-manager' == $_REQUEST['page'] && isset( $_POST[$option . '_submit'] ) )
		) {
			$request = isset( $_REQUEST[$option] ) ? $_REQUEST[$option] : array();
			$instance->update( $request, $network );
		} else { // Process single request.
			$stage = $stage ? $stage : $instance->current_stage;
			if (   !empty( $_REQUEST[$instance->add_key] )
				&& $this->request_is_valid()
			) { // Process addings.
				$instance->add( $_REQUEST[$instance->add_key], $stage, $network );
			} elseif (   !empty( $_REQUEST[$instance->delete_key] )
				      && $this->request_is_valid()
			) { // Process removings.
				$instance->delete( $_REQUEST[$instance->delete_key], $stage, $network );
			}
		}
	}

	/**
	 * Check if a request is valid.
	 */
	private function request_is_valid() {
		return check_admin_referer( $this->nonce_key );
	}

	/**
	 * Callback for modifying plugins via AJAX.
	 */
	public function process_ajax() {
		$instance = $this->instance;
		// We need to "mock" the request a little bit here.
		$query = parse_url( $_POST['href'] );
		$query_elements = explode( '&', urldecode( $query['query'] ) );
		$params = array();
		foreach ( $query_elements as $element ) {
			$temp_element = explode( '=', $element );
			$params[$temp_element[0]] = $temp_element[1];
		}
		$_REQUEST = $params;
		// Process requested plugin as needed.
		$network_requested = (   isset( $_REQUEST['network'] )
			                  && 'true' === $_REQUEST['network'] );
		$network = $network_requested ? true : false;
		$this->process_request( false, $network );
		// Get key that contains the plugin name.
		if ( !empty( $_REQUEST[$instance->add_key] ) ) {
			$key = $_REQUEST[$instance->add_key];
		} elseif ( !empty( $_REQUEST[$instance->delete_key] ) ) {
			$key = $_REQUEST[$instance->delete_key];
		}
		// Print new link for the processed plugin.
		if ( $network ) {
			echo $this->process_network_link( $key );
		} else {
			echo $this->process_link( $key );
		}
		die(); // Required to return a proper result.
	}

	/**
	 * Adds an action link to all active plugins, except for this one (this
	 * plugin needs to be always active in any environment).
	 */
	public function add_links() {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		$plugins = $this->get_all_plugins();
		// Iterate list of plugins.
		foreach ( $plugins as $plugin => $data ) {
			if ( dirname( __FILE__ ) . '/stage-wp-plugin-manager.php' != $plugin ) {
				// Add filter for individual sites.
				$filter = 'plugin_action_links_' . ( $plugin );
				add_filter( $filter, array( $this, 'add_action_link' ), 10, 2 );
				// Add filter for network admin.
				$network_filter = 'network_admin_plugin_action_links_' . ( $plugin );
				add_filter( $network_filter, array( $this, 'add_action_link' ), 10, 2 );
			}
		}
	}

	/**
	 * Adds a link to the array of action links for the given plugin.
	 *
	 * @param  array  $links  The list of links.
	 * @param  string $plugin The basename of a plugin.
	 * @return array          An array containing links.
	 */
	public function add_action_link( $links, $plugin ) {
		if ( is_network_admin() ) {
			$links[] = $this->process_network_link( $plugin );
		} else {
			$links[] = $this->process_link( $plugin );
		}
		return $links;
	}

	/**
	 * Obtains values for the current stage and calls $this->make_link() to return
	 * an option link for the given plugin.
	 *
	 * @param string $plugin The basename of a plugin.
	 */
	private function process_link( $plugin ) {
		$link = '';
		$instance = $this->instance;
		if (    isset( $instance->managed_plugins[$instance->current_stage] )
			 && in_array( $plugin, $instance->managed_plugins[$instance->current_stage] )
		) { // Process link for detaching plugin from stage.
			$active = $instance->is_really_active( plugin_basename( $plugin ) );
			$extra = $active ? __( '(activated)', $instance->text_domain ) : __( '(deactivated)', $instance->text_domain );
			$detach_text = __( 'Detach from %s stage', $this->text_domain );
			$message = sprintf( $detach_text, $instance->current_stage ) . ' ' . $extra;
			$key =  $instance->delete_key;
		} else { // Process link to attach plugin to stage.
			$attach_text = __( 'Attach to %s stage', $this->text_domain );
			$message = sprintf( $attach_text, $instance->current_stage );
			$key =  $instance->add_key;
		}
		$link = $this->make_link( $plugin, $key, $message );
		return $link;
	}

	/**
	 * Obtains values for the current stage and calls $this->make_link() to return
	 * an option link for the given plugin.
	 *
	 * @param string $plugin The basename of a plugin.
	 */
	private function process_network_link( $plugin ) {
		$link = '';
		$instance = $this->instance;
		if (    isset( $instance->managed_network_plugins[$instance->current_stage] )
			 && in_array( $plugin, $instance->managed_network_plugins[$instance->current_stage] )
		) { // Process link to detach plugin from stage.
			$active = $instance->is_really_active( plugin_basename( $plugin ), true );
			$extra = $active ? __( '(network activated)', $instance->text_domain ) : __( '(network deactivated)', $instance->text_domain );
			$detach_text = __( 'Detach from %s stage', $instance->text_domain );
			$message = sprintf( $detach_text, $instance->current_stage ) . ' ' . $extra;
			$key =  $instance->delete_key;
		} else { // Process link to attach plugin to stage.
			$attach_text = __( 'Attach to %s stage', $instance->text_domain );
			$message = sprintf( $attach_text, $this->instance->current_stage );
			$key =  $instance->add_key;
		}
		$link = $this->make_link( $plugin, $key, $message, $network = true );
		return $link;
	}

	/**
	 * Processes HTML for the current option link of a plugin.
	 *
	 * @param  string $plugin  The path of a plugin.
	 * @param  string $key     The GET key to process the link's request with.
	 * @param  string $message The message to display to the user.
	 * @return string          HTML result.
	 */
	private function make_link( $plugin, $key, $message, $network = false ) {
		$instance = $this->instance;
		$plugin_basename = plugin_basename( $plugin );
		// Obtain relative path to network admin, if needed.
		$network_part = $network ? 'network/' : '';
		// Obtain original status of plugin.
		$active = $instance->is_really_active( $plugin, $network );
		// Set original status to string.
		$status = $active ? 'active' : 'inactive';
		// Construct path for admin URL.
		$path = $network_part . 'plugins.php?';
		$path .= $key . '=' . $plugin_basename;
		$path .= $network ? '&network=true' : '';
		$path .= '&status=' . $status;
		// Pass path to admin URL.
		$url = get_admin_url( 0, $path );
		// Append security check.
		$url = wp_nonce_url( $url, $this->nonce_key );
		// Construct link tag.
		$link = '<a id="' . sanitize_title( $plugin ) . '" class="stage-wp-plugin-manager-link" href="' . $url . '">' . $message . '</a>';
		// Add spinner.
		$link .= '<span class="spinner" style="float: none; margin: 5px 5px -5px;"></span>';
		return $link;
	}

	/**
	 * Add a submenu page to Plugins menu.
	 */
	public function add_admin_menu() {
		$instance = $this->instance;
		$plugins_page = add_plugins_page( __( 'Stage WP Plugin Manager', $instance->text_domain ), __( 'Stage Management', $instance->text_domain ), 'activate_plugins', 'stage-wp-plugin-manager', array( $this, 'get_admin_menu' ) );
		// Add help tabs when get_admin_menu loads
    	add_action( 'load-' . $plugins_page, array( $this, 'add_help_tabs' ) );
	}

	/**
	 * Add contextual help to plugin settings page.
	 */
	function add_help_tabs () {
		$instance = $this->instance;
	    $screen = get_current_screen();
	    $screen->add_help_tab( array(
			'id'      => 'stage_wp_plugin_manager_help_description',
			'title'   => __( 'Description', $instance->text_domain ),
			'content' => $this->load_view(
				$file   = 'admin/help-description.php',
				$filter = 'stage_wp_plugin_manager_help_description',
				$return = true
			),
	    ) );
	    $screen->add_help_tab( array(
	        'id'	=> 'stage_wp_plugin_manager_help_getting_started',
	        'title'	=> __( 'Getting Started', $instance->text_domain ),
	        'content' => $this->load_view(
				$file   = 'admin/help-getting-started.php',
				$filter = 'stage_wp_plugin_manager_help_getting_started',
				$return = true
			),
	    ) );
	    $screen->add_help_tab( array(
	        'id'	=> 'stage_wp_plugin_manager_help_attach_detach',
	        'title'	=> __( 'Attach & Detach Plugins', $instance->text_domain ),
	        'content' => $this->load_view(
				$file   = 'admin/help-attach-and-detach.php',
				$filter = 'stage_wp_plugin_manager_help_attach_detach',
				$return = true
			),
	    ) );
	    $screen->add_help_tab( array(
	        'id'	=> 'stage_wp_plugin_manager_help_add_extend',
	        'title'	=> __( 'Adding Stages & Extending', $instance->text_domain ),
	        'content' => $this->load_view(
				$file   = 'admin/help-add-and-extend.php',
				$filter = 'stage_wp_plugin_manager_help_add_extend',
				$return = true
			),
	    ) );
	    $screen->add_help_tab( array(
	        'id'	=> 'stage_wp_plugin_manager_help_credits',
	        'title'	=> __( 'Credits', $instance->text_domain ),
	        'content' => $this->load_view(
				$file   = 'admin/help-credits.php',
				$filter = 'stage_wp_plugin_manager_help_credits',
				$return = true
			),
	    ) );
	}

	/**
	 * Output for submenu page.
	 */
	public function get_admin_menu() {
		$this->load_view( 'admin/admin-menu.php', 'stage_wp_plugin_manager_admin_menu' );
	}

	public function get_all_plugins() {
		// Obtain installed plugins.
		$plugins = get_plugins();
		// Remove sitewide active plugins when viewing individual sites.
		if ( ! is_network_admin() ) {
			$sitewide_active_plugins = $this->instance->reformat(
				get_site_option( 'active_sitewide_plugins' )
			);
			foreach ( $sitewide_active_plugins as $sitewide_active_plugin ) {
				if ( isset( $plugins[$sitewide_active_plugin] ) ) {
					unset( $plugins[$sitewide_active_plugin] );
				}
			}
		}
		// Remove this plugin to avoid lockouts.
		unset( $plugins[plugin_basename( dirname( __FILE__ ) . '/stage-wp-plugin-manager.php' )] );
		return $plugins;
	}

	/**
	 * Display a warning message.
	 */
	public function add_admin_warning() {
		if ( !$this->instance->isset_stage() ) {
			$this->load_view( 'admin/admin-warning.php', 'stage_wp_plugin_manager_admin_warning' );
		}
	}

	/**
	 * Add a message to footer of plugin settings page.
	 *
	 * @param  string $content The current content.
	 * @return string          HTML output.
	 */
	public function add_footer_message( $content ) {
		$screen = get_current_screen();
		$instance = $this->instance;
		if ( 'plugins_page_stage-wp-plugin-manager' == $screen->base ) {
			$content = '<em>' . sprintf( __( '<a href="%1$s">Stage WP Plugin Manager</a> by <a href="%2$s">Andr&eacute;s Villarreal</a>. Try it with <a href="%3$s">WP Bareboner</a> and <a href="%4$s">Stage WP</a> for more awesomeness :)', $instance->text_domain ), 'http://wordpress.org/plugins/stage-wp-plugin-manager', 'http://about.me/andrezrv', 'http://andrezrv.github.io/wordpress-bareboner', 'http://andrezrv.github.io/stage-wp/' ) . '</em> &mdash; ' . $content;
		}
		return $content;
	}

	/**
	 * Add link to plugin settings in Plugins page.
	 *
	 * @param  array $links Original array of links for plugin in Plugins page.
	 * @return array        Filtered array of links for plugin in Plugins page.
	 */
	function add_settings_link( $links ) {
		$path = is_network_admin() ? 'network/' : '';
		$path .= 'plugins.php?page=stage-wp-plugin-manager';
		$url = get_admin_url( 0, $path );
	    $settings_link = '<a href="' . $url . '">' . __( 'Settings' ) . '</a>';
	  	array_push( $links, $settings_link );
	  	return $links;
	}

	/**
	 * List of stages wrapped in <code> tags.
	 *
	 * @return string HTML result.
	 */
	public function html_listed_stages() {
		$stages = '';
		foreach ( $this->instance->stages as $stage ) {
			$stages .= '<code>' . $stage . '</code>, ';
		}
		$stages = trim( $stages );
		$stages = rtrim( $stages, ',' );
		return $stages;
	}

	/**
	 * Javascript code for AJAX management of plugins.
	 */
	public function add_script() {
		global $current_screen;
		if (   'plugins' == $current_screen->id
			|| 'plugins-network' == $current_screen->id
		) {
			$this->load_view( 'admin/script.js.php', 'stage_wp_plugin_manager_javascript' );
		}
	}

	/**
	 * Load a view for this plugin.
	 *
	 * @param  string  $file   File into `./views/` directory containing the code for the view.
	 * @param  string  $filter Tag for filter to be applied to the view.
	 * @param  boolean $return Wether return the output instead of echo it or not.
	 * @return string          The HTML output, in case $return is set to true.
	 */
	public function load_view( $file, $filter = '', $return = false ) {
		$file = dirname( __FILE__ ) . '/views/' . $file;
		// If file exists, capture contents before echo or return.
		if ( file_exists( $file ) ) {
			ob_start();
			require( $file );
			$html = ob_get_contents();
			ob_end_clean();
			// Apply filters if needed.
			if ( $filter ) {
				$html = apply_filters( $filter, $html );
			}
			// Return if needed instead of echo.
			if ( $return ) {
				return $html;
			}
			echo $html;
		}
	}

}
