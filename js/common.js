// Contain all function and common js event
$(document).ready(function(){
   // when have notification
   if ($('#have-notify').val() == '1') {
		$('.alert').fadeIn();
		setTimeout(function(){
			$('.alert').fadeOut();
		}, 5000);
	}

   $('.slide-form').click(function(){
		var invisible = $(this).data('hidden');

		if (invisible == '0') {
			$('form').slideUp();
			$(this).html('');
			$(this).data('hidden', 1);
		} else {
			$('form').slideDown();
			$(this).html('');
			$(this).data('hidden', 0);
		}

	});

});

function oneDot(input) {
  var value = input.value,
      value = value.split('.').join('');

  if (value.length > 3) {
  	var count = Math.floor((value.length - 1)/3);
  	var arr = [];
  	for (i = count; i > 0; i--) {
  		arr.push(value.substring(0, value.length - 3*i));
  		value = value.slice(value.length - 3*i)
  	}
  	arr.push(value);
  	var str = arr[0];
  	for (i = 1; i < arr.length; i++) {
  		str = str + '.' + arr[i];
  	}
    	value = str;
  }

  input.value = value;
}

function convertToCurrency(value)
{
	if (value.length > 3) {
   	var count = Math.floor((value.length - 1)/3);
   	var arr = [];
   	for (i = count; i > 0; i--) {
   		arr.push(value.substring(0, value.length - 3*i));
   		value = value.slice(value.length - 3*i)
   	}
   	arr.push(value);
   	var str = arr[0];
   	for (i = 1; i < arr.length; i++) {
   		str = str + '.' + arr[i];
   	}
     	value = str;
   }

	return value;
}
