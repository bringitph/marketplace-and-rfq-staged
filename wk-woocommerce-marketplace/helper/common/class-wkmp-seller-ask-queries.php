<?php
/**
 * Seller ask queries class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Ask_Queries' ) ) {

	/**
	 * Seller ask query related queries class
	 */
	class WKMP_Seller_Ask_Queries {

		/**
		 * DB Variable
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Constructor of the class
		 */
		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
		}

		/**
		 * Save seller ask query info into database.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $data query info.
		 *
		 * @return int Ask query id
		 */
		public function wkmp_save_seller_ask_query( $seller_id, $data ) {
			$wpdb_obj     = $this->wpdb;
			$insert_query = $wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpseller_asktoadmin',
				array(
					'seller_id'   => (int) $seller_id,
					'subject'     => sanitize_text_field( wp_unslash( $data['subject'] ) ),
					'message'     => sanitize_text_field( wp_unslash( $data['message'] ) ),
					'create_date' => gmdate( 'Y-m-d H:i:s' ),
				),
				array( '%d', '%s', '%s', '%s' ) 
			);

			$insert_id = $wpdb_obj->insert_id;

			$seller_info = get_userdata( $seller_id );

			do_action( 'wpmp_save_front_value', $seller_id, $data, $insert_id );
			do_action( 'wkmp_ask_to_admin', $seller_info->user_email, $data['subject'], $data['message'] );

			return $insert_query ? $wpdb_obj->insert_id : false;
		}

		/**
		 * Get all seller ask queries
		 *
		 * @param array $data Filter data.
		 *
		 * @return array $queries seller queries.
		 */
		public function wkmp_get_all_seller_queries( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT * FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id >= 1";

			if ( isset( $data['filter_subject'] ) && $data['filter_subject'] ) {
				$sql .= " AND subject LIKE '" . esc_attr( $data['filter_subject'] ) . "%'";
			}

			if ( isset( $data['seller_id'] ) && $data['seller_id'] ) {
				$sql .= " AND seller_id = '" . esc_attr( $data['seller_id'] ) . "'";
			}

			$sql .= ' ORDER BY id DESC LIMIT ' . esc_attr( isset( $data['start'] ) ? $data['start'] : 0 ) . ',' . esc_attr( isset( $data['limit'] ) ? $data['limit'] : 20 );

			$queries = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_all_seller_queries', $queries );

		}

		/**
		 * Get total count seller ask queries
		 *
		 * @param array $data Filter data.
		 *
		 * @return int $total seller queries.
		 */
		public function wkmp_get_total_seller_queries( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT(*) FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id >= 1";

			if ( isset( $data['filter_subject'] ) && $data['filter_subject'] ) {
				$sql .= " AND subject LIKE '" . esc_attr( $data['filter_subject'] ) . "%'";
			}

			if ( isset( $data['seller_id'] ) && $data['seller_id'] ) {
				$sql .= " AND seller_id = '" . esc_attr( $data['seller_id'] ) . "'";
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_total_seller_queries', $total );
		}

		/**
		 * Get all seller ask queries.
		 *
		 * @param int $id Query Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_get_query_info_by_id( $id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE id = %d", esc_attr( $id ) ) );

			return apply_filters( 'wkmp_get_query_info_by_id', $query );

		}

		/**
		 * Delete seller.
		 *
		 * @param int $id Id.
		 */
		public function wkmp_delete_seller_query( $id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->delete(
				"{$wpdb_obj->prefix}mpseller_asktoadmin",
				array(
					'id' => esc_attr( $id ),
				),
				array( '%d' ) 
			);
		}

		/**
		 * Update seller.
		 *
		 * @param int $id Id.
		 */
		public function wkmp_update_seller_reply_status( $id ) {
			$wpdb_obj = $this->wpdb;
			$wpdb_obj->insert(
				$wpdb_obj->prefix . 'mpseller_asktoadmin_meta',
				array(
					'id'         => esc_attr( $id ),
					'meta_key'   => 'reply_status',
					'meta_value' => 'replied',
				) 
			);
		}

		/**
		 * Check seller reply.
		 *
		 * @param Int $id Id.
		 *
		 * @return mixed|void
		 */
		public function wkmp_check_seller_replied_by_admin( $id ) {
			$wpdb_obj = $this->wpdb;
			$query    = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}mpseller_asktoadmin_meta WHERE meta_key = %s AND id = %d", esc_attr( 'reply_status' ), esc_attr( $id ) ) );
			$return   = 'replied' === $query ? $query : false;

			return apply_filters( 'wkmp_check_seller_replied_by_admin', $return );
		}
	}
}
