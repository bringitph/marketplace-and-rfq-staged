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
	
	wkmprfq('#womprfq-notify-seller').on('click', function(p) {
		p.preventDefault();
		let id = p.target.getAttribute('data-mid');
		womprfq_notify_seller(id);
	});
	
	async function womprfq_notify_seller(q_id) {
		let wrap_div = document.getElementById('wpmp-rfq-button');
		if (wrap_div) {
			wrap_div.remove();
		}
		let data = {
			action: 'womprfq_notify_seller_via_mail',
			q_id: parseInt(q_id),
			nonce: womprfq_script_obj.admin_ajax_nonce,
		}

		let res = await womprfq_ajax_call(data);

		if (res.status) {
			location.reload();
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