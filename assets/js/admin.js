jQuery( function( $ ) {

	/**
	 * Variations Price Matrix actions
	 */
	var cf7_wpdb_admin = {

		/**
		 * Initialize variations actions
		 */
		init: function() {
			$(document).on('click', '.cf7wpdb_settings', this.cf7wpdb_settings);
			$(document).on('click', '.cf7wpdb-settings-save', this.cf7wpdb_settings_save);
			$(document).on('click', '.cf7wpdb_export', this.cf7wpdb_exports);
			$(document).on('click', '.btn-cf7-apply', this.cf7wpdb_do_action);

			this.page_wpcf7();
	

		},
		
		cf7wpdb_do_action: function() {
			
			var selected = $(this).closest('.bulkactions').find('.cf7-action-select').val(); 

			var val = [];
			$('.cf7_id:checkbox:checked').each(function(i){
			  val[i] = $(this).val();
			});

			
			if( selected != '-1' && val.length > 0 ) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: 'cf7wpdb_action',
						selected: selected,
						data: val
					},
					type: 'POST',
					datatype: 'json',
					success: function( response ) {
						var rs = JSON.parse(response);
						
						if( rs.complete != undefined ) {
							location.reload();
						}
					},
					error:function(){
						alert('There was an error when processing data, please try again !');
					}
				});
			}
			
			return false;
		},
		
		page_wpcf7: function(){
			if($('body').hasClass('toplevel_page_wpcf7')){
				$('.wp-list-table thead tr, .wp-list-table tfoot tr').append('<th scope="col" id="count_send" class="manage-column cf7wwpdb-column-count">' + cf7wpdb.label.count + '</th>');

				$( "#the-list tr" ).each(function( index ) {
					var href = $(this).find('.row-title').attr('href');
					var fid = getParameterByName('post', href);
					$(this).find('.row-actions').append(' | <span class="data"><a href="' + cf7wpdb.admin_url + '&post=' + fid + '">' + cf7wpdb.label.data + '</a></span>');

	
					$(this).append('<td class="count cf7wwpdb-column-count" data-colname="Count"><a href="' + cf7wpdb.admin_url + '&post=' + fid + '">' + cf7wpdb.data['cf7' + fid] + '</a></td>');

				});
			}
		},
		cf7wpdb_exports: function(){
			var export_to = $('#cf7wpdb-exports').val();
			console.log(export_to);
			if(export_to != '-1'){

				var checked = [];
				$("input[name='cf7wpdb_row_id[]']:checked").each(function ()
				{
				    checked.push(parseInt($(this).val()));
				});


			// $.ajax({
			// 	url: cf7wpdb.ajax_url,
			// 	data: {
			// 		action: 'cf7wpdb_export',
			// 		id: $('#frm-cf7wpdb-settings').attr('data-id'),
			// 		checked: checked
			// 	},
			// 	type: 'POST',
			// 	datatype: 'json',
			// 	success: function( response ) {
			// 		var rs = JSON.parse(response);
				
					
			// 		wc_meta_boxes_price_matrix_ajax.unblock();
			// 	},
			// 	error:function(){
			// 		alert('There was an error when processing data, please try again !');
			// 		wc_meta_boxes_price_matrix_ajax.unblock();
			// 	}
			// });
				$.fileDownload(cf7wpdb.ajax_url, {
				    httpMethod: 'POST',
				    data: {
				        action: 'cf7wpdb_export', id: $('#cf7wpdb-exports').attr('data-fid'), checked: checked
				    },
				    successCallback: function (url) {
				        //insert success code
				        console.log('úccc' + url);
				    },
				    failCallback: function (html, url) {
				        //insert fail code
				    }
				});
			}else{
				alert('Please select format export.');
			}
			return false;
		},
		cf7wpdb_settings: function(){
			$.magnificPopup.open({
				items: {
					src: '#price-matrix-popup'
				},
				type: 'inline',
				midClick: true,
				mainClass: 'mfp-fade',
				 callbacks: {
				 	open: function(){
				 		
				 	}
				 }
			});
			return false;
		},
		cf7wpdb_settings_save: function(){

			$(".cf7wpdb-settings-save").prop('disabled', true);

			$.ajax({
				url: cf7wpdb.ajax_url,
				data: {
					action:     'cf7wpdb_set_label',
					id: $('#frm-cf7wpdb-settings').attr('data-id'),
					data: $('#frm-cf7wpdb-settings').serialize()
				},
				type: 'POST',
				datatype: 'json',
				success: function( response ) {						
					$(".cf7wpdb-settings-save").prop('disabled', false);
					$.magnificPopup.close();
					location.reload();
				},
				error:function(){
					alert('There was an error when processing data, please try again !');
					wc_meta_boxes_price_matrix_ajax.unblock();
				}
			});
			return false;
		}
	}

	cf7_wpdb_admin.init();
});

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}