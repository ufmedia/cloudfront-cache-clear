<?php

require plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

use Aws\CloudFront\CloudFrontClient;
use Aws\Ssm\SsmClient;

class Cloudfront_Cache_Clear_Cloudfront {

    private $distribution_id;
    private $aws_region;
    private $is_aws_environment;

    public function __construct() {
        $this->is_aws_environment = $this->is_aws_environment();
        $this->distribution_id =  $this->get_distribution_id();
        $this->aws_region = $this->get_aws_region();
    }

    private function is_aws_environment() {
        $url = 'http://169.254.169.254/latest/meta-data/';
        $response = wp_remote_get($url, [
            'timeout' => 1, // set a short timeout
        ]);
    
        // If there's no error and the response code is 200, it's likely an EC2 instance.
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    private function get_aws_region() {

        $region = get_option('CloudFrontRegion');
        if ($region) {
            return $region;
        }

        if (!$this->is_aws_environment) {
            return false;
        }

        $url = 'http://169.254.169.254/latest/meta-data/placement/availability-zone';
        $args = array(
            'timeout'     => 1,
            'redirection' => 0,
        );
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return false;
        }

        $availability_zone = wp_remote_retrieve_body($response);
        $region = substr($availability_zone, 0, -1);  // Remove the last character

        return $region;
    }

    private function get_distribution_id() {

        $distribution_id = get_option('CloudFrontDistrubutionID');
        if ($distribution_id) {
            return $distribution_id;
        }

        if (!$this->is_aws_environment) {
            return false;
        }

        $ssmClient = new SsmClient([
            'version' => 'latest',
            'region'  => $this->get_aws_region(),
        ]);

        $result = $ssmClient->getParameter([
            'Name' => 'CloudFrontDistrubutionID',
        ]);

        if (isset($result['Parameter']['Value'])) {
            return $result['Parameter']['Value'];
        } else {
            return false;
        }
    }

    public function clear_cloudfront_cache_on_media_delete($post_id) {
        // Check we have the required information
        if (!$this->is_aws_environment && !$this->distribution_id && !$this->aws_region) {
            return;
        }
        // Check if the post is an attachment (media item)
        if (get_post_type($post_id) !== 'attachment') {
            return;
        }

        // AWS SDK setup
        $cloudFront = new CloudFrontClient([
            'version'     => 'latest',
            'region'      => $this->aws_region,
        ]);

        // Create an invalidation
        $cloudFront->createInvalidation([
            'Distribution_id' => $this->distribution_id,
            'InvalidationBatch' => [
                'Paths' => [
                    'Quantity' => 1,
                    'Items' => ['/*']
                ],
                'CallerReference' => 'media-delete-' . time()
            ]
        ]);
    }
    
    /**
     * return_cloudfront_distribution_id
     *
     * @return void
     */
    public function return_cloudfront_distribution_id() {
        return $this->distribution_id;
    }
    
    /**
     * return_aws_region
     *
     * @return void
     */
    public function return_aws_region() {
        return $this->aws_region;
    }
    
    /**
     * return_is_aws_environment
     *
     * @return void
     */
    public function return_is_aws_environment() {
        return $this->is_aws_environment;
    }
    

}
