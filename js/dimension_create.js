$(document).ready(function(){
   // Dimension add
	$('#dimension_code').keyup(function(){
		$('.danger').removeClass('hidden').addClass('hidden');
		$('.success').removeClass('hidden').addClass('hidden');
		var str = $(this).val();
		if (str != '') {
			$('.checking').removeClass('hidden');
			var code_arr = $('#list-dimen-code').val().split(',');
			setTimeout(function(){
				if ($.inArray(str, code_arr) == -1 && $.trim(str) != '') {
					$('.danger').removeClass('hidden').addClass('hidden');
					$('.checking').addClass('hidden');
					$('.success').removeClass('hidden');
					$('#dimension_code').data('ok', '1');
				} else {
					$('.success').removeClass('hidden').addClass('hidden');
					$('.checking').addClass('hidden');
					$('.danger').removeClass('hidden');
					$('#dimension_code').data('ok', '0');
				}
			},300);
		} else {
			$('#dimension_code').data('ok', '0');
			$('.danger').removeClass('hidden').addClass('hidden');
			$('.success').removeClass('hidden').addClass('hidden');
			$('#add-new-type-btn').prop('disabled', true);
		}
	});

   $('#dimension_name, #layer, #sequence').keyup(function(){
		if ($('#dimension_code').data('ok') == '1' && $.trim($('#dimension_name').val()) != '' && parseInt($('#layer').val()) > 0 && $.trim($('#sequence').val()) != '') {
			$('#dimension-done').prop('disabled', false);
		} else {
			$('#dimension-done').prop('disabled', true);
		}
	});

	$('#layer, #sequence').change(function(){
		if ($('#dimension_code').data('ok') == '1' && $.trim($('#dimension_name').val()) != '' && parseInt($('#layer').val()) > 0 && $.trim($('#sequence').val()) != '') {
			$('#dimension-done').prop('disabled', false);
		} else {
			$('#dimension-done').prop('disabled', true);
		}
	});

	// >.< because when del too fast 1
	$('#dimension_code').click(function(){
		var obj = $(this);
		setInterval(function(){
			if (obj.val() == '') {
				$('.danger').removeClass('hidden').addClass('hidden');
				$('.success').removeClass('hidden').addClass('hidden');
			}
		},300);
	});

});
