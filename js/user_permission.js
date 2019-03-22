$(document).ready(function(){
   $('.check_permission').change(function(){
      $('#update-btn').prop('disabled', false);
      var id = $(this).data('id');

      var str_changed = $('#have_changed').val();

      if ($.trim(str_changed) == '') {
         $('#have_changed').val(id);
      } else {
         var arr_changed = str_changed.split(',');

         if ($.inArray(id.toString(), arr_changed) == -1) {
            arr_changed.push(id);
         }
         $('#have_changed').val(arr_changed.join(','));
      }

   });


});
