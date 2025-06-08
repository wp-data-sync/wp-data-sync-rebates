<?php
/**
 * Functions
 *
 * Plugin functions.
 *
 * @since   1.0.0
 *
 * @package WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core
 *
 * @return Core_Controller
 */

function core(): Core_Controller {
    return new Core_Controller();
}

/**
 * Is Checked.
 *
 * @param string $option
 *
 * @return bool
 */

function is_checked( string $option ): bool {
    return ( 'checked' === get_option( $option ) );
}

/**
 * Pretty Date
 *
 * @param string $datetime
 *
 * @return string
 */

function pretty_date( string $datetime ): string {
    return date_i18n( 'M j, Y', strtotime( $datetime ) );
}