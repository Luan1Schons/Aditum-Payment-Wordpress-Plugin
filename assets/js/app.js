jQuery.noConflict();
   (function( $ ) {
       $(function() {
           
            $(document).on('click', 'input#aditum_card_number', function(){
                $('input#aditum_card_number').mask('0000 0000 0000 0000');
            });
           
            $(document).on('click', 'input#aditum_card_cvv', function(){
                $('input#aditum_card_cvv').mask('000');
            });

            $(document).on('click', 'input#aditum_card_expiration_month', function(){
                $('input#aditum_card_expiration_month').mask('00');
            });

            $(document).on('click', 'input#aditum_card_year_month', function(){
                $('input#aditum_card_year_month').mask('00');
            });
       });
   })(jQuery);