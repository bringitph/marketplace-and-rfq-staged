<?php
/**
 * Google analytics settings template
 *
 * @package Multi Vendor Marketplace
 * @version 5.0.1
 */

defined( 'ABSPATH' ) || exit;
settings_errors();
?>
<form method="POST" action="options.php">
<?php settings_fields( 'wkmp-google-analytics-settings-group' ); ?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-enable-google-analytics"><?php esc_html_e( 'Enable Google analytics', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, google analytics data will be populated.', 'wk-marketplace' ), true ); // WPCS: XSS ok. ?>
				<input name="_wkmp_enable_google_analytics" type="checkbox" id="wkmp_enable_google_analytics" value="1" <?php checked( get_option( '_wkmp_enable_google_analytics' ), 1 ); ?> />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wkmp-google-account-number">
					<?php esc_html_e( 'Account Number', 'wk-marketplace' ); ?>
				</label>
			</th>
			<td class="forminp forminp-text">
			<?php echo wc_help_tip( esc_html__( 'Google analytics tracking ID to be obtained from Google Analytics Account.', 'wk-marketplace' ), true ); // WPCS: XSS ok. ?>
			<input type="text" class="regular-text" id="wkmp-google-account-number" name="_wkmp_google_account_number" value="<?php echo esc_attr( get_option( '_wkmp_google_account_number' ) ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="anonymizeIp">
				<label for="wkmp-analytics-anonymize-ip"><?php esc_html_e( 'Anonymize IP', 'wk-marketplace' ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<?php echo wc_help_tip( esc_html__( 'If checked, IP of customor will be anonymized during tracking.', 'wk-marketplace' ), true ); // WPCS: XSS ok. ?>
				<input name="_wkmp_analytics_anonymize_ip" type="checkbox" id="wkmp_analytics_anonymize_ip" value="1" <?php checked( get_option( '_wkmp_analytics_anonymize_ip' ), 1 ); ?> />
			</td>
		</tr>
		<?php do_action( 'wkmp_add_settings_field' ); ?>
	</tbody>
</table>
<?php submit_button( esc_html__( 'Save Changes', 'wk-marketplace' ), 'primary' ); ?>
</form>
<hr/>
