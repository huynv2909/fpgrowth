// click to view detail voucher
$(document).on("click", ".voucher-id", function(){
   var url = $(this).data('url');
   var id = $(this).data('id');

   $.ajax({
      url : url,
      method : "post",
      data : {
         id : id
      },
      beforeSend: function() {
         $('#root-waiting').css('display', 'flex');
      },
      success : function(result) {
         $('.title-insert').html('Thông tin chứng từ');
         $('.data-insert').html(result);
         $('#view-modal').modal('show');
      },
      complete: function() {
         $('#root-waiting').css('display', 'none');
      }
   });
});

$(document).on("click", ".value-acc", function(){
   var url = $(this).data('url');
   var id = $(this).data('id');

   $.ajax({
      url : url,
      method : "post",
      data : {
         id : id
      },
      beforeSend: function() {
         $('#root-waiting').css('display', 'flex');
      },
      success : function(result) {
         $('.title-insert').html('Trạng thái phân bổ');
         $('.data-insert').html(result);
         $('#view-modal').modal('show');
      },
      complete: function() {
         $('#root-waiting').css('display', 'none');
      }
   });


});

$(document).on("click", ".del-btn", function(){
   var id = $(this).data('id');
   var url = $(this).data('url');
   var current_btn = $(this);

   $.confirm({
       icon: 'fa fa-remove',
       title: 'Xóa?',
       content: 'Xóa bỏ bút toán?!',
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
                            var table = $('#accounting_table').DataTable();
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

$(document).on("click", ".edit-btn", function(){
   var id = $(this).data('id');
   var url = $(this).data('url');
   var current_btn = $(this);

   $.confirm({
      columnClass: 'xlarge',
      theme: 'material',
      title: 'Thay đổi thông tin bút toán có chứng từ gốc:',
      closeIcon: true,
      type: 'orange',
      typeAnimated: true,
      buttons: {
        ok: {
           text: 'Cập nhật',
           btnClass: 'btn-orange update-submit',
           isDisabled: true,
           action: function () {
               $('#edit-act-form').trigger("submit");
           }
        },
        cancel: {
            text: 'Hủy'
        }
      },
       content: function(){
           var self = this;
           return $.ajax({
               url: url,
               method: 'post',
               dataType: 'json',
               data: {
                  id : id
               }
           }).done(function (response) {
               self.setContentAppend('<div class="info-box" id="voucher-box">' + response.voucher_detail + '</div>');
               self.setContentAppend('<h4>Nội dung bút toán thay đổi: (Giá trị lớn nhất <span id="remaining">' + convertToCurrency(response.remaining.toString()) + '</span> đ)</h4>');
               self.setContentAppend(response.form);
               $('#remaining_val').val(response.remaining);
           }).fail(function(){
               self.setContentAppend('<div>Fail!</div>');
           });
       }
   });

});

$(document).on("keyup", "#value", function(){
   var str_value = $(this).val().split('.').join('');

   if ($.trim(str_value) != '') {
      if (!$.isNumeric(str_value) || (parseInt(str_value) > parseInt($('#remaining_val').val()))) {
   		$(this).val(convertToCurrency($('#remaining_val').val()));
   	}
	}


});

$(document).on("change", "#content, #value, #TOA, #debit_acc, #credit_acc", function(){
   $('.update-submit').prop('disabled', false);
});
