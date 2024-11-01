<?php 
/**
 * admin-warning.php
 *
 * Display an error message in case the stage is not set.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
?>
<div class="error">
	<p>
		<strong><?php echo __( 'Warning', 'stage_wp_plugin_manager' ); ?>:</strong> <?php echo sprintf( __( 'Stage WP Plugin Manager is active, but you have not defined the <code>WP_STAGE</code> constant. Please do it so and assign to it one of the following values in order for Stage WP Plugin Manager to work: %s.', 'stage_wp_plugin_manager', $this->instance->text_domain ), $this->html_listed_stages() ); ?>
	</p>
</div>
