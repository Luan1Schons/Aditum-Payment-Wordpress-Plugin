jQuery.noConflict();
   (function( $ ) {
       $(function() {
           
            $(document).on('focus', 'input#aditum_card_number', function(){
                $('input#aditum_card_number').mask('0000 0000 0000 0000');
            });

            $(document).on('focusout', 'input#aditum_card_number', function(){
                $.post('/wp-admin/admin-ajax.php?action=get_card_brand', {bin:$('input#aditum_card_number').val()}, function(response){ 
                    if(response.status == 'success'){
                        $('span#card-brand').text('Bandeira: ' + response.brand);
                    }else{
                        $('span#card-brand').text('');
                    }
                });
            });

            $(document).on('click', 'input#aditum_card_cvv', function(){
                $('input#aditum_card_cvv').mask('000');
            });

            $(document).on('click', 'input#card_holder_document', function(){
                $("#card_holder_document").keydown(function(){
                try {
                    $("#card_holder_document").unmask();
                } catch (e) {}

                var tamanho = $("#card_holder_document").val().length;

                if(tamanho < 11){
                    $("#card_holder_document").mask("999.999.999-99");
                } else {
                    $("#card_holder_document").mask("99.999.999/9999-99");
                }

                // ajustando foco
                var elem = this;
                setTimeout(function(){
                    // mudo a posição do seletor
                    elem.selectionStart = elem.selectionEnd = 10000;
                }, 0);
                // reaplico o valor para mudar o foco
                var currentValue = $(this).val();
                $(this).val('');
                $(this).val(currentValue);
                });
            });

            $(document).on('click', 'input#aditum_card_expiration_month', function(){
                $('input#aditum_card_expiration_month').mask('00');
            });

            $(document).on('click', 'input#aditum_card_year_month', function(){
                $('input#aditum_card_year_month').mask('00');
            });
       });
   })(jQuery);