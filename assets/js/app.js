jQuery.noConflict();
   (function( $ ) {
       $(function() {
           
            $('input#aditum_card_number').mask('0000 0000 0000 0000');
            $('input#aditum_card_cvv').mask('000');
            $('input#aditum_card_expiration_month').mask('00');
            $('input#aditum_card_year_month').mask('00');

       });
   })(jQuery);