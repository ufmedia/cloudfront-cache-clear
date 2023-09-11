<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ufmedia.co.uk
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/admin
 * @author     John Thompson <john@ufmedia.co.uk>
 */
class Cloudfront_Cache_Clear_Admin
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function register_cloudfront_options_page()
	{
		add_options_page(
			'CloudFront Cache',           // Page title
			'CloudFront Cache',           // Menu title
			'manage_options',             // Capability required
			'cloudfront-cache',           // Menu slug
			array($this, 'cloudfront_options_page')     // Function to display the content
		);
	}


	public function cloudfront_options_page()
	{

		$cloud_front = new Cloudfront_Cache_Clear_Cloudfront();

?>
		<div class="wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="postbox-container-1" class="postbox-container" style="width:50%;">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="metabox" class="postbox">
							<div class="inside">
								<div class="main">
									<h1>CloudFront Cache</h1>
									<p>This WordPress plugin automatically invalidates a CloudFront cache whenever a media item is deleted, ensuring that your CloudFront-served assets are always up-to-date.</p>
									<p>The plugin will attempt to automatically detect your CloudFront distribution ID, to ensure this happens please:</p>
									<ol>
										<li> Store your CloudFront distribution ID in the AWS Systems Manager Parameter Store under the key "CloudFrontDistrubutionID".</li>
										<li>Ensure your environment's IAM role has permissions to read from the AWS Systems Manager Parameter Store and to invalidate CloudFront caches.</li>
									</ol>
									<p>If you would rather enter your CloudFront distribution ID manually, you can do so below.</p>
									<p>The plugin will attempt to detect your AWS region from the environment, otherwise you can enter this below.</p>
									<p><strong>When populated, the below fields will override any values pulled from the environment.</strong></p>

									<form method="post" action="options.php">
										<?php
										settings_fields('cloudfront_options_group');
										do_settings_sections('cloudfront_options_page');
										submit_button();
										?>
									</form>

									<br>
									<h2>Active Settings</h2>
									<p>The following settings are currently active and will be used whenever clearing the cache:</p>
									<ul>
										<li><strong>CloudFront Distribution ID:</strong> <?php echo $cloud_front->return_cloudfront_distribution_id() ? $cloud_front->return_cloudfront_distribution_id() : 'N/A' ?></li>
										<li><strong>CloudFront Region:</strong> <?php echo $cloud_front->return_aws_region() ? $cloud_front->return_aws_region() : 'N/A' ?></li>
										<li><strong>Is AWS Environment:</strong> <?php echo $cloud_front->return_is_aws_environment() ? 'True' : 'False' ?></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php
	}

	public function cloudfront_settings_init()
	{
		register_setting(
			'cloudfront_options_group',      // Group name
			'CloudFrontDistrubutionID'       // Option name
		);

		register_setting(
			'cloudfront_options_group',      // Group name
			'CloudFrontRegion'       // Option name
		);

		add_settings_section(
			'cloudfront_settings_section',   // Section ID
			'Settings',           // Title
			'',                              // Callback function (empty in this case)
			'cloudfront_options_page'        // Page slug
		);

		add_settings_field(
			'cloudfront_distribution_id',         // Field ID
			'CloudFront Distribution ID',         // Label
			array($this, 'cloudfront_distribution_id_cb'),      // Callback function
			'cloudfront_options_page',            // Page slug
			'cloudfront_settings_section'         // Section ID
		);

		add_settings_field(
			'cloudfront_region',         // Field ID
			'CloudFront Region',         // Label
			array($this, 'cloudfront_region_cb'),      // Callback function
			'cloudfront_options_page',            // Page slug
			'cloudfront_settings_section'         // Section ID
		);
	}


	public function cloudfront_distribution_id_cb()
	{
		$cloudfront_id = get_option('CloudFrontDistrubutionID', '');   // Default value is an empty string
		echo "<input type='text' name='CloudFrontDistrubutionID' value='{$cloudfront_id}' />";
	}

	public function cloudfront_region_cb()
	{
		$cloudfront_region = get_option('CloudFrontRegion', '');   // Default value is an empty string
		echo "<input type='text' name='CloudFrontRegion' value='{$cloudfront_region}' />";
	}
}
