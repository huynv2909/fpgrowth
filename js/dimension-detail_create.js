$(document).ready(function(){
   $('#dimension-choose').change(function(){
		var id = $(this).val();
		var url = $(this).data('url');

		if (id != 0) {
			$('#detail-add-btn').prop('disabled', false);

			$.ajax({
				method: "POST",
				url: url,
				dataType: "JSON",
				data: {
					id : id
				},
            beforeSend: function() {
               $('#root-waiting').css('display', 'flex');
            },
				success: function(result) {
					var table = $('#detail_dimen_table').DataTable();
					table.row().remove().draw();
					for (var i = 0; i < result.details.length; i++) {
						table.row.add([result.details[i]['name'],result.details[i]['note'],result.details[i]['weight'],result.details[i]['parent_name'],result.details[i]['layer'],result.details[i]['sequence'],result.details[i]['exchange'] + result.details[i]['delete']]).draw();
					}
				},
            complete: function() {
               $('#root-waiting').css('display', 'none');
            }
			});

		} else {

			var table = $('#detail_dimen_table').DataTable();
			table.row().remove().draw();

			$('#detail-add-btn').prop('disabled', true);
			$('#detail-update-btn').prop('disabled', true);
		}

	});

   $(document).on("click", ".del-btn", function(){
		var id = $(this).data('id');
		var url = $(this).data('url');
		var current_btn = $(this);

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
									 	 var table = $('#detail_dimen_table').DataTable();
						 				 var row = current_btn.parent().parent();
						 				 table.row(row).remove().draw();

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

});
