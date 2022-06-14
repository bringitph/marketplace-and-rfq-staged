<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Product;

use mysql_xdevapi\Exception;
use WkMarketplace\Helper\Front;
use WkMarketplace\Includes\WKMarketplace;
use WP_STATISTICS\category_page;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Product_Form' ) ) {
	/**
	 * Seller Add / Edit Product class.
	 *
	 * Class WKMP_Product_Form
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Product
	 */
	class WKMP_Product_Form {
		/**
		 * Product id.
		 *
		 * @var int $product_id Product id.
		 */
		protected $product_id;

		/**
		 * Seller id.
		 *
		 * @var int $seller_id Seller id.
		 */
		protected $seller_id;

		/**
		 * WPDB Object.
		 *
		 * @var \QM_DB|\wpdb
		 */
		protected $wpdb;

		/**
		 * Marketplace class object.
		 *
		 * @var $wkmarketplace WKMarketplace.
		 */
		protected $wkmarketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Product_Form constructor.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function __construct( $seller_id = 0 ) {
			global $wkmarketplace, $wpdb;

			$this->wkmarketplace = $wkmarketplace;
			$this->wpdb          = $wpdb;

			$this->product_id = filter_input( INPUT_GET, 'wkmp_product_edit', FILTER_SANITIZE_NUMBER_INT );
			$this->seller_id  = intval( $seller_id );

			$this->wkmp_product_form();
		}

		/**
		 * Display add product form.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function wkmp_product_form() {
			$categories = array();
			$wpdb_obj   = $this->wpdb;

			$allowed_cat         = get_user_meta( $this->seller_id, 'wkmp_seller_allowed_categories', true );
			$dynamic_sku_enabled = get_user_meta( $this->seller_id, '_wkmp_enable_seller_dynamic_sku', true );
			$dynamic_sku_prefix  = get_user_meta( $this->seller_id, '_wkmp_dynamic_sku_prefix', true );

			if ( ! $allowed_cat ) {
				$allowed_cat = get_option( '_wkmp_seller_allowed_categories', array() );
			}

			$product_categories = wp_dropdown_categories(
				array(
					'show_option_none' => '',
					'hierarchical'     => 1,
					'hide_empty'       => 0,
					'name'             => 'product_cate[]',
					'id'               => 'mp_seller_product_categories',
					'taxonomy'         => 'product_cat',
					'title_li'         => '',
					'orderby'          => 'name',
					'order'            => 'ASC',
					'class'            => '',
					'exclude'          => '',
					'selected'         => $categories,
					'echo'             => 0,
					'value_field'      => 'slug',
					'walker'           => new WKMP_Category_Filter( $allowed_cat ),
				)
			);

			$this->wkmp_marketplace_media_fix();

			if ( $this->product_id > 0 || isset( $_POST['add_product_sub'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$this->wkmp_product_add_update();

				if ( $this->product_id > 0 ) {
					$wk_pro_id = $this->product_id;

					$categories         = wp_get_post_terms( $wk_pro_id, 'product_cat', array( 'fields' => 'slugs' ) );
					$product_categories = wp_dropdown_categories(
						array(
							'show_option_none' => '',
							'hierarchical'     => 1,
							'hide_empty'       => 0,
							'name'             => 'product_cate[]',
							'id'               => 'mp_seller_product_categories',
							'taxonomy'         => 'product_cat',
							'title_li'         => '',
							'orderby'          => 'name',
							'order'            => 'ASC',
							'class'            => '',
							'exclude'          => '',
							'selected'         => $categories,
							'echo'             => 0,
							'value_field'      => 'slug',
							'walker'           => new WKMP_Category_Filter( $allowed_cat ),
						)
					);

					$product_auth  = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT post_author FROM {$wpdb_obj->prefix}posts WHERE ID = %s", $this->product_id ) );
					$post_row_data = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}posts WHERE ID = %s", $this->product_id ) );
					$product_array = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM {$wpdb_obj->prefix}posts WHERE post_type = 'product' AND post_status = 'publish' AND post_author = %d ORDER BY ID DESC", $this->seller_id ) );

					require_once __DIR__ . '/wkmp-edit-product.php';
				}
			} else {
				require_once __DIR__ . '/wkmp-add-product.php';
			}
		}

		/**
		 * Add product category.
		 *
		 * @param int $cat_id Category id.
		 * @param int $post_id Post id.
		 */
		public function wkmp_add_pro_category( $cat_id, $post_id ) {
			if ( strpos( $cat_id, ',' ) ) {
				$cat_id = explode( ',', $cat_id );
				wp_set_object_terms( $post_id, $cat_id, 'product_cat' );
			} else {
				$term = get_term_by( 'slug', $cat_id, 'product_cat' );
				wp_set_object_terms( $post_id, $term->term_id, 'product_cat' );
			}
		}

		/**
		 * Update product category.
		 *
		 * @param int $cat_id Category id.
		 * @param int $postid Post id.
		 *
		 * @return void
		 */
		public function wkmp_update_pro_category( $cat_id, $postid ) {
			if ( is_array( $cat_id ) && array_key_exists( '1', $cat_id ) ) {
				wp_set_object_terms( $postid, $cat_id, 'product_cat' );
			} elseif ( is_array( $cat_id ) ) {
				$term = get_term_by( 'slug', $cat_id[0], 'product_cat' );
				wp_set_object_terms( $postid, $term->term_id, 'product_cat' );
			}
		}

		/**
		 * Get product image.
		 *
		 * @param int    $pro_id int prod id.
		 * @param string $meta_value meta value.
		 *
		 * @return string $product_image
		 */
		public function wkmp_get_product_image( $pro_id, $meta_value ) {
			$p = get_post_meta( $pro_id, $meta_value, true );
			if ( is_null( $p ) ) {
				return '';
			}

			return get_post_meta( $p, '_wp_attached_file', true );
		}

		/**
		 * Marketplace media fix.
		 *
		 * @param string $post_id Post Id.
		 */
		public function wkmp_marketplace_media_fix( $post_id = '' ) {
			global $frontier_post_id, $post_ID;
			$frontier_post_id = $post_id;
			add_filter( 'media_view_settings', array( $this, 'wkmp_marketplace_media_fix_filter' ), 10, 2 );
		}

		/**
		 * Fix insert media editor button filter.
		 *
		 * @param array $settings setting array.
		 * @param int   $post post.
		 */
		public function wkmp_marketplace_media_fix_filter( $settings, $post ) {
			global $frontier_post_id;
			$settings['post']['id'] = $frontier_post_id;

			return $settings;
		}

		/**
		 * Display attribute variations
		 *
		 * @param int $var_id variable id.
		 *
		 * @return void
		 */
		public function wkmp_attributes_variation( $var_id ) {
			$wk_pro_id = $var_id;
			$args      = array(
				'post_parent'    => $wk_pro_id,
				'post_type'      => 'product_variation',
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$children_array = get_children( $args );

			$i = 0;

			foreach ( $children_array as $var_att ) {
				$this->wkmp_attribute_variation_data( $var_att->ID, $wk_pro_id );
				$i ++;
			}
		}

		/**
		 * Include variations HTML
		 *
		 * @param int $var_id variable id.
		 * @param int $wk_pro_id variable id.
		 *
		 * @return void
		 */
		public function wkmp_attribute_variation_data( $var_id, $wk_pro_id ) {
			require __DIR__ . '/wkmp-variations.php';
		}

		/**
		 * WordPress text input
		 *
		 * @param int $field Field.
		 * @param int $wk_pro_id Product id.
		 *
		 * @return void
		 */
		public function wkmp_wp_text_input( $field, $wk_pro_id ) {
			global $post;

			$the_post_id            = empty( $wk_pro_id ) ? $post->ID : $wk_pro_id;
			$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
			$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
			$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
			$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
			$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $the_post_id, $field['id'], true );
			$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
			$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

			switch ( $data_type ) {
				case 'price':
					$field['class'] .= ' wc_input_price';

					$field['value'] = wc_format_localized_price( $field['value'] );
					break;
				case 'decimal':
					$field['class'] .= ' wc_input_decimal';

					$field['value'] = wc_format_localized_decimal( $field['value'] );
					break;
				case 'stock':
					$field['class'] .= ' wc_input_stock';

					$field['value'] = wc_stock_amount( $field['value'] );
					break;
				case 'url':
					$field['class'] .= ' wc_input_url';

					$field['value'] = esc_url( $field['value'] );
					break;
				default:
					break;
			}

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
				foreach ( $field['custom_attributes'] as $attribute => $value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
				}
			}

			$custom_attributes = implode( ' ', $custom_attributes );

			echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . esc_attr( $custom_attributes ) . ' /> ';

			if ( ! empty( $field['description'] ) ) {
				if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
					echo wc_help_tip( $field['description'] );
				} else {
					echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
				}
			}
			echo '</p>';
		}

		/**
		 * Add/Update product into database.
		 *
		 * @throws \Exception Throwing Exception.
		 */
		public function wkmp_product_add_update() {
			global $current_user, $children;

			$manage_stock_status = false;
			$wpdb_obj            = $this->wpdb;

			$posted_data = isset( $_POST ) ? wc_clean( $_POST ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$errors      = $this->wkmp_product_validation( $posted_data );

			if ( ! empty( $errors ) && isset( $posted_data['product_name'] ) && ! empty( $posted_data['product_name'] ) ) {
				foreach ( $errors as $key => $value ) {
					wc_add_notice( $value, 'error' );
				}
			}

			$sell_pr_id = isset( $posted_data['sell_pr_id'] ) ? intval( $posted_data['sell_pr_id'] ) : 0;
			$error      = array();

			$variation_att_id = isset( $posted_data['mp_attribute_variation_name'] ) ? $posted_data['mp_attribute_variation_name'] : '';

			if ( isset( $posted_data['sale_price'] ) && '' === $posted_data['sale_price'] ) {
				unset( $posted_data['sale_price'], $_POST['sale_price'] );
			}

			$att_val           = isset( $posted_data['pro_att'] ) ? $posted_data['pro_att'] : '';
			$upload_dir        = wp_upload_dir();
			$product_type      = isset( $posted_data['product_type'] ) ? $posted_data['product_type'] : 'simple';
			$min_regu_price    = '';
			$max_regu_price    = '';
			$min_regu_price_id = '';
			$max_regu_price_id = '';
			$min_sale_price_id = '';
			$max_sale_price_id = '';

			if ( ! empty( $variation_att_id ) && ! empty( $att_val ) ) {
				$variation_data         = array();
				$variation_data['_sku'] = array();
				$temp_var_sku           = array();
				$var_regu_price         = array();
				$var_sale_price         = array();

				foreach ( $variation_att_id as $var_id ) {
					$var_regu_price[ $var_id ] = is_numeric( $posted_data['wkmp_variable_regular_price'][ $var_id ] ) ? $posted_data['wkmp_variable_regular_price'][ $var_id ] : '';

					if ( isset( $posted_data['wkmp_variable_sale_price'][ $var_id ] ) && is_numeric( $posted_data['wkmp_variable_sale_price'][ $var_id ] ) && $posted_data['wkmp_variable_sale_price'][ $var_id ] < $posted_data['wkmp_variable_regular_price'][ $var_id ] ) {
						$var_sale_price[ $var_id ] = $posted_data['wkmp_variable_sale_price'][ $var_id ];
					} else {
						$var_sale_price[ $var_id ] = '';
					}

					foreach ( $posted_data['mp_attribute_name'][ $var_id ] as $variation_type ) {
						$variation_data[ 'attribute_' . sanitize_title( $variation_type ) ][] = trim( $posted_data[ 'attribute_' . $variation_type ][ $var_id ] );
					}
					$downloadable_vari = 'no';
					if ( isset( $posted_data['wkmp_variable_is_downloadable'][ $var_id ] ) ) {
						$downloadable_vari = ( 'yes' === $posted_data['wkmp_variable_is_downloadable'][ $var_id ] ) ? 'yes' : $downloadable_vari;
					}

					$virtual_vari = 'no';
					if ( isset( $posted_data['wkmp_variable_is_virtual'] ) && isset( $posted_data['wkmp_variable_is_virtual'][ $var_id ] ) ) {
						$virtual_vari = ( 'yes' === $posted_data['wkmp_variable_is_virtual'][ $var_id ] ) ? 'yes' : $virtual_vari;
					}

					if ( 'yes' === $downloadable_vari ) {
						if ( isset( $posted_data['wkmp_variable_download_expiry'][ $var_id ] ) && is_numeric( $posted_data['wkmp_variable_download_expiry'][ $var_id ] ) ) {
							$down_expiry       = $posted_data['wkmp_variable_download_expiry'][ $var_id ];
							$downloadable_vari = $posted_data['wkmp_variable_download_expiry'][ $var_id ];
						}
						if ( isset( $posted_data['wkmp_variable_download_limit'][ $var_id ] ) && is_numeric( $posted_data['wkmp_variable_download_limit'][ $var_id ] ) ) {
							$down_limit        = $posted_data['wkmp_variable_download_limit'][ $var_id ];
							$downloadable_vari = $posted_data['wkmp_variable_download_limit'][ $var_id ];
						}
					}

					if ( isset( $posted_data['wkmp_variable_sale_price'][ $var_id ] ) && is_numeric( $posted_data['wkmp_variable_sale_price'][ $var_id ] ) && $posted_data['wkmp_variable_sale_price'][ $var_id ] < $posted_data['wkmp_variable_regular_price'][ $var_id ] ) {
						$variation_data['_sale_price'][] = $posted_data['wkmp_variable_sale_price'][ $var_id ];
					} else {
						$variation_data['_sale_price'][] = '';
					}

					if ( '' === $posted_data['wkmp_variable_sale_price'][ $var_id ] ) {
						$variation_data['_price'][] = is_numeric( $posted_data['wkmp_variable_regular_price'][ $var_id ] ) ? $posted_data['wkmp_variable_regular_price'][ $var_id ] : '';
					} else {
						$variation_data['_price'][] = is_numeric( $posted_data['wkmp_variable_sale_price'][ $var_id ] ) ? $posted_data['wkmp_variable_sale_price'][ $var_id ] : '';
					}

					$variation_data['_regular_price'][] = is_numeric( $posted_data['wkmp_variable_regular_price'][ $var_id ] ) ? $posted_data['wkmp_variable_regular_price'][ $var_id ] : '';

					if ( isset( $posted_data['wkmp_variable_sale_price_dates_to'] ) ) {
						$variation_data['_sale_price_dates_to'][] = $posted_data['wkmp_variable_sale_price_dates_to'][ $var_id ];
					}

					if ( isset( $posted_data['wkmp_variable_sale_price_dates_from'] ) ) {
						$variation_data['_sale_price_dates_from'][] = $posted_data['wkmp_variable_sale_price_dates_from'][ $var_id ];
					}

					$variation_data['_backorders'][] = $posted_data['wkmp_variable_backorders'][ $var_id ];

					$manage_stock = 'no';
					if ( isset( $posted_data['wkmp_variable_manage_stock'] ) && isset( $posted_data['wkmp_variable_manage_stock'][ $var_id ] ) ) {
						$manage_stock = ( 'yes' === $posted_data['wkmp_variable_manage_stock'][ $var_id ] ) ? 'yes' : $manage_stock;
					}

					if ( 'yes' === $manage_stock ) {
						if ( $posted_data['wkmp_variable_stock'][ $var_id ] ) {
							$manage_stock_status = true;
						}
					} else {
						$manage_stock_status = true;
					}

					$variation_data['_manage_stock'][] = $manage_stock;

					if ( 'yes' === $manage_stock ) {
						$variation_data['_stock'][] = $posted_data['wkmp_variable_stock'][ $var_id ];
					} else {
						$variation_data['_stock'][]        = '';
						$variation_data['_stock_status'][] = $posted_data['wkmp_variable_stock_status'][ $var_id ];
					}

					$var_sku_check = wp_strip_all_tags( $posted_data['wkmp_variable_sku'][ $var_id ] );

					if ( isset( $posted_data['wkmp_variable_sku'][ $var_id ] ) && ! empty( $posted_data['wkmp_variable_sku'][ $var_id ] ) ) {
						$var_chk_sku = $posted_data['wkmp_variable_sku'][ $var_id ];
						$var_data    = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT meta_id FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s AND post_id != %d", $var_sku_check, $var_id ) );

						if ( empty( $var_data ) && ! in_array( $posted_data['wkmp_variable_sku'][ $var_id ], $temp_var_sku, true ) ) {
							$variation_data['_sku'][] = $var_sku_check;
							$temp_var_sku[]           = $var_sku_check;
						} else {
							$variation_data['_sku'][] = '';
							wc_add_notice( esc_html__( 'Invalid or Duplicate SKU.', 'wk-marketplace' ), 'error' );
						}
					} else {
						$variation_data['_sku'][] = '';
					}

					$variation_data['_width'][]        = is_numeric( $posted_data['wkmp_variable_width'][ $var_id ] ) ? $posted_data['wkmp_variable_width'][ $var_id ] : '';
					$variation_data['_height'][]       = is_numeric( $posted_data['wkmp_variable_height'][ $var_id ] ) ? $posted_data['wkmp_variable_height'][ $var_id ] : '';
					$variation_data['_length'][]       = is_numeric( $posted_data['wkmp_variable_length'][ $var_id ] ) ? $posted_data['wkmp_variable_length'][ $var_id ] : '';
					$variation_data['_virtual'][]      = $virtual_vari;
					$variation_data['_downloadable'][] = $downloadable_vari;
					$thumbnail_id                      = $posted_data['upload_var_img'][ $var_id ];

					if ( ! empty( $thumbnail_id ) ) {
						$variation_data['_thumbnail_id'][] = $thumbnail_id;
					} else {
						$variation_data['_thumbnail_id'][] = 0;
					}

					$variation_data['_weight'][]     = is_numeric( $posted_data['wkmp_variable_weight'][ $var_id ] ) ? $posted_data['wkmp_variable_weight'][ $var_id ] : '';
					$variation_data['_menu_order'][] = is_numeric( $posted_data['wkmp_variation_menu_order'][ $var_id ] ) ? $posted_data['wkmp_variation_menu_order'][ $var_id ] : '';

					/* variation for download able product */
					if ( 'yes' === $downloadable_vari ) {
						$variation_files  = $posted_data['_mp_variation_downloads_files_url'][ $var_id ];
						$variation_names  = $posted_data['_mp_variation_downloads_files_name'][ $var_id ];
						$var_downloadable = array();
						$var_down_name    = array();

						if ( isset( $posted_data['_mp_variation_downloads_files_url'][ $var_id ] ) && count( $posted_data['_mp_variation_downloads_files_url'][ $var_id ] ) > 0 ) {
							$files = array();

							if ( ! empty( $variation_files ) ) {
								$variation_count = count( $variation_files );
								for ( $i = 0; $i < $variation_count; ++ $i ) {
									$file_url = wp_unslash( trim( $variation_files[ $i ] ) );
									if ( '' !== $file_url ) {
										$files[ md5( $file_url ) ] = array(
											'name' => wc_clean( $variation_names[ $i ] ),
											'file' => $file_url,
										);
									}
								}
							}
							update_post_meta( $var_id, '_downloadable_files', $files );
						}
					}
				}

				$min_regu_price = min( $var_regu_price );

				foreach ( $var_regu_price as $key => $value ) {
					if ( $value === $min_regu_price ) {
						$min_regu_price_id = $key;
					}
				}

				$max_regu_price = max( $var_regu_price );
				foreach ( $var_regu_price as $key => $value ) {
					if ( $value === $max_regu_price ) {
						$max_regu_price_id = $key;
					}
				}

				$min_sale_price = min( $var_sale_price );
				foreach ( $var_sale_price as $key => $value ) {
					if ( $value === $min_sale_price ) {
						$min_sale_price_id = $key;
					}
				}

				$max_sale_price = max( $var_sale_price );
				foreach ( $var_sale_price as $key => $value ) {
					if ( $value === $max_sale_price ) {
						$max_sale_price_id = $key;
					}
				}

				$variation_data_key     = array_keys( $variation_data );
				$variations_values      = array_values( $variation_data );
				$variation_data_count   = count( $variation_data );
				$variation_att_id_count = count( $variation_att_id );

				for ( $i = 0; $i < $variation_data_count; ++ $i ) {
					for ( $x = 0; $x < $variation_att_id_count; ++ $x ) {
						update_post_meta( $variation_att_id[ $x ], $variation_data_key[ $i ], $variations_values[ $i ][ $x ] );
						if ( '_sale_price' === $variation_data_key[ $i ] && '' === $variations_values[ $i ][ $x ] ) {
							delete_post_meta( $variation_att_id[ $x ], '_sale_price' );
						}
					}
				}
			}

			$attrib = array();
			$att    = array();
			if ( isset( $posted_data['pro_att'] ) ) {
				$attrib = $posted_data['pro_att'];
			}

			if ( ! empty( $attrib ) ) {
				foreach ( $attrib as $attribute ) {

					if ( empty( $attribute['name'] ) || empty( $attribute['value'] ) ) {
						continue;
					}

					$rep_str            = $attribute['value'];
					$rep_str            = preg_replace( '/\s+/', ' ', $rep_str );
					$attribute['name']  = str_replace( ' ', '-', $attribute['name'] );
					$attribute['value'] = str_replace( '|', '|', $rep_str );

					if ( isset( $attribute['is_visible'] ) ) {
						$attribute['is_visible'] = (int) $attribute['is_visible'];
					} else {
						$attribute['is_visible'] = 0;
					}

					if ( isset( $attribute['is_variation'] ) ) {
						$attribute['is_variation'] = (int) $attribute['is_variation'];
					} else {
						$attribute['is_variation'] = 0;
					}

					$attribute['is_taxonomy']                           = (int) $attribute['is_taxonomy'];
					$att[ str_replace( ' ', '-', $attribute['name'] ) ] = $attribute;
				}
			}

			$product_auth = 0;
			if ( $sell_pr_id > 0 ) {
				$product_auth = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT post_author FROM {$wpdb_obj->prefix}posts WHERE ID = %d", $sell_pr_id ) );
			}

			if ( ! empty( $posted_data['product_name'] ) && isset( $posted_data['product_name'] ) ) {
				$product_name = wp_strip_all_tags( $posted_data['product_name'] );
				$product_dsc  = empty( $_POST['product_desc'] ) ? '' : $_POST['product_desc'];

				$downloadable = isset( $posted_data['_downloadable'] ) ? $posted_data['_downloadable'] : '';
				$virtual      = isset( $posted_data['_virtual'] ) ? $posted_data['_virtual'] : 'no';
				$simple       = ( 'simple' === $product_type ) ? 'yes' : 'no';

				$backorder       = isset( $posted_data['_backorders'] ) ? $posted_data['_backorders'] : 'no';
				$threshold       = isset( $posted_data['wk-mp-stock-threshold'] ) ? $posted_data['wk-mp-stock-threshold'] : 0;
				$sold_individual = isset( $posted_data['wk_sold_individual'] ) ? $posted_data['wk_sold_individual'] : 'no';
				$stock           = isset( $posted_data['_stock_status'] ) ? $posted_data['_stock_status'] : 'instock';
				$posted_sku      = isset( $posted_data['product_sku'] ) ? $posted_data['product_sku'] : '';
				$max_qty_limit   = isset( $posted_data['_wkmp_max_product_qty_limit'] ) ? $posted_data['_wkmp_max_product_qty_limit'] : '';

				if ( empty( $posted_sku ) && $sell_pr_id > 0 ) {
					$posted_sku = get_post_meta( $sell_pr_id, '_sku', true );
				}

				$price = isset( $posted_data['regu_price'] ) ? $posted_data['regu_price'] : '';
				if ( isset( $posted_data['sale_price'] ) ) {
					$sales_price = $posted_data['sale_price'];
				}

				$product_short_desc = empty( $_POST['short_desc'] ) ? '' : $_POST['short_desc'];
				$limit              = isset( $posted_data['_download_limit'] ) && $posted_data['_download_limit'] ? $posted_data['_download_limit'] : '-1';
				$expiry             = isset( $posted_data['_download_expiry'] ) && $posted_data['_download_expiry'] ? $posted_data['_download_expiry'] : '-1';

				$mang_stock = isset( $posted_data['wk_stock_management'] ) ? $posted_data['wk_stock_management'] : 'no';
				$stock_qty  = ( 'yes' === $mang_stock ) ? $posted_data['wk-mp-stock-qty'] : '';

				$usere_downloadable_file_name   = isset( $posted_data['_mp_dwnld_file_names'] ) ? $posted_data['_mp_dwnld_file_names'] : '';
				$usere_downloadable_file_hashes = isset( $posted_data['_mp_dwnld_file_hashes'] ) ? $posted_data['_mp_dwnld_file_hashes'] : '';
				$seller_downloadable_file_url   = isset( $posted_data['_mp_dwnld_file_urls'] ) ? $posted_data['_mp_dwnld_file_urls'] : '';

				$product_galary_images = '';

				if ( isset( $posted_data['product_image_Galary_ids'] ) ) {
					$product_galary_images = implode( ',', array_unique( explode( ',', $posted_data['product_image_Galary_ids'] ) ) );
				}

				$sale_from = isset( $posted_data['sale_from'] ) ? $posted_data['sale_from'] : '';

				$sale_to        = isset( $posted_data['sale_to'] ) ? $posted_data['sale_to'] : '';
				$product_status = isset( $posted_data['mp_product_status'] ) ? $posted_data['mp_product_status'] : '';

				$product_data = array(
					'post_author'           => $this->seller_id,
					'post_date'             => '',
					'post_date_gmt'         => '',
					'post_content'          => $product_dsc,
					'post_content_filtered' => $product_short_desc,
					'post_title'            => htmlspecialchars( $product_name ),
					'post_excerpt'          => $product_short_desc,
					'post_status'           => $product_status,
					'post_type'             => 'product',
					'comment_status'        => 'open',
					'ping_status'           => 'open',
					'post_password'         => '',
					'post_name'             => wp_strip_all_tags( $product_name ),
					'to_ping'               => '',
					'pinged'                => '',
					'post_modified'         => '',
					'post_modified_gmt'     => '',
					'post_parent'           => '',
					'menu_order'            => '',
					'guid'                  => '',
				);

				if ( $sell_pr_id > 0 && intval( $product_auth ) === $this->seller_id && isset( $posted_data['add_product_sub'] ) && ! empty( $posted_data['_wpnonce'] ) ) {
					wp_verify_nonce( $posted_data['_wpnonce'], 'marketplace-edid_product' );

					// Add mp shipping per product addon data.
					$product_shipping_class = ( $posted_data['product_shipping_class'] > 0 && 'external' !== $product_type ) ? absint( $posted_data['product_shipping_class'] ) : '';

					wp_set_object_terms( $sell_pr_id, $product_shipping_class, 'product_shipping_class' );

					$product_data['ID'] = $sell_pr_id;

					if ( wp_update_post( $product_data ) ) {
						wc_add_notice( __( 'Product Updated Successfully.', 'wk-marketplace' ) );

						if ( ! empty( $posted_sku ) ) {
							update_post_meta( $sell_pr_id, '_sku', wp_strip_all_tags( $posted_sku ) );
						}

						$visibility = ( 'publish' === $product_status && in_array( 'wk_marketplace_seller', $current_user->roles, true ) ) ? 'visible' : '';

						update_post_meta( $sell_pr_id, '_visibility', $visibility );

						if ( is_numeric( $price ) ) {
							update_post_meta( $sell_pr_id, '_regular_price', $price );
						} else {
							$error[] = esc_html__( 'Regular Price', 'wk-marketplace' );
						}

						if ( 'variable' !== $product_type ) {
							if ( isset( $sales_price ) && is_numeric( $sales_price ) && $sales_price < $price ) {
								update_post_meta( $sell_pr_id, '_sale_price', $sales_price );
								update_post_meta( $sell_pr_id, '_price', $sales_price );
							} else {
								update_post_meta( $sell_pr_id, '_sale_price', '' );
								if ( is_numeric( $price ) ) {
									update_post_meta( $sell_pr_id, '_price', $price );
								}
							}
						} else {
							if ( ! empty( $min_sale_price ) && ! empty( $max_sale_price ) ) {

								update_post_meta( $sell_pr_id, '_min_variation_price', $min_sale_price );
								update_post_meta( $sell_pr_id, '_max_variation_price', $max_sale_price );
								update_post_meta( $sell_pr_id, '_min_price_variation_id', $min_sale_price_id );
								update_post_meta( $sell_pr_id, '_max_price_variation_id', $max_sale_price_id );

								if ( is_numeric( $min_regu_price ) ) {
									update_post_meta( $sell_pr_id, '_min_variation_regular_price', $min_regu_price );
								} else {
									$error[] = esc_html__( 'Min Variation Price', 'wk-marketplace' );
								}

								if ( is_numeric( $max_regu_price ) ) {
									update_post_meta( $sell_pr_id, '_max_variation_regular_price', $max_regu_price );
								} else {
									$error[] = esc_html__( 'Max Variation Price', 'wk-marketplace' );
								}

								update_post_meta( $sell_pr_id, '_min_regular_price_variation_id', $min_regu_price_id );
								update_post_meta( $sell_pr_id, '_max_regular_price_variation_id', $max_regu_price_id );
								update_post_meta( $sell_pr_id, '_min_variation_sale_price', $min_sale_price );
								update_post_meta( $sell_pr_id, '_max_variation_sale_price', $max_sale_price );
								update_post_meta( $sell_pr_id, '_min_sale_price_variation_id', $min_sale_price_id );
								update_post_meta( $sell_pr_id, '_max_sale_price_variation_id', $max_sale_price_id );
								delete_post_meta( $sell_pr_id, '_price' );
								add_post_meta( $sell_pr_id, '_price', $min_sale_price );
								add_post_meta( $sell_pr_id, '_price', $max_sale_price );

							} else {
								update_post_meta( $sell_pr_id, '_min_variation_price', $min_regu_price );
								update_post_meta( $sell_pr_id, '_max_variation_price', $max_regu_price );
								update_post_meta( $sell_pr_id, '_min_price_variation_id', $min_regu_price_id );
								update_post_meta( $sell_pr_id, '_max_price_variation_id', $max_regu_price_id );

								if ( is_numeric( $min_regu_price ) ) {
									update_post_meta( $sell_pr_id, '_min_variation_regular_price', $min_regu_price );
								} else {
									$error[] = esc_html__( 'Min Variation Price', 'wk-marketplace' );
								}

								if ( is_numeric( $max_regu_price ) ) {
									update_post_meta( $sell_pr_id, '_max_variation_regular_price', $max_regu_price );
								} else {
									$error[] = esc_html__( 'Max Variation Price', 'wk-marketplace' );
								}

								update_post_meta( $sell_pr_id, '_min_regular_price_variation_id', $min_regu_price_id );
								update_post_meta( $sell_pr_id, '_max_regular_price_variation_id', $max_regu_price_id );
								update_post_meta( $sell_pr_id, '_min_variation_sale_price', null );
								update_post_meta( $sell_pr_id, '_max_variation_sale_price', null );
								update_post_meta( $sell_pr_id, '_min_sale_price_variation_id', null );
								update_post_meta( $sell_pr_id, '_max_sale_price_variation_id', null );
								delete_post_meta( $sell_pr_id, '_price' );
								add_post_meta( $sell_pr_id, '_price', $min_regu_price );
								add_post_meta( $sell_pr_id, '_price', $max_regu_price );
							}
						}

						if ( isset( $posted_data['mp_attribute_variation_name'] ) ) {
							$stock = 'instock';
							if ( $variation_att_id && ! $manage_stock_status ) {
								$stock = 'outofstock';
							}
						} else {
							if ( 'yes' === $mang_stock ) {
								$stock = 'outofstock';
								if ( $stock_qty ) {
									$stock = 'instock';
								}
							} else {
								$stock = isset( $posted_data['_stock_status'] ) ? $posted_data['_stock_status'] : 'instock';
							}
						}

						update_post_meta( $sell_pr_id, '_sold_individually', $sold_individual );
						update_post_meta( $sell_pr_id, '_low_stock_amount', $threshold );
						update_post_meta( $sell_pr_id, '_backorders', $backorder );
						update_post_meta( $sell_pr_id, '_stock_status', $stock );
						update_post_meta( $sell_pr_id, '_manage_stock', $mang_stock );
						update_post_meta( $sell_pr_id, '_virtual', $virtual );
						update_post_meta( $sell_pr_id, '_simple', $simple );
						update_post_meta( $sell_pr_id, '_wkmp_max_product_qty_limit', $max_qty_limit );

						if ( isset( $posted_data['my-virtual'] ) ) {
							update_post_meta( $sell_pr_id, '_weight', '' );
							update_post_meta( $sell_pr_id, '_length', '' );
							update_post_meta( $sell_pr_id, '_width', '' );
							update_post_meta( $sell_pr_id, '_height', '' );
						} else {
							if ( isset( $posted_data['_weight'] ) ) {
								update_post_meta( $sell_pr_id, '_weight', ( '' === $posted_data['_weight'] ) ? '' : wc_format_decimal( $posted_data['_weight'] ) );
							}

							if ( isset( $posted_data['_length'] ) ) {
								update_post_meta( $sell_pr_id, '_length', ( '' === $posted_data['_length'] ) ? '' : wc_format_decimal( $posted_data['_length'] ) );
							}

							if ( isset( $posted_data['_width'] ) ) {
								update_post_meta( $sell_pr_id, '_width', ( '' === $posted_data['_width'] ) ? '' : wc_format_decimal( $posted_data['_width'] ) );
							}

							if ( isset( $posted_data['_height'] ) ) {
								update_post_meta( $sell_pr_id, '_height', ( '' === $posted_data['_height'] ) ? '' : wc_format_decimal( $posted_data['_height'] ) );
							}
						}

						if ( 'external' === $product_type ) {
							if ( isset( $posted_data['product_url'] ) && isset( $posted_data['button_txt'] ) ) {
								$pro_url = $posted_data['product_url'];
								$btn_txt = $posted_data['button_txt'];
								update_post_meta( $sell_pr_id, '_product_url', esc_url_raw( $pro_url ) );
								update_post_meta( $sell_pr_id, '_button_text', wc_clean( $btn_txt ) );
							}
						}

						// Save upsells data.
						if ( isset( $posted_data['upsell_ids'] ) ) {
							update_post_meta( $sell_pr_id, '_upsell_ids', array_map( 'intval', $posted_data['upsell_ids'] ) );
						} else {
							update_post_meta( $sell_pr_id, '_upsell_ids', array() );
						}

						// Save cross sell data.
						if ( isset( $posted_data['crosssell_ids'] ) ) {
							update_post_meta( $sell_pr_id, '_crosssell_ids', array_map( 'intval', $posted_data['crosssell_ids'] ) );
						} else {
							update_post_meta( $sell_pr_id, '_crosssell_ids', array() );
						}

						if ( 'grouped' === $product_type ) {
							if ( isset( $posted_data['mp_grouped_products'] ) ) {
								$grouped_product_data = $posted_data['mp_grouped_products'] ? $posted_data['mp_grouped_products'] : array();
								update_post_meta( $sell_pr_id, '_children', $grouped_product_data );
							} else {
								update_post_meta( $sell_pr_id, '_children', array() );
							}
						}

						if ( 'yes' === $downloadable ) {
							$upload_file_url = array();

							$file_hashes = isset( $posted_data['_mp_dwnld_file_hashes'] ) ? $posted_data['_mp_dwnld_file_hashes'] : array();
							update_post_meta( $sell_pr_id, '_downloadable', $downloadable );
							update_post_meta( $sell_pr_id, '_virtual', 'yes' );
							$dwnload_url = $seller_downloadable_file_url ? wc_clean( $seller_downloadable_file_url ) : array();

							foreach ( $dwnload_url as $key => $value ) {
								$dw_file_name = ( ! empty( $usere_downloadable_file_name[ $key ] ) ) ? $usere_downloadable_file_name[ $key ] : '';

								$upload_file_url[ md5( $value ) ] = array(
									'id'            => md5( $value ),
									'name'          => $dw_file_name,
									'file'          => $value,
									'previous_hash' => wc_clean( $file_hashes[ $key ] ),
								);
							}

							$data_store = \WC_Data_Store::load( 'customer-download' );

							if ( $upload_file_url ) {
								foreach ( $upload_file_url as $download ) {
									$new_hash = md5( $download['file'] );

									if ( $download['previous_hash'] && $download['previous_hash'] !== $new_hash ) {
										// Update permissions.
										$data_store->update_download_id( $sell_pr_id, $download['previous_hash'], $new_hash );
									}
								}
							}
							update_post_meta( $sell_pr_id, '_downloadable_files', $upload_file_url );
						} else {
							update_post_meta( $sell_pr_id, '_downloadable', 'no' );
						}

						$att = empty( $att ) ? array() : $att;

						update_post_meta( $sell_pr_id, '_product_attributes', $att );

						if ( '' !== $stock_qty ) {
							update_post_meta( $sell_pr_id, '_stock', $stock_qty );
						} else {
							delete_post_meta( $sell_pr_id, '_stock' );
						}

						update_post_meta( $sell_pr_id, '_download_limit', $limit );
						update_post_meta( $sell_pr_id, '_download_expiry', $expiry );
						update_post_meta( $sell_pr_id, '_product_image_gallery', $product_galary_images );
						update_post_meta( $sell_pr_id, '_thumbnail_id', $posted_data['product_thumb_image_mp'] );
					}

					$download_product  = $_FILES;
					$product_image_gal = $_FILES;
					$p_category        = isset( $posted_data['product_cate'] ) ? $posted_data['product_cate'] : '';
					$this->wkmp_update_pro_category( $p_category, $sell_pr_id );
					wp_set_object_terms( $sell_pr_id, $product_type, 'product_type', false );
				} else {
					$sell_pr_id = wp_insert_post( $product_data );

					add_post_meta( $sell_pr_id, '_thumbnail_id', $posted_data['product_thumb_image_mp'] );
					$data = array(
						'ID'   => $sell_pr_id,
						'guid' => get_option( 'siteurl' ) . '/?post_type=ai1ec_event&p=' . $sell_pr_id . '&instance_id=',
					);

					$field = '';

					if ( isset( $posted_data['base_product_id'] ) ) {
						$field = $posted_data['base_product_id'];
					}

					if ( wp_update_post( $data ) ) {
						wc_add_notice( __( 'Product Created Successfully.', 'wk-marketplace' ) );

						do_action( 'marketplace_insert_product_meta', $sell_pr_id, $field );

						if ( ! empty( $posted_sku ) ) {
							update_post_meta( $sell_pr_id, '_sku', wp_strip_all_tags( $posted_sku ) );
						}

						if ( is_numeric( $price ) ) {
							add_post_meta( $sell_pr_id, '_regular_price', $price );
						} else {
							$error[] = esc_html__( 'Regular Price', 'wk-marketplace' );
						}

						if ( isset( $sales_price ) && $sales_price < $price ) {
							if ( is_numeric( $sales_price ) && $sales_price < $price ) {
								add_post_meta( $sell_pr_id, '_sale_price', $sales_price );
								add_post_meta( $sell_pr_id, '_price', $sales_price );

							} else {
								$error[] = esc_html__( 'Sale Price', 'wk-marketplace' );
							}
						} else {
							add_post_meta( $sell_pr_id, '_sale_price', '' );
							if ( is_numeric( $price ) ) {
								add_post_meta( $sell_pr_id, '_price', $price );
							} else {
								$error[] = esc_html__( 'Price', 'wk-marketplace' );
							}
						}

						add_post_meta( $sell_pr_id, '_manage_stock', $mang_stock );
						add_post_meta( $sell_pr_id, '_sale_price_dates_from', $sale_from );
						add_post_meta( $sell_pr_id, '_sale_price_dates_to', $sale_to );
						add_post_meta( $sell_pr_id, '_downloadable', $downloadable );
						add_post_meta( $sell_pr_id, '_virtual', $virtual );
						add_post_meta( $sell_pr_id, '_simple', $simple );

						if ( 'variable' === $product_type ) {
							update_post_meta( $sell_pr_id, '_min_variation_price', '' );
							update_post_meta( $sell_pr_id, '_max_variation_price', '' );
							update_post_meta( $sell_pr_id, '_min_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_max_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_min_variation_regular_price', '' );
							update_post_meta( $sell_pr_id, '_max_variation_regular_price', '' );
							update_post_meta( $sell_pr_id, '_min_regular_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_max_regular_price_variation_id', '' );
							update_post_meta( $sell_pr_id, '_min_variation_sale_price', null );
							update_post_meta( $sell_pr_id, '_max_variation_sale_price', null );
							update_post_meta( $sell_pr_id, '_min_sale_price_variation_id', null );
							update_post_meta( $sell_pr_id, '_max_sale_price_variation_id', null );
						}

						wp_set_object_terms( $sell_pr_id, $product_type, 'product_type', false );
					}

					$p_category = isset( $posted_data['product_cate'] ) ? $posted_data['product_cate'] : '';
					$this->wkmp_add_pro_category( $p_category, $sell_pr_id );
				}

				do_action( 'marketplace_process_product_meta', $sell_pr_id );

				if ( ! get_option( '_wkmp_allow_seller_to_publish' ) ) {
					if ( ! get_post_meta( $sell_pr_id, 'mp_added_noti' ) ) {
						delete_post_meta( $sell_pr_id, 'mp_admin_view' );
						update_option( 'wkmp_approved_product_count', (int) ( get_option( 'wkmp_approved_product_count', 0 ) + 1 ) );
						update_post_meta( $sell_pr_id, 'mp_added_noti', true );
					}

					do_action( 'wkmp_seller_published_product', $this->seller_id, $sell_pr_id );
				}

				if ( $sell_pr_id > 0 && intval( $product_auth ) === intval( $this->seller_id ) && isset( $posted_data['add_product_sub'] ) ) {

					if ( 'simple' === $product_type ) {
						$obj_product = new \WC_Product_Simple( $sell_pr_id );
						$obj_product->save();
					} elseif ( 'variable' === $product_type ) {
						$obj_product = new \WC_Product_Variable( $sell_pr_id );
						$obj_product->save();
						$wkmp_variations = empty( $posted_data['mp_attribute_variation_name'] ) ? array() : $posted_data['mp_attribute_variation_name'];

						foreach ( $wkmp_variations as $key => $variation_id ) {
							$variation = new \WC_Product_Variation( $variation_id );
							$variation->save();
						}
					}
				}
			}

			$this->product_id = $sell_pr_id > 0 ? $sell_pr_id : $this->product_id;
		}

		/**
		 * Validate product fields
		 *
		 * @param array $data Data.
		 *
		 * @return array
		 */
		public function wkmp_product_validation( $data ) {
			$errors   = array();
			$wpdb_obj = $this->wpdb;

			if ( isset( $data['regu_price'] ) && ! is_numeric( $data['regu_price'] ) && ! empty( $data['regu_price'] ) ) {
				$errors[] = esc_html__( 'Regular Price is not a number.', 'wk-marketplace' );
			}

			if ( isset( $data['sale_price'] ) && ! is_numeric( $data['sale_price'] ) && ! empty( $data['sale_price'] ) ) {
				$errors[] = esc_html__( 'Sale Price is not a number.', 'wk-marketplace' );
			}

			if ( isset( $data['wk-mp-stock-qty'] ) && ! is_numeric( $data['wk-mp-stock-qty'] ) && ! empty( $data['wk-mp-stock-qty'] ) ) {
				$errors[] = esc_html__( 'Stock Quantity is not a number.', 'wk-marketplace' );
			}

			$posted_sku = isset( $data['product_sku'] ) ? $data['product_sku'] : '';
			$sell_pr_id = isset( $data['sell_pr_id'] ) ? intval( $data['sell_pr_id'] ) : 0;

			if ( ! empty( $posted_sku ) ) {
				$prod_sku = ( $sell_pr_id > 0 ) ? get_post_meta( $sell_pr_id, '_sku', true ) : '';
				if ( empty( $prod_sku ) || $prod_sku !== $posted_sku ) {
					$sku_exist = $wpdb_obj->get_var( $wpdb_obj->prepare( "SELECT meta_value FROM {$wpdb_obj->prefix}postmeta WHERE meta_key='_sku' AND meta_value=%s", $posted_sku ) );
					if ( ! empty( $sku_exist ) ) {
						$errors[] = esc_html__( 'Invalid or Duplicate SKUs.', 'wk-marketplace' );
					}
				}
			}

			return $errors;
		}
	}
}
