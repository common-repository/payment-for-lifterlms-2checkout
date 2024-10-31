<?php

if ( ! defined('CG_2CO_LLMS_VERSION')) exit;

if( ! class_exists( 'Cg_2checkout_Sdk' ) ) :
	class Cg_2checkout_Sdk {

		private $host;
		private $merchantCode;
		private $secretKey;

		public function __construct() {

	        $this->host         = 'https://api.2checkout.com/rpc/6.0/';
	        $this->merchantCode = get_cg_llms_option( 'merchant_code' );
	        $this->secretKey    = get_cg_llms_option( 'secret_key' );

	    }

	    private function call( $Request ) {

	    	$response = wp_remote_post( $this->host, array(
				    'method'      => 'POST',
				    'headers'     => array('Content-Type: application/json', 'Accept: application/json'),
				    'timeout'     => 60,
				    'redirection' => 5, 
				    'blocking'    => true,
				    'sslverify'   => false,
				    'httpversion' => '1.0',
				    'body'        => json_encode( $Request )
				)
	    	);

			if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
	        
	            $response = json_decode( $response['body'] );
	        
	            if ( isset( $response->result ) ) {
	                return $response->result;
	            }
	        
	            if ( !is_null( $response->error ) ) {
	                return array( $Request->method, $response->error );
	            }
	        
	        } 
	        elseif ( is_wp_error( $response ) ) {

			    return "Something went wrong.";
			
			} 
			else {
			    return null;
			}

	    }

	    public function process( $data, $method = 'placeOrder' ) {

		    $string = strlen( $this->merchantCode ) . $this->merchantCode . strlen( gmdate('Y-m-d H:i:s') ) . gmdate('Y-m-d H:i:s');
		    $hash = hash_hmac( 'md5', $string, $this->secretKey );

		    $i = 1;

		    $jsonRpcRequest = new stdClass();
		    $jsonRpcRequest->jsonrpc = '2.0';
		    $jsonRpcRequest->method = 'login';
		    $jsonRpcRequest->params = array( $this->merchantCode, gmdate('Y-m-d H:i:s'), $hash );
		    $jsonRpcRequest->id = $i++;

		    $sessionID = $this->call( $jsonRpcRequest );

		    $jsonRpcRequest = new stdClass();
		    $jsonRpcRequest->jsonrpc = '2.0';
		    $jsonRpcRequest->method = $method;
		    $jsonRpcRequest->params = array( $sessionID, $data );
		    $jsonRpcRequest->id = $i++;

			return $this->call( $jsonRpcRequest );

	    }

	}
endif;