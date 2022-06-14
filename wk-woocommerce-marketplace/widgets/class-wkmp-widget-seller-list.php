<?php
/**
 * Marketplace email class
 *
 * @package Multi Vendor Marketplace
 *
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Widget_Seller_List' ) ) {

	/**
	 * Class WKMP_Widget_Seller_List
	 */
	class WKMP_Widget_Seller_List extends WP_Widget {
		/**
		 * WKMP_Widget_Seller_List constructor.
		 */
		public function __construct() {
			parent::__construct(
				'mp_marketplace-seller-widget',
				__( 'Marketplace Seller list', 'wk-marketplace' ),
				array(
					'classname'   => 'mp_marketplace_list',
					'description' => __( 'Display seller list.', 'wk-marketplace' ),
				)
			);
		}

		/**
		 * Widget.
		 *
		 * @param array $args Arguments.
		 * @param array $instance Widget instance.
		 */
		public function widget( $args, $instance ) {
			global $wkmarketplace, $wpdb;
			extract( $args );

			$list_count      = empty( $instance['list_count'] ) ? 10 : intval( $instance['list_count'], 10 );
			$no              = 0;
			$check_value     = empty( $instance['check_value'] ) ? false : $instance['check_value'];
			$value           = empty( $instance['value'] ) ? __( 'Seller List', 'wk-marketplace' ) : $instance['value'];
			$selected        = empty( $instance['selected'] ) ? 'nick' : $instance['selected'];
			$current_user_id = get_current_user_id();

			$seller_ids = $wpdb->get_results( "SELECT user_id FROM {$wpdb->prefix}mpsellerinfo WHERE seller_value='seller'", ARRAY_A );
			$seller_ids = wp_list_pluck( $seller_ids, 'user_id' );

			if ( is_array( $seller_ids ) && count( $seller_ids ) > 0 ) {
				echo "<div class='wkmp_seller'><h2>" . esc_html( $value ) . '</h2></div>';
				echo "<ul class='wkmp_sellermenu'>";

				foreach ( $seller_ids as $seller_id ) {
					$no ++;
					$shop_address = get_user_meta( $seller_id, 'shop_address', true );

					if ( intval( $seller_id ) === $current_user_id && 'yes' !== $check_value ) {
						continue;
					}

					if ( $seller_id < 2 ) { // Seller can't be admin.
						continue;
					}

					$name = '';

					if ( 'nick' === $selected ) {
						$seller_user = get_user_by( 'ID', $seller_id );
						$name        = $seller_user->user_nicename;
					} elseif ( 'full' === $selected ) {
						$fname = get_user_meta( $seller_id, 'first_name', true );
						$lname = get_user_meta( $seller_id, 'last_name', true );
						$name  = $fname . ' ' . $lname;
					} else {
						$name = get_user_meta( $seller_id, 'shop_name', true );
					}

					$name = trim( $name );
					if ( ! empty( $name ) ) {
						?>
						<li class="wkmp-selleritem">
							<a href="<?php echo esc_url( home_url( $wkmarketplace->seller_page_slug . '/' . get_option( '_wkmp_store_endpoint', 'store' ) . '/' . strtolower( $shop_address ) ) ); ?>">
								<?php echo esc_html( $name ); ?>
							</a>
						</li>
						<?php
					}
					if ( $no > $list_count ) {
						break;
					}
				}
				echo '</ul>';
			}
		}

		/**
		 * Update instance.
		 *
		 * @param array $new_instance New instance.
		 * @param array $old_instance Old instance.
		 *
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance                = $old_instance;
			$instance['value']       = empty( $new_instance['value'] ) ? __( 'Sellers List', 'wk-marketplace' ) : $new_instance['value'];
			$instance['check_value'] = empty( $new_instance['check_value'] ) ? false : $new_instance['check_value'];
			$instance['selected']    = empty( $new_instance['options'] ) ? 'nick' : $new_instance['options'];
			$instance['list_count']  = empty( $new_instance['list_count'] ) ? 10 : $new_instance['list_count'];

			return $instance;
		}

		/**
		 * Widget form.
		 *
		 * @param array $instance Instance.
		 *
		 * @return string|void
		 */
		public function form( $instance ) {
			$object   = array(
				'value'       => __( 'Seller list', 'wk-marketplace' ),
				'check_value' => false,
				'user_msg'    => __( 'Display Seller Including Current Seller?', 'wk-marketplace' ),
				'list_msg'    => __( 'Enter list Name', 'wk-marketplace' ),
				'name_option' => __( 'Show Seller list as:', 'wk-marketplace' ),
				'options'     => array(
					'nick'      => __( 'Nick Name', 'wk-marketplace' ),
					'full'      => __( 'Full Name', 'wk-marketplace' ),
					'shop_name' => __( 'Shop Name', 'wk-marketplace' ),
				),
				'selected'    => 'nick',
				'no_of_users' => __( 'No. of Users:', 'wk-marketplace' ),
				'list_count'  => 10,
			);
			$instance = wp_parse_args( (array) $instance, $object );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $instance['value'] ) ); ?>"><?php echo esc_html( $instance['list_msg'] ); ?></label>
				<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'value' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'value' ) ); ?>" style="width:100%" value="<?php echo esc_attr( $instance['value'] ); ?>">
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'check_value' ) ); ?>"><?php echo esc_html( $instance['user_msg'] ); ?></label>
				<input value="yes" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'check_value' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'check_value' ) ); ?>" <?php checked( 'yes', $instance['check_value'] ); ?>>
			</p>
			<p>
			<div><b><?php echo esc_html( $instance['name_option'] ); ?></b></div>
			<?php
			foreach ( $instance['options'] as $filed_key => $label ) {
				?>
				<input type="radio" id="<?php echo esc_attr( $this->get_field_id( $filed_key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'options' ) ); ?>" value="<?php echo esc_attr( $filed_key ); ?>" <?php echo checked( $filed_key, $instance['selected'] ); ?>>
				<label for="<?php echo esc_attr( $this->get_field_id( $filed_key ) ); ?>"><?php echo esc_html( $label ); ?></label>
				<?php
			}
			?>
			</p>
			<p>
				<b><label for="<?php echo esc_attr( $this->get_field_id( 'list_count' ) ); ?>"><?php echo esc_html( $instance['no_of_users'] ); ?></label></b>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'list_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'list_count' ) ); ?>" value="<?php echo esc_attr( $instance['list_count'] ); ?>">
			</p>
			<?php
		}
	}
}
