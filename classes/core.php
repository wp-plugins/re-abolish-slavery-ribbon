<?php

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( "Access denied." );

if ( ! class_exists( 'ReAbolishSlaveryRibbon' ) ) {
	/**
	 * Adds a "re-abolish slavery" ribbon to the upper right-hand corner of your site, which links to the Not For Sale campaign
	 *
	 * @package ReAbolishSlaveryRibbon
	 * @author  Ian Dunn <ian@iandunn.name>
	 * @link    http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
	 */
	class ReAbolishSlaveryRibbon {
		// Declare variables and constants
		protected $displayRibbon, $newWindow, $ribbonPosition, $bottomForMobile, $imageLocation, $imageLinkURL;

		const VERSION       = '1.0.3';
		const PREFIX        = 'rasr_';
		const SETTINGS_PAGE = 'rasr_settings';

		/**
		 * Constructor
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function __construct() {
			// Actions
			add_action( 'init',          array( $this, 'init' ) );
			add_action( 'wp',            array( $this, 'setDisplayRibbon' ) );
			add_action( 'wp',            array( $this, 'loadResources' ) );
			add_action( 'admin_menu',    array( $this, 'addSettingsPage' ) );
			add_action( 'admin_init',    array( $this, 'addSettings' ) );
			add_action( 'wp_footer',     array( $this, 'printRibbon' ) );
			add_action( 'wpmu_new_blog', array( $this, 'activateNewSite' ) );

			// Filters
			add_filter( 'plugin_action_links_re-abolish-slavery-ribbon/re-abolish-slavery-ribbon.php', array( $this, 'addSettingsLink' ) );
		}

		/*
		 * Assign variables and other initilization
		 */
		public function init() {
			$this->displayRibbon   = null;
			$this->newWindow       = get_option( self::PREFIX . 'new-window', '' );
			$this->ribbonPosition  = get_option( self::PREFIX . 'ribbon-position', 'top-right' );
			$this->bottomForMobile = get_option( self::PREFIX . 'bottom-for-mobile', 'on' );
			$this->imageLocation   = apply_filters( 'rasr_image_location', plugins_url( 're-abolish-slavery-ribbon/images/ribbon-' . $this->ribbonPosition . '.png' ) );
			$this->imageLinkURL    = apply_filters( 'rasr_image_link_url', 'http://www.notforsalecampaign.org/about/slavery/' );
		}

		/**
		 * Adds a page to Settings menu
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettingsPage() {
			add_options_page( RASR_NAME . ' Settings', RASR_NAME, 'manage_options', self::SETTINGS_PAGE, array( $this, 'markupSettingsPage' ) );
		}

		/**
		 * Creates the markup for the settings page
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function markupSettingsPage() {
			if ( current_user_can( 'manage_options' ) ) {
				require_once( dirname( dirname( __FILE__ ) ) . '/views/settings.php' );
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Adds a 'Settings' link to the Plugins page
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 *
		 * @param array $links The links currently mapped to the plugin
		 *
		 * @return array
		 */
		public function addSettingsLink( $links ) {
			array_unshift( $links, '<a href="http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/faq/">Help</a>' );
			array_unshift( $links, '<a href="options-general.php?page=' . self::SETTINGS_PAGE . '.php">Settings</a>' );

			return $links;
		}

		/**
		 * Adds our custom settings to the admin Settings pages
		 * We intentionally don't register the map-latitude and map-longitude settings because they're set by updateMapCoordinates()
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettings() {
			add_settings_section( self::SETTINGS_PAGE, '', '__return_empty_string', self::SETTINGS_PAGE );

			add_settings_field( self::PREFIX . 'ribbon-position', 'Ribbon Position', array( $this, 'markupSettingFields' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'ribbon-position' ) );
			add_settings_field( self::PREFIX . 'new-window', 'Open Link in New Window', array( $this, 'markupSettingFields' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'new-window' ) );
			add_settings_field( self::PREFIX . 'bottom-for-mobile', 'Move to Bottom on Small Screens', array( $this, 'markupSettingFields' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'bottom-for-mobile' ) );

			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'ribbon-position' );
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'new-window' );
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'bottom-for-mobile' );
		}

		/**
		 * Adds the bottom-for-mobile field to the Settings page
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function markupSettingFields( $field ) {
			require( dirname( dirname( __FILE__ ) ) . '/views/setting-fields.php' );
		}

		/**
		 * Load CSS file
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function loadResources() {
			wp_register_script(
				self::PREFIX . 'functions',
				plugins_url( 'javascript/functions.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				self::VERSION,
				true
			);

			wp_register_style(
				self::PREFIX . 'style',
				plugins_url( 'css/style.css', dirname( __FILE__ ) ),
				false,
				self::VERSION
			);

			if ( $this->displayRibbon ) {
				wp_enqueue_script( self::PREFIX . 'functions' );
				wp_enqueue_style( self::PREFIX . 'style' );
			}
		}

		/**
		 * Determines if the ribbon should be displayed on the current page
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function setDisplayRibbon() {
			if ( isset( $this->displayRibbon ) ) {
				return;
			}

			if ( ! is_admin() && ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) )
				$this->displayRibbon = true;
			else
				$this->displayRibbon = false;
		}

		/**
		 * Outputs the ribbon
		 * Note: The icon is from http://icons.mysitemyway.com/orange-white-pearls-icons-media/
		 *
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function printRibbon() {
			if ( $this->displayRibbon ) {
				require_once( dirname( dirname( __FILE__ ) ) . '/views/ribbon-markup.php' );
			}
		}
	} // end ReAbolishSlaveryRibbon
}

