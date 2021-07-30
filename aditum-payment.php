<?php
/**
 * Plugin Name:       Aditum Gateway
 * Plugin URI:        https://aditum.com.br/
 * Description:       Gateway de pagamento de boleto do Aditum para o WooCommerce
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            ER Soluções Web
 * Author URI:        https://www.ersolucoesweb.com.br/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://www.ersolucoesweb.com.br/
 * Text Domain:       aditum-gateway-boleto
 */

require_once dirname( __FILE__, 1 ) . '/vendor/autoload.php';

define( 'WEBHOOK_KEY', '55c734b28fdef5b14d722e17809b4a46' );

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

register_activation_hook( __FILE__, 'child_plugin_activate' );
/**
 * Dependency.
 */
function child_plugin_activate() {
	// ! Require parent plugin
	if ( ! is_plugin_active( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) and current_user_can( 'activate_plugins' ) ) {
		// ! Stop activation redirect and show error
		wp_die( 'Desculpe, mas este plugin requer que o plugin "Brazilian Market on WooCommerce" esteja instalado e ativo. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Voltar para os Plugins</a>' );
	}
}

add_action( 'wp_enqueue_scripts', 'aditum_scripts_method' );
/**
 * Enqueue a script with jQuery as a dependency.
 */
function aditum_scripts_method() {
	wp_enqueue_script( 'jquerymask', plugins_url() . '/aditum-payment-gateway/assets/js/jquery.mask.js', array( 'jquery' ), '1.0', false );
	wp_enqueue_script( 'main-scripts', plugins_url() . '/aditum-payment-gateway/assets/js/app.js', array(), '1.0', false );
}

add_filter(
	'template_include',
	function ( $template ) {

		if ( is_page( wp_strip_all_tags( 'WebHook Aditum Boleto' ) ) ) {
			return __DIR__ . '/pages/webhook.php';
		}

		return $template;
	},
	99
);

add_filter('woocommerce_checkout_fields', 'custom_billing_fields', 1000, 1);
/**
 * Set billing_neighborhood to required
 */
function custom_billing_fields( $fields ) {
    $fields['billing']['billing_neighborhood']['required'] = true;

    return $fields;
}

register_activation_hook( __FILE__, 'aditum_function_to_run' );
/**
 * Aditum Function to Run
 */
function aditum_function_to_run() {

	$page = array(
		'post_slug'    => 'webhook_boleto_aditum',
		'post_title'   => wp_strip_all_tags( 'WebHook Aditum Boleto' ),
		'post_content' => '',
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_author'  => 1,
	);

	// ! Insert the post into the database
	$id = wp_insert_post( $page );

	update_option( 'aditum_boleto_webhook_id', $id, '', 'yes' );
}

register_deactivation_hook( __FILE__, 'trellwoo_function_to_deactive' );
/**
 * Aditum Function to Deactive
 */
function trellwoo_function_to_deactive() {
	wp_delete_post( get_option( 'aditum_boleto_webhook_id' ), true );
}

add_action( 'plugins_loaded', 'aditum_gateways_init', 0 );
/**
 * Add gateway class and register with woocommerce
 */
function aditum_gateways_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	// ! Boleto Gateway Class Register
	include_once plugin_dir_path( __FILE__ ) . 'classes/AditumBoleto.class.php';
	add_filter( 'woocommerce_payment_gateways', 'wc_gateway_aditum_boleto', 1000 );
	function wc_gateway_aditum_boleto( $methods ) {
		$methods[] = 'WC_Aditum_Boleto_Pay_Gateway';
		return $methods;
	}

	// ! Credit Card Gateway Class Register
	include_once plugin_dir_path( __FILE__ ) . 'classes/AditumCard.class.php';
	add_filter( 'woocommerce_payment_gateways', 'wc_gateway_aditum_card', 1000 );
	function wc_gateway_aditum_card( $methods ) {
		$methods[] = 'WC_Aditum_Card_Pay_Gateway';
		return $methods;
	}

}


