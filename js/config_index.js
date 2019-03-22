function updateChanged(type_id) {
	var list = $('#list_changed').val().split(',');
	list = list.filter(Number);
	if (list.includes(type_id.toString())) {
		var index = list.indexOf(type_id);
		list.splice(index, 1);
	} else {
		list.push(type_id);
	}

	var value_new = list.join(',');
	$('#list_changed').val(value_new);
	$('#list_changed').trigger("change");
}

$(document).ready(function(){
   $('.value').change(function(){
		var type_id = $(this).data('id');

      if ($('#changed_' + type_id.toString()).val() == '0') {
         $('#changed_' + type_id.toString()).val('1');
         updateChanged(type_id);
      }

	});

   $('#list_changed').change(function(){
		if ($(this).val() == '') {
			$('#save_changed').prop('disabled', true);
		} else {
			$('#save_changed').prop('disabled', false);
		}
	});


});
