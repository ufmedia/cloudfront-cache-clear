# CloudFront Cache Invalidation Plugin for WordPress

This WordPress plugin automatically invalidates a CloudFront cache whenever a media item is deleted, ensuring that your CloudFront-served assets are always up-to-date.

## Prerequisites

- An AWS CloudFront Distribution
- The AWS CloudFront Distribution ID stored in the AWS Systems Manager Parameter Store under the key "CloudFrontDistrubutionID"
- An AWS IAM role with permissions to read from the Systems Manager Parameter Store and to invalidate CloudFront caches

## Installation

2. Download or clone this repository.
3. Upload the plugin files to your `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
4. Activate the plugin through the 'Plugins' screen in WordPress.

## Configuration

1. Store your CloudFront distribution ID in the AWS Systems Manager Parameter Store under the key "CloudFrontDistrubutionID".
2. Ensure your environment's IAM role has permissions to read from the AWS Systems Manager Parameter Store and to invalidate CloudFront caches.
3. The plugin will automatically detect the AWS region. If the WordPress installation is outside AWS, it defaults to 'us-west-2'.

## Usage

Once activated and properly configured, the plugin will automatically clear the CloudFront cache for the specified distribution whenever a media item is deleted in WordPress.

## Support

This is a basic plugin provided as-is. For support, please open an issue on the GitHub repository (if applicable) or contact the original developer.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.