<?php
/**
 * admin-menu.php
 *
 * Display a menu for instructions and bulk activations.
 *
 * @package Stage_WP_Plugin_Manager
 * @since   1.0
 */
// Get a shortener for the plugin instance.
$instance = $this->instance;
// Get flag for network admin.
$network = is_network_admin();
// Obtain wp_option name to be used.
$option = $network ? $instance->wp_network_option : $instance->wp_option;
// Obtained list of managed plugins.
$managed_plugins = $network ? $instance->managed_network_plugins : $instance->managed_plugins;
// Obtain names for activated status.
$activated_status = $network ? __( 'Network Activated', $instance->text_domain ) : __( 'Activated', $instance->text_domain );
// Obtain names for deactivated status.
$deactivated_status = $network ? __( 'Network Deactivated', $instance->text_domain ) : __( 'Deactivated', $instance->text_domain );
?>
<div class="wrap">

	<h2><?php _e( 'Stage WP Plugin Manager' ); ?></h2>

	<p class="description"><?php _e( 'Here you can manage different plugin configurations for every one of the stages your site is being developed in or deployed to. The plugins you select for each stage will be automatically activated when that stage is marked as the working one. These are the configurations for the currently managed stages.' ); ?></p>

	<div class="metabox-holder">

		<form action="plugins.php?page=stage-wp-plugin-manager" method="post">

			<?php if ( $instance->isset_stage() ) : wp_nonce_field( $this->nonce_key ); endif; ?>

			<?php foreach( $instance->stages as $stage ) : ?>

				<div class="postbox">

					<h3 class="hndle"><span><?php echo $instance->get_stage_txt( $stage ); ?></span></h3>

					<div class="inside">

						<table>

							<tr valign="top">

								<td width="50%">

									<?php if ( $stage == $instance->current_stage ) : ?>

										<h4><?php _e( 'This is your current working stage.', $instance->text_domain ); ?></h4>
										<p class="description"><?php _e( 'If you switch to another stage and want to go back to this one later, you must have the following code in your <code>wp-config.php</code> file (or whatever your configuration file is):', $instance->text_domain ); ?></p>

									<?php else : ?>

										<h4><?php _e( 'This is a currently non-working stage.', $instance->text_domain ); ?></h4>
										<p class="description"><?php _e( 'If you want to switch to this stage, you must have the following code in your <code>wp-config.php</code> file (or whatever your configuration file is):', $instance->text_domain ); ?></p>

									<?php endif; ?>

									<pre>define( 'WP_STAGE', '<?php echo $stage; ?>' );</pre>

								</td>

								<?php if ( $instance->isset_stage() ) : ?>

									<td>

										<h4><?php echo sprintf( __( 'Active plugins for <em>%s</em> stage', $instance->text_domain ), $instance->get_stage_txt( $stage ) ); ?></h4>

										<select multiple="multiple" id="<?php echo $option; ?>[<?php echo $stage; ?>]" name="<?php echo $option; ?>[<?php echo $stage; ?>][]" size="7">

											<?php foreach( $this->get_all_plugins() as $wp_plugin_key => $wp_plugin_data ) : ?>

												<?php $original_status = $this->instance->is_really_active( $wp_plugin_key ) ? $activated_status : $deactivated_status; ?>

												<?php $selected = ( isset( $managed_plugins[$stage] ) && is_array( $managed_plugins[$stage] ) && in_array( $wp_plugin_key, $managed_plugins[$stage] ) ) ? ' selected="selected"' : ''; ?>

												<option value="<?php echo $wp_plugin_key; ?>"<?php echo $selected; ?>><?php echo $wp_plugin_data['Name'] ?> &mdash; <?php echo sprintf( __( 'Original state: %s', $instance->text_domain ), $original_status ); ?></option>

											<?php endforeach; ?>

										</select>

										<p class="description"><?php echo sprintf( __( 'The plugins you select here will be automatically activated in your <em>%s</em> stage.', $instance->text_domain ), $instance->get_stage_txt( $stage ) ); ?></p>

									</td>

								<?php endif; ?>
							</tr>

						</table>

					</div>

				</div>

			<?php endforeach; ?>

			<?php if ( $instance->isset_stage() ) : ?>

				<p class="submit">
					<input id="submit" class="button button-primary" type="submit" value="<?php _e( 'Save Changes' ); ?>" name="submit">
				</p>

			<?php endif; ?>

		</form>

	</div>

</div>
