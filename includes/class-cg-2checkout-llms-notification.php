<?php

if ( ! defined('CG_2CO_LLMS_VERSION')) exit;

if( ! class_exists( 'Cg_2checkout_Llms_Notification' ) ) :
	class Cg_2checkout_Llms_Notification {

		/**
		 * Singleton instance
		 */
		protected static $_instance = null;

		/**
		 * @return   Cg_2checkout_Llms_Notification - Main instance
		 * @since    1.0.0
		 * @version  1.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cg_2checkout_Llms_Notification Constructor.
		 * @since 1.0
		 * @return void
		 */
		private function __construct() {

			add_action( 'init', array( $this, 'listen_notification' ), 10 );

		}

		public function listen_notification() {

			if ( isset( $_GET['action'] ) && $_GET['action'] == '2CO_INS_Handler' ) {

	            if ( $this->authenticate_ins_request() ) {

					$insData = array(
						'vendor_id'           => intval( $_POST['vendor_id'] ),
						'sale_id'             => intval( $_POST['sale_id'] ),
						'invoice_id'          => intval( $_POST['invoice_id'] ),
						'md5_hash'            => wp_strip_all_tags( $_POST['md5_hash'] ),
						'vendor_order_id'     => intval( $_POST['vendor_order_id'] ),
						'message_type'        => sanitize_text_field( $_POST['message_type'] ),
						'invoice_list_amount' => floatval( $_POST['invoice_list_amount'] ),
						'fraud_status'        => sanitize_text_field( $_POST['fraud_status'] ),
						'order_ref'           => sanitize_text_field( $_POST['order_ref'] ),
						'order_no'            => sanitize_text_field( $_POST['order_no'] ),
						'item_list_amount_1'  => floatval( $_POST['item_list_amount_1'] ),
					);

	                $order = new LLMS_Order( intval( $insData['vendor_order_id'] ) );

	                if ( $insData['message_type'] == 'ORDER_CREATED' && $insData['invoice_list_amount'] > 0 ) {

	                    do_action( 'cg_2co_llms_ins_before_order_created', $insData, $order );

	                    $status = apply_filters( 'cg_2co_llms_ins_order_created_status', 'llms-txn-succeeded', $insData, $order );
	                    $payment_type = apply_filters( 'cg_2co_llms_ins_order_created_payment_type', 'single', $insData, $order );

	                    $order->record_transaction(
	                        array(
	                            'amount'             => $order->get('total'),
	                            'source_description' => __( '2Checkout', 'payment-for-lifterlms-2checkout' ),
	                            'transaction_id'     => uniqid(),
	                            'status'             => 'llms-txn-succeeded',
	                            'payment_gateway'    => '2Checkout',
	                            'payment_type'       => $payment_type,
	                        )
	                    );

	                    do_action( 'cg_2co_llms_ins_after_order_created', $insData, $order );

	                }
	                else if ( $insData['message_type'] == 'FRAUD_STATUS_CHANGED' ) {

	                    if ( $insData['fraud_status'] == 'fail' ) {
	                        
	                        $order->set_status( 'llms-txn-failed' );
	                        $order->add_note( __( 'Payment Fraud Suspected.', 'payment-for-lifterlms-2checkout' ) );

	                    }

	                    do_action( 'cg_2co_llms_ins_fraud_status_changed', $insData, $order );

	                }

	                do_action( 'cg_2co_llms_ins_notification', $insData, $order );

	            }

	        }
			
		}

		public function authenticate_ins_request() {

			$auth = false;

			if ( 
				! empty( $_POST['vendor_id'] ) && 
				! empty( $_POST['sale_id'] ) && 
				! empty( $_POST['invoice_id'] ) &&  
				! empty( $_POST['md5_hash'] ) &&
				! empty( $_POST['vendor_order_id'] ) && 
				! empty( $_POST['message_type'] )
			) {

				$hashSid     = intval( $_POST['vendor_id'] );
	            $hashOrder   = intval( $_POST['sale_id'] );
	            $hashInvoice = intval( $_POST['invoice_id'] );
	            $md5Hash     = wp_strip_all_tags( $_POST['md5_hash'] );
	            $secretWord  = get_cg_llms_option( 'secret_word' );

	            if ( strtoupper( md5( $hashOrder . $hashSid . $hashInvoice . $secretWord ) ) == $md5Hash ) {
	            	$auth = true;
	            }	
            	
            }

            return $auth;

		}

	}
endif;

function cg_2checkout_llms_notification() {
	return Cg_2checkout_Llms_Notification::instance();
}
return cg_2checkout_llms_notification();