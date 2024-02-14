<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://affinity-digital.com
 * @since      1.0.0
 *
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @since 1.0.0
 * @package    Cloudfront_Cache_Clear
 * @subpackage Cloudfront_Cache_Clear/admin
 * @author     John Thompson <john@affinity-digital.com>
 */
class Cloudfront_Cache_Clear_Admin {

	/**
	 * Register the options page.
	 *
	 * @return void
	 */
	public function register_cloudfront_options_page(): void {
		add_options_page(
			'CloudFront Cache',           // Page title.
			'CloudFront Cache',           // Menu title.
			'manage_options',             // Capability required.
			'cloudfront-cache',           // Menu slug.
			array( $this, 'cloudfront_options_page' ) // Function to display the content.
		);
	}


	/**
	 * Display the options page.
	 *
	 * @return void
	 */
	public function cloudfront_options_page(): void {
		$cloud_front = new Cloudfront_Cache_Clear_Aws();
		?>
		
		<div class="wrap">
		<h1>CloudFront Cache Clear</h1>
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="postbox-container-1" class="postbox-container" style="width:50%;">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="metabox" class="postbox">
							<div class="inside">
								<div class="main">
								
									<p><strong>This WordPress plugin automatically invalidates a CloudFront cache whenever one of the following conditions are met:</strong></p>
									<ul>
										<li> - An attachment is deleted - Scheduled.</li>
										<li> - An attachment is updated - Scheduled.</li>
										<li> - Attachment metadata is updated - Scheduled.</li>
										<li> - W3 Total Cache is cleared - Instant.</li>
										<li> - WP Super Cache is cleared - Instant.</li>
									</ul>
									<p><strong>In the case of sheduled invalidations, the plugin will wait 2 minutes before invalidating the cache. This is to prevent multiple invalidations when bulk updating attachments.</strong></p>
									
									<h2>Prerequisites</h2>
									<p>Ensure your IAM user has the following permissions:</p>
									<ul>
										<li><strong>cloudfront:CreateInvalidation</strong></li>
									</ul>

									<h2>Configuration</h2>
									<p>You can enter your CloudFront Distribution ID and Region below or you can define these in your wp-config.php file like so:</p>
									<code>
									define( 'CFCC_DISTRIBUTION_ID', 'XXXXXXXXXXXXXX' );<br>
									define( 'CFCC_REGION', 'eu-west-1' );
									</code>
									<p>Settings entered here will override the wp-config.php settings.</p>
									<p>The plugin will attempt to retrieve your AWS region automatically if it's not set below or the wp-config.php file.</p>
									<form method="post" action="options.php">
										<?php
										settings_fields( 'cloudfront_options_group' );
										do_settings_sections( 'cloudfront_options_page' );
										submit_button();
										?>
									</form>
									<br>
									<h2>Active Settings</h2>
									<p>The following settings are currently active and will be used whenever clearing the cache:</p>
									<ul>
										<li><strong>CloudFront Distribution ID:</strong> <?php echo $cloud_front->return_cloudfront_distribution_id() ? esc_html( $cloud_front->return_cloudfront_distribution_id() ) : 'N/A'; ?></li>
										<li><strong>CloudFront Region:</strong> <?php echo $cloud_front->return_aws_region() ? esc_html( $cloud_front->return_aws_region() ) : 'N/A'; ?></li>
										<li><strong>Is AWS Environment:</strong> <?php echo $cloud_front->return_is_aws_environment() ? 'True' : 'False'; ?></li>
									</ul>
									<h2>Manually Clear Cache</h2>
									<p>You can manually create an invalidation by clicking the button below. This will clear the entire cache for the distribution ID entered above.</p>
									<br>
									<form action="" method="post">
									<?php
									wp_nonce_field( 'cf_clear_cache_action', 'cf_clear_cache_nonce' );
									?>
									<input type="submit" name="cf_clear_cache" value="Clear CloudFront Cache" class="button button-secondary"/>
								</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Initialise the settings.
	 *
	 * @return void
	 */
	public function cloudfront_settings_init(): void {
		register_setting(
			'cloudfront_options_group',
			'CloudFrontDistrubutionID'
		);

		register_setting(
			'cloudfront_options_group',
			'CloudFrontRegion'
		);

		add_settings_section(
			'cloudfront_settings_section',
			'',
			'',
			'cloudfront_options_page'
		);

		add_settings_field(
			'cloudfront_distribution_id',
			'CloudFront Distribution ID',
			array( $this, 'cloudfront_distribution_id_cb' ),
			'cloudfront_options_page',
			'cloudfront_settings_section'
		);

		add_settings_field(
			'cloudfront_region',
			'CloudFront Region',
			array( $this, 'cloudfront_region_cb' ),
			'cloudfront_options_page',
			'cloudfront_settings_section'
		);
	}


	/**
	 * Display the CloudFront Distribution ID field.
	 *
	 * @return void
	 */
	public function cloudfront_distribution_id_cb(): void {
		$cloudfront_id = get_option( 'CloudFrontDistrubutionID', '' );   // Default value is an empty string.
		echo "<input type='text' name='CloudFrontDistrubutionID' value='" . esc_html( $cloudfront_id ) . "' />";
	}

	/**
	 * Display the CloudFront Region field.
	 *
	 * @return void
	 */
	public function cloudfront_region_cb(): void {
		$cloudfront_region = get_option( 'CloudFrontRegion', '' );   // Default value is an empty string.
		echo "<input type='text' name='CloudFrontRegion' value='" . esc_html( $cloudfront_region ) . "' />";
	}

	/**
	 * Handle the clear cache button click.
	 *
	 * @return void
	 */
	public function handle_clear_cache_button_click(): void {
		if ( isset( $_POST['cf_clear_cache'] ) && check_admin_referer( 'cf_clear_cache_action', 'cf_clear_cache_nonce' ) ) {
			set_transient( 'cf_cache_cleared_notice', true, 60 ); // Create admin notice.

			do_action( 'queue_cache_clear' );
		}
	}

	/**
	 * Show the admin notice when the cache is cleared manually.
	 *
	 * @return void
	 */
	public function cf_show_admin_notice(): void {
		if ( get_transient( 'cf_cache_cleared_notice' ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>CloudFront Invalidation Created.</p></div>';
			delete_transient( 'cf_cache_cleared_notice' ); // Clear the transient.
		}
	}
}
