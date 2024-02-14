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
			<h1><?php esc_html_e( 'CloudFront Cache Clear', 'cloudfront-cache-clear' ); ?></h1>
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="postbox-container-1" class="postbox-container" style="width:50%;">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<div id="metabox" class="postbox">
							<div class="inside">
								<div class="main">
									<p><strong><?php esc_html_e( 'This WordPress plugin automatically invalidates a CloudFront cache whenever one of the following conditions are met:', 'cloudfront-cache-clear' ); ?></strong></p>
									<ul>
										<li><?php esc_html_e( '- An attachment is deleted - Scheduled.', 'cloudfront-cache-clear' ); ?></li>
										<li><?php esc_html_e( '- An attachment is updated - Scheduled.', 'cloudfront-cache-clear' ); ?></li>
										<li><?php esc_html_e( '- Attachment metadata is updated - Scheduled.', 'cloudfront-cache-clear' ); ?></li>
										<li><?php esc_html_e( '- W3 Total Cache is cleared - Instant.', 'cloudfront-cache-clear' ); ?></li>
										<li><?php esc_html_e( '- WP Super Cache is cleared - Instant.', 'cloudfront-cache-clear' ); ?></li>
									</ul>
									<p><strong><?php esc_html_e( 'In the case of scheduled invalidations, the plugin will wait 2 minutes before invalidating the cache. This is to prevent multiple invalidations when bulk updating attachments.', 'cloudfront-cache-clear' ); ?></strong></p>
									<h2><?php esc_html_e( 'Prerequisites', 'cloudfront-cache-clear' ); ?></h2>
									<p><?php esc_html_e( 'Ensure your IAM user has the following permissions:', 'cloudfront-cache-clear' ); ?></p>
									<ul>
										<li><strong>cloudfront:CreateInvalidation</strong></li>
									</ul>
									<h2><?php esc_html_e( 'Configuration', 'cloudfront-cache-clear' ); ?></h2>
									<p><?php esc_html_e( 'You can enter your CloudFront Distribution ID and Region below or you can define these in your wp-config.php file like so:', 'cloudfront-cache-clear' ); ?></p>
									<code>
										define('CFCC_DISTRIBUTION_ID', 'XXXXXXXXXXXXXX');<br>
										define('CFCC_REGION', 'eu-west-1');
									</code>
									<p><?php esc_html_e( 'Settings entered here will override the wp-config.php settings.', 'cloudfront-cache-clear' ); ?></p>
									<form method="post" action="options.php">
										<?php
										settings_fields( 'cloudfront_options_group' );
										do_settings_sections( 'cloudfront_options_page' );
										submit_button();
										?>
									</form>
									<h2><?php esc_html_e( 'Active Settings', 'cloudfront-cache-clear' ); ?></h2>
									<p><?php esc_html_e( 'The following settings are currently active and will be used whenever clearing the cache:', 'cloudfront-cache-clear' ); ?></p>
									<ul>
										<li><strong><?php esc_html_e( 'CloudFront Distribution ID:', 'cloudfront-cache-clear' ); ?></strong> <?php echo esc_html( $cloud_front->return_cloudfront_distribution_id() ) ?: 'N/A'; ?></li>
										<li><strong><?php esc_html_e( 'CloudFront Region:', 'cloudfront-cache-clear' ); ?></strong> <?php echo esc_html( $cloud_front->return_aws_region() ) ?: 'N/A'; ?></li>
										<li><strong><?php esc_html_e( 'Is AWS Environment:', 'cloudfront-cache-clear' ); ?></strong> <?php echo $cloud_front->return_is_aws_environment() ? esc_html__( 'True', 'cloudfront-cache-clear' ) : esc_html__( 'False', 'cloudfront-cache-clear' ); ?></li>
									</ul>
									<h2><?php esc_html_e( 'Manually Clear Cache', 'cloudfront-cache-clear' ); ?></h2>
									<p><?php esc_html_e( 'You can manually create an invalidation by clicking the button below. This will clear the entire cache for the distribution ID entered above.', 'cloudfront-cache-clear' ); ?></p>
									<form action="" method="post">
										<?php
										wp_nonce_field( 'cf_clear_cache_action', 'cf_clear_cache_nonce' );
										?>
										<input type="submit" name="cf_clear_cache" value="<?php esc_html_e( 'Clear CloudFront Cache', 'cloudfront-cache-clear' ); ?>" class="button button-secondary"/>
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
			__( 'CloudFront Distribution ID', 'cloudfront-cache-clear' ),
			array( $this, 'cloudfront_distribution_id_cb' ),
			'cloudfront_options_page',
			'cloudfront_settings_section'
		);

		add_settings_field(
			'cloudfront_region',
			__( 'CloudFront Region', 'cloudfront-cache-clear' ),
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
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'CloudFront Invalidation Created', 'cloudfront-cache-clear' ) . '</p></div>';
			delete_transient( 'cf_cache_cleared_notice' ); // Clear the transient.
		}
	}
}
