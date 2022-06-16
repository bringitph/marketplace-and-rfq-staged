<!--Jesse edit: CSS for profile card-->
<style type="text/css">
/*92 PS page title spacing */
.mp-profile-wrapper .mp-page-title {
letter-spacing: 1px;
}

/*93 PS page profile card */
*{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
/*   list-style: none; */
}

/* body{
   background-color: #f3f3f3;
} */

.profile-wrapper{
  position: visible;
/*   top: 20%;
  left: 20%; */
/*   transform: translate(-50%,-50%); */
/*   width: 450px; */
  display: flex;
  box-shadow: 0 1px 20px 10px rgba(69,90,100,.08);
}

.profile-wrapper .left{
  width: 30%;
  background: linear-gradient(to right,#ffe0c4,#ffe0c4);
  padding: 20px 10px;
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
  text-align: center;
  color: #fff;
}

.profile-wrapper .left img{
  border-radius: 5px;
  margin-bottom: 10px;
	
}

.profile-wrapper .left h4{
  margin-bottom: 10px;
}

.profile wrapper .left p{
  font-size: 12px;
}

.profile-wrapper .right{
  width: 70%;
  background: #fff;
  padding: 20px 25px;
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
}

.profile-wrapper .right .info,
.profile-wrapper .right .projects{
  margin-bottom: 25px;
}

.profile-wrapper .right .info h3,
.profile-wrapper .right .projects h3{
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e0e0e0;
    color: #353c4e;
  text-transform: uppercase;
  letter-spacing: 5px;
}

.profile-wrapper .right .info_data,
.profile-wrapper .right .projects_data{
  display: flex;
  justify-content: space-between;
}

.profile-wrapper .right .info_data .data,
.profile-wrapper .right .projects_data .data{
  width: 100%;
}

.profile-wrapper .right .info_data .data h4,
.profile-wrapper .right .projects_data .data h4{
    color: #353c4e;
    margin-bottom: 5px;
}

.profile-wrapper .right .info_data .data p,
.profile-wrapper .right .projects_data .data p{
  font-size: 13px;
  margin-bottom: 10px;
  color: #919aa3;
}

.profile-wrapper .social_media ul{
  display: flex;
}

.profile-wrapper .social_media ul li{
  width: 45px;
  height: 45px;
  background: linear-gradient(to right,#01a9ac,#01dbdf);
  margin-right: 10px;
  border-radius: 5px;
  text-align: center;
  line-height: 45px;
}

.profile-wrapper .social_media ul li a{
  color :#fff;
  display: block;
  font-size: 18px;
}

/*97 About text in Seller card */
.about-text {
	line-height: 120%;
	font-size: 14px;
	text-align: start;
	color :#737373;
}

.wkmp-img-thumbnail img {
    height: 150px;
    width: 150px;
}
</style>



<?php
/**
 * Store info.
 *
 * @package WkMarketplace
 */

defined( 'ABSPATH' ) || exit;

$current_user_id = get_current_user_id();
$review_status   = isset( $review_check[0]->status ) ? $review_check[0]->status : '3';
$mp_page_title   = empty( $seller_info->shop_name ) ? '' : $seller_info->shop_name;
if ( empty( $mp_page_title ) ) {
	$seller_name   = empty( $seller_info->first_name ) ? '' : $seller_info->first_name;
	$seller_name   = ( ! empty( $seller_name ) && ! empty( $seller_info->last_name ) ) ? $seller_name . ' ' . $seller_info->last_name : $seller_name;
	$mp_page_title = empty( $seller_name ) ? esc_html__( 'Store Page', 'wk-marketplace' ) : $seller_name;
}
?>
    <p></p>
	<div class="profile-wrapper">
    <div class="left">
        <img src="<?php echo esc_url( $shop_logo ); ?>" alt="user" width="150" class="mp-shop-logo">
        <h4 class="mp-page-title">Hello!</h4>
         <p class="about-text"><?php echo isset( $seller_info->about_shop ) ? esc_html( $seller_info->about_shop ) : ''; ?></p>
    </div>
    <div class="right">
        <div class="info">
            <h3>Personal Shopper</h3>
            <div class="info_data">
                 <div class="data">
                    <h4 class="mp-page-title"><?php echo esc_html( $mp_page_title ); ?></h4>
                 </div>
                 <!--<div class="data">-->
                 <!--  <h4>Phone</h4>-->
                 <!--   <p>0001-213-998761</p>-->
                 <!--</div>-->
            </div>
        </div>
      
        <div class="projects">
            <h3>Past orders</h3>
            <div class="projects_data">
                <div class="data">
                    <div class="mp-profile-wrapper woocommerce">
                        
	                <?php do_action( 'mkt_before_seller_preview_products', $this->seller_id ); ?>

	                    <div class="mp-seller-recent-product">
		                <?php
		                $page_no    = ( get_query_var( 'pagenum' ) ) ? get_query_var( 'pagenum' ) : 1;
		                $query_args = array(
			                'author'         => $this->seller_id,
			                'post_type'      => 'product',
			                'post_status'    => 'publish',
			                'posts_per_page' => 9,
			                'paged'          => $page_no,
		                                   );
		                $query_args = apply_filters( 'mp_seller_collection_product_args', $query_args );
		                $products = new \WP_Query( $query_args );

		                if ( $products->have_posts() ) {
			                do_action( 'marketplace_before_shop_loop', $products->max_num_pages );
			                woocommerce_product_loop_start();
			                while ( $products->have_posts() ) :
				                $products->the_post();
				                wc_get_template_part( 'content', 'product' );
			                endwhile;
			                woocommerce_product_loop_end();
			                do_action( 'marketplace_after_shop_loop', $products->max_num_pages );
		                } else {
			                esc_html_e( 'No deliveries yet. They\'re excited for your order!', 'wk-marketplace' );
		                }
		                wp_reset_postdata();
		                ?>
	                    </div>
	
	                <?php do_action( 'mkt_after_seller_preview_products' ); ?>

                    </div> <!--end of mp-profile-wrapper woocommerce--> 
                </div> <!--end of data-->
            </div> <!--end of projects_data-->
        </div> <!--end of projects-->
      
        <!--<div class="social_media">-->
        <!--    <ul>-->
        <!--      <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>-->
        <!--      <li><a href="#"><i class="fab fa-twitter"></i></a></li>-->
        <!--      <li><a href="#"><i class="fab fa-instagram"></i></a></li>-->
        <!--  </ul>-->
        <!--</div>-->
        
        </div> <!--end of right-->
    </div> <!--end of profile-wrapper-->
	<p></p>