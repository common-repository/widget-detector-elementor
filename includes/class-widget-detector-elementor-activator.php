<?php

/**
 * Fired during plugin activation
 *
 * @link       elementinvader.com
 * @since      1.0.0
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/includes
 * @author     ElementInvader <support@elementinvader.com>
 */
class Widget_Detector_Elementor_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        $prefix = 'wde_';

        // Default options
        add_option($prefix.'editor_hover', '0');

	}

}
