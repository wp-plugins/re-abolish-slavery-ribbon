/**
 * @package ReAbolishSlaveryRibbon
 * @author Ian Dunn <ian@iandunn.name>
 * @link http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
 */

// Main jQuery function
jQuery( document ).ready( function()
{
	// Show the link to download the plugin when the user hovers over the ribbon
	jQuery( '#rasr_container' ).hover(
		function() {
			jQuery( '#rasr_add-icon' ).fadeIn();
		},
		
		function() {
			jQuery( '#rasr_add-icon' ).fadeOut();
		}
	);
} );