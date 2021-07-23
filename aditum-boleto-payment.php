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
function child_plugin_activate() {

	// Require parent plugin
	if ( ! is_plugin_active( 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) and current_user_can( 'activate_plugins' ) ) {
		// Stop activation redirect and show error
		wp_die( 'Desculpe, mas este plugin requer que o plugin "Brazilian Market on WooCommerce" esteja instalado e ativo. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Voltar para os Plugins</a>' );
	}
}

add_action( 'plugins_loaded', 'aditum_boleto_payment_init', 11 );
/**
 * Init Aditum_boleto_payment_init class method
 */
function aditum_boleto_payment_init() {
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
		/**
		 * Class Init WooCommerce Gateway
		 */
		class WC_aditum_Boleto_Pay_Gateway extends WC_Payment_Gateway {

			/**
			 * Whether or not logging is enabled
			 *
			 * @var bool
			 */
			public static $log_enabled = true;

			/**
			 * Logger instance
			 *
			 * @var WC_Logger
			 */
			public static $log = true;

			/**
			 * Merchant Key Credentials
			 *
			 * @var string
			 */
			public $merchant_key = '';

			/**
			 * Merchant CNPJ Credentials
			 *
			 * @var string
			 */
			public $merchant_cnpj = '';

			/**
			 * Ambient Environment
			 *
			 * @var string
			 */
			public $environment = '';

			/**
			 * Function Plugin constructor
			 */
			public function __construct() {
				$this->id                 = 'aditum_boleto';
				$this->icon               = apply_filters( 'woocommerce_aditum_boleto_icon', plugins_url() . '/aditum_boleto_payment/assets/icon.png' );
				$this->has_fields         = true;
				$this->method_title       = __( 'Aditum Boleto', 'wc-aditum-boleto' );
				$this->method_description = __( 'Aditum Pagamento por Boleto', 'wc-aditum-boleto' );

				$this->title        = $this->get_option( 'title' );
				$this->description  = $this->get_option( 'description' );
				$this->instructions = $this->get_option(
					'instructions',
					$this->description
				);

				$this->supports = array(
					'products',
				);

				$this->merchant_key  = $this->get_option( 'aditum_boleto_merchantKey' );
				$this->merchant_cnpj = $this->get_option( 'aditum_boleto_cnpj' );
				$this->environment   = $this->get_option( 'aditum_boleto_environment' );

				$this->init_form_fields();
				$this->init_settings();

				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thank_you_' . $this->id, array( $this, 'thankyou_page' ) );
				add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
			}
			/**
			 * Init init_form_fields form fields
			 */
			public function init_form_fields() {
				$this->form_fields = apply_filters(
					'woo_aditum_boleto_pay_fields',
					array(
						'enabled'                   => array(
							'title'   => __( 'Habilitar/Desabilitar', 'wc-aditum-boleto' ),
							'type'    => 'checkbox',
							'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum-boleto' ),
							'default' => 'no',
						),
						'aditum_boleto_environment' => array(
							'title'   => __( 'Ambiente do Gateway', 'wc-aditum-boleto' ),
							'type'    => 'select',
							'options' => array(
								'production' => __( 'Produção', 'wc-aditum-boleto' ),
								'sandbox'    => __( 'Sandbox', 'wc-aditum-boleto' ),
							),
						),
						'title'                     => array(
							'title'       => __( 'Título do Gateway', 'wc-aditum-boleto' ),
							'type'        => 'text',
							'description' => __( 'Adicione um novo título ao aditum Boleto Gateway, os clientes vão visualizar ese título no checkout.', 'wc-aditum-boleto' ),
							'default'     => __( 'Aditum Boleto Gateway', 'wc-aditum-boleto' ),
							'desc_tip'    => true,
						),
						'description'               => array(
							'title'       => __( 'Descrição do Gateway:', 'wc-aditum-boleto' ),
							'type'        => 'textarea',
							'description' => __( 'Adicione uma nova descrição para o aditum Boleto Gateway.', 'wc-aditum-boleto' ),
							'default'     => __( 'Porfavor envie o comprovante do seu pagamento para a loja processar o seu pedido..', 'wc-aditum-boleto' ),
							'desc_tip'    => true,
						),
						'instructions'              => array(
							'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum-boleto' ),
							'type'        => 'textarea',
							'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum-boleto' ),
							'default'     => __( '', 'wc-aditum-boleto' ),
							'desc_tip'    => true,
						),
						'aditum_boleto_cnpj'        => array(
							'title'       => __( 'CNPJ Do aditum:', 'wc-aditum-boleto' ),
							'type'        => 'text',
							'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum-boleto' ),
							'default'     => __( '', 'wc-aditum-boleto' ),
							'desc_tip'    => true,
						),
						'aditum_boleto_merchantKey' => array(
							'title'       => __( 'Merchant Key Do aditum:', 'wc-aditum-boleto' ),
							'type'        => 'text',
							'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum-boleto' ),
							'default'     => __( '', 'wc-aditum-boleto' ),
							'desc_tip'    => true,
						),
					)
				);
			}

			/**
			 * Logging method.
			 *
			 * @param string $message Log message.
			 * @param string $level Optional. Default 'info'. Possible values:
			 *                      emergency|alert|critical|error|warning|notice|info|debug.
			 */
			public static function log( $message, $level = 'info' ) {
				if ( self::$log_enabled ) {
					if ( empty( self::$log ) ) {
						self::$log = wc_get_logger();
					}
					self::$log->log( $level, $message, array( 'source' => 'wc-aditum-boleto' ) );
				}
			}

			/**
			 * Process_payment method.
			 *
			 * @param int $order_id Id of order.
			 */
			public function process_payment( $order_id ) {
				global $woocommerce;
				$order = new WC_Order( $order_id );

				// Mark as on-hold (we're awaiting the cheque)
				$order->update_status( 'on-hold', __( 'Aguardando o pagamento do boleto', 'wc-aditum-boleto' ) );

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}

			/**
			 * Thankyou_page method.
			 */
			public function thankyou_page() {
				if ( $this->instructions ) {
					echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
				}
			}
		}
	}
}
add_action( 'woocommerce_thankyou', 'boleto_aditum_add_content_thankyou' );
/**
 * Add Boleto Infos
 *
 * @param int $order_id Order Id.
 */
