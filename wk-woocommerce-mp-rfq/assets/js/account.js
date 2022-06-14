"use strict";
const wkmprfq = jQuery.noConflict();

wkmprfq(document).ready(function ($) {
	wkmprfq('#wpmp-rfq-form-upload-button').on('click', function (e) {
		e.preventDefault();
		var image_id_field = document.getElementById('wpmp-rfq-form-sample-img').value;
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
						wkmprfq('#wpmp-rfq-form-image').append("<img src='" + attachment.sizes.thumbnail.url + "' class='wpmp-rfq-form-pro-img'/>");
					} else {
						wkmprfq('#wpmp-rfq-form-image').append("<img src='" + attachment.url + "' class='wpmp-rfq-form-pro-img'/>");

					}
					return attachment.id;
				} else {
					typeError = 1;
				}
			});
			if (typeError == 0) {
				galary_ids = galary_ids.replace(/,\s*$/, "");
				wkmprfq('#wpmp-rfq-form-sample-img').val(galary_ids);
			}
		}).open();
	});
});
