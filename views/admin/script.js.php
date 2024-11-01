<?php $instance = $this->instance; ?>
<?php $add_key = $instance->add_key; ?>
<?php $delete_key = $instance->delete_key; ?>
<?php $activate_text = is_network_admin() ? __( 'Network Activate', $instance->text_domain ) :  __( 'Activate', $instance->text_domain ); ?>
<?php $deactivate_text = is_network_admin() ? __( 'Network Deactivate', $instance->text_domain ) :  __( 'Deactivate', $instance->text_domain ); ?>

<script type="text/javascript" id="stage-wp-plugin-manager-process-ajax">
	
	function switch_status( parent, container, old_status, new_status, old_class, new_class, hide ) {
		var $ = jQuery;
		var new_text;
		if ( 'active' == old_status ) {
			new_text = '<?php echo $activate_text; ?>';
		} else {
			new_text = '<?php echo $deactivate_text; ?>';
		}
		// Reverse class of container.
		$( container ).removeClass( old_status ).addClass( new_status );
		// Reverse class of parent, modify text of contained link and obtained its URL.
		var url = $( parent ).prevAll( '.' + old_class ).removeClass( old_class ).addClass( new_class ).children( 'a' ).html( new_text ).attr( 'href' );
		// Modify href of URL.
		url = url.replace( 'action=' + old_class, 'action=' + new_class );
		// Apply new URL.
		$( container ).find( '.' + new_class + ' a' ).attr( 'href', url );
		// Show or hide "delete" link.
		if ( false === hide ) {
			$( parent ).prevAll( '.delete' ).show();
		} else {
			$( parent ).prevAll( '.delete' ).hide();
		}
	}

	jQuery( document ).ready( function( $ ) {
		$( '#wpbody' )
			.on( 'click', 'a.stage-wp-plugin-manager-link', function( event ) {			
				// Prevent click from redirecting to link's href.
				event.preventDefault();
				// Save clicked element.
				var link = this;
				// Save URL of clicked element.
				var href = $( link ).attr( 'href' );
				// Save parent of clicked element.
				var parent = $( link ).parent();
				// Save table row element containing plugin data.
				var container = $( parent ).parent().parent().parent();
				// Data to be sent through AJAX.
				var data = { action: 'stage_wp_plugin_manager_process_ajax', href: href }
				// Show spinner.
				$( parent ).find( '.spinner' ).css( 'display', 'inline-block' );
				$( parent ).parent().css( 'margin-top', '-5px' );
				// Make AJAX request.
				$.ajax( { url: ajaxurl, type: 'POST', data: data, async: true } )
					.done( function( html ) {
						// Remove spinner.
						$( parent ).find( '.spinner' ).remove();
						$( parent ).parent().css( 'margin-top', '0' );
						// Show new link.
						$( link ).replaceWith( html );
						// Switch plugin status.
						if ( $( container ).hasClass( 'inactive' ) ) {
							switch_status( parent, container, 'inactive', 'active', 'activate', 'deactivate', true );
						} else if (   $( container ).hasClass( 'active' ) 
							       && ( href.indexOf( '<?php echo $delete_key; ?>' ) != -1 )
							       && ( href.indexOf( 'status=inactive' ) != -1  ) 
						) {
							switch_status( parent, container, 'active', 'inactive', 'deactivate', 'activate', false );
						}
					} 
				);
			}
		);
	} );

</script>
