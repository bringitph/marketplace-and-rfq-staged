<?php
/**
 * Load add account.
 *
 * @author Webkul.
 */

namespace wooMarketplaceRFQ\Templates\Admin\Menu\Attribute;

use wooMarketplaceRFQ\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Womprfq_Manage_Attribute' ) ) {
	/**
	 * Class for add/update Attribute.
	 */
	class Womprfq_Manage_Attribute {

		protected $action;
		protected $attrid = '';

		/**
		 * Class constructor.
		 */
		public function __construct( $action, $attrid ) {
			$this->action = $action;
			$this->attrid = (int) $attrid;
			$this->womprfq_get_attribute_template();
		}

		/**
		 * Class for edit price template.
		 */
		public function womprfq_get_attribute_template() {
			if ( $this->action == 'add' ) {
				$page_title = esc_html__( 'Add Attribute', 'wk-mp-rfq' );
			} elseif ( $this->action == 'update' ) {
				$page_title = esc_html__( 'Update Attribute', 'wk-mp-rfq' );
			}
			$att_obj = new Helper\Womprfq_Attribute_Handler();

			?>
			<div class="wrap woocommerce">
				<h1 class="wp-heading-inline"><?php esc_html_e( $page_title ); ?></h1>
				<a class="page-title-action" href="<?php echo esc_url( admin_url() ); ?>admin.php?page=wc-mp-rfq-attributes" class="page-title-action"><?php esc_html_e( 'Back', 'wk-mp-rfq' ); ?></a>
				<hr>
				<h2><?php esc_html_e( 'Attribute Information', 'wk-mp-rfq' ); ?></h2>
				<?php
				if ( isset( $_POST['mprfq-newattribute-submit'] ) ) {
					$postdta = $_POST;

					if ( ! empty( $postdta['wc-mprfq-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-mprfq-nonce'] ), 'wc-mprfq-nonce-action' ) ) {

						if ( isset( $postdta['womprfq-deafult-label'] ) && ! empty( $postdta['womprfq-deafult-label'] ) ) {
							$attr_label = wc_sanitize_textarea( $postdta['womprfq-deafult-label'] );
						}
						if ( isset( $postdta['wpmprfq-attribute-type'] ) && ! empty( $postdta['wpmprfq-attribute-type'] ) ) {
							$attr_type = wc_sanitize_textarea( $postdta['wpmprfq-attribute-type'] );
						}
						if ( isset( $postdta['wpmprfq-attribute-require-type'] ) && ! empty( $postdta['wpmprfq-attribute-require-type'] ) ) {
							$attr_require = intval( $postdta['wpmprfq-attribute-require-type'] );
						}
						if ( isset( $postdta['wpmprfq-attribute-status'] ) && ! empty( $postdta['wpmprfq-attribute-status'] ) ) {
							$attr_status = intval( $postdta['wpmprfq-attribute-status'] );
						}
						if ( ! empty( $attr_label ) && ! empty( $attr_type ) && intval( $attr_require ) != 0 && intval( $attr_status ) != 0 ) {

							$info = array(
								'label'    => $attr_label,
								'type'     => $attr_type,
								'required' => $attr_require,
								'status'   => $attr_status,
							);

							if ( $this->attrid == 0 ) {
								$response = $att_obj->wkwooshopify_update_attribute_info( $info );
							} else {
								$response = $att_obj->wkwooshopify_update_attribute_info( $info, $this->attrid );
							}

							if ( $response['success'] ) {
								$wk_message[] = array(
									'status' => 'updated',
									'msg'    => $response['msg'],
								);
							} else {
								$wk_message[] = array(
									'status' => 'error',
									'msg'    => $response['msg'],
								);
							}
						} else {
							$wk_message[] = array(
								'status' => 'error',
								'msg'    => __( 'Please enter values in all required fields.', 'wk-mp-rfq' ),
							);
						}
					}
				}

				if ( ! empty( $wk_message ) ) {
					foreach ( $wk_message as $wk_msg ) {
						?>
							<div id="message" class="<?php esc_attr_e( $wk_msg['status'] ); ?> inline"><p><strong><?php esc_html_e( $wk_msg['msg'] ); ?></strong></p></div>
						<?php
					}
				}
				$fieldtype = array(
					array(
						'type'  => 'text',
						'title' => esc_html__( 'Text', 'wk-mp-rfq' ),
					),
					array(
						'type'  => 'number',
						'title' => esc_html__( 'Number', 'wk-mp-rfq' ),
					),
				);
				if ( $this->attrid != 0 ) {
					$attr_dta = $att_obj->womprfq_get_attribute_info( $this->attrid );
					if ( $attr_dta ) {
						$require_type = $attr_dta[0]->required;
						$deflt_label  = $attr_dta[0]->label;
						$selected     = $attr_dta[0]->type;
						$status       = $attr_dta[0]->status;
					} else {
						wp_safe_redirect( admin_url( 'admin.php?page=wc-mp-rfq-attributes' ) );
						wp_die();
					}
				} else {
					$require_type = '';
					$deflt_label  = '';
					$selected     = '';
					$status       = '';
				}

				?>
				<form method="POST" class="wk-wpomprfq-form" id="wk-womprfq-manage-attribute-form">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="womprfq-deafult-label"><?php esc_html_e( 'Default Label', 'wk-mp-rfq' ); ?></label>
								</th>
								<td class="forminp">
									<span class="required" style="display:inline-block;!important">*</span>
									<input type="text" class="regular-text" id="womprfq-deafult-label" autoComplete="Off" name="womprfq-deafult-label" value="<?php echo isset( $deflt_label ) ? esc_html( $deflt_label ) : ''; ?> "/>
									<?php echo wc_help_tip( esc_html__( 'Set default label to the Attribute.', 'wk-mp-rfq' ), false ); ?>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wpmprfq-attribute-type"><?php esc_html_e( 'Attribute Type', 'wk-mp-rfq' ); ?></label>
								</th>
								<td class="forminp">
									<span class="required" style="display:inline-block;!important">*</span>
									<select name="wpmprfq-attribute-type" class="regular-text" id="wpmprfq-attribute-type">
										<option value=""><?php esc_html_e( 'Select', 'wk-mp-rfq' ); ?></option>
										<?php
										foreach ( $fieldtype as $field ) {
											?>
											<option value="<?php echo esc_attr( $field['type'] ); ?>" <?php selected( ( isset( $field['type'] ) && ( $field['type'] == $selected ) ) ? $field['type'] : '', $selected ); ?>>
												<?php echo esc_attr( $field['title'] ); ?>
											</option>
											<?php
										}
										?>
									</select>
									<?php echo wc_help_tip( esc_html( 'Set Field type.', 'wk-mp-rfq' ), false ); ?>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wpmprfq-attribute-require-type"><?php esc_html_e( 'Required Type', 'wk-mp-rfq' ); ?></label>
								</th>
								<td class="forminp">
									<span class="required" style="display:inline-block;!important">*</span>
									<select class="regular-text" name="wpmprfq-attribute-require-type" id="wpmprfq-attribute-require-type">
										<option value="1" <?php selected( ( isset( $require_type ) && ( $require_type == 1 ) ) ? $require_type : '', 1 ); ?>><?php echo esc_html__( 'Not required', 'wk-mp-rfq' ); ?></option>
										<option value="2" <?php selected( ( isset( $require_type ) && ( $require_type == 2 ) ) ? $require_type : '', 2 ); ?>><?php echo esc_html__( 'Required', 'wk-mp-rfq' ); ?></option>
									</select>
									<?php echo wc_help_tip( esc_html( 'Set required type.', 'wk-mp-rfq' ), false ); ?>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="wpmprfq-attribute-status"><?php esc_html_e( 'Attribute Status', 'wk-mp-rfq' ); ?></label>
								</th>
								<td class="forminp">
									<span class="required" style="display:inline-block;!important">*</span>
									<select name="wpmprfq-attribute-status" class="regular-text" id="wpmprfq-attribute-status">
										<option value="1" <?php selected( ( isset( $status ) && ( $status == 1 ) ) ? $status : '', 1 ); ?>><?php echo esc_html__( 'Enable', 'wk-mp-rfq' ); ?></option>
										<option value="2" <?php selected( ( isset( $status ) && ( $status == 2 ) ) ? $status : '', 2 ); ?>><?php echo esc_html__( 'Disable', 'wk-mp-rfq' ); ?></option>
									</select>
									<?php echo wc_help_tip( esc_html( 'Set attribute status.', 'wk-mp-rfq' ), false ); ?>
								</td>
							</tr>
						</tbody>
					</table>

					<p id="wk-mprfq_attribute-submit-section">
						<?php wp_nonce_field( 'wc-mprfq-nonce-action', 'wc-mprfq-nonce' ); ?>
						<input type="submit" name="mprfq-newattribute-submit" value="<?php esc_html_e( 'Save', 'wk-mp-rfq' ); ?>" class="button button-primary" />
					</p>
				</form>
			</div>
			<?php
		}
	}
}
