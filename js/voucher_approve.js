;(function($) {
   $.fn.fixMe = function() {
      return this.each(function() {
         var $this = $(this),
            $t_fixed;
         function init() {
            $t_fixed = $this.clone();
            $t_fixed.find("tbody").remove().end().addClass("fixed").insertBefore($this);
            resizeFixed();
         }
         function resizeFixed() {
            $t_fixed.find("th").each(function(index) {
               $(this).css("width",$this.find("th").eq(index).outerWidth()+"px");
            });
         }
         function scrollFixed() {
            var offset = $(this).scrollTop(),
            tableOffsetTop = $this.offset().top,
            tableOffsetBottom = tableOffsetTop + $this.height() - $this.find("thead").height();
            if(offset < tableOffsetTop || offset > tableOffsetBottom)
               $t_fixed.css('display', 'none');
            else if(offset >= tableOffsetTop && offset <= tableOffsetBottom && $t_fixed.is(":hidden"))
            {
               $t_fixed.css('width', $this.width());
               $t_fixed.addClass('hide-scrollbar');
               $t_fixed.css('display', 'block');
            }


         }
         $(window).resize(resizeFixed);
         $(window).scroll(scrollFixed);
         init();
      });
   };
})(jQuery);


$(document).ready(function(){
   $("#new-vouchers").fixMe();

   $('.have-cod').change(function(){
      var id = $(this).data('id');
      if ($(this).is(":checked")) {
         $('#cod_value_' + id).attr('disabled', false);
      } else {
         $('#cod_value_' + id).attr('disabled', true);
      }
   });

   $('.approve').click(function(){
      var url = $('#url').val();
      var id = $(this).data('id');
      var have_cod = 0;
      var have_auto = 1;

      if ($('#cod_' + id).is(":checked")) {
         have_cod = 1;
      } else {
         have_cod = 0;
      }

      if ($('#auto_' + id).is(":checked")) {
         have_auto = 1;
      } else {
         have_auto = 0;
      }

      $.ajax({
         url : url,
         method : "post",
         data : {
            id : id,
            type_id : $('#type_' + id).val(),
            content : $('#content_' + id).val(),
            course : $('#course_' + id).val(),
            value : $('#value_' + id).val(),
            cod : have_cod,
            cod_value : $('#cod_value_' + id).val(),
            tot : $('#tot_' + id).val(),
            executor : $('#executor_' + id).val(),
            method : $('#method_' + id).val(),
            provider : $('#provider_' + id).val(),
            auto : have_auto
         },
         beforeSend: function() {
            $('#root-waiting').css('display', 'flex');
         },
         success : function(result) {
            $('#remaining').html(parseInt($('#remaining').html()) - 1);

            $('#row-' + id).slideUp(1000);
            $('.alert').html(result);
            $('.alert').addClass('alert-success');

            $('.alert').fadeIn();
				setTimeout(function(){
					$('.alert').fadeOut();
					$('.alert').removeClass('alert-success alert-danger');
				}, 4000);
         },
         complete: function() {
            $('#root-waiting').css('display', 'none');
         }
      });

   });

});
