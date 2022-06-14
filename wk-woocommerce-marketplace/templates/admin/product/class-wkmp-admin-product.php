<?php
/**
 * Seller Order List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Product;

defined( 'ABSPATH' ) || exit;

use WkMarketplace\Helper\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WKMP_Admin_Product' ) ) {
	/**
	 * Seller List Class
	 */
	class WKMP_Admin_Product extends \WP_List_Table {
		/**
		 * Product DB Object.
		 *
		 * @var Admin\WKMP_Seller_Product_Data
		 */
		private $product_db_obj;

		/**
		 * Seller DB Object.
		 *
		 * @var Admin\WKMP_Seller_Data
		 */
		private $seller_db_obj;

		/**
		 * Marketplace.
		 *
		 * @var $marketplace \Marketplace
		 */
		private $marketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Admin_Product constructor.
		 */
		public function __construct() {
			global $wkmarketplace;

			$this->product_db_obj = new Admin\WKMP_Seller_Product_Data();
			$this->seller_db_obj  = new Admin\WKMP_Seller_Data();
			$this->marketplace    = $wkmarketplace;

			parent::__construct(
				array(
					'singular' => esc_html__( 'Seller Product', 'wk-marketplace' ),
					'plural'   => esc_html__( 'Seller Product', 'wk-marketplace' ),
					'ajax'     => false,
				)
			);
		}

		/**
		 * Extra Table navigation
		 *
		 * @param [type] $which screen.
		 *
		 * @return void
		 */
		public function extra_tablenav( $which ) {
			$nonce = wp_create_nonce();

			$total      = $this->seller_db_obj->wkmp_get_total_sellers();
			$seller_ids = $this->seller_db_obj->wkmp_get_sellers(
				array(
					'start' => 0,
					'limit' => $total,
				)
			);
			$sellers    = array();
			$sellers[1] = 'Admin';

			foreach ( $seller_ids as $value ) {
				$seller_info = $this->marketplace->wkmp_get_seller_info( $value->user_id );
				if ( $seller_info ) {
					$sellers[ $value->user_id ] = $seller_info->user_login;
				}
			}

			if ( 'top' === $which ) {
				?>
				<div class="alignleft actions bulkactions">
					<select name="mp-assign-product" id="mp-product-seller-select-list" class="regular-text wkmp-select" style="min-width:200px;">
						<option value=""><?php esc_html_e( 'Select Seller', 'wk-marketplace' ); ?></option>
						<?php foreach ( $sellers as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php } ?>
					</select>
					<input type="hidden" name="product-assign_nonce" value="<?php echo esc_attr( $nonce ); ?>">
					<?php submit_button( 'Assign', 'button', 'mp-assign-product-seller', false ); ?>
				</div>

				<div class="alignleft actions bulkactions">
					<?php $chkpro = filter_input( INPUT_GET, 'check-pro', FILTER_SANITIZE_STRING ); ?>
					<select name="check-pro" class="ewc-filter-cat">
						<option value=""><?php esc_html_e( 'Filter by Product', 'wk-marketplace' ); ?></option>
						<option value="assign"<?php echo ( 'assign' === $chkpro ) ? 'selected' : ''; ?>><?php esc_html_e( 'Assigned', 'wk-marketplace' ); ?></option>
						<option value="<?php echo esc_attr( get_current_user_id() ); ?>" <?php echo ( intval( $chkpro ) === get_current_user_id() ) ? 'selected' : ''; ?>><?php esc_html_e( 'UnAssigned', 'wk-marketplace' ); ?></option>
					</select>

					<select name="changeSeller" class="ewc-filter-cat">
						<?php $seller_id = filter_input( INPUT_GET, 'changeSeller', FILTER_SANITIZE_NUMBER_INT ); ?>
						<option value=""><?php esc_html_e( 'Filter by Seller', 'wk-marketplace' ); ?></option>
						<?php foreach ( $sellers as $key => $value ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( intval( $seller_id ) === intval( $key ) ) ? 'selected' : ''; ?>><?php echo esc_html( $value ); ?></option>
						<?php } ?>
					</select>

					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">
					<?php submit_button( 'Filter', 'button', 'changeBySeller', false ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$sortable = $this->get_sortable_columns();
			$hidden   = $this->get_hidden_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->process_bulk_action();

			$per_page     = $this->get_items_per_page( 'product_per_page', 20 );
			$current_page = $this->get_pagenum();
			$screen       = get_current_screen();
			$prod_seller  = filter_input( INPUT_GET, 'mp-assign-product-seller', FILTER_SANITIZE_STRING );

			if ( 'Assign' === $prod_seller ) {
				$assign_product = filter_input( INPUT_GET, 'mp-assign-product', FILTER_SANITIZE_STRING );
				$ids            = isset( $_GET['ids'] ) ? wc_clean( $_GET['ids'] ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( ! empty( $assign_product ) && ! empty( $ids ) ) {
					$this->wkmp_assign_product_to_seller( $assign_product, $ids );
				}
			}

			$change_by_seller = filter_input( INPUT_GET, 'changeBySeller', FILTER_SANITIZE_STRING );

			$f_seller_id = 0;
			$f_assign    = false;

			if ( 'Filter' === $change_by_seller ) {
				$f_seller_id = filter_input( INPUT_GET, 'changeSeller', FILTER_SANITIZE_NUMBER_INT );
				$f_assign    = filter_input( INPUT_GET, 'check-pro', FILTER_SANITIZE_NUMBER_INT );
			}

			$filter_name = isset( $_REQUEST['s'] ) ? wc_clean( $_REQUEST['s'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$filter_data = array(
				'start'         => ( $current_page - 1 ) * $per_page,
				'limit'         => $per_page,
				'filter_name'   => $filter_name,
				'filter_seller' => $f_seller_id,
				'filter_assign' => $f_assign,
			);

			$product_ids = $this->product_db_obj->wkmp_get_products( $filter_data );
			$data        = $this->wkmp_get_table_data( $product_ids );
			$total_items = $this->product_db_obj->wkmp_get_total_products( $filter_data );

			usort( $data, array( $this, 'usort_reorder' ) );

			$total_pages = ceil( $total_items / $per_page );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $data;
		}

		/**
		 * Define the columns that are going to be used in the table
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			return array(
				'cb'           => '<input type="checkbox" />',
				'image'        => esc_html__( 'Image', 'wk-marketplace' ),
				'product'      => esc_html__( 'Product', 'wk-marketplace' ),
				'sku'          => esc_html__( 'SKU', 'wk-marketplace' ),
				'stock'        => esc_html__( 'Stock', 'wk-marketplace' ),
				'price'        => esc_html__( 'Price', 'wk-marketplace' ),
				'categories'   => esc_html__( 'Categories', 'wk-marketplace' ),
				'tags'         => esc_html__( 'Tags', 'wk-marketplace' ),
				'featured'     => esc_html__( 'Featured', 'wk-marketplace' ),
				'type'         => esc_html__( 'Type', 'wk-marketplace' ),
				'date_created' => esc_html__( 'Date', 'wk-marketplace' ),
				'seller'       => esc_html__( 'Seller', 'wk-marketplace' ),
			);
		}

		/**
		 * Column default.
		 *
		 * @param array|object $item Item.
		 * @param string       $column_name Column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'image':
				case 'product':
				case 'sku':
				case 'stock':
				case 'price':
				case 'categories':
				case 'tags':
				case 'featured':
				case 'type':
				case 'date_created':
				case 'seller':
					return $item[ $column_name ];
				default:
					return '-';
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on
		 *
		 * @return array, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'product'      => array( 'product', true ),
				'sku'          => array( 'sku', true ),
				'stock'        => array( 'stock', true ),
				'price'        => array( 'price', true ),
				'type'         => array( 'type', true ),
				'date_created' => array( 'date', true ),
				'seller'       => array( 'seller', true ),
			);
		}

		/**
		 * Column actions.
		 *
		 * @param array $item Item.
		 *
		 * @return string
		 */
		public function column_product( $item ) {
			$actions = array(
				'edit'   => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_edit_post_link( $item['id'] ), esc_html__( 'Edit', 'wk-marketplace' ) ),
				'manage' => sprintf( '<a class="wkmp-seller-edit-link" href="%s">%s</a>', get_the_permalink( $item['id'] ), esc_html__( 'View', 'wk-marketplace' ) ),
			);

			return sprintf( '%1$s %2$s', $item['product'], $this->row_actions( apply_filters( 'wkmp_seller_product_list_line_actions', $actions ) ) );
		}

		/**
		 * Get hidden columns.
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Column callback.
		 *
		 * @param array|object $item Item.
		 *
		 * @return string|void
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="feedback_%d" name="ids[]" value="%d" />', $item['id'], $item['id'] );
		}

		/**
		 * Get bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = array(
				'trash' => esc_html__( 'Trash', 'wk-marketplace' ),
			);

			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === esc_attr( 'trash' ) ) {
				$ids     = isset( $_REQUEST['ids'] ) ? wc_clean( $_REQUEST['ids'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$success = 0;
				if ( is_iterable( $ids ) ) {
					foreach ( $ids as $id ) {
						$product_trashed = array(
							'ID'          => $id,
							'post_status' => 'trash',
						);
						wp_update_post( $product_trashed );
					}
					$success = 1;
				}

				$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$url = 'admin.php?page=' . $page_name . '&success=' . $success;
				wp_safe_redirect( admin_url( $url ) );
				exit( 0 );
			}
		}

		/**
		 * Get table data.
		 *
		 * @param array $product_ids Product ids.
		 *
		 * @return array
		 */
		public function wkmp_get_table_data( $product_ids ) {
			global $wkmarketplace;
			$data = array();
			foreach ( $product_ids as $id ) {
				$product_info = wc_get_product( $id->product_id );
				$image        = wc_placeholder_img_src();

				if ( $product_info->get_image_id() ) {
					$image = wp_get_attachment_image_src( $product_info->get_image_id() )[0];
				}

				$image = '<img class="attachment-shop_thumbnail wp-post-image" width="50" height="50" alt="" src="' . esc_url( $image ) . '">';

				if ( $product_info->is_type( 'simple' ) ) {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				} elseif ( $product_info->is_type( 'variable' ) ) {
					$price = '<span class="price"><span class="amount">' . wc_price( $product_info->get_variation_prices()['price'] ? min( $product_info->get_variation_prices()['price'] ) : 0 ) . '</span>&ndash;<span class="amount">' . wc_price( $product_info->get_variation_prices()['price'] ? max( $product_info->get_variation_prices()['price'] ) : 0 ) . '</span></span>';
				} elseif ( $product_info->is_type( 'external' ) ) {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				} elseif ( $product_info->is_type( 'grouped' ) ) {
					$price = '<span class="amount">-</span>';
				} else {
					$price = '<span class="amount">' . wc_price( $product_info->get_price() ) . '</span>';
				}

				$product_cats = get_the_terms( $id->product_id, 'product_cat' );
				$product_tags = get_the_terms( $id->product_id, 'product_tag' );

				$category = array();
				if ( ! empty( $product_cats ) ) {
					foreach ( $product_cats as $cat ) {
						$category[] = $cat->name;
					}
				}

				$tags = array();
				if ( ! empty( $product_tags ) ) {
					foreach ( $product_tags as $tag ) {
						$tags[] = '<a href="' . esc_url( admin_url( 'edit.php?product_tag=' . $tag->slug . '&post_type=product' ) ) . ' ">' . esc_html( $tag->name ) . '</a>';
					}
				}

				$created_date = gmdate( 'Y-n-j', strtotime( $product_info->get_date_created() ) );

				$seller = 'Admin';
				if ( 1 !== $id->post_author ) {
					$seller = get_user_meta( $id->post_author, 'first_name', true ) . ' ' . get_user_meta( $id->post_author, 'last_name', true );
				}

				$data[] = array(
					'id'           => $id->product_id,
					'image'        => $image,
					'product'      => $product_info->get_name(),
					'sku'          => $wkmarketplace->wkmp_get_sku( $product_info ),
					'stock'        => '<mark class="instock">' . ucfirst( $product_info->get_stock_status() ) . '</mark>',
					'price'        => $price,
					'categories'   => implode( ',', $category ),
					'tags'         => empty( $tags ) ? '<span class="na">&ndash;</span>' : implode( ', ', $tags ),
					'featured'     => $product_info->is_featured() ? 'Yes' : 'No',
					'type'         => ucfirst( $product_info->get_type() ),
					'date_created' => $created_date . '</br>' . ucfirst( $product_info->get_status() ),
					'seller'       => $seller,
				);
			}

			return $data;
		}

		/**
		 * Assign product to seller.
		 *
		 * @param int   $seller_id Seller id.
		 * @param array $product_ids Product ids.
		 */
		public function wkmp_assign_product_to_seller( $seller_id, $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$arg = array(
					'ID'          => intval( $product_id ),
					'post_author' => $seller_id,
				);
				wp_update_post( $arg );

				$args       = array(
					'numberposts' => - 1,
					'order'       => 'ASC',
					'post_parent' => intval( $product_id ),
					'post_type'   => 'product_variation',
				);
				$variations = get_children( $args );

				if ( $variations ) {
					foreach ( $variations as $val ) {
						$arg = array(
							'ID'          => $val->ID,
							'post_author' => $seller_id,
						);
						wp_update_post( $arg );
					}
				}
			}

			$page_name = isset( $_REQUEST['page'] ) ? wc_clean( $_REQUEST['page'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$url       = 'admin.php?page=' . $page_name . '&success=2';

			wp_safe_redirect( admin_url( $url ) );
			exit( 0 );
		}

		/**
		 * Usort reorder.
		 *
		 * @param array $a First Argument.
		 * @param array $b Second Argument.
		 *
		 * @return float|int
		 */
		public function usort_reorder( $a, $b ) {
			$request_data = isset( $_REQUEST ) ? wc_clean( $_REQUEST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby      = ! empty( $request_data['orderby'] ) ? $request_data['orderby'] : 'product';
			$order        = ! empty( $request_data['order'] ) ? $request_data['order'] : 'desc';
			$result       = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : - $result;
		}
	}
}
