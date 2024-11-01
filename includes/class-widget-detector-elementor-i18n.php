<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       elementinvader.com
 * @since      1.0.0
 *
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Widget_Detector_Elementor
 * @subpackage Widget_Detector_Elementor/includes
 * @author     ElementInvader <support@elementinvader.com>
 */
class Widget_Detector_Elementor_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'w-d-e',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
