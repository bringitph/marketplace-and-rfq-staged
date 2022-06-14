<?php
/**
 * Seller Order List In Admin Dashboard
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Admin\Extension;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'MPExtensions' ) ) {
	/**
	 * Class for extension tab.
	 *
	 * Class WKMP_Extensions
	 *
	 * @package WkMarketplace\Templates\Admin\Extension
	 */
	class WKMP_Extensions {
		/**
		 * Constructor of the class.
		 *
		 * WKMP_Extensions constructor.
		 */
		public function __construct() {
			$this->show_extensions();
		}

		/**
		 * Displaying extensions list navigation.
		 */
		public function show_extensions() {
			$sections        = $this->get_extension_sections();
			$section         = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
			$current_section = empty( $section ) ? 'all' : $section;
			$section_title   = ( 'all' === $current_section ) ? esc_html__( 'Browse Extensions', 'wk-marketplace' ) : $sections[ $section ];
			?>
			<div class="wrap mp-extensions-wrap">
				<h1 class="wkmp-extension-title">
					<?php echo esc_html( $section_title ); ?>
				</h1>
				<?php $this->show_extensions_navigation( $sections, $current_section ); ?>
			</div>
			<?php
		}

		/**
		 * Get filter sections.
		 *
		 * @return mixed|void
		 */
		public function get_extension_sections() {
			$sections = array(
				'all'         => esc_html__( 'All', 'wk-marketplace' ),
				'wordpress'   => esc_html__( 'WordPress Extensions', 'wk-marketplace' ),
				'woocommerce' => esc_html__( 'WooCommerce Addons', 'wk-marketplace' ),
				'marketplace' => esc_html__( 'MarketPlace Addons', 'wk-marketplace' ),
				'featured'    => esc_html__( 'Featured Addons', 'wk-marketplace' ),
			);

			return apply_filters( 'marketplace_get_sections', $sections );
		}

		/**
		 * Adding sections navigation.
		 *
		 * @param array $sections Sections.
		 * @param int   $current_section Current Section.
		 */
		public function show_extensions_navigation( $sections, $current_section ) {
			$posted_data    = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$search_keyword = '';
			if ( isset( $posted_data['submit'] ) && isset( $posted_data['s'] ) && ! empty( $posted_data['s'] ) ) {
				$search_keyword = $posted_data['s'];
			}
			?>
			<div class="mp-extensions-submenu-wrap">
				<ul class="subsubsub mp-extensions-submenu">
					<?php
					foreach ( $sections as $id => $label ) {
						?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wk-marketplace-extensions&section=' . sanitize_title( $id ) ) ); ?>" class=" <?php echo ( $current_section === $id ) ? 'current' : ''; ?>"><?php echo esc_html( $label ); ?></a>
						</li>
						<?php
					}
					?>
				</ul>

				<form class="search-form search-mp-extensions" method="POST">
					<input type="hidden" name="tab" value="search">
					<label><span class="mp-extensions-search-text"><?php esc_html_e( 'Search Plugins', 'wk-marketplace' ); ?></span>
						<input type="search" name="s" value="<?php echo esc_attr( $search_keyword ); ?>" class="mp-extensions-filter" placeholder="<?php esc_attr_e( 'Search plugins...', 'wk-marketplace' ); ?>" aria-describedby="live-search-desc">
					</label>
					<input type="submit" id="mp-extensions-submit" name="submit" class="button hide-search-box" value="<?php esc_attr_e( 'Search Plugins', 'wk-marketplace' ); ?>"></form>
			</div>
			<br class="clear"/>
			<?php
			$this->show_extensions_per_section( $current_section, $search_keyword, $sections );
		}

		/**
		 * Showing all or section's extensions.
		 *
		 * @param string $section Section.
		 * @param string $search_keyword Search keyword.
		 * @param array  $sections Sections.
		 */
		public function show_extensions_per_section( $section, $search_keyword, $sections ) {
			$response    = $this->mp_extensions_call_api( $section );
			$addon_found = false;
			if ( isset( $response ) && ! empty( $response ) ) {
				if ( 'featured' === $section ) {
					$response = wp_list_filter( $response, array( 'type' => 'featured product' ) );
				}
				?>
				<div class="all-extensions">
					<?php
					foreach ( $response as $value ) {
						$extension_type = $value['type'];
						$extension_type = explode( ' ', $extension_type );
						$tab_name       = ( is_array( $extension_type ) && count( $extension_type ) > 0 ) ? $extension_type[0] : '';
						$tab_name       = array_key_exists( $tab_name, $sections ) ? $tab_name : '';
						$store_link     = isset( $value['link'] ) ? $value['link'] : '';
						$product_title  = isset( $value['name'] ) ? $value['name'] : 'NA';
						$image_src      = isset( $value['image'] ) ? $value['image'] : '';

						if ( ! empty( $search_keyword ) && false === stripos( $product_title, $search_keyword ) ) {
							continue;
						}
						$addon_found = true;
						?>
						<div class="mp-addons-block-item">
							<div class="mp-extension-block">
								<div class="over">
									<a class="over-link" href="<?php echo empty( $store_link ) ? 'javascript:void(0);' : esc_url( $store_link ); ?>" title="<?php echo esc_attr( $product_title ); ?>" target="_blank">
										<img class="backend-image" src="<?php echo esc_url( $image_src ); ?>" alt="product-1">
									</a>
									<div id="rollover" class="over-text">
										<h3>
											<a href="<?php echo empty( $store_link ) ? 'javascript:void(0);' : esc_url( $store_link ); ?>" title="<?php echo esc_attr( $product_title ); ?>" target="_blank"><?php echo esc_html( $product_title ); ?></a>
										</h3>

										<?php if ( 'all' === $section ) { ?>
											<h4 class="made-for"><?php esc_html_e( 'For-', 'wk-marketplace' ); ?></h4>
											<a class="made-for-link" href="<?php echo esc_url( admin_url( 'admin.php?page=wk-marketplace-extensions&section=' . $tab_name ) ); ?>" title="<?php echo esc_attr( $tab_name ); ?>"><?php echo esc_html( $tab_name ); ?></a>
											<br>
											<?php
										}
										?>
										<h4 class="made-for"><?php esc_html_e( 'By-', 'wk-marketplace' ); ?></h4>
										<a class="made-for-link" target="_blank" href="https://webkul.com/" title="webkul"><?php esc_html_e( 'Webkul', 'wk-marketplace' ); ?></a><br>
										<?php
										$now       = time();
										$your_date = strtotime( $value['date_updation'] );
										$datediff  = $now - $your_date;
										$days      = floor( $datediff / ( 60 * 60 * 24 ) );
										if ( 0 === $days ) {
											$days = 'today';
										} else {
											$days = $days . ' days ago';
										}
										?>
										<h4 class="last-updated-on"><?php esc_html_e( 'Last Updated -', 'wk-marketplace' ); ?> </h4>
										<p class="last-updated-on-date"><?php echo esc_html( $days ); ?></p><br>
										<h4 class="last-updated-on"><?php esc_html_e( 'Compatible -', 'wk-marketplace' ); ?> </h4>
										<p class="last-updated-on-date"><?php esc_html_e( 'WordPress -', 'wk-marketplace' ); ?><?php echo esc_html( $value['compatible'] ); ?></p>
									</div>
								</div>
								<div class="mp-extension-bottom">
									<div class="last-updated">
										<p><?php echo esc_html( $value['about'] ); ?></p>
									</div>
									<div class="buy-now-class">
										<p class="link-extension">
											<a href="<?php echo esc_url( $value['link_doc'] ); ?>" id="doc" target="_blank" title="<?php esc_attr_e( 'Read Doc', 'wk-marketplace' ); ?>" class="button-mp-fadded button-primary-mp-fadded"><?php esc_html_e( 'Read Doc', 'wk-marketplace' ); ?></a>
										</p>
										<p class="link-extension">
											<a href="<?php echo empty( $store_link ) ? 'javascript:void(0);' : esc_url( $store_link ); ?>" id="submit" target="_blank" title="<?php esc_attr_e( 'Buy Now', 'wk-marketplace' ); ?>" class="button-mp button-primary-mp"><?php echo sprintf( /* translators: %s: Price. */ esc_html__( 'Buy Now -$%s', 'wk-marketplace' ), esc_attr( $value['price'] ) ); ?></a>
										</p>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			if ( ! $addon_found ) {
				$message = esc_html__( 'No Extensions Found.', 'wk-marketplace' );
				if ( ! empty( $search_keyword ) ) {
					$message = sprintf( /* translators: %s: Search keyword. */ esc_html__( 'No Extensions Found with keyword "%s"', 'wk-marketplace' ), $search_keyword );
				}
				?>
				<h1><b><?php echo esc_html( $message ); ?></b></h1>
				<?php
			}
		}

		/**
		 * Preparing url for call API.
		 *
		 * @param string $section Section.
		 *
		 * @return bool|string
		 */
		public function mp_extensions_call_api( $section ) {
			$method = 'GET';
			$url    = 'http://wordpressdemo.webkul.com/xtremo-marketplace-theme/wp-json/webkul/v1/extensions';
			if ( 'all' !== $section && 'featured' !== $section ) {
				$url .= '/' . $section;
			}

			$response = $this->wkmp_call_api( $method, $url );
			$result   = json_decode( $response, true );

			if ( is_array( $result ) && count( $result ) > 0 && isset( $result['data'] ) && isset( $result['data']['status'] ) && 200 !== $result['data']['status'] ) {
				$result = array();
			}

			return $result;
		}

		/**
		 * Making API call to get extensions.
		 *
		 * @param string $method Method.
		 * @param string $url URL.
		 * @param array  $data Data.
		 *
		 * @return bool|string
		 */
		public function wkmp_call_api( $method, $url, $data = false ) {
			$curl = curl_init();

			switch ( $method ) {
				case 'POST':
					curl_setopt( $curl, CURLOPT_POST, 1 );

					if ( $data ) {
						curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
					}
					break;
				case 'PUT':
					curl_setopt( $curl, CURLOPT_PUT, 1 );
					break;
				default:
					if ( $data ) {
						$url = sprintf( '%s?%s', $url, http_build_query( $data ) );
					}
			}

			// Optional Authentication.
			curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $curl, CURLOPT_USERPWD, 'username:password' );

			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

			$result = curl_exec( $curl );
			curl_close( $curl );

			return $result;
		}
	}
}
