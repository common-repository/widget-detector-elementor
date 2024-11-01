<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       elementinvader.com
 * @since      1.0.0
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/admin
 * @author     ElementInvader <support@elementinvader.com>
 */
class Widget_Detector_Elementor_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Widget_Detector_Elementor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Widget_Detector_Elementor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/widget-detector-elementor-admin.css', array(), $this->version, 'all' );

        wp_register_style( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/jquery-confirm/jquery-confirm.min.css' );

		wp_enqueue_style( 'jquery-confirm' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Widget_Detector_Elementor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Widget_Detector_Elementor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/widget-detector-elementor-admin.js', array( 'jquery' ), $this->version, false );

        wp_register_script( 'jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/jquery-confirm/jquery-confirm.min.js' );

		wp_enqueue_script( 'jquery-confirm' );

	}



	/**
	 * Admin AJAX
	 */

	public function elementdetector_action()
	{
		global $Winter_MVC;

		$page = '';
		$function = '';

		if(isset($_GET['page']))$page = wmvc_xss_clean($_GET['page']);
		if(isset($_GET['function']))$function = wmvc_xss_clean($_GET['function']);

		if(isset($_POST['page']))$page = wmvc_xss_clean($_POST['page']);
		if(isset($_POST['function']))$function = wmvc_xss_clean($_POST['function']);

		$Winter_MVC = new MVC_Loader(plugin_dir_path( __FILE__ ).'../');
		$Winter_MVC->load_helper('basic');
		$Winter_MVC->load_controller($page, $function, array());
	}

    /**
	 * Admin Page Display
	 */
	public function admin_page_display() {
		global $Winter_MVC, $submenu, $menu;

		$page = '';
        $function = '';

		if(isset($_GET['page']))$page = wmvc_xss_clean($_GET['page']);
		if(isset($_GET['function']))$function = wmvc_xss_clean($_GET['function']);

		$Winter_MVC = new MVC_Loader(plugin_dir_path( __FILE__ ).'../');
		$Winter_MVC->load_helper('basic');
        $Winter_MVC->load_controller($page, $function, array());
	}

    /**
     * To add Plugin Menu and Settings page
     */
    public function plugin_menu() {

        if(!function_exists('elementor_fail_php_version'))
        {
            //add_action( 'admin_notices', 'wde_elementor_local_fail_load' ); // cancel warning
            return;
        }

        ob_start();

        add_menu_page(__('Widget Detector','w-d-e'), __('Widget Detector','w-d-e'), 
            'manage_options', 'wde', array($this, 'admin_page_display'),
            //plugin_dir_url( __FILE__ ) . 'resources/logo.png',
            'dashicons-table-row-before',
            30 );
        
        add_submenu_page('wde', 
            __('Installed EL widgets','w-d-e'), 
            __('Installed EL widgets','w-d-e'),
            'manage_options', 'wde', array($this, 'admin_page_display'));

        add_submenu_page('wde', 
                        __('EL Widgets used','w-d-e'), 
                        __('EL Widgets used','w-d-e'),
                        'manage_options', 'wde_used_widgets', array($this, 'admin_page_display'));
                        
        add_submenu_page('wde', 
                        __('EL Widgets not in use','w-d-e'), 
                        __('EL Widgets not in use','w-d-e'),
                        'manage_options', 'wde_not_in_use', array($this, 'admin_page_display'));
        
        add_submenu_page('wde', 
                        __('EL Plugins usage','w-d-e'), 
                        __('EL Plugins usage','w-d-e'),
                        'manage_options', 'wde_plugins_not_in_use', array($this, 'admin_page_display'));

        add_submenu_page('wde', 
                        __('EL Widgets used but deactivated','w-d-e'), 
                        __('EL Widgets used but deactivated','w-d-e'),
                        'manage_options', 'wde_used_missing', array($this, 'admin_page_display'));

        add_submenu_page('wde', 
                        __('Images used inside Elementor','w-d-e'), 
                        __('Images used inside Elementor','w-d-e'),
                        'manage_options', 'wde_used_images', array($this, 'admin_page_display'));

        add_submenu_page('wde', 
                        __('Templates used inside Elementor','w-d-e'), 
                        __('Templates used inside Elementor','w-d-e'),
                        'manage_options', 'wde_used_templates', array($this, 'admin_page_display'));

        /*
        add_submenu_page('wde', 
                        __('Support','w-d-e'), 
                        __('Support','w-d-e'),
                        'manage_options', 'https://wordpress.org/support/plugin/widget-detector-elementor/');
        */
        
        add_submenu_page('wde', 
                        __('Settings','w-d-e'), 
                        __('Settings','w-d-e'),
                        'manage_options', 'wde_settings', array($this, 'admin_page_display'));

        /*
        add_submenu_page('wde', 
                        __('Reports','w-d-e'), 
                        __('Reports','w-d-e'),
                        'manage_options', 'actt_reports', array($this, 'admin_page_display'));
        */

        /*
        add_submenu_page('wde', 
                        __('Contact Us','w-d-e'), 
                        __('Contact Us','w-d-e'),
                        'manage_options', 'actt_contact_us', array($this, 'admin_page_display'));
        */

        

    }

}
