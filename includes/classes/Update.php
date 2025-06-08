<?php
/**
 * Update
 *
 * Update class.
 *
 * @since   1.0.0
 *
 * @package WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Update {

    /**
     * Init
     *
     * @return void
     */

    public function init(): void {

        if ( WPDSYNC_REBATES_VERSION !== get_option( 'WPDSYNC_REBATES_VERSION' ) ) {

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            core()->rebate->create_table();

            update_option( 'WPDSYNC_REBATES_VERSION', WPDSYNC_REBATES_VERSION );

        }

    }
}
