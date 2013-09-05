<?php
/*
Plugin Name: Re-Abolish Slavery Ribbon
Plugin URI: http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
Description: Adds a "re-abolish slavery" ribbon to the upper right-hand corner of your site, which links to the Not For Sale campaign
Version: 1.0.2
Author: Ian Dunn
Author URI: http://iandunn.name
License: GPL2
*/

// Disclaimer: This plugin was created independently and isn't officially affiliated with the Not For Sale campaign. Using it on your site doesn't imply endorsement of the site by NFS.

/*  
 * Copyright 2011-2013 Ian Dunn (email : ian@iandunn.name)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( 'Access denied.' );

define( 'RASR_NAME', 'Re-Abolish Slavery Ribbon' );
define( 'RASR_REQUIRED_PHP_VERSON', '5' );
define( 'RASR_REQUIRED_WP_VERSION', '2.7' );

/**
 * Checks if the system requirements are met
 * @author Ian Dunn <ian@iandunn.name>
 * @return bool True if system requirements are met, false if not
 */
function RASR_requirementsMet() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, RASR_REQUIRED_PHP_VERSON, '<' ) )
		return false;

	if ( version_compare( $wp_version, RASR_REQUIRED_WP_VERSION, '<' ) )
		return false;

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 * @author Ian Dunn <ian@iandunn.name>
 */
function RASR_requirementsNotMet() {
	global $wp_version;

	echo sprintf( '
		<div id="message" class="error">
			<p>
				%s <strong>requires PHP %s</strong> and <strong>WordPress %s</strong> in order to work. You\'re running PHP %s and WordPress %s. You\'ll need to upgrade in order to use this plugin. If you\'re not sure how to <a href="http://codex.wordpress.org/Switching_to_PHP5">upgrade to PHP 5</a> you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.
			</p>
		</div>',
		RASR_NAME,
		RASR_REQUIRED_PHP_VERSON,
		RASR_REQUIRED_WP_VERSION,
		PHP_VERSION,
		$wp_version
	);
}

// Check requirements and instantiate
if ( RASR_requirementsMet() ) {
	require_once( dirname( __FILE__ ) . '/core.php' );

	if ( class_exists( 'ReAbolishSlaveryRibbon' ) )
		$rasr = new ReAbolishSlaveryRibbon();
}
else
	add_action( 'admin_notices', 'RASR_requirementsNotMet' );

?>