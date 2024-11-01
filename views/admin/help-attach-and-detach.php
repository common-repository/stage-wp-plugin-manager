<?php
/**
 * help-attach-and-detach.php
 *
 * Help tab for attaching and detaching plugins.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
$instance = $this->instance;
?>
<p>
	<?php _e( 'Once you have installed this plugin, you will notice that a new link appears under each active plugin of the list, which reads "Attach to [your-stage] stage". By clicking that link, you are setting a plugin to be always active in the stage you\'re working at, and not active on the other stages (unless you attach the plugin to the other stages too).', $instance->text_domain ); ?>
</p>
<p>
	<?php _e( 'In case you want to remove a plugin from the list of active plugins for a stage, you just need to click the "Detach from [your-stage] stage".', $instance->text_domain ); ?>
</p>
<p>
	<?php _e( 'Additionally, you can make bulk selections of plugins to be attached or detached for each stage in this same page.', $instance->text_domain ); ?>
</p>
