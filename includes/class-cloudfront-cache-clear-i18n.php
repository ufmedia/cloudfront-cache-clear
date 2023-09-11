<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://ufmedia.co.uk
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 * @author     John Thompson <john@ufmedia.co.uk>
 */
class Cloudfront_Cache_Clear_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cloudfront-cache-clear',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
