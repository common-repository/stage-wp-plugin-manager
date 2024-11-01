<?php
/**
 * help-credits.php
 *
 * Help tab for credits.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
$instance = $this->instance;
?>
<p>
	<?php echo sprintf( __( 'This plugin is written and mantained by <a href="%1$s">Andr&eacute;s Villarreal</a>. It is meant as a complement for <a href="%2$s">WP Bareboner</a>, an advanced Git model repo, and <a href="%3$s">Stage WP</a>, a deployment tool based in Capistrano. The three projects work really well separated, but their real power can only be seen by using them together.', $instance->text_domain ), 'http://about.me/andrezrv', 'http://andrezrv.github.io/wordpress-bareboner', 'http://andrezrv.github.io/stage-wp/' ); ?>
</p>