# CloudFront Cache Invalidator Plugin for WordPress

This WordPress plugin automatically invalidates a CloudFront cache whenever a media item is deleted, ensuring that your CloudFront-served assets are always up-to-date.

The plugin is also compatible with WP Super Cache and W3 Total Cache and will create a validation whenever these caches are purged.

## Prerequisites

- An AWS CloudFront Distribution
- The AWS CloudFront Distribution ID.
- An AWS IAM role with permission to invalidate CloudFront caches

## Installation

2. Download or clone this repository.
3. Upload the plugin files to your `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
4. Activate the plugin.

## Configuration

1. Store your CloudFront distribution ID and Region to the wp-config.php file or directly within the plugin admin interface *(Settings->CloudFront Cache)*:

```
define( 'CFCC_DISTRIBUTION_ID', 'XXXXXXXXXXXXXX' );
define( 'CFCC_REGION', 'eu-west-1' );
```

2. Ensure your environment's IAM role has permission to invalidate CloudFront caches:

```
cloudfront:CreateInvalidation
```


## Usage

Once activated and configured, the plugin will automatically clear the CloudFront cache for the specified distribution whenever one of the following takes place:


 - An attachment is deleted - Scheduled.
 - An attachment is updated - Scheduled.
 - Attachment metadata is updated - Scheduled.
 - W3 Total Cache is cleared - Instant.
 - WP Super Cache is cleared - Instant.

 In the case of sheduled invalidations, the plugin will wait 2 minutes before invalidating the cache. This is to prevent multiple invalidations when bulk updating attachments.

 In addition to the automations above, you can also manually create an invalidation within the plugin settings page.

## Support

This is a basic plugin provided as-is. For support, please open an issue on the [GitHub repository](https://github.com/ufmedia/cloudfront-cache-invalidator).

## License

This project is licensed under the GNU General Public License - see the [LICENSE.txt](LICENSE.txt) file for details.