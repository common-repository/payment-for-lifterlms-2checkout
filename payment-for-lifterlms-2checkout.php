<?php
/**
 * Plugin Name: Payment For LifterLMS 2checkout
 * Plugin URI: https://codeglitters.com/payment-for-lifterlms-2checkout
 * Description: Accept Credit Card and PayPal payments in LifterLMS via 2Checkout
 * Version: 1.0
 * Author: codeglitters
 * Author URI: https://codeglitters.com/
 * Text Domain: payment-for-lifterlms-2checkout
 * Domain Path: /languages
 * Requires at least: 4.8
 * Tested up to: 5.7.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CG_2CO_LLMS_VERSION' ) ) {
	define( 'CG_2CO_LLMS_VERSION', '1.0' );
}

if ( ! defined( 'CG_2CO_LLMS_FILE' ) ) {
	define( 'CG_2CO_LLMS_FILE', __FILE__ );
}

if ( ! defined( 'CG_2CO_LLMS_DIR' ) ) {
	define( 'CG_2CO_LLMS_DIR', dirname( CG_2CO_LLMS_FILE ) . '/' );
}

if ( ! class_exists( 'CG_2checkout_Llms' ) ) {
	require_once CG_2CO_LLMS_DIR . 'class-cg-2checkout-llms.php';
}

function cg_2checkout_llms() {
	return CG_2checkout_Llms::instance();
}
add_action( 'lifterlms_init', 'cg_2checkout_llms');
