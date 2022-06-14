"use strict";
const wkmprfq = jQuery.noConflict();

wkmprfq(document).ready(function ($) {
	wkmprfq('#seller-quote-comment-image-add').on('click', function (e) {
		e.preventDefault();
		var image_id_field = document.getElementById('seller-quote-comment-image').value;
		var galary_ids = '';
		var typeError = 0;

		if (image_id_field == '') {
			galary_ids = '';
		} else {
			galary_ids = image_id_field + ',';
		}
		var file_frame;

		if (file_frame) {
			file_frame.open();
			return;
		}

		var file_frame = wp.media({
			title: womprfq_script_obj.rfq_trans_arr.rfq1,
			button: {
				text: womprfq_script_obj.rfq_trans_arr.rfq2,
			},
			multiple: true
		}).on('select', function () {
			var selection = file_frame.state().get('selection');
			var attachment_ids = selection.map(function (attachment) {
				attachment = attachment.toJSON();
				if (attachment.sizes != undefined) {
					galary_ids = galary_ids + attachment.id + ',';
					if (typeof (attachment.sizes.thumbnail) != 'undefined') {
						wkmprfq('#wk-mp-rfq-image-container').append("<img src='" + attachment.sizes.thumbnail.url + "' class='wpmp-rfq-form-pro-img'/>");
					} else {
						wkmprfq('#wk-mp-rfq-image-container').append("<img src='" + attachment.url + "' class='wpmp-rfq-form-pro-img'/>");

					}
					return attachment.id;
				} else {
					typeError = 1;
				}
			});
			if (typeError == 0) {
				galary_ids = galary_ids.replace(/,\s*$/, "");
				wkmprfq('#seller-quote-comment-image').val(galary_ids);
			}
		}).open();
	});

	wkmprfq(document).on('click', '#wkmp-rfq-create-product, #wkmp-rfq-update-product', function(et) {
		et.preventDefault();
		let m_quote_id = wkmprfq(this).attr('data-mqid');
		let s_quote_id = wkmprfq(this).attr('data-sqid');
		if (m_quote_id && s_quote_id){
			womprfq_close_quote_after_approval(parseInt(m_quote_id), parseInt(s_quote_id));
		}
	});

	async function womprfq_close_quote_after_approval(q_id, s_id) {
		let loader = document.getElementById('wk-mp-loader-rfq');
		let data = {
			action: 'womprfq_product_update_after_approval',
			m_quote_id: parseInt(q_id),
			s_quote_id: parseInt(s_id),
			nonce: womprfq_script_obj.seller_ajax_nonce,
		}
		if (loader) {
			loader.style.display = 'block';
		}
		let res = await womprfq_ajax_call(data);
		if (res.status) {
			location.reload();
		}else{
			if (loader) {
				loader.style.display = 'none';
			}
			alert(res.message);
		}
	}

	async function womprfq_ajax_call(info) {
		let strg = '';
		Object.keys(info).forEach(function (key) {
			strg = strg + key + '=' + info[key] + '&';
		});
		let retndta = await fetch(womprfq_script_obj.ajaxurl, {
			method: 'post',
			headers: new Headers({
				'Content-Type': 'application/x-www-form-urlencoded',
				'Accept': 'application/json'
			}),
			body: strg,
			credentials: 'same-origin',
		});
		return retndta.json();
	}
});
