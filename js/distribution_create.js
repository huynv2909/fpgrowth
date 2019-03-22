function dimenIdToName($dimen_id) {
	var dimen_arr = $('#dimension-list').val().split('|');
	var response = '';

	for (var i = 0; i < dimen_arr.length; i++) {
		var attr_arr = dimen_arr[i].split('~');
		if (attr_arr[0] == $dimen_id) {
			response = attr_arr[1];
			if ($.trim(attr_arr[2]) != '') {
				response += " : " + attr_arr[2];
			}
			break;
		}
	}

	return response;
}

function mngIdToName($mng_id) {
	var mng_arr = $('#mng-list').val().split('|');
	var response = '';

	for (var i = 0; i < mng_arr.length; i++) {
		var attr_arr = mng_arr[i].split('~');
		if (attr_arr[0] == $mng_id) {
			response = attr_arr[1];
			break;
		}
	}

	return response;
}

$(document).ready(function(){

   $('#voucher-choose').change(function(){
		var id = $(this).val();
		var url = $(this).data('url');

		if (id != 0) {
			$('#act-add-btn').prop('disabled', false);
			$('#act-choose').prop('disabled', false);

			// Set id chosen
			$('#act-add-btn').data('id', id);

			$.ajax({
				method: "POST",
				url: url,
				dataType: "JSON",
				data: {
					id : id
				},
				beforeSend: function() {
					$('.load-info').css('display', 'flex');
				},
				success: function(result) {
					$('.contain-voucher-info').html(result.voucher);
					$('#voucher_id').val(result.voucher_id);
					$('#TOT').val(result.TOT);

					var table = $('#act_table').DataTable();
					table.row().remove().draw();
					var html = '<option value="0" class="hidden">(Chọn bút toán)</option>';
					for (var i = 0; i < result.act.length; i++) {
						table.row.add([result.act[i].TOA,result.act[i].content,convertToCurrency(result.act[i].value) + ' đ',result.act[i].debit_acc,result.act[i].credit_acc]).draw();
						// Only distribution
						html += '<option value="' + result.act[i].id + '">' + result.act[i].TOA + ':' + result.act[i].content + ':' + convertToCurrency(result.act[i].value) + '</option>';
					}
					$('#act-choose').html(html);
					if (result.act.length == 1) {
						$('#act-choose').val(result.act[0].id);
						$('#act-choose').trigger("change");
					}
				},
				complete: function() {
					$('.load-info').css('display', 'none');
				}
			});

		} else {
			$('.contain-voucher-info').html('<h2 class="empty-info">(Hãy lựa chọn chứng từ)</h2>');

			var table = $('#act_table').DataTable();
			table.row().remove().draw();

			$('#act-add-btn').prop('disabled', true);
			$('#act-choose').prop('disabled', true);
		}

		$('.contain-act-info').html('<h2 class="empty-info">(Hãy lựa chọn bút toán)</h2>');

		var table = $('#distribution_table').DataTable();
		table.row().remove().draw();

		$('#distribute-btn').prop('disabled', true);

	});

   // When chooose accounting entry
	$('#act-choose').change(function(){
		var id = $(this).val();
		var url = $(this).data('url');

		if (id != 0) {
			$('#distribute-btn').prop('disabled', false);
			$('#distribute-update-btn').data('id', id);

			$.ajax({
				url : url,
				method : "POST",
				dataType : "json",
				data : {
					id : id
				},
				beforeSend : function(){
					$('.load-info').css('display', 'flex');
				},
				success : function(result){
					$('.contain-act-info').html(result.act);
					$('#tot').val(result.tot);
					$('#toa').val(result.toa);

					var table = $('#distribution_table').DataTable();
					table.row().remove().draw();
					for (var i = 0; i < result.distributes.length; i++) {
						table.row.add([result.distributes[i]['mng'],result.distributes[i]['dimensional_id'],convertToCurrency(result.distributes[i]['value']) + ' đ',result.distributes[i]['content']]).draw();
					}

					var val = "";
					for (var i = 0; i < result.dimensionals.length; i++) {
						var temp = result.dimensionals[i].id + "~" + result.dimensionals[i].name + "~" +  result.dimensionals[i].note + "|";
						val += temp;
					}
					$('#dimension-list').val(val);

					var val = "";
					for (var i = 0; i < result.mngs.length; i++) {
						var temp = result.mngs[i].id + "~" +  result.mngs[i].name + "|";
						val += temp;
					}
					$('#mng-list').val(val);
				},
				complete : function(){
					$('.load-info').css('display', 'none');
				}
			});

		} else {
			$('.contain-act-info').html('<h2 class="empty-info">(Hãy lựa chọn bút toán)</h2>');

			var table = $('#distribution_table').DataTable();
			table.row().remove().draw();

			$('#distribute-btn').prop('disabled', true);
		}
	});

   $(document).on("change", "#dimension", function(){
		var id = $(this).val();
		$('#detail-dimen-select').val('0');

		if (id > 0) {
			var url = $(this).data('url');

			$.ajax({
				url : url,
				method: "POST",
				dataType: "JSON",
				data: {
					id : id
				},
				success: function(result){
					var html = '<option value="0" selected class="hidden">(Lựa chọn)</option>';
					for (var i = 0; i < result.length; i++) {
						temp = '<option value="' + result[i].id + '" >' + result[i].name;
						if ($.trim(result[i].note) != '') {
							temp += ' : ' + $.trim(result[i].note);
						}
						temp += '</option>';

						html += temp;
					}
					$('#detail-dimen-select').html(html);
					$('#detail-dimen-select').prop('disabled', false);
				}
			});

		} else {
			$('#detail-dimen-select').html('');
			$('#detail-dimen-select').prop('disabled', true);
		}



	});

   // When distribute by get url
	if ($('#have-a-act-id').length && $.isNumeric($('#have-a-act-id').val())) {
		var id = $('#have-a-act-id').val();
		var url = $('#have-a-act-id').data('url');

		$.ajax({
			url : url,
			method : "POST",
			dataType : "JSON",
			data : {
				id : id
			},
			success : function(result) {
				if (result.success) {
					$('#voucher-choose').val(result.voucher_id);
					$('#voucher-choose').trigger("change");

					setTimeout(function(){
						$('#act-choose').val(id);
						$('#act-choose').trigger("change");
					},100);

				}
			}
		});

	}

	$('#distribute-btn').click(function(){
		$('.notice').fadeIn();

		setTimeout(function(){
			$('.notice').fadeOut();
		},5000)

		$(this).prop('disabled', true);
		var url = $(this).data('url');

		$.ajax({
			url : url,
			dataType : 'json',
			success : function(result) {
				var row = [];

				for(var i in result) {
					row.push(result[i]);
				}

				$('#distribution_table').DataTable().row.add(row).draw();
			}
		});

	});

   $('#distribute-update-btn').click(function(){
		var data = {
			'entry_id' : $(this).data('id'),
			'mng' : $('#dimension').val(),
			'dimen_id' : $('#detail-dimen-select').val(),
			'TOT' : $('#tot').val(),
			'TOA' : $('#toa').val(),
			'value' : $('#value').val(),
			'content' : $('#content').val()
		};
		var url = $(this).data('url');
		$.ajax({
			url : url,
			method : "POST",
			dataType : "json",
			data : data,
			beforeSend: function() {
				$('.load-info-act').css('display', 'flex');
			},
			success : function(result) {
				$('.alert').html(result.message);
				var table = $('#distribution_table').DataTable();
				var form_row = $('.form-cell').parent().parent();
				table.row(form_row).remove().draw();
				$('#distribute-btn').prop('disabled', false);
				$('#distribute-update-btn').prop('disabled', true);
				if (result.success) {
					$('#distribution_table').DataTable().row.add([mngIdToName(data.mng),dimenIdToName(data.dimen_id),data.value.toString() + " đ",data.content]).draw();

					$('.alert').addClass('alert-success');
				} else {
					$('.alert').addClass('alert-danger');
				}

				$('.alert').fadeIn();
				setTimeout(function(){
					$('.alert').fadeOut();
					$('.alert').removeClass('alert-success alert-danger');
				}, 3000);
			},
			complete: function() {
				$('.load-info-act').css('display', 'none');
			}
		});

	});

   // When change, check valid input
	$(document).on("change", "#dimension, #detail-dimen-select, #value", checkToEnableOk);
	$(document).on("keyup", "#value", checkToEnableOk);

});

function checkToEnableOk() {
	var flag = true;
	var str_value = $('#value').val();
	if ($.trim(str_value) == '') {
		flag = false;
	}

	str_value = str_value.split('.').join('');
	if (!$.isNumeric(str_value)) {
		flag = false;
	}
	if ($('#dimension').val() == 0) {
		flag = false;
	}
	if ($('#detail-dimen-select').length && ($('#detail-dimen-select').val() == 0 || $('#detail-dimen-select').val() == null)) {
		flag = false;
	}

	if (flag) {
		$('#distribute-update-btn').prop('disabled', false);
	}
	else {
		$('#distribute-update-btn').prop('disabled', true);
	}
}
