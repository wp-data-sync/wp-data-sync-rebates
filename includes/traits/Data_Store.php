<?php
/**
 * Data_Store
 *
 * Data_Store class.
 *
 * @since   1.0.0
 *
 * @package WP_DataSync_Data_Stores
 */

namespace WP_DataSync\Integration;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

trait Data_Store {

    /**
     * @var string
     */

    private string $table;

    /**
     * Get Table
     *
     * @return void
     */

    public function set_table(): void {
        global $wpdb;

        $this->table = $wpdb->prefix . $this->table_name;
    }

    /**
     * Get Table
     *
     * @return string
     */

    public function get_table(): string {
        return $this->table;
    }

    /**
     * Sync
     *
     * @param array $args
     *
     * @return void
     */

    public function sync( array $args ): void {

        global $wpdb;

        extract( $args );

        $wpdb->query( $wpdb->prepare(
            "
            INSERT INTO $this->table 
                (product_id, rebate_id, brand, name, promo_message, source, sponsor, type, url, start_date, end_date)
            VALUES
                (%d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY 
            UPDATE
                brand = VALUES(brand),
                name = VALUES(name),
                promo_message = VALUES(promo_message),
                source = VALUES(source),
                sponsor = VALUES(sponsor),
                type = VALUES(type),
                url = VALUES(url),
                start_date = VALUES(start_date),
                end_date = VALUES(end_date)    
            ",
            intval( $product_id ),
            intval( $rebate_id ),
            esc_sql( $brand ),
            esc_sql( $name ),
            esc_sql( $promo_message ),
            esc_sql( $source ),
            esc_sql( $sponsor ),
            esc_sql( $type ),
            esc_sql( $url ),
            esc_sql( $start_date ),
            esc_sql( $end_date )
        ) );

    }

    /**
     * Get Product Rebates
     *
     * @param int $product_id
     *
     * @return array
     */

    public function get_product_rebates( int $product_id ): array {

        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "
            SELECT * 
            FROM $this->table
            WHERE product_id = %d
            AND end_date > NOW()
            ORDER BY start_date
            ",
            $product_id
        ), ARRAY_A );

        if ( empty( $results ) || is_wp_error( $results ) ) {
            return [];
        }

        return $results;

    }

    /**
     * Get All Rebates
     *
     * @return array
     */

    public function get_all_rebates(): array {

        global $wpdb;

        $results = $wpdb->get_results(
            "
            SELECT *
            FROM $this->table
            WHERE start_date < now() 
              AND end_date > NOW()
            ORDER BY end_date
            ",
            ARRAY_A
        );

        if ( empty( $results ) || is_wp_error( $results ) ) {
            return [];
        }

        return $results;

    }

    /**
     * Get Brands
     *
     * @return array
     */

    public function get_brands(): array {

        global $wpdb;

        $results = $wpdb->get_col(
            "
            SELECT DISTINCT brand
            FROM $this->table
            WHERE start_date < now() 
              AND end_date > NOW()
            ORDER BY brand
            "
        );

        if ( empty( $results ) || is_wp_error( $results ) ) {
            return [];
        }

        return $results;

    }

    /**
     * Get Categories
     *
     * @return array
     */

    public function get_categories(): array {

        global $wpdb;

        $results = $wpdb->get_results(
            "
            SELECT DISTINCT t.term_id AS id, t.name AS name
            FROM $wpdb->term_relationships tr
            INNER JOIN $wpdb->term_taxonomy tt
                ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN $wpdb->terms t
                ON t.term_id = tt.term_id
            WHERE tr.object_id IN (
                SELECT DISTINCT product_id
                FROM $this->table
                WHERE start_date < now() AND end_date > NOW()
            )
            AND taxonomy = 'product_cat'
            ORDER BY t.name
            ",
            ARRAY_A
        );

        if ( empty( $results ) || is_wp_error( $results ) ) {
            return [];
        }

        return $results;

    }

    /**
     * Create Table
     *
     * @return void
     */

    public function create_table() {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $wpdb->query( "
			CREATE TABLE IF NOT EXISTS $this->table (
  				id bigint(20) NOT NULL AUTO_INCREMENT,
  				product_id bigint(20) NOT NULL,
  				rebate_id bigint(20) NOT NULL,
  				brand varchar(100) NOT NULL,
  				name varchar(300) NOT NULL DEFAULT '',
  				promo_message longtext NOT NULL DEFAULT '',
  				source varchar(50) NOT NULL DEFAULT '',
  				sponsor varchar(50) NOT NULL DEFAULT '',
  				type varchar(50) NOT NULL DEFAULT '',
  				url varchar(300) NOT NULL DEFAULT '',
  				start_date datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  				end_date datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
  				timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  				PRIMARY KEY (id),
  				UNIQUE KEY product_id_rebate_id (product_id, rebate_id),
                KEY rebate_id (rebate_id),
                KEY product_id (product_id),
                KEY start_date (start_date),
                KEY end_date (end_date)
			) $charset_collate;
        " );

    }

}