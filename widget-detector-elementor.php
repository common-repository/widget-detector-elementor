<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              elementinvader.com
 * @since             1.0.0
 * @package           Widget_Detector_Elementor
 *
 * @wordpress-plugin
 * Plugin Name:       Widget Detector for Elementor
 * Plugin URI:        https://elementdetector.com
 * Description:       Detect Which Elementor widgets Used on Pages, also not Used Widgets or Missing Widgets.
 * Version:           1.2.9
 * Author:            ElementInvader & FreelancersTools (Ivica Delić)
 * Author URI:        https://elementinvader.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       w-d-e
 * Domain Path:       /languages
 * 
 * Elementor tested up to: 3.24.7
 * Elementor Pro tested up to: 3.26.7
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function wde_elementor_local_fail_load() {
        
        $message = sprintf(
                /* translators: 1: Plugin name 2: Elementor */
                esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'w-d-e' ),
                '<strong>' . esc_html__( 'Widget Detector for Elementor', 'w-d-e' ) . '</strong>',
                '<strong>' . esc_html__( 'Elementor', 'w-d-e' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WIDGET_DETECTOR_ELEMENTOR_VERSION', '1.2.7' );
define( 'WIDGET_DETECTOR_ELEMENTOR_NAME', 'wde' );
define( 'WIDGET_DETECTOR_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WIDGET_DETECTOR_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) );
define( 'ELEMENTDETECTOR_SYNC_URL', 'https://elementdetector.com/sync_plugins.php' );

define( 'WDE_HIDDER_OPTION_KEY', 'wde_hidder_elements' );
define( 'WDE_UNREGISTER_OPTION_KEY', 'wde_unregister_elements' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-widget-detector-elementor-activator.php
 */
function activate_widget_detector_elementor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-detector-elementor-activator.php';
	Widget_Detector_Elementor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-widget-detector-elementor-deactivator.php
 */
function deactivate_widget_detector_elementor() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-detector-elementor-deactivator.php';
	Widget_Detector_Elementor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_widget_detector_elementor' );
register_deactivation_hook( __FILE__, 'deactivate_widget_detector_elementor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-widget-detector-elementor.php';

if ( ! function_exists( 'wde_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wde_fs() {
        global $wde_fs;

        if ( ! isset( $wde_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wde_fs = fs_dynamic_init( array(
                'id'                  => '9861',
                'slug'                => 'widget-detector-elementor',
                'type'                => 'plugin',
                'public_key'          => 'pk_ee67a703cbd398e2944806544c1da',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'wde',
                    'account'        => true,
                    'support'        => false,
                ),
                'anonymous_mode' => true,
            ) );
        }

        return $wde_fs;
    }

    // Init Freemius.
    wde_fs();
    // Signal that SDK was initiated.
    do_action( 'wde_fs_loaded' );
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_widget_detector_elementor() {

	$plugin = new Widget_Detector_Elementor();
	$plugin->run();

}
run_widget_detector_elementor();
