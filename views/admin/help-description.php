<?php
/**
 * help-description.php
 *
 * Help tab for plugin description.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
$instance = $this->instance;
?>
<p>
	<?php _e( 'If you develop in a local machine, at some point you\'ll have some active plugins there that you don\'t need in the servers that you\'re deploying to. Sure, you can deactivate them in your local machine before deploying, or after deploying in the remote ones, but you\'re gonna need them again to be active if you want to perform local work in the future, specially when you update your local database from a remote one. On such kind of scenarios, the whole process of manually activating and deactivating plugins for each stage can be really frustrating, sometimes even taking a lot of your time.', $instance->text_domain ); ?>
</p>
<p>
	<?php _e( 'Stage WP Plugin Manager is meant to solve that problem in an quick and elegant way, by doing an automatic "fake"  activation of the plugins you select for each stage: every plugin you attach to a stage will be immediatly treated as an active plugin on that stage, no matter what its previous status was, or its current status on the other stages. Since the list of active plugins is just filtered instead of rewritten, you can restore the previous status of a plugin by detaching it, and go back to your original setup by deactivating Stage WP Plugin Manager.', $instance->text_domain ); ?>
</p>
<p>
	<?php _e( 'Of course, there\'s always a catch: Stage WP Plugin Manager doesn\'t work right out of the box in most installations, so besides from installing this plugin, you may need to do a little additional work.', $instance->text_domain ); ?>
</p>