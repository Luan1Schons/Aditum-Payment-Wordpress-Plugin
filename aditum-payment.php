<?php

/**
 * Plugin Name:       Aditum Boleto Gateway
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
	wp_enqueue_script( 'jquerymask', plugins_url() . '/aditum-boleto-gateway/assets/js/jquery.mask.js', array( 'jquery' ), '1.0', false );
	wp_enqueue_script( 'main-scripts', plugins_url() . '/aditum-boleto-gateway/assets/js/app.js', array(), '1.0', false );
}


add_action( 'plugins_loaded', 'aditum_gateways_init', 0 );
/**
 * add gateway class and register with woocommerce
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
	$order       = new WC_Order( $order_id );
	$credentials = new WC_Aditum_Boleto_Pay_Gateway();

	$amount = str_replace( '.', '', $order->get_total() );

	if ( $order->get_payment_method() === 'aditum_boleto' ) {
		$boleto_data = $order->get_meta( '_params_aditum_boleto' );
		if ( empty( $boleto_data ) ) {

			AditumPayments\ApiSDK\Configuration::initialize();

			if ( 'sandbox' === $credentials->environment ) {
				AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
			}

			AditumPayments\ApiSDK\Configuration::setCnpj( $credentials->merchant_cnpj );
			AditumPayments\ApiSDK\Configuration::setMerchantToken( $credentials->merchant_key );
			AditumPayments\ApiSDK\Configuration::setlog( false );
			AditumPayments\ApiSDK\Configuration::login();

			$customer_phone_area_code = substr( $order->get_billing_phone(), 0, 2 );
			$customer_phone           = substr( $order->get_billing_phone(), 2 );

			$gateway = new AditumPayments\ApiSDK\Gateway();
			$boleto  = new AditumPayments\ApiSDK\Domains\Boleto();

			$boleto->setDeadline( $credentials->deadline );

			// ! Customer
			$boleto->customer->setId( "$order_id" );
			$boleto->customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
			$boleto->customer->setEmail( $order->get_billing_email() );
			if ( strlen( $order->get_meta( '_billing_cpf' ) ) === 14 ) {

				$boleto->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ );

				$cpf = str_replace( '.', '', $order->get_meta( '_billing_cpf' ) );
				$cpf = str_replace( '-', '', $cpf );
				$boleto->customer->setDocument( $credentials->merchant_cnpj );
			} else {
				$boleto->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ );

				$cnpj = str_replace( '.', '', $order->get_meta( '_billing_cnpj' ) );
				$cnpj = str_replace( '-', '', $cnpj );
				$boleto->customer->setDocument( $credentials->merchant_cnpj );
			}

			// ! Customer->address
			$boleto->customer->address->setStreet( $order->get_billing_address_1() );
			$boleto->customer->address->setNumber( $order->get_meta( '_billing_number' ) );
			$boleto->customer->address->setNeighborhood( $order->get_billing_city() );
			$boleto->customer->address->setCity( $order->get_billing_city() );
			$boleto->customer->address->setState( $order->get_billing_state() );
			$boleto->customer->address->setCountry( $order->get_billing_country() );
			$boleto->customer->address->setZipcode( $order->get_billing_postcode() );
			$boleto->customer->address->setComplement( '' );

			// ! Customer->phone
			$boleto->customer->phone->setCountryCode( '55' );
			$boleto->customer->phone->setAreaCode( $customer_phone_area_code );
			$boleto->customer->phone->setNumber( $customer_phone );
			$boleto->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

			// ! Transactions
			$boleto->transactions->setAmount( $amount );
			$boleto->transactions->setInstructions( 'Crédito de teste' );

			$res = $gateway->charge( $boleto );
			echo '<p>Debug:</p>';
			var_dump( $res );
			echo '<br><br>';
			if ( isset( $res['status'] ) ) {
				if ( AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED === $res['status'] ) {

					// ! Insert params to metadata
					$order->update_meta_data(
						'_params_aditum_boleto',
						array(
							'order_id'                    => $order_id,
							'boleto_chargeId'             => $res['charge']->id,
							'boleto_chargeStatus'         => $res['charge']->chargeStatus,
							'boleto_transaction_id'       => $res['charge']->transactions[0]->transactionId,
							'boleto_transaction_barcode'  => $res['charge']->transactions[0]->barcode,
							'boleto_transaction_digitalLine' => $res['charge']->transactions[0]->digitalLine,
							'boleto_transaction_amount'   => $res['charge']->transactions[0]->amount,
							'boleto_transaction_transactionStatus' => $res['charge']->transactions[0]->transactionStatus,
							'boleto_transaction_bankSlipUrl' => $res['charge']->transactions[0]->bankSlipUrl,
							'boleto_transaction_deadline' => $res['charge']->transactions[0]->deadline,
						)
					);

					$order->save();

					// ! This will output the barcode as HTML output to display in the browser
					echo 'Código de Barras:';
					$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
					echo $generator->getBarcode( $res['charge']->transactions[0]->barcode, $generator::TYPE_CODE_128 );
					echo '<p>' . esc_attr( $res['charge']->transactions[0]->digitalLine ) . '</p>';
				}
			} else {
				if ( $res != null ) {
					echo 'httStatus: ' . esc_attr( $res['httpStatus'] )
					. "\n httpMsg: " . esc_attr( $res['httpMsg'] )
					. "\n";
				}
			}
		} else {
			// ! This will output the barcode as HTML output to display in the browser
			echo 'Código de Barras:';
			$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
			echo $generator->getBarcode( $boleto_data['boleto_transaction_barcode'], $generator::TYPE_CODE_128 );
			echo '<p>' . $boleto_data['boleto_transaction_digitalLine'] . '</p>';
		}
	} elseif ( $order->get_payment_method() === 'aditum_card' ) {

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
