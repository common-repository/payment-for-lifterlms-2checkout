<?php
if ( ! defined('CG_2CO_LLMS_VERSION')) exit;


if( ! class_exists( 'Cg_2checkout_Llms_Gateway' ) ) :
	class Cg_2checkout_Llms_Gateway extends LLMS_Payment_Gateway {

	    /**
	     * @var string
	     * @since 3.0.0
	     */
	    public $merchant_code;
	    public $secret_word;
	    public $publishable_key;
	    public $private_key;

	    /**
	     * Constructor
	     *
	     * @return  void
	     * @since   3.0.0
	     * @version 3.10.0
	     */
	    public function __construct() {

	        $this->id                    = '2Checkout';
	        $this->admin_description     = __( 'Collect payments using 2Checkout.', 'payment-for-lifterlms-2checkout' );
	        $this->admin_title           = '2Checkout';
	        $this->title                 = '2Checkout';
	        $this->description           = __( 'Pay via Card', 'payment-for-lifterlms-2checkout' );
	        $this->test_mode_title       = __( 'Test Mode', 'payment-for-lifterlms-2checkout' );
	        $this->test_mode_description = '<span class="description">'.__( '2Checkout sandbox can be used to test payments.', 'payment-for-lifterlms-2checkout' ).'</span>';
	        $this->merchant_code         = '';
	        $this->secret_key            = '';
	        $this->secret_word           = '';

	        $this->supports = array(
	            'checkout_fields'    => true,
	            'single_payments'    => true,
	            'test_mode'          => true,
	        );

	        add_filter( 'llms_get_gateway_settings_fields', array( $this, 'get_settings_fields' ), 10, 2 );

	    }

	    /**
	     * Get admin setting fields
	     *
	     * @param    array  $fields      default fields
	     * @param    string $gateway_id  gateway ID
	     * @return   array
	     * @since    3.0.0
	     * @version  3.0.0
	     */
	    public function get_settings_fields( $fields, $gateway_id ) {

	        if ( $this->id !== $gateway_id ) {
	            return $fields;
	        }

	        $fields[] = array(
	            'id'    => $this->get_option_name( 'merchant_code' ),
	            'desc'  => '<br>' . __( 'Enter your 2Checkout account number/ merchant code.', 'payment-for-lifterlms-2checkout' ),
	            'title' => __( 'Merchant Code', 'payment-for-lifterlms-2checkout' ),
	            'type'  => 'text',
	        );

	        $fields[] = array(
	            'id'      => $this->get_option_name( 'secret_key' ),
	            'desc'    => '<br>' . __( 'Enter your 2Checkout secret key.', 'payment-for-lifterlms-2checkout' ),
	            'title'   => __( 'Secret Key', 'payment-for-lifterlms-2checkout' ),
	            'type'    => 'text',
	        );

	        $fields[] = array(
	            'id'      => $this->get_option_name( 'secret_word' ),
	            'desc'    => '<br>' . __( 'Enter your 2Checkout secret word.', 'payment-for-lifterlms-2checkout' ),
	            'title'   => __( 'Secret Word', 'payment-for-lifterlms-2checkout' ),
	            'type'    => 'text',
	        );

	        $fields[] = array(
	            'id'      => $this->get_option_name( 'form_language' ),
	            'desc'    => '<br>' . __( 'Select checkout payment form language.', 'payment-for-lifterlms-2checkout' ),
	            'title'   => __( 'Language', 'payment-for-lifterlms-2checkout' ),
	            'type'    => 'select',
	            'options' => $this->get_language_options(),
	        );

	        $fields[] = array(
	            'id'      => $this->get_option_name( 'ins_endpoint' ),
	            'desc'    => '<br>' . __( 'Copy this Endpoint.', 'payment-for-lifterlms-2checkout' ),
	            'title'   => __( 'INS Endpoint', 'payment-for-lifterlms-2checkout' ),
	            'type'    => 'text',
	            'default' => site_url() . '/?action=2CO_INS_Handler',
	            'custom_attributes' => array(
  					'readonly' => 'readonly',
  				)
	        );

	        return $fields;

	    }

	    /**
	     * Handle a Pending Order
	     * Called by LLMS_Controller_Orders->create_pending_order() on checkout form submission
	     * All data will be validated before it's passed to this function
	     *
	     * @param   obj       $order   Instance LLMS_Order for the order being processed
	     * @param   obj       $plan    Instance LLMS_Access_Plan for the order being processed
	     * @param   obj       $person  Instance of LLMS_Student for the purchasing customer
	     * @param   obj|false $coupon  Instance of LLMS_Coupon applied to the order being processed, or false when none is being used
	     * @return  void
	     * @since   3.0.0
	     * @version 3.10.0
	     */
	    public function handle_pending_order( $order, $plan, $person, $coupon = false ) {

	        if ( $order->get_price( 'total', array(), 'float' ) > 0  ) {

	        	$product = $plan->get_product();

	        	$twoCoOrder = new stdClass();
            	$twoCoOrder->Currency          = get_lifterlms_currency();
		        $twoCoOrder->Country           = get_lifterlms_country();
		        $twoCoOrder->Source            = get_permalink( $product->get('id') );
		        $twoCoOrder->LocalTime         = date('Y-m-d H:i:s');
		        $twoCoOrder->ExternalReference = $order->get('id');
		        $twoCoOrder->CustomerReference = $person->get_id();

		        $item1 = new stdClass();
		        $item1->Code         = null;
		        $item1->Name         = $product->get('title');
		        $item1->Quantity     = 1;
		        $item1->PurchaseType = 'PRODUCT';
		        $item1->Tangible     = false;
		        $item1->IsDynamic    = true;

		        $item1Price = new stdClass();
		        $item1Price->Amount = $order->get('total');

		        $item1->Price = $item1Price;

		        $twoCoOrder->Items = array( $item1 );

		        $billing = new stdClass();
		        $billing->Address1    = $order->get('billing_address_1');
		        $billing->City        = $order->get('billing_city');
		        $billing->State       = $order->get('billing_state');
		        $billing->CountryCode = $order->get('billing_country');
		        $billing->Email       = $order->get('billing_email');
		        $billing->FirstName   = $order->get('billing_first_name');
		        $billing->LastName    = $order->get('billing_last_name');
		        $billing->Zip         = $order->get('billing_zip');

		        $twoCoOrder->BillingDetails = $billing;

		        $payment = new stdClass();
		        $payment->Type       = 'EES_TOKEN_PAYMENT';
		        $payment->Currency   = get_lifterlms_currency();
		        $payment->CustomerIP = get_2co_client_ip();

		        $paymentMethod = new stdClass();
		        $paymentMethod->EesToken           = wp_strip_all_tags( $_POST['llms_2co_token'] );
		        $paymentMethod->Vendor3DSReturnURL = $order->get_view_link();
		        $paymentMethod->Vendor3DSCancelURL = $order->get_view_link();

		        $payment->PaymentMethod = $paymentMethod;

		        $twoCoOrder->PaymentDetails = $payment;

		        $twoCoAPI = new Cg_2checkout_Sdk();
		        $response = $twoCoAPI->process( $twoCoOrder );

			    if ( empty( $response->Errors ) ) {

			    	if ( ! empty( $response->Items[0]->ProductDetails->Subscriptions[0]->SubscriptionReference ) ) {
			    		update_post_meta( $order->get('id'), '2co_SubscriptionReference', $response->Items[0]->ProductDetails->Subscriptions[0]->SubscriptionReference );
			    	}

			    	if ( ! empty( $response->RefNo ) ) {
			    		update_post_meta( $order->get('id'), '2co_RefNo', $response->RefNo );
			    	}

			    	if ( ! empty( $response->OrderNo ) ) {
			    		update_post_meta( $order->get('id'), '2co_OrderNo', $response->OrderNo );
			    	}

			    	$order->add_note( __( 'Payment in fraud review.', 'payment-for-lifterlms-2checkout' ) );
            		llms_add_notice( __( 'Please wait a while for the payment fraud review.', 'payment-for-lifterlms-2checkout' ), 'notice' );
	        		llms_redirect_and_exit( $order->get_view_link() );

			    }
			    else {

			    	$errors = (array) $response->Errors;

			    	foreach ( $errors as $errorCode => $errorMsg ) {

			    		$errorMsg = str_replace( '<li>', '<br>- ', $errorMsg );
			    		
			    		$order->add_note( strip_tags( $errorMsg, '<br>' ) );
            			llms_add_notice( $errorMsg, 'error' );

			    	}
	        		llms_redirect_and_exit( $order->get_view_link() );

			    }
                
	        }

	    }

	    /**
	     * Determine if the gateway is enabled according to admin settings checkbox
	     *
	     * @return   boolean
	     * @since    3.0.0
	     * @version  3.0.0
	     */
	    public function is_enabled() {
	        return ( 'yes' === $this->get_enabled() ) ? true : false;
	    }

	    public function get_fields() {

	        return '<div class="llms-2co-payment-method">
	                    <p>'. __( 'Pay securely using your credit card or PayPal', 'payment-for-lifterlms-2checkout' ) .'</p>
	                    <div id="llms-2co-card-element"></div>
	                </div>';

	    }

	    public function get_language_options() {
	        return array(
            	array( 'key' => 'en', 'title' => 'English' ),
            	array( 'key' => 'ar', 'title' => 'Arabic' ),
            	array( 'key' => 'bg', 'title' => 'Bulgarian' ),
            	array( 'key' => 'cs', 'title' => 'Czech' ),
            	array( 'key' => 'da', 'title' => 'Danish' ),
            	array( 'key' => 'de', 'title' => 'German' ),
            	array( 'key' => 'el', 'title' => 'Greek' ),
            	array( 'key' => 'es', 'title' => 'Spanish' ),
            	array( 'key' => 'fa', 'title' => 'Persian' ),
            	array( 'key' => 'fi', 'title' => 'Finnish' ),
            	array( 'key' => 'fr', 'title' => 'French' ),
            	array( 'key' => 'he', 'title' => 'Hebrew' ),
            	array( 'key' => 'hi', 'title' => 'Hindi' ),
            	array( 'key' => 'hr', 'title' => 'Croatian' ),
            	array( 'key' => 'hu', 'title' => 'Hungarian' ),
            	array( 'key' => 'it', 'title' => 'Italian' ),
            	array( 'key' => 'ja', 'title' => 'Japanese' ),
            	array( 'key' => 'ko', 'title' => 'Korean' ),
            	array( 'key' => 'nl', 'title' => 'Dutch' ),
            	array( 'key' => 'no', 'title' => 'Norwegian' ),
            	array( 'key' => 'pl', 'title' => 'Polish' ),
            	array( 'key' => 'pt', 'title' => 'Portuguese' ),
            	array( 'key' => 'ro', 'title' => 'Romanian' ),
            	array( 'key' => 'ru', 'title' => 'Russian' ),
            	array( 'key' => 'sk', 'title' => 'Slovak' ),
            	array( 'key' => 'sl', 'title' => 'Slovenian' ),
            	array( 'key' => 'sr', 'title' => 'Serbian' ),
            	array( 'key' => 'sv', 'title' => 'Swedish' ),
            	array( 'key' => 'th', 'title' => 'Thai' ),
            	array( 'key' => 'tr', 'title' => 'Turkish' ),
            	array( 'key' => 'uk', 'title' => 'Ukrainian' ),
            	array( 'key' => 'zh', 'title' => 'Chinese (Simplified)' ),
            	array( 'key' => 'zy', 'title' => 'Chinese (Traditional)' )
            );
	    }
	}
endif;

