<?php

if( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die("Access denied.");

if( !class_exists('ReAbolishSlaveryRibbon') )
{
	/**
	 * Adds a "re-abolish slavery" ribbon to the upper right-hand corner of your site, which links to the Not For Sale campaign
	 * Requires PHP5+ because of various OOP features, pass by reference, etc
	 * Requires Wordpress 2.1 in order to use the wp action
	 *
	 * @package ReAbolishSlaveryRibbon
	 * @author Ian Dunn <ian@iandunn.name>
	 * @link http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
	 */
	class ReAbolishSlaveryRibbon
	{
		// Declare variables and constants
		protected $settings, $options, $updatedOptions, $userMessageCount, $displayRibbon, $newWindow, $bottomForMobile;
		const VERSION		= '0.1';
		const PREFIX		= 'rasr_';
		const SETTINGS_PAGE	= 'general';
		
		/**
		 * Constructor
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function __construct()
		{
			$this->displayRibbon	= null;
			$this->newWindow		= get_option( self::PREFIX . 'new-window' );
			$this->bottomForMobile	= get_option( self::PREFIX . 'bottom-for-mobile' );
			
			add_action( 'wp',				array( $this, 'setDisplayRibbon' ) );
			add_action( 'wp', 				array( $this, 'loadResources'	), 11 );
			add_action( 'admin_init', 		array( $this, 'addSettings'		) );
			add_action( 'wp_head',			array( $this, 'addHeaderNote'	) );
			add_action( 'wp_footer',		array( $this, 'printRibbon'		) );
			add_action( 'wpmu_new_blog', 	array( $this, 'activateNewSite'	) );
			
			add_filter( 'plugin_action_links_re-abolish-slavery-ribbon/re-abolish-slavery-ribbon.php', array( $this, 'addSettingsLink' ) );
			register_activation_hook( dirname(__FILE__) . '/re-abolish-slavery-ribbon.php', array( $this, 'networkActivate') );
		}
		
		/**
		 * Handles extra activation tasks for MultiSite installations
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function networkActivate()
		{
			global $wpdb;
			
			if( function_exists('is_multisite') && is_multisite() )
			{
				// Activate the plugin across the network if requested
				if( array_key_exists( 'networkwide', $_GET ) && ( $_GET['networkwide'] == 1) )
				{
					$blogs = $wpdb->get_col( "SELECT blog_id FROM ". $wpdb->blogs );
					
					foreach( $blogs as $b ) 
					{
						switch_to_blog( $b );
						$this->singleActivate();
					}
					
					restore_current_blog();
				}
				else
					$this->singleActivate();
			}
			else
				$this->singleActivate();
		}
		
		/**
		 * Prepares a single blog to use the plugin
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		protected function singleActivate()
		{
			// Save default settings
			if( !get_option( self::PREFIX . 'new-window' ) )
				add_option( self::PREFIX . 'new-window', '' );
			if( !get_option( self::PREFIX . 'bottom-for-mobile' ) )
				add_option( self::PREFIX . 'bottom-for-mobile', 'on' );
		}
		
		/**
		 * Runs activation code on a new WPMS site when it's created
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param int $blogID
		 */
		public function activateNewSite( $blogID )
		{
			switch_to_blog( $blogID );
			$this->singleActivate();
			restore_current_blog();
		}
		
		
		/**
		 * Adds a 'Settings' link to the Plugins page
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param array $links The links currently mapped to the plugin
		 * @return array
		 */
		public function addSettingsLink( $links )
		{
			array_unshift( $links, '<a href="options-'. self::SETTINGS_PAGE .'.php">Settings</a>' );
			return $links; 
		}
		
		/**
		 * Adds our custom settings to the admin Settings pages
		 * We intentionally don't register the map-latitude and map-longitude settings because they're set by updateMapCoordinates()
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettings()
		{
			add_settings_section( self::PREFIX . 'settings', RASR_NAME, array( $this, 'settingsSectionCallback' ), self::SETTINGS_PAGE );
			add_settings_field( self::PREFIX . 'new-window', 'Open Link in New Window', array( $this, 'newWindowCallback' ), self::SETTINGS_PAGE, self::PREFIX . 'settings', array( 'label_for' => self::PREFIX . 'new-window' ) );
			add_settings_field( self::PREFIX . 'bottom-for-mobile', 'Move to Bottom on Small Screens', array( $this, 'bottomForMobileCallback' ), self::SETTINGS_PAGE, self::PREFIX . 'settings', array( 'label_for' => self::PREFIX . 'bottom-for-mobile' ) );
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'new-window');
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'bottom-for-mobile');
		}
		
		/**
		 * Adds the section introduction text to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function settingsSectionCallback()
		{
			// intentionally blank
		}
		
		/**
		 * Adds the new-window field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function newWindowCallback()
		{
			echo '<input id="'. self::PREFIX .'new-window" name="'. self::PREFIX .'new-window" type="checkbox" '. checked( $this->newWindow, 'on', false ) .' />';
			echo '<label for="'. self::PREFIX .'new-window"><span class="description">If checked, the link to the NFS website open in a new window. <strong>Warning:</strong> Forcing links to open in a new window is <a href="http://uxdesign.smashingmagazine.com/2008/07/01/should-links-open-in-new-windows/">considered a bad practice</a>. Please consider leaving this off.</span></label>';
		}
		
		/**
		 * Adds the bottom-for-mobile field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function bottomForMobileCallback()
		{
			echo '<input id="'. self::PREFIX .'bottom-for-mobile" name="'. self::PREFIX .'bottom-for-mobile" type="checkbox" '. checked( $this->bottomForMobile, 'on', false ) .' />';
			echo '<label for="'. self::PREFIX .'bottom-for-mobile"><span class="description">If checked, the ribbon will appear at the bottom of the page when viewed on a smartphone so that it doesn\'t overlap the header. Note that this won\'t work in Internet Expolorer versions 8 and below, because they don\'t support modern web standards.</span></label>';
		}
		
		/**
		 * Load CSS file
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function loadResources()
		{
			wp_register_style(
				self::PREFIX .'style',
				plugins_url( 'style.css', __FILE__ ),
				false,
				self::VERSION,
				false
			);
			
			if( $this->displayRibbon )
				wp_enqueue_style( self::PREFIX . 'style' );
		}
		
		/**
		 * Determines if the ribbon should be displayed on the current page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function setDisplayRibbon()
		{
			if( isset( $this->displayRibbon ) )
				return;
				
			if( !is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) )
				$this->displayRibbon = true;
			else
				$this->displayRibbon = false;
		}
		
		/**
		 * Adds a note to the <head> tag telling people how they can add the plugin to their own site
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addHeaderNote()
		{
			echo '
				<!-- 
				Re-Abolish Slavery Ribbon
				The ribbon you see on this page is a WordPress plugin. If you\'d like it install it on your own
				WordPress site you can download it from http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
				-->
			';
		}
		
		/**
		 * Outputs the ribbon
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function printRibbon()
		{
			if( $this->displayRibbon )
			{
				echo sprintf('
					<a href="http://www.notforsalecampaign.org/about/slavery/" %s>
						<img id="re-abolish-slavery-ribbon" src="%s" %s alt="re-abolish slavery" />
					</a>',
					( $this->newWindow ? 'target="_blank"' : '' ),
					plugins_url( 're-abolish-slavery-ribbon.png', __FILE__ ),
					( $this->bottomForMobile ? 'class="bottom"' : '' )
				);
			}
		}
	} // end ReAbolishSlaveryRibbon
}

?>