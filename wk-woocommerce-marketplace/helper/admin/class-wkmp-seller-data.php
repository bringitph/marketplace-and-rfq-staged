<?php
/**
 * Seller Data Helper
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WKMP_Seller_Data' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Seller_Data {

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
		 * Approve Marketplace seller
		 *
		 * @param int    $seller_id seller id.
		 * @param string $role role.
		 *
		 * @return void
		 */
		public function wkmp_approve_seller( $seller_id, $role ) {
			if ( $seller_id && $role ) {
				$this->wpdb->update(
					$this->wpdb->prefix . 'mpsellerinfo',
					array(
						'seller_value' => sanitize_text_field( wp_unslash( $role ) ),
					),
					array( 'user_id' => $seller_id ),
					array( '%s' ),
					array( '%d' )
				);
			}
		}

		/**
		 * Get all sellers.
		 *
		 * @param array $data data.
		 *
		 * @return array
		 */
		public function wkmp_get_sellers( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT mp.user_id, mp.seller_key, mp.seller_value, u.user_email, u.user_registered, u.display_name FROM {$wpdb_obj->prefix}mpsellerinfo mp LEFT JOIN {$wpdb_obj->base_prefix}users u ON (mp.user_id = u.ID)";

			if ( isset( $data['filter_email'] ) && $data['filter_email'] ) {
				$sql .= $wpdb_obj->prepare( ' WHERE u.user_email LIKE %s OR u.user_nicename LIKE %s OR u.display_name LIKE %s OR u.user_login LIKE %s', esc_attr( $data['filter_email'] ) . '%', esc_attr( $data['filter_email'] ) . '%', esc_attr( $data['filter_email'] ) . '%', esc_attr( $data['filter_email'] ) . '%' );
			}

			$sql .= $wpdb_obj->prepare( ' LIMIT %d, %d', esc_attr( $data['start'] ), esc_attr( $data['limit'] ) );

			$sellers = $wpdb_obj->get_results( $sql );

			return apply_filters( 'wkmp_get_sellers', $sellers );
		}

		/**
		 * Get All Sellers Count
		 *
		 * @param array $data data.
		 *
		 * @return int
		 */
		public function wkmp_get_total_sellers( $data = array() ) {
			$wpdb_obj = $this->wpdb;
			$sql      = "SELECT COUNT(*) FROM {$this->wpdb->prefix}mpsellerinfo mp LEFT JOIN {$this->wpdb->base_prefix}users u ON (mp.seller_id = u.ID)";

			if ( isset( $data['filter_email'] ) && $data['filter_email'] ) {
				$sql .= $wpdb_obj->prepare( ' WHERE u.user_email LIKE %s', esc_attr( $data['filter_email'] ) . '%' );
			}

			$total = $wpdb_obj->get_var( $sql );

			return apply_filters( 'wkmp_get_total_sellers', $total );
		}

		/**
		 * Delete seller by seller id
		 *
		 * @param int $seller_id seller id.
		 *
		 * @return void
		 */
		public function wkmp_delete_seller( $seller_id ) {
			global $wkmarketplace;

			$wpdb_obj = $this->wpdb;

			if ( get_userdata( $seller_id ) instanceof \WP_User ) {
				$wpdb_obj->delete( "{$wpdb_obj->base_prefix}users", array( 'ID' => esc_attr( $seller_id ) ), array( '%d' ) );
				$wpdb_obj->delete( "{$wpdb_obj->base_prefix}usermeta", array( 'user_id' => esc_attr( $seller_id ) ), array( '%d' ) );
			}

			$wpdb_obj->delete( "{$wpdb_obj->prefix}mpsellerinfo", array( 'user_id' => esc_attr( $seller_id ) ), array( '%d' ) );

			$post_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT ID FROM {$this->wpdb->prefix}posts WHERE post_author = %d", esc_attr( $seller_id ) ) );

			if ( $post_ids ) {
				if ( get_option( '_wkmp_seller_delete' ) ) {
					foreach ( $post_ids as $post_id ) {
						$wpdb_obj->delete( "{$wpdb_obj->prefix}posts", array( 'ID' => esc_attr( $post_id->ID ) ), array( '%d' ) );
						$wpdb_obj->delete( "{$wpdb_obj->prefix}postmeta", array( 'post_id' => esc_attr( $post_id->ID ) ), array( '%d' ) );
					}
				} else {
					$first_admin_id = $wkmarketplace->wkmp_get_first_admin_user_id();
					foreach ( $post_ids as $post_id ) {
						$wpdb_obj->update( $wpdb_obj->prefix . 'posts', array( 'post_author' => $first_admin_id ), array( 'ID' => $post_id->ID ), array( '%d' ), array( '%d' ) );
					}
				}
			}

			$query_ids = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT id FROM {$wpdb_obj->prefix}mpseller_asktoadmin WHERE seller_id =%d", esc_attr( $seller_id ) ) );

			if ( $query_ids ) {
				foreach ( $query_ids as $id ) {
					$wpdb_obj->delete( "{$wpdb_obj->prefix}mpseller_asktoadmin", array( 'id' => esc_attr( $id->id ) ), array( '%d' ) );
					$wpdb_obj->delete( "{$wpdb_obj->prefix}mpseller_asktoadmin_meta", array( 'id' => esc_attr( $id->id ) ), array( '%d' ) );
				}
			}
		}

		/**
		 * Get product count by seller id
		 *
		 * @param int $seller_id Seller ID.
		 *
		 * @return $count
		 */
		public function wkmp_get_seller_product_count( $seller_id ) {
			$count    = 0;
			$wpdb_obj = $this->wpdb;

			if ( $seller_id ) {
				$count = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT count(id) AS total FROM {$wpdb_obj->prefix}posts WHERE post_author=%d AND post_type = 'product'", intval( $seller_id ) ) );
			}

			return apply_filters( 'wkmp_seller_product_count', $count, $seller_id );
		}

		/**
		 * Get seller commission info by seller id
		 *
		 * @param int $seller_id Seller ID.
		 *
		 * @return array $commission_info
		 */
		public function wkmp_get_seller_commission_info( $seller_id ) {
			$wpdb_obj        = $this->wpdb;
			$commission_info = array();

			if ( $seller_id ) {
				$commission_info = $wpdb_obj->get_row( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mpcommision where seller_id = %d", intval( $seller_id ) ) );
			}

			return apply_filters( 'wkmp_get_seller_commission_info', $commission_info, $seller_id );
		}

		/**
		 * Update seller commission
		 *
		 * @param int $seller_id Seller ID.
		 * @param int $info Commission info.
		 *
		 * @return boolean
		 */
		public function wkmp_update_seller_commission_info( $seller_id, $info ) {
			if ( $seller_id ) {
				$query = $this->wpdb->update(
					$this->wpdb->prefix . 'mpcommision',
					array(
						'commision_on_seller' => sanitize_text_field( wp_unslash( $info['wkmp_seller_commission'] ) ),
					),
					array( 'seller_id' => $seller_id ),
					array( '%f' ),
					array( '%d' )
				);
			}

			return isset( $query ) && $query ? true : false;
		}
	}
}
