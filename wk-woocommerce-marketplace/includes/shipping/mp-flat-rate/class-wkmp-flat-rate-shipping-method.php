<?php
/**
 * Class WKMP_Flat_Rate_Shipping_Method file.
 *
 * @package Multi Vendor Marketplace
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WKMP_Flat_Rate_Shipping_Method' ) ) {
	/**
	 * Class WKMP_Flat_Rate_Shipping_Method
	 */
	class WKMP_Flat_Rate_Shipping_Method extends WC_Shipping_Method {
		/**
		 * WKMP_Flat_Rate_Shipping_Method constructor.
		 *
		 * @param int $instance_id instance id.
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'mp_flat_rate';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = esc_html__( 'Marketplace Flat Rate Shipping', 'wk-marketplace' );
			$this->method_description = esc_html__( 'Custom Flat Rate Shipping Method for WooCommerce Marketplace Plugin', 'wk-marketplace' );

			// Load the settings.
			$this->availability = 'including';
			$this->init_form_fields();
			$this->instance_form_fields = $this->wkmp_get_flat_rate_settings();
			$this->init_settings();

			$this->supports = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);

			// Define user set variables.
			$this->enabled = $this->get_option( 'enabled' );
			$this->title   = $this->get_option( 'title' );

			add_filter( 'woocommerce_package_rates', array( $this, 'wc_mp_flat_rate_handler' ), 1, 2 );
		}

		/**
		 * Function to iterate through all packages.
		 *
		 * @param array $rates rates array.
		 * @param array $package package rates.
		 */
		public function wc_mp_flat_rate_handler( $rates, $package ) {
			$seller_ids               = array();
			$matching_zone_ids        = array();
			$ids_supported_methods    = array();
			$allowed_shipping_methods = array( 0 );

			foreach ( $package['contents'] as $values ) {
				$product_id = $values['product_id'];
				$seller_id  = get_post_field( 'post_author', $product_id );

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$seller_id = $values[ "assigned-seller-$product_id" ];
				}

				if ( ! in_array( $seller_id, $seller_ids, true ) ) {
					$seller_ids[] = $seller_id;
				}
			}

			$seller_ids = apply_filters( 'wkmp_shipping_seller_id', $seller_ids, $this->id );

			$country   = strtoupper( wc_clean( $package['destination']['country'] ) );
			$state     = strtoupper( wc_clean( $package['destination']['state'] ) );
			$postcode  = wc_normalize_postcode( wc_clean( $package['destination']['postcode'] ) );
			$cache_key = WC_Cache_Helper::get_cache_prefix( 'shipping_zones' ) . 'wc_shipping_zone_' . md5( sprintf( '%s+%s+%s', $country, $state, $postcode ) );
			wp_cache_delete( $cache_key, 'shipping_zones' );

			$matching_zone_id = wp_cache_get( $cache_key, 'shipping_zones' );

			if ( 1 === count( $seller_ids ) ) {
				if ( false === $matching_zone_id ) {
					$matching_zone_id = $this->get_zone_id_from_package( $package, $seller_ids[0] );
					wp_cache_set( $cache_key, $matching_zone_id, 'shipping_zones' );
				}
			} else {
				foreach ( $seller_ids as $s_value ) {
					$matching_zone_id = $this->get_zone_id_from_package( $package, $s_value );
					if ( null !== $matching_zone_id ) {
						wp_cache_set( $cache_key, $matching_zone_id, 'shipping_zones' );
						$matching_zone_ids[] = $matching_zone_id;
					}
				}
			}

			if ( ! empty( $matching_zone_ids ) && count( $matching_zone_ids ) > 1 ) {
				foreach ( $matching_zone_ids as $mz_value ) {
					$ids_zone             = new WC_Shipping_Zone( $mz_value ? $mz_value : 0 );
					$ids_shipping_methods = $ids_zone->get_shipping_methods( true );
					foreach ( $ids_shipping_methods as $ids_value ) {
						$ids_supported_methods[ $mz_value ][] = $ids_value->id;
					}
				}
				if ( count( $ids_supported_methods ) > 1 ) {
					$allowed_shipping_methods = call_user_func_array( 'array_intersect', $ids_supported_methods );
				} else {
					$allowed_shipping_methods = reset( $ids_supported_methods );
				}
			}

			if ( empty( $matching_zone_id ) ) {
				$matching_zone_id = 0;
			}

			$zone             = new WC_Shipping_Zone( $matching_zone_id );
			$shipping_methods = $zone->get_shipping_methods( true );

			foreach ( $shipping_methods as $shipping_method ) {

				if ( ! empty( $allowed_shipping_methods ) && ! in_array( $shipping_method->id, $allowed_shipping_methods, true ) ) {
					continue;
				} elseif ( ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() ) ) {
					$package['rates'] = $package['rates'] + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys
				}
			}

			return $package['rates'];
		}

		/**
		 * Function for getting zone id.
		 *
		 * @param array $package package array.
		 * @param array $seller_ids seller ids.
		 */
		public function get_zone_id_from_package( $package, $seller_ids = array() ) {
			global $wpdb;
			$seller_ids = is_array( $seller_ids ) ? $seller_ids : array( $seller_ids );
			$country    = strtoupper( wc_clean( $package['destination']['country'] ) );
			$state      = strtoupper( wc_clean( $package['destination']['state'] ) );
			$continent  = strtoupper( wc_clean( WC()->countries->get_continent_code_for_country( $country ) ) );
			$postcode   = wc_normalize_postcode( wc_clean( $package['destination']['postcode'] ) );

			// Work out criteria for our zone search.
			$criteria   = array();
			$criteria[] = $wpdb->prepare( "( ( location_type = 'country' AND location_code = %s )", $country );
			$criteria[] = $wpdb->prepare( "OR ( location_type = 'state' AND location_code = %s )", $country . ':' . $state );
			$criteria[] = $wpdb->prepare( "OR ( location_type = 'continent' AND location_code = %s )", $continent );
			$criteria[] = 'OR ( location_type IS NULL ) )';

			// Postcode range and wildcard matching.
			$postcode_locations = $wpdb->get_results( "SELECT zone_id, location_code FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_type = 'postcode'" );

			if ( $postcode_locations ) {
				$zone_ids_with_postcode_rules = array_map( 'absint', wp_list_pluck( $postcode_locations, 'zone_id' ) );
				$matches                      = wc_postcode_location_matcher( $postcode, $postcode_locations, 'zone_id', 'location_code', $country );
				$do_not_match                 = array_unique( array_diff( $zone_ids_with_postcode_rules, array_keys( $matches ) ) );

				if ( ! empty( $do_not_match ) ) {
					$criteria[] = 'AND zones.zone_id NOT IN (' . implode( ',', $do_not_match ) . ')';
				}
			}

			$seller_id = count( $seller_ids ) > 0 ? $seller_ids[0] : 0;

			// Get matching zones.
			return $wpdb->get_var( "SELECT zones.zone_id FROM {$wpdb->prefix}woocommerce_shipping_zones as zones LEFT OUTER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as locations ON zones.zone_id = locations.zone_id AND location_type != 'postcode' JOIN {$wpdb->prefix}mpseller_meta as my_zones on zones.zone_id = my_zones.zone_id and my_zones.seller_id= $seller_id WHERE " . implode( ' ', $criteria ) . ' ORDER BY zone_order ASC LIMIT 1 ' );
		}

		/**
		 * Marketplace Flat Rate Form Fields goes here.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => esc_html__( 'Enable/Disable', 'wk-marketplace' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable WooCommerce Marketplace Flat Rate Shipping', 'wk-marketplace' ),
					'default' => 'yes',
				),
				'title'   => array(
					'title'       => esc_html__( 'Marketplace Flat Rate Shipping', 'wk-marketplace' ),
					'type'        => 'text',
					'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
					'default'     => esc_html__( 'Marketplace Flat Rate Shipping', 'wk-marketplace' ),
				),
			);
		}

		/**
		 * Calculate shipping function.
		 *
		 * @param array $package packages array.
		 */
		public function calculate_shipping( $package = array() ) {
			$cost            = 0;
			$ids             = array();
			$instance_id_arr = array();

			if ( 'yes' === $this->enabled && ! empty( $package['contents'] ) ) {
				foreach ( $package['contents'] as $values ) {
					$product_id     = $values['product_id'];
					$seller_details = get_post_field( 'post_author', $product_id );

					if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
						$seller_details = $values[ "assigned-seller-$product_id" ];
					}

					$check          = get_option( 'wkmp_shipping_option', 'marketplace' );
					$seller_ids     = apply_filters( 'wkmp_shipping_seller_id', $seller_details, $this->id );
					$seller_details = $seller_ids;

					if ( ! empty( $seller_details ) ) {
						$seller_zones     = $this->get_zone_id_from_package( $package, $seller_details );
						$method           = false;
						$zone             = new WC_Shipping_Zone( $seller_zones ? $seller_zones : 0 );
						$shipping_methods = $zone->get_shipping_methods( true );

						foreach ( $shipping_methods as $sm_key => $sm_value ) {
							if ( $this->id === $sm_value->id ) {
								if ( empty( $instance_id_arr ) || ! in_array( $sm_key, $instance_id_arr, true ) ) {
									$instance_id_arr[] = $sm_key;
								}
								$method = true;
							}
						}
					}

					if ( 'marketplace' !== $check && empty( $seller_zones ) ) {
						$seller_zones = 0;
					}

					if ( false === $method ) {
						$cost = 0;
						break;
					} else {
						if ( isset( $seller_zones ) ) {
							$zones   = new WC_Shipping_Zone( $seller_zones );
							$methods = $zones->get_shipping_methods();

							if ( 'marketplace' !== $check ) {
								if ( ! empty( $methods ) ) {
									foreach ( $methods as $shipping_method ) {
										if ( $this->id === $shipping_method->id ) {
											$eval_cost = $this->evaluate_cost(
												$shipping_method->instance_settings['cost'],
												array(
													'qty'  => $this->get_package_item_qty( $package, $seller_details ),
													'cost' => $this->get_cart_content_total( $package, $seller_details ),
												)
											);
											$cost     += $eval_cost;
										}
									}
								}
								break;

							} elseif ( ! empty( $methods ) && isset( $methods ) ) {
								$seller_details = $seller_details[0];
								foreach ( $methods as $shipping_method ) {
									if ( $this->id === $shipping_method->id && ! in_array( $seller_details, $ids, true ) ) {
										if ( isset( $shipping_method->instance_settings['cost'] ) ) {
											$eval_cost = $this->evaluate_cost(
												$shipping_method->instance_settings['cost'],
												array(
													'qty'  => $this->get_package_item_qty( $package, $seller_details ),
													'cost' => $this->get_cart_content_total( $package, $seller_details ),
												)
											);

											$cost += $eval_cost;

											if ( empty( $seller_zones ) && 'marketplace' !== $check ) {
												$this->title = $shipping_method->title;
											}

											$ses_obj            = WC()->session->get( 'shipping_sess_cost', array() );
											$shipping_cost_list = WC()->session->get( 'shipping_cost_list', array() );

											$ses_obj[ $seller_details ] = array(
												'cost'  => $eval_cost,
												'title' => $shipping_method->id,
											);

											$shipping_cost_list[ $shipping_method->id ][ $seller_details ] = array(
												'cost'  => $eval_cost,
												'title' => $shipping_method->id,
											);

											WC()->session->set( 'shipping_sess_cost', $ses_obj );
											WC()->session->set( 'shipping_cost_list', $shipping_cost_list );
										}
									}
								}
							} else {
								$cost = 0;
							}
						} else {
							$cost = 0;
						}
					}

					$ids[] = $seller_details;
				}

				$shipping_classes = WC()->shipping->get_shipping_classes();

				if ( ! empty( $shipping_classes ) ) {

					$found_shipping_classes = $this->find_shipping_classes( $package );
					$highest_class_cost     = 0;
					$seller_class_cost      = array();

					if ( ! empty( $instance_id_arr ) ) {
						foreach ( $found_shipping_classes as $shipping_class => $products ) {
							$class_seller_id = 0;

							foreach ( $instance_id_arr as $instance_id ) {

								// Also handles BW compatibility when slugs were used instead of ids.
								$shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );

								$options = get_option( 'woocommerce_' . $this->id . '_' . $instance_id . '_settings', array() );

								$type = '';
								if ( ! empty( $options ) && isset( $options['type'] ) ) {
									$type = $options['type'];
								}

								$class_cost_string = '';
								if ( ! empty( $options ) && ! empty( $shipping_class_term ) && isset( $options[ 'class_cost_' . $shipping_class_term->term_id ] ) ) {
									$class_cost_string = $options[ 'class_cost_' . $shipping_class_term->term_id ];
								}

								if ( ! empty( $options ) && empty( $class_cost_string ) && empty( $shipping_class_term ) && 'no_class_cost' === $shipping_class && ! empty( $options[ $shipping_class ] ) ) {
									$class_cost_string = $options[ $shipping_class ];
								}

								if ( '' === $class_cost_string ) {
									continue;
								}

								$class_cost = $this->evaluate_cost(
									$class_cost_string,
									array(
										'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
										'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
									)
								);

								$sell_product_ids = wp_list_pluck( $products, 'product_id' );
								$first_product_id = ( is_iterable( $sell_product_ids ) && count( $sell_product_ids ) > 0 ) ? $sell_product_ids[ key( $sell_product_ids ) ] : 0;
								$class_seller_id  = $first_product_id > 0 ? get_post_field( 'post_author', $first_product_id ) : 0;

								$seller_class_cost[ $class_seller_id ] = isset( $seller_class_cost[ $class_seller_id ] ) ? $seller_class_cost[ $class_seller_id ] : 0;

								if ( 'class' === $type ) {
									$cost                                  += $class_cost;
									$seller_class_cost[ $class_seller_id ] += $class_cost;
								} else {
									$highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
								}
							}

							if ( 'order' === $type && $highest_class_cost ) {
								$cost                                  += $highest_class_cost;
								$seller_class_cost[ $class_seller_id ] += $highest_class_cost;
							}
						}

						$ses_obj            = WC()->session->get( 'shipping_sess_cost', array() );
						$shipping_cost_list = WC()->session->get( 'shipping_cost_list', array() );

						foreach ( array_keys( $ses_obj ) as $sel_id ) {
							if ( array_key_exists( $sel_id, $seller_class_cost ) ) {
								$prev_cost                  = floatval( $ses_obj[ $sel_id ]['cost'] );
								$ses_obj[ $sel_id ]['cost'] = $prev_cost + floatval( $seller_class_cost[ $sel_id ] );
							}
						}

						foreach ( $shipping_cost_list as $ship_method => $ship_data ) {
							if ( $this->id === $ship_method ) {
								foreach ( $ship_data as $sel_id => $sel_data ) {
									$prev_cost = floatval( $sel_data['cost'] );
									if ( array_key_exists( $sel_id, $seller_class_cost ) ) {
										$shipping_cost_list[ $ship_method ][ $sel_id ]['cost'] = $prev_cost + floatval( $seller_class_cost[ $sel_id ] );
									}
								}
							}
						}

						WC()->session->set( 'shipping_sess_cost', $ses_obj );
						WC()->session->set( 'shipping_cost_list', $shipping_cost_list );
					}
				}

				if ( $cost > 0 ) {
					// Send the final rate to the user.
					$rate = array(
						'id'    => $this->id,
						'label' => $this->title,
						'cost'  => $cost,
					);

					$this->add_rate( $rate );
				}
			}
		}

		/**
		 * Finds and returns shipping classes and the products with said class.
		 *
		 * @param array $package Shipping package.
		 *
		 * @return array
		 */
		public function find_shipping_classes( $package ) {
			$found_shipping_classes = array();

			foreach ( $package['contents'] as $item_id => $values ) {
				if ( $values['data']->needs_shipping() ) {
					$found_class = $values['data']->get_shipping_class();
					$found_class = empty( $found_class ) ? 'no_class_cost' : $found_class;

					if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
						$found_shipping_classes[ $found_class ] = array();
					}
					$found_shipping_classes[ $found_class ][ $item_id ] = $values;
				}
			}

			return $found_shipping_classes;
		}

		/**
		 * Get items in package.
		 *
		 * @param array $package Package information.
		 * @param array $seller_id Seller id.
		 *
		 * @return int
		 */
		public function get_package_item_qty( $package, $seller_id ) {
			$total_quantity = 0;
			foreach ( $package['contents'] as $values ) {
				$product_id = $values['product_id'];

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$item_seller_id = $values[ "assigned-seller-$product_id" ];
				} else {
					$item_seller_id = get_post_field( 'post_author', $product_id );
				}

				$item_seller_id = apply_filters( 'wkmp_shipping_seller_id', $item_seller_id, $this->id );
				$item_seller_id = $item_seller_id[0];

				if ( (int) $seller_id === (int) $item_seller_id && $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
					$total_quantity += $values['quantity'];
				}
			}

			return $total_quantity;
		}

		/**
		 * Get items in package.
		 *
		 * @param array $package Package information.
		 * @param array $seller_id Seller id.
		 *
		 * @return int
		 */
		public function get_cart_content_total( $package, $seller_id ) {
			$total_cost = 0;
			foreach ( $package['contents'] as $values ) {
				$product_id = $values['product_id'];

				if ( isset( $values[ "assigned-seller-$product_id" ] ) ) {
					$item_seller_id = $values[ "assigned-seller-$product_id" ];
				} else {
					$item_seller_id = get_post_field( 'post_author', $product_id );
				}

				$item_seller_id = apply_filters( 'wkmp_shipping_seller_id', $item_seller_id, $this->id );
				$item_seller_id = $item_seller_id[0];

				if ( (int) $seller_id === (int) $item_seller_id && $values['line_total'] > 0 && $values['data']->needs_shipping() ) {
					$total_cost += $values['line_total'];
				}
			}

			return $total_cost;
		}

		/**
		 * Work out fee (shortcode).
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 */
		public function fee( $atts ) {
			$atts = shortcode_atts(
				array(
					'percent' => '',
					'min_fee' => '',
				),
				$atts,
				'fee'
			);

			$calculated_fee = 0;

			if ( $atts['percent'] ) {
				$calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
			}

			if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
				$calculated_fee = $atts['min_fee'];
			}

			return $calculated_fee;
		}

		/**
		 * Evaluate a cost from a sum/string.
		 *
		 * @param string $sum Sum to evaluate.
		 * @param array  $args Arguments.
		 *
		 * @return string
		 */
		protected function evaluate_cost( $sum, $args = array() ) {
			include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

			$locale   = localeconv();
			$decimals = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

			$this->fee_cost = $args['cost'];
			// Expand shortcodes.
			add_shortcode( 'fee', array( $this, 'fee' ) );

			$sum = do_shortcode(
				str_replace(
					array(
						'[qty]',
						'[cost]',
					),
					array(
						$args['qty'],
						$args['cost'],
					),
					$sum
				)
			);

			remove_shortcode( 'fee', array( $this, 'fee' ) );

			// Remove whitespace from string.
			$sum = preg_replace( '/\s+/', '', $sum );

			// Remove locale from string.
			$sum = str_replace( $decimals, '.', $sum );

			// Trim invalid start/end characters.
			$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

			// Do the math.
			return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
		}

		/**
		 * Retuning the setting array.
		 *
		 * @return array
		 */
		public function wkmp_get_flat_rate_settings() {
			global $wpdb;
			$settings = array();

			$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. ', 'wk-marketplace' ) . '<code>10.00 * [qty]</code>.<br/><br/>' . __( 'Use ', 'wk-marketplace' ) . '<code>[qty]</code>' . __( ' for the number of items, ', 'wk-marketplace' ) . '<br/><code>[cost]</code>' . __( ' for the total cost of items, and ', 'wk-marketplace' ) . '<code>[fee percent="10" min_fee="20" max_fee=""]</code>' . __( ' for percentage based fees.', 'wk-marketplace' );

			if ( is_admin() ) {
				$settings = array_merge(
					$settings,
					array(
						'title' => array(
							'title'       => esc_html__( 'Marketplace Flat Rate Shipping', 'wk-marketplace' ),
							'type'        => 'text',
							'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
							'default'     => esc_html__( 'Marketplace Flat Rate Shipping', 'wk-marketplace' ),
						),
					)
				);
			}

			$user_id = 0;
			$zone_id = filter_input( INPUT_GET, 'zone_id', FILTER_SANITIZE_NUMBER_INT );
			if ( $zone_id > 0 ) {
				$seller = $wpdb->get_row( $wpdb->prepare( "SELECT seller_id FROM {$wpdb->prefix}mpseller_meta where zone_id=%d", intval( $zone_id ) ) );
				if ( ! empty( $seller ) ) {
					$user_id = isset( $seller->seller_id ) ? $seller->seller_id : 0;
				}
			}

			$user_id = ( $user_id > 0 ) ? $user_id : get_current_user_id();

			$user_shipping_classes = get_user_meta( $user_id, 'shipping-classes', true );

			if ( ! empty( $user_shipping_classes ) ) {
				$u_shipping_classes = maybe_unserialize( $user_shipping_classes );
			}

			$settings = array_merge(
				$settings,
				array(
					'enabled'    => array(
						'title'   => esc_html__( 'Enable/Disable', 'wk-marketplace' ),
						'type'    => 'checkbox',
						'label'   => esc_html__( 'Enable WooCommerce Marketplace Flat Rate Shipping', 'wk-marketplace' ),
						'default' => 'yes',
					),
					'title'      => array(
						'title'       => __( 'Title', 'wk-marketplace' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'wk-marketplace' ),
						'default'     => $this->method_title,
						'desc_tip'    => true,
					),
					'tax_status' => array(
						'title'   => esc_html__( 'Tax status', 'wk-marketplace' ),
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'default' => 'taxable',
						'options' => array(
							'taxable' => esc_html__( 'Taxable', 'wk-marketplace' ),
							'none'    => _x( 'None', 'Tax status', 'wk-marketplace' ),
						),
					),
					'cost'       => array(
						'title'       => esc_html__( 'Cost', 'wk-marketplace' ),
						'type'        => 'text',
						'placeholder' => '',
						'description' => $cost_desc,
						'default'     => '0',
						'desc_tip'    => true,
					),
				)
			);

			$shipping_classes = WC()->shipping->get_shipping_classes();

			if ( ! empty( $shipping_classes ) ) {
				if ( is_admin() ) {
					$settings['class_costs'] = array(
						'title'       => __( 'Shipping class costs', 'wk-marketplace' ),
						'type'        => 'title',
						'default'     => '',
						'description' => sprintf( /* translators: %s Shipping URL. */ __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'wk-marketplace' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
					);
				} else {
					$settings['class_costs'] = array(
						'title'       => __( 'Shipping class costs', 'wk-marketplace' ),
						'type'        => 'title',
						'default'     => '',
						'description' => __( 'These costs can optionally be added based on the product shipping class', 'wk-marketplace' ),
					);
				}

				foreach ( $shipping_classes as $shipping_class ) {

					if ( ! isset( $shipping_class->term_id ) ) {
						continue;
					}

					if ( user_can( $user_id, 'administrator' ) || ( ! empty( $u_shipping_classes ) && in_array( $shipping_class->term_id, $u_shipping_classes, true ) ) ) :

						$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
							/* translators: %s: shipping class name */
							'title'       => sprintf( __( '"%s" shipping class cost', 'wk-marketplace' ), esc_html( $shipping_class->name ) ),
							'type'        => 'text',
							'placeholder' => __( 'N/A', 'wk-marketplace' ),
							'description' => $cost_desc,
							'default'     => $this->get_option( 'class_cost_' . $shipping_class->slug ),
							'desc_tip'    => true,
						);

					endif;
				}

				$settings['no_class_cost'] = array(
					'title'       => __( 'No shipping class cost', 'wk-marketplace' ),
					'type'        => 'text',
					'placeholder' => __( 'N/A', 'wk-marketplace' ),
					'description' => $cost_desc,
					'default'     => '',
					'desc_tip'    => true,
				);
				$settings['type']          = array(
					'title'   => __( 'Calculation type', 'wk-marketplace' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'default' => 'class',
					'options' => array(
						'class' => __( 'Per class: Charge shipping for each shipping class individually', 'wk-marketplace' ),
						'order' => __( 'Per order: Charge shipping for the most expensive shipping class', 'wk-marketplace' ),
					),
				);
			}

			return $settings;
		}
	}
}
