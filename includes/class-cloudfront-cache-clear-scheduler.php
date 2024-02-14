<?php
/**
 * Cloudfront_Cache_Clear_Scheduler
 *
 * This class is responsible for scheduling cache clear.
 *
 * @link       https://ufmedia.co.uk
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 */

/**
 * Cloudfront_Cache_Clear_Scheduler
 *
 * This class is responsible for scheduling cache clear.
 *
 * @since      1.0.0
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 * @author     John Thompson <john@ufmedia.co.uk>
 */
class Cloudfront_Cache_Clear_Scheduler {

	/**
	 * Schdule a cache clear.
	 *
	 * @since   1.0.0
	 * @return void
	 */
	public function schedule_cache_clear(): void {

		if ( false === get_option( 'cfcc_in_progress' ) ) {
			as_schedule_single_action( time() + 120, 'queue_cache_clear', array() );
			update_option( 'cfcc_in_progress', true );
		}
	}
}
