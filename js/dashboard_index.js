$(document).ready(function(){

   $('#date_choose').change(function(){
      var choose = $(this).val();
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth() + 1;
      var yyyy = today.getFullYear();
      var url = $('#dashboard_url').val();

      if (choose != 0) {
         location.href = url + '?date_range=' + choose.toString();
      } else {
         
      }

   });

});
