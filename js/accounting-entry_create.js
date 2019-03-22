$(document).ready(function(){

   $('#voucher-choose').change(function(){
		var id = $(this).val();
		var url = $(this).data('url');

		if (id != 0) {
			$('#act-add-btn').prop('disabled', false);

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
               $('#voucher-choose').data('value', result.value);

               var total_act = 0;
					var table = $('#act_table').DataTable();
					table.row().remove().draw();
					for (var i = 0; i < result.act.length; i++) {
						table.row.add([result.act[i].TOA,result.act[i].content,convertToCurrency(result.act[i].value) + ' đ',result.act[i].debit_acc,result.act[i].credit_acc]).draw();
                  total_act += parseInt(result.act[i].value);
					}

               $('#total_act').val(total_act);
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
		}

	});

   // When add accounting entry by  get method
	if ($('#set-voucher').length && $('#set-voucher').val()) {
		var id_arr = $('#list_id_voucher').val().split(',');
		var id = $('#set-voucher').val();

		if (id_arr.includes(id)) {
			$('#voucher-choose').val(id);
			$('#voucher-choose').trigger("change");
		}
	}

   $('#act-add-btn').click(function(){
		$(this).prop('disabled', true);
		var url = $(this).data('url');

		$.ajax({
			url : url,
			dataType : 'json',
         data : {
            voucher_id : $('#voucher-choose').val()
         },
			success : function(result) {
				var row = [];

				for(var i in result) {
					row.push(result[i]);
				}

				$('#act_table').DataTable().row.add(row).draw();
			}
		});

	});
	// 1
	$('#update-act-btn').click(function(){
		var data = {
			'voucher_id' : $('#voucher_id').val(),
			'TOT' : $('#TOT').val(),
			'TOA' : $('#TOA').val(),
			'value' : $('#value').val(),
			'debit_acc' : $('#debit_acc').val(),
			'credit_acc' : $('#credit_acc').val(),
			'content' : $('#content').val()
		};
		var url = $(this).data('url');

		$.ajax({
			url : url,
			method : "POST",
			dataType : "JSON",
			data : data,
			beforeSend: function() {
				$('.load-info-act').css('display', 'flex');
			},
			success : function(result) {
				$('.alert').html(result.message);
				var table = $('#act_table').DataTable();
				var form_row = $('.contain-act-row tr:last-child');
				table.row(form_row).remove().draw();
				$('#act-add-btn').prop('disabled', false);
				if (result.success) {
					$('#act_table').DataTable().row.add([data.TOA,data.content,data.value + ' đ',data.debit_acc,data.credit_acc]).draw();
               // Check complete voucher
               $('#total_act').val( parseInt($('#total_act').val()) + parseInt(data.value.split('.').join('')) );
               if (parseInt($('#total_act').val()) >= parseInt($('#voucher-choose').data('value'))) {
                  $('#voucher-choose').val(0);
                  $('#voucher-choose').trigger('change');
                  $('#voucher-choose').children('option[value="' + data.voucher_id.toString() + '"]').hide();
               }

               $('#update-act-btn').prop('disabled', true);

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

   $(document).on("change", "#TOA, #value, #debit_acc, #credit_acc", checkToEnableOk);
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
	if ($('#TOA').val() == '') {
		flag = false;
	}

	if (flag) {
		$('#update-act-btn').prop('disabled', false);
	}
	else {
		$('#update-act-btn').prop('disabled', true);
	}
}
