<?php
/**
 * Cloudfront_Cache_Clear_Aws
 *
 * This class is responsible for handling all AWS related functionality.
 *
 * @link       https://affinity-digital.com
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 */

/**
 * Cloudfront_Cache_Clear_Aws
 *
 * This class is responsible for handling all AWS related functionality.
 *
 * @since      1.0.0
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/includes
 * @author     John Thompson <john.thompson@affinity-digital.com>
 */

use Aws\Exception\AwsException;
use Aws\CloudFront\CloudFrontClient;
use Aws\Credentials\CredentialProvider;

/**
 * Cloudfront_Cache_Clear_Aws
 */
class Cloudfront_Cache_Clear_Aws {


	/**
	 * The AWS CloudFront distribution ID.
	 *
	 * @var int $distribution_id
	 */
	private $distribution_id;

	/**
	 * The AWS region.
	 *
	 * @var string $aws_region
	 */
	private $aws_region;

	/**
	 * Is this an AWS environment?
	 *
	 * @var bool $is_aws_environment
	 */
	private $is_aws_environment;

	/**
	 * AWS Credential provider.
	 *
	 * @var string $provider
	 */
	private $provider;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->is_aws_environment = $this->is_aws_environment();
		$this->aws_region         = $this->get_aws_region();
		$this->distribution_id    = $this->get_distribution_id();
		$this->provider           = CredentialProvider::defaultProvider();
	}

	/**
	 * Test if the environment is AWS.
	 *
	 * @return int
	 */
	private function is_aws_environment() {
		$url      = 'http://169.254.169.254/latest/meta-data/';
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 1, // set a short timeout.
			)
		);

		// If there's no error and the response code is 200, it's likely an EC2 instance.
		return ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200;
	}

	/**
	 *  Get the AWS region.
	 *
	 * Check the database options table for the CloudFront region.
	 * If that fails, attempt to get the region from the wp-config file.
	 * If that fails, attempt to get the region automatically.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	private function get_aws_region() {

		// Check if the region has been set in the options.
		$region = get_option( 'CloudFrontRegion' );
		if ( $region ) {
			return $region;
		}

		// Attempt to get the region from the wp-config.php file.
		if ( defined( 'CFCC_AWS_REGION' ) ) {
			return CFCC_AWS_REGION;
		}

		// Not much point in continuing if we're not in an AWS environment.
		if ( ! $this->is_aws_environment ) {
			return false;
		}

		// Attempt to get the region from the instance metadata.
		$url      = 'http://169.254.169.254/latest/meta-data/placement/availability-zone';
		$args     = array(
			'timeout'     => 1,
			'redirection' => 0,
		);
		$response = wp_remote_get( $url, $args );
		if ( 200 === wp_remote_retrieve_response_code( $response ) && ! is_wp_error( $response ) ) {
			$availability_zone = wp_remote_retrieve_body( $response );
			$region            = substr( $availability_zone, 0, -1 );  // Remove the last character.
			if ( ! is_array( $region ) ) {
				return $region;
			}
		}
		return false;
	}

	/**
	 * Get the CloudFront distribution ID.
	 *
	 * Attempt to get the CloudFront distribution ID from the database options table.
	 * If that fails, attempt to get the distribution ID from the wp-config file.
	 * If that fails, attempt to get the distribution ID from the secrets manager.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	private function get_distribution_id() {

		// Check if the distribution ID has been set in the options.
		$distribution_id = get_option( 'CloudFrontDistrubutionID' );
		if ( $distribution_id ) {
			return $distribution_id;
		}

		// Attempt to get the distribution ID from the wp-config.php file.
		if ( defined( 'CFCC_DISTRIBUTION_ID' ) ) {
			return CFCC_DISTRIBUTION_ID;
		}

		return false;
	}

	/**
	 * Clear the CloudFront cache.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function clear_cloudfront_cache() {

		$response = array();
		// Check we have the required information.
		if ( ! $this->is_aws_environment || ! $this->distribution_id || ! $this->aws_region ) {
			return;
		}

		// AWS SDK setup.
		$cloud_front = new CloudFrontClient(
			array(
				'version'     => 'latest',
				'region'      => $this->aws_region,
				'credentials' => $this->provider,
			)
		);

		try {

			// Create an invalidation.
			$response['response'] = $cloud_front->createInvalidation(
				array(
					'DistributionId'    => $this->distribution_id,
					'InvalidationBatch' => array(
						'Paths'           => array(
							'Quantity' => 1,
							'Items'    => array( '/*' ),
						),
						'CallerReference' => 'media-delete-' . time(),
					),
				)
			);
		} catch ( AwsException $e ) {
			$response['error'] = $e->getMessage();
		}

		update_option( 'cfcc_in_progress', false );
		return $response;
	}

	/**
	 * Return the CloudFront distribution ID.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function return_cloudfront_distribution_id() {
		return $this->distribution_id;
	}

	/**
	 * Return the AWS region.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function return_aws_region() {
		return $this->aws_region;
	}

	/**
	 * Return the AWS environment status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function return_is_aws_environment() {
		return $this->is_aws_environment;
	}
}
