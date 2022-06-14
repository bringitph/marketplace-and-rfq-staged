<?php
/**
 * Seller Data Helper
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Seller_Product_Data' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Seller_Product_Data {

		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Get all seller's product.
		 *
		 * @param [type] $filter_data filter data.
		 *
		 * @return array
		 */
		public function wkmp_get_products( $filter_data ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT p.ID as product_id, p.post_author FROM {$wpdb_obj->prefix}posts p ";

			$search_key = empty( $filter_data['filter_name'] ) ? '' : $filter_data['filter_name'];

			if ( ! empty( $search_key ) ) {
				$sql .= " LEFT JOIN {$wpdb_obj->prefix}postmeta pm ON p.ID=pm.post_id AND pm.meta_key='_sku'";
			}

			$sql .= " WHERE p.post_type='product' AND (p.post_status = 'publish' OR p.post_status = 'draft')";

			if ( ! empty( $search_key ) ) {
				$sql .= " AND (p.post_title LIKE '" . esc_attr( $search_key ) . "%' OR pm.meta_value LIKE '" . esc_attr( $search_key ) . "%'";

				if ( is_numeric( $search_key ) ) {
					$sql .= " OR p.ID='" . esc_attr( $search_key ) . "' OR pm.meta_value='" . esc_attr( $search_key ) . "'";
				}
				$sql .= ')';
			}

			if ( $filter_data['filter_seller'] ) {
				$sql .= ' AND p.post_author = ' . esc_attr( $filter_data['filter_seller'] );
			}

			if ( $filter_data['filter_assign'] ) {
				if ( 1 === $filter_data['filter_assign'] ) {
					$sql .= ' AND p.post_author = ' . esc_attr( 1 );
				} else {
					$sql .= ' AND p.post_author != ' . esc_attr( 1 );
				}
			}

			$sql .= ' LIMIT ' . esc_attr( $filter_data['start'] ) . ',' . esc_attr( $filter_data['limit'] );

			$product_ids = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_products', $product_ids );
		}

		/**
		 * Get total products.
		 *
		 * @param array $filter_data filter data.
		 *
		 * @return int
		 */
		public function wkmp_get_total_products( $filter_data ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT( * ) FROM {$wpdb_obj->prefix}posts WHERE post_type = 'product' AND ( post_status = 'publish' OR post_status = 'draft' )";

			if ( $filter_data['filter_name'] ) {
				$sql .= " AND post_title LIKE '" . esc_attr( $filter_data['filter_name'] ) . "%'";
			}

			if ( $filter_data['filter_seller'] ) {
				$sql .= ' AND post_author = ' . esc_attr( $filter_data['filter_seller'] );
			}

			if ( $filter_data['filter_assign'] ) {
				if ( 1 === intval( $filter_data['filter_assign'] ) ) {
					$sql .= ' AND post_author = ' . esc_attr( 1 );
				} else {
					$sql .= ' AND post_author != ' . esc_attr( 1 );
				}
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_total_products', $total );
		}
	}
}
