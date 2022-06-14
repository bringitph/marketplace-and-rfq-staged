"use strict";
const wkmprfq = jQuery.noConflict();

wkmprfq(document).ready(function ($) {	
	
	var quote_div = document.getElementById('womp-rfq-quote-wraper');
	if (quote_div){
		var variation = document.getElementsByClassName('variations');
		
		if (variation[0]) {
			let selctdiv = variation[0].querySelectorAll('select');
			selctdiv.forEach(sltdiv => {
				sltdiv.addEventListener('change', function (et) {
					et.preventDefault();
					let prev_div = document.getElementById('wpmp-rfq-button');
					if (prev_div) {
						prev_div.remove();
					}
					setTimeout(() => {
						let var_id = document.getElementsByClassName('variation_id')[0];
						if (parseInt(var_id.value) > 0) {
							womprfq_get_variation_template(parseInt(var_id.value))
						}
					}, 100);
				});
			});
		}
	}
	
	document.addEventListener('click', function (q) {
		if (q.target && q.target.id == 'wpmp-rfq-button') {
			q.preventDefault();
			let pro_id = q.target.getAttribute('data-product');
			let var_id = q.target.getAttribute('data-variation');
			if (parseInt(pro_id) > 0) {
				womprfqShowQuoationPopUp(pro_id, var_id);	
			}
		} else if(q.target && q.target.id == 'wpmp-rfq-form-upload-button') {
			wkMpImageUpload(q);
		} else if (q.target && (q.target.id == 'wpmp-rfq-quote-form-close' || q.target.id == 'wpmp-rfq-quote-dialog-box-wrap')) {
			document.getElementById('wpmp-rfq-quote-dialog-box-wrap').remove();
			document.getElementsByTagName('body')[0].style.overflow = 'auto';
		} else if (q.target && q.target.id == 'wpmp-rfq-form-quote-submit') {
			q.preventDefault();
			// put additional checks regading form
			let formData = wkmprfq('#wpmp-rfq-quote-form').serializeArray();
			let err = false;
			let form = document.getElementById('wpmp-rfq-quote-form');
			
			for (var i = 0; i < form.elements.length; i++) {
				if (form.elements[i].value === '' && form.elements[i].hasAttribute('required')) {
					err = true;
				}
			}
			if (!err) {
				womprfqSubmitQuotationForm(formData);
			}else{
				alert(womprfq_script_obj.rfq_trans_arr.rfq3);
			}
		} else if (q.target && q.target.classList.contains('wpmp-rfq-form-pro-img-wrap')) {
			let img_id = q.target.getAttribute('data-image-id');
			if(typeof(img_id)!='undefined'&& img_id!='') {
				let img_arr = document.querySelector('#wpmp-rfq-form-sample-img').value;
				if (typeof(img_arr)!='undefined'&& img_arr!='') {
					let im_ar = img_arr.split(',');
					let ind = im_ar.indexOf(img_id);
					im_ar.splice(ind, 1);
					img_arr = im_ar.join(',');
					if (typeof (img_arr)!='undefined') {
						document.querySelector('#wpmp-rfq-form-sample-img').value = img_arr;
						q.target.remove();
					}
				}
			}
		}
	});
	
	async function womprfqShowQuoationPopUp(pro_id, var_id)
	{
		let data = {
			action: 'womprfq_return_productdata_quotation_form',
			product_id: parseInt(pro_id),
			variation_id: parseInt(var_id),
			nonce: womprfq_script_obj.product_ajax_nonce,
		}
		
		let res = await womprfq_ajax_call(data);
		
		if (res.status) {
			let quoteDialog = wp.template("womprfq_popup_template");
			document.getElementsByTagName('body')[0].style.overflow = 'hidden';
			document.getElementById('wpmp-rfq-button').insertAdjacentHTML("afterend", quoteDialog({
				product_name: res.data.pname,
				image: res.data.imgdata,
				admin_attribute: res.data.adminattrdata
			}));
		} else {
			alert(res.message)
			location.reload();
		}
	} 
	
	async function womprfqSubmitQuotationForm(formData) {
		let pro_info = document.getElementById('wpmp-rfq-button');
		let pro_id = pro_info.getAttribute('data-product');
		let loader = document.getElementById('wk-mp-loader-rfq');
		let var_id = pro_info.getAttribute('data-variation');
		if (loader){
			loader.style.display = 'block';
		}
		let data = {
			action: 'womprfq_submit_quotation_form',
			product: parseInt(pro_id),
			user_id: parseInt(womprfq_script_obj.customer_id),
			variation_id: parseInt(var_id),
			form_data: JSON.stringify(formData),
			nonce: womprfq_script_obj.product_ajax_nonce,
		}
		let res = await womprfq_ajax_call(data);
		if (res.success) {
			window.location.reload();
		} else{
			if (loader) {
				loader.style.display = 'none';
			}	
			alert(res.msg);
		}
	}
	
	function wkMpImageUpload(evt) {
		evt.preventDefault();
		var file_frame;
		var image_id_field = document.getElementById('wpmp-rfq-form-sample-img').value;
		var galary_ids = '';
		var typeError = 0;
		document.getElementById('wpmp-rfq-form-sample-img-error').innerHTML = '';
		if (image_id_field == '') {
			galary_ids = '';
		} else {
			galary_ids = image_id_field + ',';
		}
		if (file_frame) {
			file_frame.open();
			return;
		}
		file_frame = wp.media.frames.file_frame = wp.media({
			title: womprfq_script_obj.rfq_trans_arr.rfq1,
			button: { text: womprfq_script_obj.rfq_trans_arr.rfq2 },
			multiple: true
		});
		file_frame.on('open', function () {
			var selection = file_frame.state().get('selection');
		});
		var query = wp.media.query();
		query.filterWithIds = function (ids) {
			return _(this.models.filter(function (c) { return _.contains(ids, c.id); }));
		};
		file_frame.on('select', function () {
			var selection = file_frame.state().get('selection');
			var attachment_ids = selection.map(function (attachment) {
				attachment = attachment.toJSON();
				if (attachment.sizes != undefined) {
					galary_ids = galary_ids + attachment.id + ',';
					
					if (typeof(attachment.sizes.thumbnail) !== 'undefined'){
						wkmprfq('#wpmp-rfq-form-image').append("<span class='wpmp-rfq-form-pro-img-wrap' data-image-id='" + attachment.id+"'><img src='" + attachment.sizes.thumbnail.url + "' class='wpmp-rfq-form-pro-img'/></span>");
					}else{
						wkmprfq('#wpmp-rfq-form-image').append("<span class='wpmp-rfq-form-pro-img-wrap' data-image-id='" + attachment.id +"'><img src='" + attachment.url + "' class='wpmp-rfq-form-pro-img'/></span>");
					}
					return attachment.id;
				} else {
					typeError = 1;
				}
			});
			if (typeError) {
				wkmprfq('#wpmp-rfq-form-sample-img-error').append("<p class=error-class>" + wkmprfq(".mp_product_thumb_image.button").data('type-error') + "</p>");
			}
			galary_ids = galary_ids.replace(/,\s*$/, "");
			wkmprfq('#wpmp-rfq-form-sample-img').val(galary_ids);
		});
		file_frame.open();
	}
		
	async function womprfq_get_variation_template(var_id){
		let wrap_div = document.getElementById('wpmp-rfq-button');
		if (wrap_div){
			wrap_div.remove();
		}
		let data = {
			action: 'womprfq_get_product_template',
			product: womprfq_script_obj.product_id,
			variation_id: parseInt(var_id),
			nonce: womprfq_script_obj.product_ajax_nonce,
		}

		let res = await womprfq_ajax_call(data);

		if (res.status) {
			document.getElementById('womp-rfq-quote-wraper').insertAdjacentHTML("afterend", res.template);
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