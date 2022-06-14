<?php
/**
 * Seller product at front
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.0
 */

namespace WkMarketplace\Templates\Front\Seller\Dashboard;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WKMP_Dashboard_Top_Billing_Country' ) ) {
	/**
	 * Dashboard top billing country.
	 *
	 * Class WKMP_Dashboard_Top_Billing_Country
	 *
	 * @package WkMarketplace\Templates\Front\Seller\Dashboard
	 */
	class WKMP_Dashboard_Top_Billing_Country {
		/**
		 * Dashboard DB Object.
		 *
		 * @var object $dashboard_db_obj Dashboard DB Object.
		 */
		private $dashboard_db_obj;

		/**
		 * Seller orders.
		 *
		 * @var array $seller_orders Seller orders.
		 */
		private $seller_orders;

		/**
		 * Marketplace class object.
		 *
		 * @var object $marketplace Marketplace class object.
		 */
		private $marketplace;

		/**
		 * Constructor of the class.
		 *
		 * WKMP_Dashboard_Top_Billing_Country constructor.
		 *
		 * @param object $db_obj DB Object.
		 * @param object $marketplace Marketplace object.
		 * @param object $seller_orders Seller orders.
		 * @param int    $seller_id Seller id.
		 */
		public function __construct( $db_obj, $marketplace, $seller_orders, $seller_id ) {
			$this->dashboard_db_obj = $db_obj;
			$this->marketplace      = $marketplace;
			$this->seller_orders    = $seller_orders;
			$this->wkmp_index( $seller_id );
		}

		/**
		 * Indexing.
		 *
		 * @param int $seller_id Seller id.
		 *
		 * @return void
		 */
		public function wkmp_index( $seller_id ) {
			$array_data_str = $this->wkmp_get_data();
			if ( $array_data_str ) {
				?>
				<script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
				<script src="https://www.gstatic.com/charts/loader.js"></script>
				<script>
					var data_array = <?php echo json_encode( $array_data_str ); ?>;

					google.charts.load('current', {
						'packages': ['geochart'],
					});

					google.charts.setOnLoadCallback(drawRegionsMap);

					function drawRegionsMap() {
						var data = google.visualization.arrayToDataTable([data_array]);
						var view = new google.visualization.DataView(data);
						view.setColumns([0, {
							type: 'number',
							label: 'Country',
							calc: function (dt, row) {
								return {
									v: dt.getValue(row, 1),
									f: dt.getFormattedValue(row, 1) + ' (' + dt.getFormattedValue(row, 2) + ' Country, ' + dt.getFormattedValue(row, 3) + ' Total, ' + dt.getFormattedValue(row, 4) + ' Order)'
								}
							}
						}]);

						var options = {
							region: 'IND',
							displayMode: 'markers',
							colorAxis: {colors: ['green', 'blue']}
						};
						var chart = new google.visualization.GeoChart(document.getElementById('regions_div'));
						chart.draw(view, data, options);
					}
				</script>
				<div class="mp-store-top-billing-country">
					<h4><?php esc_html_e( 'Top Billing Countries', 'wk-marketplace' ); ?></h4>
					<div id="regions_div" style=""></div>
				</div>
				<?php
			}
		}

		/**
		 * Get data.
		 *
		 * @return array
		 */
		private function wkmp_get_data() {
			global $wpdb;
			$per_page       = 10;
			$postid         = $this->seller_orders;
			$array_data_str = array();
			$array_data     = array();

			if ( $postid['order_item_id'] ) {

				$order_items = $wpdb->get_results(
					"SELECT sum(woi.meta_value) AS 'Total', postmeta.meta_value AS 'BillingCountry',Count(*) AS 'OrderCount' FROM {$wpdb->prefix}woocommerce_order_itemmeta woi
        left join {$wpdb->prefix}woocommerce_order_items wois on woi.order_item_id=wois.order_item_id
        left join {$wpdb->prefix}postmeta as postmeta on postmeta.post_id=wois.order_id
        WHERE  woi.meta_key='_line_total' AND wois.order_item_id in(" . $postid['order_item_id'] . ") AND  postmeta.meta_key='_billing_country'
        GROUP BY  postmeta.meta_value
        Order By OrderCount DESC
        LIMIT {$per_page}",
					ARRAY_A
				);

				$array_data[] = array( 'Country', 'Total', 'Order' );

				foreach ( $order_items as $key ) {
					$country      = WC()->countries->countries[ $key['BillingCountry'] ];
					$total        = (float) $key['Total'];
					$order_count  = (int) $key['OrderCount'];
					$array_local  = array( $country, $total, $order_count );
					$array_data[] = $array_local;
				}

				$array_data_str = $array_data;
			}

			return $array_data_str;
		}
	}
}