add_action( 'woocommerce_thankyou', 'aditum_add_content_thankyou' );
/**
 * Thank You Page Content
 *
 * @param int $order_id Order Id.
 */
function aditum_add_content_thankyou( $order_id ) {
	$order = new WC_Order( $order_id );

	if ( $order->get_payment_method() === 'aditum_boleto' ) {

		$boleto_data = $order->get_meta( '_params_aditum_boleto' );

		if ( ! empty( $boleto_data ) ) {

			// ! This will output the barcode as HTML output to display in the browser
			echo 'Código de Barras:';
			$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
			echo $generator->getBarcode( $boleto_data['boleto_transaction_barcode'], $generator::TYPE_CODE_128 );
			echo '<p>' . $boleto_data['boleto_transaction_digitalLine'] . '</p>';

			echo '<a href="' . $boleto_data['boleto_transaction_bankSlipUrl'] . '">Clique aqui para acessar o boleto.</a>';
		}
	}
}

add_filter( 'woocommerce_gateway_description', 'gateway_aditum_card_custom_fields', 20, 2 );
/**
 * Card Fields Checkout
 *
 * @param int $description Description.
 * @param int $payment_id  Payment Id.
 */
function gateway_aditum_card_custom_fields( $description, $payment_id ) {
	if ( 'aditum_card' === $payment_id ) {

		ob_start(); // ! Start buffering

		echo '<div  class="aditum-card-fields" style="padding:10px 0;">';

		woocommerce_form_field(
			'card_holder_name',
			array(
				'type'     => 'text',
				'label'    => __( 'Nome do Proprietário do cartão', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'card_holder_document',
			array(
				'type'     => 'text',
				'label'    => __( 'Documento do Proprietário do cartão (CPF/CNPJ)', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_number',
			array(
				'type'     => 'text',
				'label'    => __( 'Informe o número do cartão', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		echo '<span id="card-brand"></span>';

		woocommerce_form_field(
			'aditum_card_cvv',
			array(
				'type'     => 'text',
				'label'    => __( 'Código de segurança (CVV)', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_expiration_month',
			array(
				'type'     => 'number',
				'label'    => __( 'Mês Expiração', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_year_month',
			array(
				'type'     => 'number',
				'label'    => __( 'Ano Expiração', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		echo '<div>';

		$description .= ob_get_clean(); // ! Append buffered content
	}
	return $description;
}


add_action( 'wp_ajax_get_card_brand', 'aditum_get_card_brand' );
add_action( 'wp_ajax_nopriv_get_card_brand', 'aditum_get_card_brand' );
/**
 * Get card number
 *
 * @param int $bin card number.
 */
function aditum_get_card_brand() {

	$data = wp_unslash( $_POST );

	$credentials = new WC_Aditum_Card_Pay_Gateway();
	AditumPayments\ApiSDK\Configuration::initialize();
	if ( 'sandbox' === $credentials->environment ) {
		AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
	}
	AditumPayments\ApiSDK\Configuration::setCnpj( $credentials->merchant_cnpj );
	AditumPayments\ApiSDK\Configuration::setMerchantToken( $credentials->merchant_key );
	AditumPayments\ApiSDK\Configuration::setlog( false );
	AditumPayments\ApiSDK\Configuration::login();

	$brand_name = AditumPayments\ApiSDK\Helper\Utils::getBrandCardBin( str_replace( ' ', '', $data['bin'] ) );

	if ( $brand_name === null ) {
		$array_result = array(
			'status' => 'error',
			'brand'  => 'null',
		);
	} else {
		if ( true === $brand_name['status'] ) {
			$array_result = array(
				'status' => 'success',
				'brand'  => $brand_name['brand'],
			);
		} else {
			$array_result = array(
				'status' => 'error',
				'brand'  => 'null',
			);
		}
	}

	// ! Make your array as json
	wp_send_json( $array_result );
	// ! Don't forget to stop execution afterward.
	wp_die();
}

add_option( 'woocommerce_pay_page_id', get_option( 'woocommerce_thanks_page_id' ) );
