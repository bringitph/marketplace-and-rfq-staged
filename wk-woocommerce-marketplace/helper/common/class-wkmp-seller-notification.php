<?php
/**
 * Seller Notification DB class
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Helper\Common;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Seller_Notification' ) ) {

	/**
	 * Seller Notification related queries class
	 */
	class WKMP_Seller_Notification {

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
		 * Save notification
		 *
		 * @param array $data Data.
		 */
		public function wkmp_add_new_notification( $data ) {
			$this->wpdb->insert( $this->wpdb->prefix . 'mp_notifications', $data );
		}

		/**
		 * Update notification read status
		 *
		 * @param array $data Data.
		 */
		public function wkmp_update_notification_read_status( $data ) {
			if ( 0 === intval( $data['read_flag'] ) ) {
				$this->wpdb->update(
					$this->wpdb->prefix . 'mp_notifications',
					array(
						'read_flag' => 1,
					),
					array( 'id' => $data['id'] )
				);
			}
		}

		/**
		 * Get notification data
		 *
		 * @param string $type Notification type.
		 * @param string $keyword Keyword.
		 */
		public function wkmp_get_notification_data( $type, $keyword ) {
			global $current_user;
			$wpdb_obj = $this->wpdb;

			$query = $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mp_notifications WHERE type=%s", $type );

			if ( in_array( $keyword, array( 'processing', 'complete' ), true ) ) {
				$query .= $wpdb_obj->prepare( ' AND content LIKE %s', '%' . $keyword . '%' );
			}

			if ( in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) {
				$query .= $wpdb_obj->prepare( ' AND author_id = %d', $current_user->ID );
			}

			return $this->wkmp_return_notification( $query );

		}

		/**
		 * Get notification data
		 *
		 * @param String $type_query Type query.
		 */
		public function wkmp_return_notification( $type_query ) {
			$wpdb_obj       = $this->wpdb;
			$pagination     = '';
			$page_no        = filter_input( INPUT_GET, 'n-page', FILTER_SANITIZE_NUMBER_INT );
			$page_no        = empty( $page_no ) ? 1 : abs( $page_no );
			$query          = $type_query;
			$total_query    = "SELECT COUNT(1) FROM (${query}) AS total";
			$total          = $wpdb_obj->get_var( $total_query );
			$items_per_page = get_option( 'posts_per_page' );
			$offset         = ( $page_no * $items_per_page ) - $items_per_page;
			$sql            = $wpdb_obj->get_results( $query . " ORDER BY id DESC LIMIT ${offset}, ${items_per_page}", ARRAY_A );
			$total_page     = ceil( $total / $items_per_page );

			if ( $total_page > 1 ) {
				$pagination = '<div class="mp-notification-pagination">' . paginate_links(
					array(
						'base'      => add_query_arg( 'n-page', '%#%' ),
						'format'    => '',
						'prev_text' => __( '&laquo;', 'wk-marketplace' ),
						'next_text' => __( '&raquo;', 'wk-marketplace' ),
						'total'     => $total_page,
						'current'   => $page_no,
					)
				) . '</div>';
			}

			return array(
				'data'       => $sql,
				'pagination' => $pagination,
			);
		}

		/**
		 * Get seller email by product id
		 *
		 * @param int $item_id Item Id.
		 */
		public function wkmp_get_author_email_by_item_id( $item_id ) {
			$wpdb_obj = $this->wpdb;
			$email    = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT user_email FROM {$wpdb_obj->base_prefix}users AS user JOIN {$wpdb_obj->prefix}posts AS post ON post.post_author = user.ID WHERE post.ID = %d", esc_attr( $item_id ) ) );

			return apply_filters( 'wkmp_get_author_email_by_item_id', $email, $item_id );
		}

		/**
		 * Get notification count
		 *
		 * @param int $seller_id Seller id.
		 */
		public function wkmp_seller_panel_notification_count( $seller_id ) {
			$wpdb_obj = $this->wpdb;
			$total    = $wpdb_obj->get_results( "SELECT*FROM {$wpdb_obj->prefix}mp_notifications WHERE read_flag = '0' AND author_id = '$seller_id' ", ARRAY_A );

			$total_count = 0;

			if ( ! empty( $total ) ) {
				foreach ( $total as $value ) {
					$author_id = empty( $value['author_id'] ) ? 0 : intval( $value['author_id'] );
					if ( intval( $seller_id ) === $author_id ) {
						$total_count ++;
					}
				}
			}

			return apply_filters( 'wkmp_seller_panel_notification_count', $total_count, $seller_id );
		}

		/**
		 * Get seller notifications data
		 *
		 * @param string $type Notification type.
		 * @param int    $offset Offset.
		 * @param int    $limit Limit.
		 *
		 * @return array $data
		 */
		public function wkmp_get_seller_notification_data( $type, $offset, $limit ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = get_current_user_id();

			$data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}mp_notifications WHERE type=%s AND author_id=%d ORDER BY id DESC LIMIT %d, %d", $type, $seller_id, $offset, $limit ), ARRAY_A );

			return apply_filters( 'wkmp_get_seller_notification_data', $data, $type );
		}

		/**
		 * Get seller notification count
		 *
		 * @param string $type Notification type.
		 *
		 * @return int $total
		 */
		public function wkmp_get_seller_notification_count( $type ) {
			$wpdb_obj  = $this->wpdb;
			$seller_id = get_current_user_id();

			$total = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT COUNT(*) FROM {$this->wpdb->prefix}mp_notifications WHERE type=%s AND author_id=%d", $type, $seller_id ) );

			return apply_filters( 'wkmp_get_seller_notification_count', $total, $type );
		}
	}
}
