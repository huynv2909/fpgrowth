$(document).ready(function(){

   // Voucher type
	$('#code').keyup(function(){
		$('.danger').removeClass('hidden').addClass('hidden');
		$('.success').removeClass('hidden').addClass('hidden');
		var str = $(this).val();
		if (str != '') {
			$('.checking').removeClass('hidden');
			var code_arr = $('#list_code').val().split(',');
			setTimeout(function(){
				if ($.inArray(str, code_arr) == -1 && $.trim(str) != '') {
					$('.danger').removeClass('hidden').addClass('hidden');
					$('.checking').addClass('hidden');
					$('.success').removeClass('hidden');
					$('#code').data('ok', '1');
					if ($.trim($('#name').val()) != '') {
						$('#add-new-type-btn').prop('disabled', false);
					} else {
						$('#add-new-type-btn').prop('disabled', true);
					}
				} else {
					$('.success').removeClass('hidden').addClass('hidden');
					$('.checking').addClass('hidden');
					$('.danger').removeClass('hidden');
					$('#code').data('ok', '0');
					$('#add-new-type-btn').prop('disabled', true);
				}
			},300);
		} else {
			$('#code').data('ok', '0');
			$('.danger').removeClass('hidden').addClass('hidden');
			$('.success').removeClass('hidden').addClass('hidden');
			$('#add-new-type-btn').prop('disabled', true);
		}
	});

   // >.< because when del too fast
	$('#code').click(function(){
		var obj = $(this);
		setInterval(function(){
			if (obj.val() == '') {
				$('.danger').removeClass('hidden').addClass('hidden');
				$('.success').removeClass('hidden').addClass('hidden');
			}
		},300);
	});

   // Check to enable add new voucher type btn
	$('#name').keyup(function(){
		if ($('#code').data('ok') != '0' && $.trim($('#name').val()) != '') {
			$('#add-new-type-btn').prop('disabled', false);
		} else {
			$('#add-new-type-btn').prop('disabled', true);
		}
	});

   // Active a type
	$(document).on("click", ".exchange-btn", function(){
		var id = $(this).data('id');
		var url = $(this).data('url');
		var active = $(this).data('active');

		var current_tag = $(this);

		$.confirm({
			 icon: 'fa fa-exchange',
			 title: 'Chuyển trạng thái?',
			 content: 'Tiếp tục?',
			 theme: 'material',
			 type: 'yellow',
			 buttons: {
				  Ok: {
						text: 'Ok',
						btnClass: 'btn-green',
						keys: ['enter'],
						action: function(){
							 $.ajax({
								 url : url,
								 method : "POST",
								 data : {
									 id : id,
									 active : active
								 },
								 success : function(result) {
									 if (active == 1) {
	 									current_tag.data('active', '0');
	 									current_tag.html('');
	 									current_tag.removeClass('active-color');
	 								 } else {
	 									current_tag.data('active', '1');
	 									current_tag.html('');
	 									current_tag.addClass('active-color');
	 								}
								 }
							 });
						}
				  },
				  later: {
					  text: 'Hủy',
					  keys: ['esc'],
					  action: function(){
					  }
				  }
			 }
		});

	});

	$(document).on("click", ".del-btn", function(){
		var id = $(this).data('id');
		var url = $(this).data('url');

		$.confirm({
			 icon: 'fa fa-remove',
			 title: 'Xóa?',
			 content: 'Hãy chắc chắn rằng mã chưa từng được sử dụng!',
			 theme: 'material',
			 type: 'red',
			 buttons: {
				  Ok: {
						text: 'Ok',
						btnClass: 'btn-green',
						keys: ['enter'],
						action: function(){
							 $.ajax({
								 url : url,
								 method : "POST",
								 dataType : "JSON",
								 data : {
									 id : id
								 },
								 success : function(result) {
									 if (result.success) {
									 	 var table = $('#voucher_types_table').DataTable();
						 				 var row = $('#type-' + id.toString());
										 var old_code = $(row).children('td:first-child').html();
						 				 table.row(row).remove().draw();

										 $('#list_code').val($('#list_code').val().replace(old_code + ",",''));
										 // console.log(result.message);
									 } else {
										 $.alert(result.message);
									 }
								 }
							 });
						}
				  },
				  cancel: {
					  text: 'Hủy',
					  keys: ['esc'],
					  action: function(){
					  }
				  }
			 }
		});

	});

});
