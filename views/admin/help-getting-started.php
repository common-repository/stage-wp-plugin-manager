<?php
/**
 * help-getting-started.php
 *
 * Help tab for instructions on how to use this plugin.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
$instance = $this->instance;
?>
<p>
	<strong><?php _e( 'Stage WP Plugin Manager works on some assumptions about your workflow:', $instance->text_domain ); ?></strong>
</p>

<ol>
	<li><?php _e( 'You have a constant named <code>WP_STAGE</code> defined in your WordPress configuration file (often <code>wp-config.php</code>).', $instance->text_domain ); ?></li>
	<li><?php echo sprintf( __( 'The <code>WP_STAGE</code> value is one of the supported stages. The currently supported stages are: %s', $instance->text_domain ), $this->html_listed_stages() ); ?></li>
	<li><?php _e( 'The value of <code>WP_STAGE</code> is different in each of your stages.', $instance->text_domain ); ?></li>
</ol>
<p>
	<?php _e( ' Some developers prefer to keep different configuration files for each one of their stages, or change the values of their constants based on some evaluation. For example, you could have something like this in your <code>wp-config.php</code> file:', $instance->text_domain ); ?>
</p>
<pre>
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	define( 'WP_STAGE', 'local' );
} elseif ( file_exists( dirname( __FILE__ ) . '/staging-config.php' ) ) {
	define( 'WP_STAGE', 'staging' );
} else {
	define( 'WP_STAGE', 'production' );
}
</pre>
<p><?php _e( 'If you follow this example, note that <code>local-config.php</code> should not be included in your deployments to staging and production, and both <code>local-config.php</code> and <code>staging-config.php</code> should not exist in your production stage.', $instance->text_domain ); ?></p>
