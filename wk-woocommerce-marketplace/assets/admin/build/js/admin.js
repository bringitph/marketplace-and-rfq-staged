"use strict";
var wkJQ = jQuery.noConflict();

document.addEventListener("DOMContentLoaded", function () {
	if (wkJQ('.wkmp-select2').length) {
		wkJQ('.wkmp-select2').select2();
	}
});

wkJQ(document).ready(function () {
	// Paying seller amount from backend by clicking 'Pay' button.
	wkJQ('.wp-list-table.sellerorders').on('click', '.admin-order-pay', function () {
		let confirm = window.confirm(wkmpObj.commonConfirmMsg);
		if (confirm) {
			let order_seller_id = wkJQ(this).data('id');
			let anchor_el = wkJQ(this);
			let parent_el_td = wkJQ(anchor_el).parent('td');
			wkmp_update_order_status(order_seller_id, parent_el_td);
		}
	});

	wkJQ('.wkmp-approve-for-seller').on('click', function () {
		let curr_obj = wkJQ(this);
		let seller_id = curr_obj.data('seller-id');

		wkJQ.ajax({
			type: 'POST',
			url: wkmpObj.ajax.ajaxUrl,
			data: {
				"action": "wkmp_approve_seller",
				"seller_id": seller_id,
				"wkmp_nonce": wkmpObj.ajax.ajaxNonce
			},
			beforeSend: function () {
				curr_obj.text('Processing');
			},
			success: function (json) {
				if (json['success']) {
					if ('approve' === json['action']) {
						curr_obj.removeClass('button-warning').addClass('button-success').text(json['message']);
					} else if ('disapprove' === json['action']) {
						curr_obj.removeClass('button-success').addClass('button-warning').text(json['message']);
					}
				}
			}
		})
	});

	wkJQ('.seller-query-revert').on('click', function (evt) {
		wkJQ('.text-danger').remove();
		let query_id = wkJQ(this).data('qid');
		let reply_message = wkJQ(this).prev('div').find('.admin_msg_to_seller').val();
		reply_message = reply_message.replace(/\r\n|\r|\n/g, "<br/>");
		if (reply_message.length < 5) {
			wkJQ(this).prev('div').find('.admin_msg_to_seller').before('<div class="text-danger">Message should be more than five character</div>');
			return false;
		}

		wkJQ.ajax({
			type: 'POST',
			url: wkmpObj.ajax.ajaxUrl,
			data: {
				"action": "wkmp_admin_replied_to_seller",
				"qid": query_id,
				"reply_message": reply_message,
				"wkmp_nonce": wkmpObj.ajax.ajaxNonce
			},
			success: function (json) {
				if (json['success']) {
					alert(json['message']);
					location.reload()
				} else {
					alert(json['message']);
				}
			}

		})
	});

	if (wkJQ(".return-seller select").length) {
		wkJQ(".return-seller select").select2()
	}

	wkJQ('select#role').on('change', function () {
		if (wkJQ(this).val() === 'wk_marketplace_seller') {
			wkJQ('.mp-seller-details').show();
			wkJQ('#org-name').focus();
		} else {
			wkJQ('.mp-seller-details').hide();
		}
	});

	wkJQ('#org-name').on('focusout', function () {
		var value = wkJQ(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
		if ('' === value) {
			wkJQ('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text(wkmpObj.shop_name);
			wkJQ('#org-name').focus();
		} else {
			wkJQ('#seller-shop-alert-msg').text("");
		}
		wkJQ('#seller-shop').val(value);
	});

	wkJQ('#seller-shop').on('focusout', function () {
		var self = wkJQ(this);
		wkJQ.ajax({
			type: 'POST',
			url: wkmpObj.ajax.ajaxUrl,
			data: {"action": "wkmp_check_myshop", "shop_slug": self.val(), "wkmp_nonce": wkmpObj.ajax.ajaxNonce},
			success: function (response) {
				if (0 === response) {
					wkJQ('#seller-shop-alert').removeClass('text-success').addClass('text-danger');
					wkJQ('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text('Not Available');
				} else if (2 === response) {
					wkJQ('#seller-shop-alert').removeClass('text-success').addClass('text-danger');
					wkJQ('#seller-shop-alert-msg').removeClass('text-success').addClass('text-danger').text('Already Exists');
					wkJQ('#org-name').focus();
				} else {
					wkJQ('#seller-shop-alert').removeClass('text-danger').addClass('text-success');
					wkJQ('#seller-shop-alert-msg').removeClass('text-danger').addClass('text-success').text('Available');
				}
			}
		});
    });

    //Changing dashboard from frontend to backend and vice versa.
	wkJQ('#wp-admin-bar-mp-seperate-seller-dashboard a').on('click', function (ev) {
		ev.preventDefault();
		wkJQ.ajax({
			type: 'POST',
			url: wkmpObj.ajax.ajaxUrl,
			data: {
				"action": "wkmp_change_seller_dashboard",
				"change_to": 'front_dashboard',
				"wkmp_nonce": wkmpObj.ajax.ajaxNonce
			},
			success: function (data) {
				if (data) {
					window.location.href = data.redirect;
				}
			}
		})
	});

	wkJQ('#wk-endpoint-submit').on('click', function (ev) {
		ev.preventDefault();
		wkJQ('.text-danger').remove();
		let error = false;
		let value = '';

		wkJQ('#wkmp-endpoint-form').find('input[type=text]').each(function () {
			if ('endpoint' === wkJQ(this).attr('etype')) {
				value = wkJQ(this).val().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
				wkJQ(this).val(value);
			}

			if ('' === wkJQ.trim(wkJQ(this).val())) {
				wkJQ(this).after('<div class="text-danger" style="margin-left:20px;">' + wkmpObj.text_required + '</div>');
				error = true;
			}
		});

		if (error === false) {
			wkJQ('#wkmp-endpoint-form').find('input[type=text]').each(function () {
				var current = wkJQ(this);
				wkJQ('#wkmp-endpoint-form').find('input[type=text]').each(function () {
					if (wkJQ(this).val() === current.val() && wkJQ(this).attr('name') !== current.attr('name')) {
						current.after('<div class="text-danger" style="margin-left:20px;">' + wkmpObj.text_unique + '</div>');
						error = true;
					}
				});
			});

			if (false === error) {
				wkJQ('#wkmp-endpoint-form').submit();
			} else {
				wkJQ('html, body').animate({scrollTop: 100}, 'slow');
			}
		}
	});

	// Showing/hiding maximum qty field depending on Sold individually checkbox status.
	wkJQ('input#_sold_individually').on('change', function () {
		if (wkJQ(this).is(':checked')) {
			wkJQ('._wkmp_max_product_qty_limit_field').hide();
		} else {
			wkJQ('._wkmp_max_product_qty_limit_field').show();
		}
	}).trigger('change');

	// Performing order action on seller action.
	wkJQ('select.wkmp_seller_order_action').on('change', function () {
		let select_el = wkJQ(this);
		let action_data = wkJQ(select_el).val();
		if (action_data) {
			let confirm = window.confirm(wkmpObj.commonConfirmMsg);
			if (confirm) {
				let parent_el_td = wkJQ(select_el).parent('td');
				wkmp_update_order_status(action_data, parent_el_td);
			}else{
				wkJQ(select_el).prop('selectedIndex', 0);
			}
		}
	});

	/**
	 * Common function for paying and updating order status.
	 */
	function wkmp_update_order_status(action_data, parent_td_el) {
		wkJQ.ajax({
			type: 'POST',
			url: wkmpObj.ajax.ajaxUrl,
			data: {
				"action": "wkmp_update_seller_order_status",
				"action_data": action_data,
				"wkmp_nonce": wkmpObj.ajax.ajaxNonce
			},
			beforeSend: function () {
				parent_td_el.html('<span class="wkmp-order-status spinner"></span>');
			},
			success: function (response) {
				if (true === response.success) {
					parent_td_el.find('.wkmp-order-status.spinner').replaceWith(response.new_action_html);
					wkJQ('.my-acf-notice.is-dismissible').html('<p>' + response.message + '</p>').removeClass('wkmp-hide');
				} else {
					parent_td_el.find('.wkmp-order-status.spinner').replaceWith('<button class="button button-primary" disabled>' + wkmpObj.failed_btn + '</button>');
					wkJQ('.my-acf-notice.is-dismissible').html('<p>' + response.message + '</p>').removeClass('wkmp-hide');
				}
			},
		});
	}
});
