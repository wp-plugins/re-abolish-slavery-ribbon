<?php

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ )
	die( "Access denied." );

if ( ! class_exists( 'ReAbolishSlaveryRibbon' ) ) {
	/**
	 * Adds a "re-abolish slavery" ribbon to the upper right-hand corner of your site, which links to the Not For Sale campaign
	 * Requires PHP5+ because of various OOP features, pass by reference, etc
	 * Requires Wordpress 2.7 because of add_settings_field()
	 *
	 * @package ReAbolishSlaveryRibbon
	 * @author  Ian Dunn <ian@iandunn.name>
	 * @link    http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/
	 */
	class ReAbolishSlaveryRibbon {
		// Declare variables and constants
		protected $displayRibbon, $newWindow, $ribbonPosition, $bottomForMobile;

		const VERSION       = '1.0.2';
		const PREFIX        = 'rasr_';
		const SETTINGS_PAGE = 'rasr_settings';

		/**
		 * Constructor
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

			// Miscellaneous
			register_activation_hook( dirname( __FILE__ ) . '/re-abolish-slavery-ribbon.php', array( $this, 'networkActivate' ) );
		}

		/*
		 * Assign variables and other initilization
		 */
		public function init() {
			$this->displayRibbon   = null;
			$this->newWindow       = get_option( self::PREFIX . 'new-window', '' );
			$this->ribbonPosition  = get_option( self::PREFIX . 'ribbon-position', 'top-right' );
			$this->bottomForMobile = get_option( self::PREFIX . 'bottom-for-mobile', 'on' );
			$this->imageLocation   = apply_filters( 'rasr_image_location', plugins_url( 're-abolish-slavery-ribbon/images/ribbon-'. $this->ribbonPosition .'.png' ) );
			$this->imageLinkURL    = apply_filters( 'rasr_image_link_url', 'http://www.notforsalecampaign.org/about/slavery/' );
		}

		/**
		 * Handles extra activation tasks for MultiSite installations
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function networkActivate() {
			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// Activate the plugin across the network if requested
				if ( array_key_exists( 'networkwide', $_GET ) && ( $_GET['networkwide'] == 1 ) ) {
					$blogs = $wpdb->get_col( "SELECT blog_id FROM " . $wpdb->blogs );

					foreach ( $blogs as $b ) {
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
		protected function singleActivate() {
			// Save default settings
			if ( ! get_option( self::PREFIX . 'new-window' ) )
				add_option( self::PREFIX . 'new-window', '' );
			if ( ! get_option( self::PREFIX . 'ribbon-position' ) )
				add_option( self::PREFIX . 'ribbon-position', 'top-right' );
			if ( ! get_option( self::PREFIX . 'bottom-for-mobile' ) )
				add_option( self::PREFIX . 'bottom-for-mobile', 'on' );
		}

		/**
		 * Runs activation code on a new WPMS site when it's created
		 * @author Ian Dunn <ian@iandunn.name>
		 *
		 * @param int $blogID
		 */
		public function activateNewSite( $blogID ) {
			switch_to_blog( $blogID );
			$this->singleActivate();
			restore_current_blog();
		}

		/**
		 * Adds a page to Settings menu
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettingsPage() {
			add_options_page( RASR_NAME . ' Settings', RASR_NAME, 'manage_options', self::SETTINGS_PAGE, array( $this, 'markupSettingsPage' ) );
		}

		/**
		 * Creates the markup for the settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function markupSettingsPage() {
			if ( current_user_can( 'manage_options' ) )
				require_once( dirname( __FILE__ ) . '/views/settings.php' );
			else
				wp_die( 'Access denied.' );
		}

		/**
		 * Adds a 'Settings' link to the Plugins page
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
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettings() {
			add_settings_section( self::SETTINGS_PAGE, '', array( $this, 'settingsSectionCallback' ), self::SETTINGS_PAGE );

			add_settings_field( self::PREFIX . 'ribbon-position', 'Ribbon Position', array( $this, 'ribbonPositionCallback' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'ribbon-position' ) );
			add_settings_field( self::PREFIX . 'new-window', 'Open Link in New Window', array( $this, 'newWindowCallback' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'new-window' ) );
			add_settings_field( self::PREFIX . 'bottom-for-mobile', 'Move to Bottom on Small Screens', array( $this, 'bottomForMobileCallback' ), self::SETTINGS_PAGE, self::SETTINGS_PAGE, array( 'label_for' => self::PREFIX . 'bottom-for-mobile' ) );

			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'ribbon-position' );
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'new-window' );
			register_setting( self::SETTINGS_PAGE, self::PREFIX . 'bottom-for-mobile' );
		}

		/**
		 * Adds the section introduction text to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function settingsSectionCallback() {
			// intentionally blank
		}

		/**
		 * Adds the bottom-for-mobile field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function ribbonPositionCallback() {
			?>

			<input id="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-right" name="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position" type="radio" value="top-right" <?php echo checked( $this->ribbonPosition, 'top-right', false ); ?> />
			<label for="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-right"><span class="description">Top Right Corner.</span></label>
			<br />

			<input id="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-left" name="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position" type="radio" value="top-left" <?php echo checked( $this->ribbonPosition, 'top-left', false ); ?> />
			<label for="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-left"><span class="description">Top Left Corner.</span></label>

		<?php
		}

		/**
		 * Adds the new-window field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function newWindowCallback() {
			?>

			<input id="<?php echo esc_attr( self::PREFIX ); ?>new-window" name="<?php echo esc_attr( self::PREFIX ); ?>new-window" type="checkbox" <?php checked( $this->newWindow, 'on', false ); ?> />
			<label for="<?php echo esc_attr( self::PREFIX ); ?>new-window">
				<span class="description">
					If checked, the link to the NFS website open in a new window.<br />
					<strong>Note:</strong> Forcing links to open in a new window is <a href="http://uxdesign.smashingmagazine.com/2008/07/01/should-links-open-in-new-windows/">considered a bad practice</a>. Please consider leaving this off.
				</span>
			</label>

			<?php
		}

		/**
		 * Adds the bottom-for-mobile field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function bottomForMobileCallback() {
			?>

			<input id="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile" name="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile" type="checkbox" <?php echo checked( $this->bottomForMobile, 'on', false ); ?> />
			<label for="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile">
				<span class="description">If checked, the ribbon will appear at the bottom of the page when viewed on a smartphone so that it doesn't overlap the header. Note that this won't work in Internet Explorer versions 8 and below, because they don't support modern web standards.
				</span>
			</label>

			<?php
		}

		/**
		 * Load CSS file
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function loadResources() {
			wp_register_script(
				self::PREFIX . 'functions',
				plugins_url( 'functions.js', __FILE__ ),
				array( 'jquery' ),
				self::VERSION,
				true
			);

			wp_register_style(
				self::PREFIX . 'style',
				plugins_url( 'style.css', __FILE__ ),
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
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function setDisplayRibbon() {
			if ( isset( $this->displayRibbon ) )
				return;

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
			if ( $this->displayRibbon )
				require_once( dirname( __FILE__ ) . '/views/ribbon-markup.php' );
		}
	} // end ReAbolishSlaveryRibbon
}

?>