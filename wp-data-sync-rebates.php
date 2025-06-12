<?php
/**
 * Plugin Name:          WP Data Sync - Rebates Integration
 * Plugin URI:           https://wpdatasync.com/products/
 * Description:          Process rebates for WooCommerce products.
 * Version:              1.0.1
 * Author:               WP Data Sync
 * Author URI:           https://wpdatasync.com
 * Stable tag:           1.0.1
 * Requires PHP:         7.4
 * Requires at least:    6.6
 * Tested up to:         6.8.1
 * WC requires at least: 9.6
 * WC tested up to:      9.8.5
 * Requires Plugins:     woocommerce, wp-data-sync
 * License:              GPL2
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wpds-rebates
 * Domain Path:          /languages
 *
 * Package:              WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPDSYNC_REBATES_VERSION', '1.0.1' );
define( 'WPDSYNC_REBATES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPDSYNC_REBATES_URL', plugin_dir_url( __FILE__ ) );

require WPDSYNC_REBATES_PATH . '/vendor/autoload.php';

core();