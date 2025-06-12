<?php
/**
 * Hoooks
 *
 * Hook functions.
 *
 * @since   1.0.2
 *
 * @package WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Declare WooComerce HPOS Compatibility
 *
 * @return void
 * @since 1.0.2
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( 'Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
        FeaturesUtil::declare_compatibility( 'custom_order_tables', WPDSYNC_REBATES_PATH );
    }
} );