jQuery.noConflict();
   (function( $ ) {
       $(function() {

            $(document).ready(function(){
                $('#aditum_card_installment').hide();
            });

            $(document).on('focus', 'input#aditum_card_number', function(){
                $('input#aditum_card_number').mask('0000 0000 0000 0000');
            });

            $(document).on('keyup', 'input#aditum_card_number', function(){
                if($('input#aditum_card_number').cleanVal().length == 16){
                    $(".installment_aditum_card").show();
                }else{
                    $(".installment_aditum_card").hide();
                }
            });

            $(document).on('click', '#payment_method_aditum_debitcard', function(){
                $('span#card-brand').html();
            });

            $(document).on('focusout', 'input#aditum_card_number', function(){

                $.post('/wp-admin/admin-ajax.php?action=get_card_brand', {bin:$('input#aditum_card_number').val()}, function(response){ 
                    if(response.status == 'success'){

                        //$('span#card-brand').text('Bandeira: ' + response.brand);
                        if(response.brand == "Mastercard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/mastercard.svg"/>');
                        } else if(response.brand == "Visa"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/visa.svg"/>');
                        } else if(response.brand == "Alelo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/alelo.svg"/>');
                        } else if(response.brand == "Vr"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/vr.svg"/>');
                        } else if(response.brand == "Vero"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/vero.svg"/>');
                        } else if(response.brand == "VerdeCard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/verdecard.svg"/>');
                        } else if(response.brand == "Ticket"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/ticket.svg"/>');
                        } else if(response.brand == "Sorocred"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/sorocred.svg"/>');
                        } else if(response.brand == "Sodexo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/sodexo.svg"/>');
                        } else if(response.brand == "Maestro"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/maestro.svg"/>');
                        } else if(response.brand == "Jcb"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/JCB.svg"/>');
                        } else if(response.brand == "Hipercard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/hipercard.svg"/>');
                        } else if(response.brand == "Hiper"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/hiper.svg"/>');
                        } else if(response.brand == "Elo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/elo.svg"/>');
                        } else if(response.brand == "Discover"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/discover.svg"/>');
                        } else if(response.brand == "Dinerclub"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/dinerclub.svg"/>');
                        } else if(response.brand == "Cabal"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/cabal.svg"/>');
                        } else if(response.brand == "Banricard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/banricard.svg"/>');
                        } else if(response.brand == "Amex"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/amex.svg"/>');
                        }else{
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/outras.svg"/>');
                        }
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

            $(document).on('focus', 'input#aditum_debitcard_number', function(){
                $('input#aditum_debitcard_number').mask('0000 0000 0000 0000');
            });
            
            $(document).on('focusout', 'input#aditum_debitcard_number', function(){
                $.post('/wp-admin/admin-ajax.php?action=get_card_brand', {bin:$('input#aditum_debitcard_number').val()}, function(response){ 
                    if(response.status == 'success'){
                       // $('span#card-brand').text('Bandeira: ' + response.brand);
                        if(response.brand == "Mastercard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/mastercard.svg"/>');
                        } else if(response.brand == "Visa"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/visa.svg"/>');
                        } else if(response.brand == "Alelo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/alelo.svg"/>');
                        } else if(response.brand == "Vr"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/vr.svg"/>');
                        } else if(response.brand == "Vero"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/vero.svg"/>');
                        } else if(response.brand == "VerdeCard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/verdecard.svg"/>');
                        } else if(response.brand == "Ticket"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/ticket.svg"/>');
                        } else if(response.brand == "Sorocred"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/sorocred.svg"/>');
                        } else if(response.brand == "Sodexo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/sodexo.svg"/>');
                        } else if(response.brand == "Maestro"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/maestro.svg"/>');
                        } else if(response.brand == "Jcb"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/JCB.svg"/>');
                        } else if(response.brand == "Hipercard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/hipercard.svg"/>');
                        } else if(response.brand == "Hiper"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/hiper.svg"/>');
                        } else if(response.brand == "Elo"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/elo.svg"/>');
                        } else if(response.brand == "Discover"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/discover.svg"/>');
                        } else if(response.brand == "Dinerclub"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/dinerclub.svg"/>');
                        } else if(response.brand == "Cabal"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/cabal.svg"/>');
                        } else if(response.brand == "Banricard"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/banricard.svg"/>');
                        } else if(response.brand == "Amex"){
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/amex.svg"/>');
                        }else{
                            $('span#card-brand').html('<img src="/wp-content/plugins/aditum-payment-gateway/assets/bandeiras/outras.svg"/>');
                        }
                    }else{
                        $('span#card-brand').text('');
                    }
                });
            });
            
            $(document).on('click', 'input#aditum_debitcard_cvv', function(){
                $('input#aditum_debitcard_cvv').mask('000');
            });
            
            $(document).on('click', 'input#debitcard_holder_document', function(){
                $("#debitcard_holder_document").keydown(function(){
                try {
                    $("#debitcard_holder_document").unmask();
                } catch (e) {}
            
                var tamanho = $("#debitcard_holder_document").val().length;
            
                if(tamanho < 11){
                    $("#debitcard_holder_document").mask("999.999.999-99");
                } else {
                    $("#debitcard_holder_document").mask("99.999.999/9999-99");
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
            
            $(document).on('click', 'input#aditum_debitcard_expiration_month', function(){
                $('input#aditum_debitcard_expiration_month').mask('00');
            });
            
            $(document).on('click', 'input#aditum_debitcard_year_month', function(){
                $('input#aditum_debitcard_year_month').mask('00');
            });
       });
   })(jQuery);