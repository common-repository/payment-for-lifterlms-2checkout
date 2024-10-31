<?php
defined( 'CG_2CO_LLMS_VERSION' ) || exit;

/**
 * @since 1.0
 * @version 1.0
 */
final class CG_2checkout_Llms {

	/**
	 * Singleton instance
	 */
	protected static $_instance = null;

	/**
	 * @return   CG_2checkout_Llms - Main instance
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function define( $name, $value ) {

		if ( ! defined( $name ) ) {
			define( $name, $value );
		}

	}

	private function file( $required_file ) {

		if ( file_exists( $required_file ) ) {
			require_once $required_file;
		}
	
	}

	/**
	 * CG_2checkout_Llms Constructor.
	 * @since 1.0
	 * @return void
	 */
	private function __construct() {

		$this->define_constants();
		$this->init();
		$this->load_textdomain();

	}

	/**
	 * Define LifterLMS Constants
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_constants() {

		$this->define( 'CG_2CO_LLMS_URL',        plugin_dir_url( CG_2CO_LLMS_FILE ) );
		$this->define( 'CG_2CO_LLMS_ASSETS_URL', CG_2CO_LLMS_URL . 'assets/' );

	}

    /**
     * Init CG_2checkout_Llms when WordPress Initialises.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {

        if ( class_exists('LifterLMS') ) {

            add_action( 'wp_enqueue_scripts',         array( $this, 'enqueue_assets' ) );

            add_filter( 'lifterlms_payment_gateways', array( $this, 'add_cg_2checkout_llms_gateway' ) );

            $this->includes();

            do_action( 'cg_2checkout_llms_loaded' );

        }

    }

	/**
	 * Load Files
	 *
	 * @since 1.0
	 * @return void
	 */
	public function includes() {

		$this->file( CG_2CO_LLMS_DIR . 'includes/class-cg-2checkout-llms-functions.php' );
		$this->file( CG_2CO_LLMS_DIR . 'includes/class-cg-2checkout-sdk.php' );
		$this->file( CG_2CO_LLMS_DIR . 'includes/class-cg-2checkout-llms-gateway.php' );
        $this->file( CG_2CO_LLMS_DIR . 'includes/class-cg-2checkout-llms-notification.php' );

	}

    /**
     * Add the Gateway to LifterLMS
     **/
    public function add_cg_2checkout_llms_gateway( $gateways ) {
        $gateways[] = apply_filters( 'cg_2checkout_llms_gateway', 'Cg_2checkout_Llms_Gateway' );
        return $gateways;
    }

	public function enqueue_assets() {

		if ( is_llms_checkout() || is_llms_account_page() ) {

            wp_enqueue_style( 'cg-2checkout-lifterlms-styles', CG_2CO_LLMS_ASSETS_URL . 'css/style.css', array(), CG_2CO_LLMS_VERSION );

            wp_register_script( '2pay', 'https://2pay-js.2checkout.com/v1/2pay.js', array(), CG_2CO_LLMS_VERSION, true );
            wp_register_script( 'cg-2checkout-lifterlms', CG_2CO_LLMS_ASSETS_URL . 'js/script.js', array( 'jquery', '2pay' ), CG_2CO_LLMS_VERSION, true );

            $llms_2co_gateway_data = array(
                'seller_id' => get_cg_llms_option( 'merchant_code' ),
                'language' => get_cg_llms_option( 'form_language' ),
            );
            wp_localize_script( 'cg-2checkout-lifterlms', 'llms_2co_gateway_data', $llms_2co_gateway_data );
             
            wp_enqueue_script( 'cg-2checkout-lifterlms' );
        
        }

	}

	/**
	 * Localize
	 *
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		load_plugin_textdomain( '2checkout-for-lifterlms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

}
