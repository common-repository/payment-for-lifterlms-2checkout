(function($) {
    
    $(document).ready(function () {
        
        if( $('#llms-2co-card-element').length ) {

            let jsPaymentClient = new TwoPayClient( llms_2co_gateway_data.seller_id );
    
            jsPaymentClient.setup.setLanguage( llms_2co_gateway_data.language );
    
            let component = jsPaymentClient.components.create('card');
    
            component.mount('#llms-2co-card-element');
    
            window.llms.checkout.add_before_submit_event({
                handler: function( data, callback ) {
    
                    var selected_gateway = $('input[name="llms_payment_gateway"]:checked');
    
                    if ( selected_gateway.val() != '2Checkout' || $('input.llms_2co_token').length ) {
    
                        callback( true );
    
                    }
                    else {
    
                        callback( false );
    
                        const billingDetails = {
                            name: ( $('#first_name').val() + ' ' + $('#last_name').val() ).trim()
                        };
    
                        jsPaymentClient.tokens.generate( component, billingDetails ).then(( response ) => {
                            window.llms.checkout.$checkout_form.append("<input type='hidden' class='llms_2co_token' name='llms_2co_token' value='" + response.token + "' />");
                            window.llms.checkout.$checkout_form.submit();
                        }).catch((error) => {
                            window.llms.checkout.add_error(error);
                            window.llms.checkout.focus_errors();
                            window.llms.checkout.processing( 'stop' );
                        });
    
                    }
    
                }
    
            });
            
        }

    });

})(jQuery);

