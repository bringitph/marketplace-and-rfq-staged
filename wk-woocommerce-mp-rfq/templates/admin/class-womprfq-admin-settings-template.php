<?php
/**
 * Load ajax functions.
 *
 * @author     Webkul.
 * @implements Assets_Interface
 */

namespace wooMarketplaceRFQ\Templates\Admin;
use \WC_Product_Variation;
use \WC_Product_Data_Store_CPT;
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Womprfq_Admin_Settings_Template')) {
    /**
     * Class loads scripts.
     */
    class Womprfq_Admin_Settings_Template
    {
        /**
         * Show price rule template.
         *
         * @return void.
         */
        public function womprfq_get_setting_templates()
        {
            $plug_status = get_option('womprfq_status');
            $approve_status = get_option('womprfq_approval_require');
            ?>
            <div class="wrap">

                <h1 class="wp-heading-inline">
                <?php echo esc_html__('Request For Quote Settings', 'wk-mp-rfq'); ?>
                </h1>

                <form method="post" action="options.php">

                <?php settings_errors();?>

                <?php settings_fields('woo-mp-rfq-settings-group');?>

                <?php do_settings_sections('woo-mp-rfq-settings-group');?>

                    <table class="form-table">

                        <tbody>

                            <tr valign="top">
                                <th width="250" scope="row"><?php esc_html_e('Select Status', 'wk-mp-rfq');?></th>
                                <td class="forminp">
                                    <select width="250" name="womprfq_status" id="womprfq_status" style="min-width: 450px;">
                                        <option value="1" <?php if ($plug_status==1) { echo esc_attr('selected'); 
                                       } ?>><?php esc_html_e('Disable', 'wk-mp-rfq') ?></option>
                                        <option value="2" <?php if ($plug_status==2) { echo esc_attr('selected'); 
                                       } ?>><?php esc_html_e('Enable', 'wk-mp-rfq') ?></option>
                                    </select>
                                    <?php echo wc_help_tip(esc_html('Select status fo plugin.', 'wk-mp-rfq'), false); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th width="250" scope="row"><?php esc_html_e('Minimum Quantity for “Request For Quote”', 'wk-mp-rfq');?></th>
                                <td class="forminp forminp-text">
                                    <input type="text" id="womprfq_minimum_quantity" name="womprfq_minimum_quantity" value="<?php echo esc_attr(get_option('womprfq_minimum_quantity')); ?>" style="min-width: 450px;" />
                                    <br />
                                    <p class="description"><?php echo esc_html__('Enter minimum Quantity of quotation.', 'wk-mp-rfq'); ?></p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th width="250" scope="row"><?php esc_html_e('Request For Quote Approval Required', 'wk-mp-rfq');?></th>
                                <td class="forminp">
                                    <select width="250" name="womprfq_approval_require" id="womprfq_approval_require" style="min-width: 450px;">
                                        <option value="1" 
                                        <?php 
                                        if ($approve_status==1) {
                                            echo esc_attr('selected'); 
                                        } 
                                        ?>>
                                            <?php esc_html_e('Disable', 'wk-mp-rfq') ?>
                                        </option>
                                        <option value="2" 
                                        <?php 
                                        if ($approve_status==2) { 
                                            echo esc_attr('selected'); 
                                        } 
                                        ?>>
                                            <?php esc_html_e('Enable', 'wk-mp-rfq') ?>
                                        </option>
                                    </select>
                                    <?php echo wc_help_tip(esc_html('Select Yes if admin approval is required for sending the RFQs to the sellers.', 'wk-mp-rfq'), false); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php submit_button();?>
                </form>
            </div>
            <?php
        }
    
        /**
         * Add screen option
         *
         * @return void.
         */
        public function womprfq_add_screen_option()
        {
            $options = 'per_page';
            $args = array(
                'label' => esc_html__('Items Per Page', 'wk-mp-rfq'),
                'default' => 20,
                'option' => 'womprfq_per_page',
            );
            add_screen_option($options, $args);
        }
    }
}