<?php
/**
 * This file handles templates.
 *
 * @author Webkul
 */

namespace wooMarketplaceRFQ\Templates\Front\Seller;

use wooMarketplaceRFQ\Helper;
use wooMarketplaceRFQ\Templates\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Seller_Template' ) ) {
	/**
	 * Load hooks.
	 */
	class Womprfq_Seller_Template {

		public $helper;
		protected $tab_title;
		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->helper    = new Helper\Womprfq_Quote_Handler();
			$this->tab_title = array(
				'open'     => esc_html__( 'Open', 'wk-mp-rfq' ),
				'pending'  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				'answered' => esc_html__( 'Answered', 'wk-mp-rfq' ),
				'resolved' => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				'closed'   => esc_html__( 'Closed', 'wk-mp-rfq' ),
			);
		}

		/**
		 * Seller main quote
		 */
		public function womprfq_manage_rfq_template() {
			$post_data = $_REQUEST;

			if ( isset( $post_data['tab'] ) && ! empty( $post_data['tab'] ) ) {
				$tab = $post_data['tab'];
			} else {
				$tab = 'open';
			}

			if ( get_query_var( 'info' ) ) {
				$page = get_query_var( 'info' );
			} else {
				$page = 1;
			}
			// JS edit. Add country and city drop down filter and country preference. Step 14
			$limit  = 10;
			$offset = ( $page == 1 ) ? 0 : ( $page - 1 ) * $limit;
			?>
			
			<!-- JS edit. Add country and city drop down filter and country preference. Step 15 -->
			<style>
				.wk-mp-rfq-header .dropbtn{
					background-color: #eb9a72;
					color: #fff;
					padding: 12px;
					font-size: 16px;
					border: 1px solid #eb9a72;
					cursor: pointer;
					border-radius: 5px;
					padding-right: 20px;
				}
				.dropdown:before {
					content: "▼";
					position: absolute;
					color: #fff;
					right: 0;
					font-size: 12px;
					margin: 11px 4px;
				}
				.wk-mp-rfq-header .dropdown {
				  position: relative;
				  display: inline-block;
				}

				.wk-mp-rfq-header .dropdown-content {
				  	display: none;
				    position: absolute;
				    background-color: #f9f9f9;
				    min-width: 100%;
				    overflow: auto;
				    box-shadow: 0px 8px 16px 0px rgb(0 0 0 / 20%);
				    z-index: 1;
				    height: 300px;
				    overflow-y: auto;
				}

				.wk-mp-rfq-header .dropdown-content a {
				    color: black;
				    padding: 12px 16px;
				    text-decoration: none;
				    display: block;
				}
				.wk-mp-rfq-header .dropdown-content a:hover {
					background:#eee;
				}
				.wk-mp-rfq-header .dropdown:hover .dropdown-content {
				  display: block;
				}
				.wk-mp-rfq-header{
					display: flex;
					align-items: center;
					justify-content: space-between;
				}
				@media only screen and (max-width: 767px){
					.wk-mp-rfq-header{
						display:block;
						margin-bottom:30px
					}
				}
				
			</style>
			
			<div class="woocommerce-account woocommerce">
				<?php apply_filters( 'mp_get_wc_account_menu', 'marketplace' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="wk-mp-rfq-header">
						<h2>
							<?php echo ucfirst( esc_html( $this->tab_title[ $tab ] ) . ' ' . esc_html__( 'Quotation Request', 'wk-mp-rfq' ) ); ?>
						</h2>
					
					<!-- JS edit. Add country and city drop down filter and country preference. Step 16 -->
					<?php if($tab == 'open'){ ?>
							<div class="dropdown">
								<button class="dropbtn">
									<?php 
									$subscribe_email = get_user_meta(get_current_user_id(),'subscribe_country', true);
									if(isset($_GET['c'])){ 
										if( $_GET['c'] == 'all' ) { 
											echo "All";
										}else{
											echo WC()->countries->countries[ $_GET['c'] ];
										}
									?>
										
									<?php }elseif(!empty($subscribe_email)) {
										if($subscribe_email == "all"){
											echo "All";
										}else{
											echo WC()->countries->countries[ $subscribe_email ];
										}
									?>
									<?php }else{ ?>
										All
									<?php } ?>
								</button>
							 	<div class="dropdown-content">
								    <a href="?c=all">All</a>
							 		<?php 
							 			$countries_obj = new \WC_Countries();
										$countries     = $countries_obj->__get( 'countries' );
										foreach ( $countries as $key => $country ) {
							 		?>
								    	<a href="?c=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></a>
								    <?php } ?>
								</div> 
							</div>
						<?php } ?>
						
					</div>
					<div id="main_container" class="wk_transaction woocommerce-MyAccount-content wk-mp-rfq" style="display: contents;">
					<?php
					$this->womprfq_get_quote_tab_template( $tab );
					$this->womprfq_get_quote_data_template( $tab, $offset, $page, $limit );
					?>
					</div>
				</div>
			</div>
			<?php
		}

		public function womprfq_get_quote_tab_template( $tab ) {
			global $wkmarketplace;
			$page_name = $wkmarketplace->seller_page_slug ? $wkmarketplace->seller_page_slug : get_query_var( 'pagename' );

			$tabs = array(
				'open'     => esc_html__( 'Open', 'wk-mp-rfq' ),
				'pending'  => esc_html__( 'Pending', 'wk-mp-rfq' ),
				'answered' => esc_html__( 'Answered', 'wk-mp-rfq' ),
				'resolved' => esc_html__( 'Resolved', 'wk-mp-rfq' ),
				'closed'   => esc_html__( 'Closed', 'wk-mp-rfq' ),
			);
			$tabs = apply_filters( 'womprfq_add_status_tab', $tabs );
			?>
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper">

			<?php

			foreach ( $tabs as $slug => $value ) {
				$ac = '';
				if ( $tab == $slug ) {
					$ac = 'nav-tab-active';
				}
				$editurl = site_url( '/' . $page_name . '/manage-rfq/?tab=' . esc_html( $slug ) );
				?>

				<a href="<?php echo esc_url( $editurl ); ?>" class="nav-tab <?php echo esc_attr( $ac ); ?> "> <?php echo esc_html( $value ); ?> </a>

				<?php
			}
			?>
			</nav>
			<?php
		}

		public function womprfq_get_quote_data_template( $tab, $offset, $page, $limit ) {
			$data = array(
				'tab'  => $tab,
				'data' => array(),
			);

			$tab_data = $this->helper->womprfq_get_seller_quotations( get_current_user_id(), $tab, $offset, $limit );
			
			// JS edit. Add country and city drop down filter and country preference. Step 17
			if($tab == 'open'){
				if(isset($_GET['c'])){
					$seller_country = $_GET['c'];
				}else{
					$seller_country = get_user_meta(get_current_user_id(),'subscribe_country',true);
				}
				$limit = 10;
				if($_GET['c'] === "all" OR  empty($seller_country) OR  $seller_country == "all" ){ 
					$tab_data = $this->helper->womprfq_get_seller_quotations( get_current_user_id(), $tab, $offset, $limit );
				}else{
					$tab_data = $this->helper->womprfq_get_seller_quotations_by_country( get_current_user_id(), $tab, $offset, $limit,$seller_country );
				}		
				
				
				$tab_data_new = array();
				if(!empty($tab_data['data'])){
					foreach ($tab_data['data'] as $key => $data) {
							$tab_data_new[] = $data;
					}
				} 

				if ( ! empty( $tab_data ) ) {
					$data['data'] = $tab_data_new;
					$data['tab'] = $tab;
				}
				$total_count = $tab_data['tcount'];
			}else{
			
			if ( ! empty( $tab_data ) ) {	
				$data['data'] = $tab_data['data'];
			}
			$total_count = $tab_data['tcount'];
			
			// JS edit. Add country and city drop down filter and country preference. Step 18
			}
			
			$table       = new Front\Seller\Womprfq_Seller_Table_Template( $data, $tab, $page, $limit, $total_count );
		}

		/**
		 * Seller quote list
		 */
		public function womprfq_manage_add_seller_quote_template() {
			global $wp_query;
			$post_data = $_REQUEST;
			do_action( 'womprfq_seller_quotation_save_form', $post_data, intval( $wp_query->query_vars['info'] ), 'add' );
			?>
			<div class="woocommerce-account woocommerce">
				<?php apply_filters( 'mp_get_wc_account_menu', 'marketplace' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="wk-mp-rfq-quotation-header">
						<h2>
							<?php echo ucfirst( esc_html__( 'Add Quotation', 'wk-mp-rfq' ) ); ?>
						</h2>
					</div>
					<!-- Jesse: PS: Add help links under Make an Offer -->
                    Offer Reminders
					<div id="main_container" class="wk_transaction woocommerce-MyAccount-content" style="display: contents;">
					<?php
					if ( intval( $wp_query->query_vars['info'] ) ) {
						$main_quote_info = $this->helper->womprfq_get_main_quotation_by_id( intval( $wp_query->query_vars['info'] ) );
						$temp_obj        = new Front\Womprfq_Front_Templates();
						$temp_obj->womprfq_get_main_quote_template( $main_quote_info );
						$this->womprfq_get_seller_add_quote_form( $main_quote_info );
					}
					?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Seller main quote
		 */
		public function womprfq_get_edit_quotation_template() {
			global $wp_query;
			$post_data = $_REQUEST;
			do_action( 'womprfq_seller_quotation_save_form', $post_data, intval( $wp_query->query_vars['info'] ), 'edit' );
			?>
			<div class="woocommerce-account woocommerce">
				<?php apply_filters( 'mp_get_wc_account_menu', 'marketplace' ); ?>
				<div class="woocommerce-MyAccount-content">
					<div class="wk-mp-rfq-quotation-header">
						<h2>
							<?php echo ucfirst( esc_html( 'Edit Quotation', 'wk-mp-rfq' ) ); ?>
						</h2>
					</div>
					<div id="main_container" class="wk_transaction woocommerce-MyAccount-content" style="display: contents;">
					<?php
					if ( intval( $wp_query->query_vars['info'] ) ) {
						$seller_data = $this->helper->womprfq_get_seller_quotation_details( intval( $wp_query->query_vars['info'] ) );
						$seller_id = get_current_user_id();	
						if ( $seller_data ) {
							
							if($seller_id == $seller_data->seller_id ){
								$main_quote_info = $this->helper->womprfq_get_main_quotation_by_id( intval( $seller_data->main_quotation_id ) );
								$temp_obj        = new Front\Womprfq_Front_Templates();
								$temp_obj->womprfq_get_main_quote_template( $main_quote_info );
								$edit_obj = new Front\Seller\Womprfq_Edit_Seller_Quote( intval( $wp_query->query_vars['info'] ) );
								$edit_obj->womprfq_prepare_seller_edit_template( $seller_data );
							} else {
								wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
								die;
							}
						}
					}
					?>
					</div>
				</div>
			</div>
			<?php
		}

		public function womprfq_get_seller_add_quote_form( $main_quote_info ) {
			if ( $main_quote_info ) {
				?>
				<div class="wkmp-rfq-sut-edit-quote">
					<form  method="POST" class="wk-seller-quotation-form" id="wk-seller-quotation-form">
						<table class="form-table wc_status_table widefat">
							<tbody>
								<?php
								if ( intval( $main_quote_info->status ) == 1 ) {
									?>
									<tr valign="top">
										<th>
											<label for="seller-quote-quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
											<span class="required">*</span>
										</th>
										<td class="forminp">
											<input type="number" id="seller-quote-quantity" name="seller-quote-quantity">

										</td>
									</tr>
									<tr valign="top">
										<th>
											<label for="seller-quote-price"><?php esc_html_e( 'Price/Product', 'wk-mp-rfq' ); ?></label>
											<span class="required">*</span>
										</th>
										<td class="forminp">
											<input type="text" id="seller-quote-price" name="seller-quote-price">
										</td>
									</tr>
									<tr valign="top">
										<th>
											<label for="seller-quote-commission"><?php esc_html_e( 'Commission/Order', 'wk-mp-rfq' ); ?></label>
											<span class="required">*</span>
										</th>
										<td class="forminp">
											<input type="text" id="seller-quote-commission" name="seller-quote-commission">
										</td>
									</tr>
									<tr valign="top">
										<th>
											<label for="seller-quote-comment"><?php esc_html_e( 'Comment', 'wk-mp-rfq' ); ?></label>
											<span class="required">*</span>
										</th>
										<td class="forminp">
											<textarea rows="6" cols="23" id="seller-quote-comment" class="regular-text" name="seller-quote-comment"></textarea>
											<?php echo wc_help_tip( esc_html__( 'Enter text to add comment to quote.', 'wk-mp-rfq' ), false ); ?>
											<span id="wk-mp-rfq-image-container"></span>
											<input type="hidden" id="seller-quote-comment-image" name="seller-quote-comment-image">
											<span class="seller-quote-comment-image-add" title="<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>" id="seller-quote-comment-image-add">
												<?php esc_html_e( 'Add image', 'wk-mp-rfq' ); ?>
											</span>
										</td>
									</tr>
									<!-- Jesse edit: Add Quotation submit button overflow -->
									<tr valign="top">
										<td colspan=2 class="forminp">
											<?php wp_nonce_field( 'wc-seller-quote-nonce-action', 'wc-seller-quote-nonce' ); ?>
											<input type="submit" name="update-seller-new-quotation-submit" value="<?php esc_html_e( 'Add Quotation', 'wk-mp-rfq' ); ?>" class="button button-primary" />
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</form>
				</div>
				<?php
			}
		}
	}
}
