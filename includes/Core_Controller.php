<?php
/**
 * Core_Controller
 *
 * Core_Controller class.
 *
 * @since   1.0.0
 *
 * @package WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Core_Controller {

    /**
     * @var Rebate
     */

    public Rebate $rebate;

    /**
     * @var Update
     */

    public Update $update;

    /**
     * @var Core_Controller
     */

    private static $instance;

    /**
     * Core_Controller constructor.
     */

    public function __construct() {

        $properties = array_keys( get_class_vars( __CLASS__ ) );

        foreach ( $properties as $property ) {

            $class = __NAMESPACE__ . '\\' . ucwords( $property );

            if ( class_exists( $class ) ) {

                $this->$property = new $class();

                if ( method_exists( $class, 'init' ) ) {
                    add_action( 'plugins_loaded', [ $this->$property, 'init' ], 1 );
                }

            }

        }

        self::$instance = $this;

    }

    /**
     * @return Core_Controller
     */

    public static function instance(): Core_Controller {

        if ( self::$instance === null ) {
            self::$instance = new self();
        }

        return self::$instance;

    }

}
