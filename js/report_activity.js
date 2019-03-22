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

            // console.log(offset);
            // console.log($(window).height() + offset);
            // console.log($($('.catalog-table')[1]).height());
            if ($(window).scrollTop() + $(window).height() - tableOffsetTop < parseInt($('#height-table').val())) {
               $this.css('height', $(window).scrollTop() + $(window).height() - tableOffsetTop);
            } else {
               $this.css('height', parseInt($('#height-table').val()));
            }


         }
         $(window).resize(resizeFixed);
         $(window).scroll(scrollFixed);
         init();
      });
   };
})(jQuery);


$(document).ready(function(){
   // Why 18? Fuck!
   $('#height-table').val($('.report').height() - 18);
   $("table").fixMe();

   $('.report').scroll(function(){
      $('.report').scrollLeft($(this).scrollLeft());
   });

});
