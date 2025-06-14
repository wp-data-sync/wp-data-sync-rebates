<?php
/**
 * Rebate
 *
 * Rebate class.
 *
 * @since   1.0.0
 *
 * @package WP_DataSync_Rebates
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Rebate {

    /**
     * Traits
     */
    use Data_Store;

    /**
     * @var string
     */

    protected string $id = 'wpds_rebates';

    /**
     * @var string
     */

    protected string $table_name = 'data_sync_rebates';

    /**
     * Construct
     */

    public function __construct() {
        $this->set_table();
    }

    /**
     * Init
     *
     * @return void
     */

    public function init(): void {
        add_action( "wp_data_sync_integration_$this->id", [ $this, 'import' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ], 999 );

        add_filter( 'wp_data_sync_settings', [ $this, 'settings' ] );
        add_filter( 'woocommerce_product_tabs', [ $this, 'maybe_show_product_tab' ] );
        add_filter( 'woocommerce_before_single_product', [ $this, 'maybe_show_product_slideout' ] );

        add_shortcode( 'wpds_rebates_table', [ $this, 'render_table' ] );
    }

    /**
     * Load Scripts
     *
     * @return void
     */

    public function load_scripts(): void {
        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'selectWoo' );

        wp_enqueue_style( 'rebates_css', WPDSYNC_REBATES_URL . 'assets/css/app.min.css', [], WPDSYNC_REBATES_VERSION );
        wp_enqueue_script( 'rebates_js', WPDSYNC_REBATES_URL . 'assets/js/app.min.js', ['jquery'], WPDSYNC_REBATES_VERSION );
    }

    /**
     * Import
     *
     * @param int $product_id
     * @param array $values
     *
     * @return void
     */

    public function import( int $product_id, array $values ): void {

        extract( $values );

        if ( ! is_array( $rebates ) ) {
            return;
        }

        foreach ( $rebates as $rebate ) {

            extract( $rebate );

            $args = [
                'product_id'    => $product_id,
                'rebate_id'     => $id,
                'brand'         => $brand,
                'name'          => $name,
                'promo_message' => $promo_message,
                'source'        => $source,
                'sponsor'       => $sponsor,
                'type'          => $type,
                'url'           => $url,
                'start_date'    => date_i18n( 'Y-m-d 00:00:00', strtotime( $start_date ) ),
                'end_date'      => date_i18n( 'Y-m-d 23:59:59', strtotime( $end_date ) )
            ];

            $this->sync( $args );

        }

    }

    /**
     * Settings
     *
     * @param array $args
     *
     * @return array
     */

    public function settings( array $args ): array {

        $args['woocommerce'][] = [
            'key'      => 'wp_data_sync_show_rebates_tab',
            'label'    => __( 'Show Rebates Tab', 'wpds-rebates' ),
            'callback' => 'input',
            'args'     => [
                'sanitize_callback' => 'sanitize_text_field',
                'basename'          => 'checkbox',
                'type'              => '',
                'class'             => '',
                'placeholder'       => '',
                'info'              => __( 'Show the rebates tab on single product pages.', 'wpds-rebates' )
            ]
        ];

        $args['woocommerce'][] = [
            'key'      => 'wp_data_sync_rebates_tab_priority',
            'label'    => __( 'Rebates Tab Priority', 'wpds-rebates' ),
            'callback' => 'input',
            'args'     => [
                'sanitize_callback' => 'intval',
                'basename'          => 'text-input',
                'type'              => 'number',
                'class'             => 'regular-text',
                'placeholder'       => 10,
                'info'              => __( 'Set the display order for the rebates product tab.', 'wpds-rebates' )
            ]
        ];

        $args['woocommerce'][] = [
            'key'      => 'wp_data_sync_show_rebates_slideout',
            'label'    => __( 'Show Rebates Slideout', 'wpds-rebates' ),
            'callback' => 'input',
            'args'     => [
                'sanitize_callback' => 'sanitize_text_field',
                'basename'          => 'checkbox',
                'type'              => '',
                'class'             => '',
                'placeholder'       => '',
                'info'              => __( 'Show the rebates slideout on single product pages.', 'wpds-rebates' )
            ]
        ];

        $args['woocommerce'][] = [
            'key'      => 'wp_data_sync_rebates_slideout_priority',
            'label'    => __( 'Rebates Slideout Priority', 'wpds-rebates' ),
            'callback' => 'input',
            'args'     => [
                'sanitize_callback' => 'intval',
                'basename'          => 'text-input',
                'type'              => 'number',
                'class'             => 'regular-text',
                'placeholder'       => 10,
                'info'              => __( 'Set the display order for the rebates product slideout button.', 'wpds-rebates' )
            ]
        ];

        $args['woocommerce'][] = [
            'key'      => 'wp_data_sync_rebates_slideout_button_text',
            'label'    => __( 'Rebates Slideout Button Text', 'wpds-rebates' ),
            'callback' => 'input',
            'args'     => [
                'sanitize_callback' => 'sanitize_text_field',
                'basename'          => 'text-input',
                'type'              => 'text',
                'class'             => 'regular-text',
                'placeholder'       => __( 'Product Rebates', 'wpds-rebates' ),
                'info'              => __( 'Product rebates slideout button text.', 'wpds-rebates' )
            ]
        ];

        return $args;

    }

    /**
     * Maybe Show Product Tab
     * 
     * @param array $tabs
     *
     * @return array
     */
    
    public function maybe_show_product_tab( array $tabs ): array {
        
        if ( is_checked( 'wp_data_sync_show_rebates_tab' ) ) {

            $tabs['docs'] = [
                'title'    => __( 'Rebates', 'wpds-rebates' ),
                'priority' => (int) get_option( 'wp_data_sync_rebates_tab_priority', 10 ),
                'callback' => [ $this, 'product_output' ]
            ];
            
        }
        
        return $tabs;

    }

    /**
     * Matbe Show Product Slideout
     *
     * @return void
     */

    public function maybe_show_product_slideout(): void {

        if ( is_checked( 'wp_data_sync_show_rebates_slideout' ) ) {

            $priority = (int) get_option( 'wp_data_sync_rebates_slideout_priority', 10 );
            add_action( 'woocommerce_before_add_to_cart_form', [ $this, 'render_slideout_button' ], $priority );
            add_action( 'woocommerce_after_single_product', [ $this, 'render_slideout' ] );

        }

    }

    /**
     * Product Output
     *
     * @return void
     */

    public function product_output(): void {

        global $product;

        $rebates = $this->get_product_rebates( $product->get_id() );

        if ( empty( $rebates ) ) {
            printf( '<div class="no-rebates">%s</div>', esc_html( $this->none_message() ) );
            return;
        }

        print( '<div class="rebates-table table-responsive">' );
        print( '<table class="table">' );

        foreach ( $rebates as $rebate ) {
            $this->render( $rebate );
        }

        print( '</table>' );
        print( '</div>' );

    }

    /**
     * None Message
     *
     * @return string
     */

    public function none_message(): string {
        return __( 'There are currently no rebates available for this product.', 'wpds-rebates' );
    }

    /**
     * Render
     *
     * @param array $rebate
     *
     * @return void
     */

    public function render( array $rebate ): void {

        extract( $rebate );

        printf(
            '<tbody class="rebate rebate-id-%d source-%s">',
            intval( $rebate_id ),
            esc_attr( $source )
        );

        printf(
            '<tr><th colspan="2" scope="col"><a href="%s" target="_blank">%s</a></th></tr>',
            esc_url_raw( $url ),
            esc_html( $name )
        );

        printf(
            '<tr><th class="promo-message" scope="row">%s</th><td>%s</td></tr>',
            esc_html__( 'Promotion', 'wpds-rebates' ),
            esc_html( $promo_message )
        );

        printf(
            '<tr><th class="rebate-brand" scope="row">%s</th><td>%s</td></tr>',
            esc_html__( 'Brand', 'wpds-rebates' ),
            esc_html( $brand )
        );

        printf(
            '<tr><th class="rebate-type" scope="row">%s</th><td>%s</td></tr>',
            esc_html__( 'Type', 'wpds-rebates' ),
            esc_html( $type )
        );

        printf(
            '<tr><th class="rebate-dates" scope="row">%s</th><td>%s</td></tr>',
            esc_html__( 'Starts', 'wpds-rebates' ),
            esc_html( pretty_date( $start_date ) )
        );

        printf(
            '<tr><th class="rebate-dates" scope="row">%s</th><td>%s</td></tr>',
            esc_html__( 'Endss', 'wpds-rebates' ),
            esc_html( pretty_date( $end_date ) )
        );

        print( '</tbody>' );

    }

    /**
     * Render Table
     *
     * @return string
     */

    public function render_table(): string {

        $rebates = $this->get_all_rebates();

        if ( empty( $rebates ) ) {
            return sprintf( '<div class="no-rebates">%s</div>', esc_html( $this->none_message() ) );
        }

        ob_start();

        print( '<div class="container rebates-table list table-responsive">' );

        print( '<div class="row">' );
        $this->search_input();
        $this->filter_dropdown();
        $this->sort_dropdown();
        print( '</div>' );

        /**
         * Table
         */
        print( '<table class="table">' );
        print( '<thead>' );
        print( '<tr>' );
        printf( '<th scope="col" colspan="3">%s</th>', esc_html__( 'Rebate Name', 'wpds-rebates' ) );
        printf( '<th scope="col">%s</th>', esc_html__( 'Brand', 'wpds-rebates' ) );
        printf( '<th scope="col">%s</th>', esc_html__( 'Type', 'wpds-rebates' ) );
        printf( '<th scope="col" colspan="2">%s</th>', esc_html__( 'Product', 'wpds-rebates' ) );
        printf( '<th scope="col">%s</th>', esc_html__( 'Expires', 'wpds-rebates' ) );
        print( '</tr>' );
        print( '</thead>' );

        print( '<tbody>' );

        foreach ( $rebates as $rebate ) {

            extract( $rebate );

            if ( $product = wc_get_product( $product_id ) ) {

                printf(
                    '<tr data-name="%s" data-start="%d" data-expires="%d" data-brand="%s" data-cat_ids="[%s]" data-search="%s %s %s">',
                    esc_attr( sanitize_title( $name ) ),
                    strtotime( $start_date ),
                    strtotime( $end_date ),
                    esc_attr( sanitize_title( $brand ) ),
                    esc_attr( join( ',', $product->get_category_ids() ) ),
                    esc_attr( strtolower( $product->get_name() ) ),
                    esc_attr( strtolower( $brand ) ),
                    esc_attr( strtolower( $type ) )
                );
                print( '<th scope="row" colspan="3" class="name">' );
                printf( '<a href="%s" target="_blank">%s</a>', esc_url_raw( $url ), esc_html( $name ) );
                print( '</th>' );
                printf( '<td class="brand">%s</td>', esc_html( $brand ) );
                printf( '<td class="type">%s</td>', esc_html( $type ) );
                printf(
                    '<td class="product" colspan="2"><a href="%s">%s</a></td>',
                    esc_url_raw( $product->get_permalink() ),
                    esc_html( $product->get_name() )
                );
                printf( '<td class="date">%s</td>', esc_html( pretty_date( $end_date ) ) );
                print( '</tr>' );

            }
        }

        print( '</tbody>' );
        print( '</table>' );
        print( '</div>' );

        return ob_get_clean();
    }

    /**
     * Render Slideout Button
     *
     * @return void
     */

    public function render_slideout_button(): void {

        $button_text = get_option( 'wp_data_sync_rebates_slideout_button_text', __( 'Product Rebates', 'wpds-rebates' ) );

        print( '<div class="rebates-button-wrap">' );
        printf( '<button class="show-rebates-slideout button" data-bs-toggle="offcanvas" href="#rebates-slideout" role="button" aria-controls="rebates-slideout">%s</button>', esc_html( $button_text ) );
        print( '</div>' );

    }

    /**
     * Render Slideout
     *
     * @return void
     */

    public function render_slideout(): void {

        global $product;

        $rebates = $this->get_product_rebates( $product->get_id() );

        if ( empty( $rebates ) ) {
            printf( '<div class="no-rebates">%s</div>', esc_html( $this->none_message() ) );
            return;
        }

        print( '<div class="offcanvas offcanvas-end rebates-slideout container" tabindex="-1" id="rebates-slideout" aria-labelledby="offcanvasLabel">' );

        /**
         * Button
         */
        print( '<div class="offcanvas-header">' );
        printf( '<h5 class="offcanvas-title" id="offcanvasLabel">%s</h5>', esc_html__( 'Rebates', 'wpds-rebates' ) );
        print( '<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>' );
        print( '</div>' );

        /**
         * Slideout
         */
        print( '<div class="rebates-table table-responsive">' );
        print( '<table class="table">' );

        foreach ( $rebates as $rebate ) {
            $this->render( $rebate );
        }

        print( '</table>' );
        print( '</div>' );

        print( '</div>' );

    }

    /**
     * Sort Dropdown
     *
     * @return void
     */

    public function sort_dropdown(): void {

        print( '<div class="rebate-filter-wrap col-md">');
        printf( '<select class="rebate-sort" aria-label="%s">', esc_html__( 'Sort', 'wpds-rebates' ) );
        printf( '<option value="name||0">%s</option>', esc_html__( 'Sort Order', 'wpds-rebates' ) );
        printf( '<option value="name||asc">%s</option>', esc_html__( 'Name - Assending', 'wpds-rebates' ) );
        printf( '<option value="name||desc">%s</option>', esc_html__( 'Name - Desending', 'wpds-rebates' ) );
        printf( '<option value="expires||asc">%s</option>', esc_html__( 'Expires - Assending', 'wpds-rebates' ) );
        printf( '<option value="expires||desc">%s</option>', esc_html__( 'Expires- Desending', 'wpds-rebates' ) );
        print( '</select>');
        print( '</div>');

    }

    /**
     * Filter Dropdown
     *
     * @return void
     */

    public function filter_dropdown(): void {

        print( '<div class="rebate-filter-wrap col-md">');
        printf( '<select class="rebate-filter" aria-label="%s">', esc_html__( 'Filter Rebates', 'wpds-rebates' ) );
        printf( '<option value="brand||0">%s</option>', esc_html__( 'Filter Rebates', 'wpds-rebates' ) );

        printf( '<optgroup label="%s">', esc_attr__( 'Brands', 'wpds-rebates' ) );
        foreach ( $this->get_brands() as $brand ) {
            printf( '<option value="brand||%s">%s</option>', esc_attr( sanitize_title( $brand ) ), esc_html( $brand ) );
        }
        print( '</optgroup>' );

        printf( '<optgroup label="%s">', esc_attr__( 'Product Categories', 'wpds-rebates' ) );
        foreach ( $this->get_categories() as $cat ) {
            printf( '<option value="cat_ids||%d">%s</option>', esc_attr( $cat['id'] ), esc_html( $cat['name'] ) );
        }
        print( '</optgroup>' );

        print( '</select>');
        print( '</div>');

    }

    /**
     * Search Input
     *
     * @return void
     */

    public function search_input() {

        print( '<div class="rebate-filter-wrap search-wrap col-md">');
        printf(
            '<input type="search" class="rebate-search" aria-label="%s" placeholder="%s">',
            esc_html__( 'Search', 'wpds-rebates' ),
            esc_attr__( 'Search Rebates', 'wpds-rebates' )
        );
        print( '</div>');

    }


}