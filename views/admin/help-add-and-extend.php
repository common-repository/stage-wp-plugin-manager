<?php
/**
 * help-add-and-extend.php
 *
 * Help tab for adding and extending functionality.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
$instance = $this->instance;
?>
<p>
	<?php _e( 'Stage WP Plugin Manager allows you to extend its functionality by offering hooks for filters and actions.', $instance->text_domain ); ?>
</p>
<p>
	<?php _e( 'For example, you can add your custom stages or even remove the default ones by hooking in the <code>stage_wp_plugin_manager_stages</code> filter, having something like this in your plugin:', $instance->text_domain ); ?>
</p>
<pre>
function add_stages( $stages ) {
	$stages[] = 'other';
	return $stages;
}
add_filter( 'stage_wp_plugin_manager_stages', 'add_stages' );
</pre>
<p>
	<?php _e( 'Additionally, you can give a nicer name to your new stage by hooking in the <code>stage_wp_plugin_manager_stages_txt</code> filter:', $instance->text_domain ); ?>
</p>
<pre>
function add_stages_txt( $stages_txt ) {
	$stages_txt['other'] = __( 'Other stage' );
	return $stages_txt;
}
add_filter( 'stage_wp_plugin_manager_stages_txt', 'add_stages_txt' );
</pre>
<p>
	<?php _e( 'Here\'s the complete list of actions and filters that Stage WP Plugin Manager offers:', $instance->text_domain ); ?>
</p>
<h5><?php _e( 'Action hooks', $instance->text_domain ); ?></h5>
<ul>
	<li><code>stage_wp_plugin_manager_before_init</code>: <?php _e( 'Perform some process before Stage WP Plugin Manager initializes.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_init</code>: <?php _e( 'Perform some process after Stage WP Plugin Manager initializes.', $instance->text_domain ); ?></li>
</ul>
<h5><?php _e( 'Filter hooks', $instance->text_domain ); ?></h5>
<ul>
	<li><code>stage_wp_plugin_manager_stages></code>: <?php _e( 'Modifiy the current supported stages.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_default_stage</code>: <?php _e( 'Modify the default stage.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_managed_plugins</code>: <?php _e( 'Modify the list of plugins managed by Stage WP Plugin Manager.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_stage_plugins</code>: <?php _e( 'Modify the list of plugins attached to the current stage.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_non_stage_plugins</code>: <?php _e( 'Modify the list of managed plugins that are not attached to the current stage.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_nonce_key</code>: <?php _e( 'Modify the nonce key used for data validation.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_help_description</code>: <?php _e( 'Modify contents of "Description" help tab.', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_help_getting_started</code>: <?php _e( 'Modify contents of "Getting Started" help tab', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_help_attach_detach</code>: <?php _e( 'Modify contents of "Attach & Detach Plugins" help tab', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_help_add_extend</code>: <?php _e( 'Modify contents of "Adding Stages & Extending" help tab', $instance->text_domain ); ?></li>
	<li><code>stage_wp_plugin_manager_help_credits</code>: <?php _e( 'Modify contents of "Credits" help tab', $instance->text_domain ); ?></li>
</ul>