function boleto_aditum_add_content_thankyou( $order_id ) {
	$order       = new WC_Order( $order_id );
	$boleto_data = $order->get_meta( '_params_aditum_boleto' );
	$credentials = new WC_aditum_Boleto_pay_Gateway();

	$amount = str_replace( '.', '', $order->get_total() );

	if ( $order->get_payment_method() === 'aditum_boleto' ) {
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

			$boleto->setDeadline( '2' );

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
	}
}

/*
CPF/CNPJ Input
add_filter( 'woocommerce_gateway_description', 'gateway_aditum_boleto_custom_fields', 20, 2 );
function gateway_aditum_boleto_custom_fields( $description, $payment_id ) {
	if ( 'aditum_boleto' === $payment_id ) {
		ob_start(); // Start buffering

		echo '<div  class="aditum-boleto-fields" style="padding:10px 0;">';

		woocommerce_form_field(
			'aditum_boleto_cnpj_cpf',
			array(
				'type'     => 'text',
				'label'    => __( 'Informe o CNPJ/CPF', 'woocommerce' ),
				'class'    => array( 'form-row-wide' ),
				'required' => true,
			),
			''
		);

		echo '<div>';

		$description .= ob_get_clean(); // Append buffered content
	}
	return $description;
}
*/
add_action( 'woocommerce_view_order', 'aditum_boleto_pending_payment_instructions' );
/**
 * Boleto Pending Payment Instructios
 *
 * @param int $order_id Order Id.
 */
function aditum_boleto_pending_payment_instructions( $order_id ) {
	$order = new WC_Order( $order_id );

	if ( 'on-hold' === $order->status && $this->id == $order->payment_method ) {
		$html  = '<div class="woocommerce-info">';
		$html .= sprintf( '<a class="button" href="%s" target="_blank">%s</a>', get_post_meta( $order->id, 'aditum_boleto_url', true ), __( 'Billet print', 'aditum_boleto-woocommerce' ) );

		$message  = sprintf( __( '%1$sAttention!%2$s Not registered the billet payment for this order yet.', 'aditum_boleto-woocommerce' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Please click the following button and pay the billet in your Internet Banking.', 'aditum_boleto-woocommerce' ) . '<br />';
		$message .= __( 'If you prefer, print and pay at any bank branch or home lottery.', 'aditum_boleto-woocommerce' ) . '<br />';
		$message .= __( 'Ignore this message if the payment has already been made​​.', 'aditum_boleto-woocommerce' ) . '<br />';

		$html .= apply_filters( 'woocommerce_aditum_boleto_pending_payment_instructions', $message, $order );

		$html .= '</div>';

		echo $html;
	}
}

add_filter( 'woocommerce_payment_gateways', 'add_to_woo_aditum_boleto_payment_gateway' );
/**
 * Add Gateway to List
 *
 * @param array $gateways
 */
function add_to_woo_aditum_boleto_payment_gateway( $gateways ) {
	$gateways[] = 'WC_aditum_Boleto_pay_Gateway';
	return $gateways;
}
add_option( 'woocommerce_pay_page_id', get_option( 'woocommerce_thanks_page_id' ) );
