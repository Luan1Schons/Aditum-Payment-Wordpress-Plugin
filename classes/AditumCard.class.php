<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
/**
 * Class Init WooCommerce Gateway
 */
class WC_Aditum_Card_Pay_Gateway extends WC_Payment_Gateway {

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
	 * Ambient Deadline
	 *
	 * @var string
	 */
	public $deadline = 0;


	/**
	 * Ambient Initial Status
	 *
	 * @var string
	 */
	public $initial_status = '';

	/**
	 * Function Plugin constructor
	 */
	public function __construct() {
		$this->id = 'aditum_card';
		// $this->icon               = apply_filters( 'woocommerce_aditum_card_icon', plugins_url() . '/../plugins/aditum-boleto-gateway/assets/icon.png' );
		$this->has_fields         = true;
		$this->method_title       = __( 'Aditum Cartão', 'wc-aditum-card' );
		$this->method_description = __( 'Aditum Pagamento por Cartão', 'wc-aditum-card' );

		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option(
			'instructions',
			$this->description
		);

		$this->supports = array(
			'products',
		);

		$this->merchant_key   = $this->get_option( 'aditum_card_merchantKey' );
		$this->merchant_cnpj  = $this->get_option( 'aditum_card_cnpj' );
		$this->environment    = $this->get_option( 'aditum_card_environment' );
		$this->initial_status = $this->get_option( 'aditum_boleto_initial_status' );

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

		$inputs_address = array();
		$wc_address     = WC()->countries->get_address_fields( $country = '', $type = '_billing_' );
		foreach ( $wc_address as $key => $address ) {
			$inputs_address[ $key ] = $key;
		}
		$this->form_fields = apply_filters(
			'woo_aditum_card_pay_fields',
			array(
				'enabled'                    => array(
					'title'   => __( 'Habilitar/Desabilitar', 'wc-aditum_card' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum_card' ),
					'default' => 'no',
				),
				'aditum_card_environment'    => array(
					'title'   => __( 'Ambiente do Gateway', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum_card' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum_card' ),
					),
				),
				'title'                      => array(
					'title'       => __( 'Título do Gateway', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Adicione um novo título ao aditum Boleto Gateway, os clientes vão visualizar ese título no checkout.', 'wc-aditum_card' ),
					'default'     => __( 'Aditum Boleto Gateway', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'description'                => array(
					'title'       => __( 'Descrição do Gateway:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'Adicione uma nova descrição para o aditum Boleto Gateway.', 'wc-aditum_card' ),
					'default'     => __( 'Porfavor envie o comprovante do seu pagamento para a loja processar o seu pedido..', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'instructions'               => array(
					'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_initial_status' => array(
					'title'       => __( 'Status do Pedido criado', 'wc-aditum_card' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum_card' ),
					'default'     => __( '2', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_cnpj'           => array(
					'title'       => __( 'CNPJ Do aditum:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_merchantKey'    => array(
					'title'       => __( 'Merchant Key Do aditum:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'def_endereco_rua'           => array(
					'title'   => __( 'Definições do Endereço - Rua:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_numero'        => array(
					'title'   => __( 'Definições do Endereço - Número:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_comp'          => array(
					'title'   => __( 'Definições do Endereço - Complemento:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_bairro'        => array(
					'title'   => __( 'Definições do Endereço - Bairro:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
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
			self::$log->log( $level, $message, array( 'source' => 'wc-aditum-card' ) );
		}
	}

	public function validateInputs( $data ) {

		$keys = array(
			'card_holder_name',
			'aditum_card_number',
			'aditum_card_cvv',
			'aditum_card_expiration_month',
			'aditum_card_year_month',
		);
		foreach ( $data as $key => $input ) {
			if ( in_array( $key, $keys ) ) {
				if ( empty( $data[ $key ] ) ) {
					return false;
				}
			}
		}

		return true;

	}
	/**
	 * Process_payment method.
	 *
	 * @param int $order_id Id of order.
	 */
	public function process_payment( $order_id ) {

		global $woocommerce;
		$order = new WC_Order( $order_id );

		$address_1    = str_replace( '_billing_', '', $this->get_option( 'def_endereco_rua' ) );
		$address_2    = str_replace( '_billing_', '', $this->get_option( 'def_endereco_comp' ) );
		$address_city = str_replace( '_billing_', '', $this->get_option( 'def_endereco_bairro' ) );

		AditumPayments\ApiSDK\Configuration::initialize();
		if ( 'sandbox' === $this->environment ) {
			AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
		}
		AditumPayments\ApiSDK\Configuration::setCnpj( $this->merchant_cnpj );
		AditumPayments\ApiSDK\Configuration::setMerchantToken( $this->merchant_key );
		AditumPayments\ApiSDK\Configuration::setlog( false );
		AditumPayments\ApiSDK\Configuration::login();

		if ( ! $this->validateInputs( $_POST ) ) {
			return wc_add_notice( 'Preencha todos os campos do cartão de crédito.', 'error' );
		} else {
			$data = wp_unslash( $_POST );
		}

		$gateway       = new AditumPayments\ApiSDK\Gateway();
		$authorization = new AditumPayments\ApiSDK\Domains\Authorization();

		$customer_phone_area_code = substr( $order->get_billing_phone(), 0, 2 );
		$customer_phone           = substr( $order->get_billing_phone(), 2 );
		$amount                   = str_replace( '.', '', $order->get_total() );

		// ! Customer
		$authorization->customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$authorization->customer->setEmail( $order->get_billing_email() );
		$authorization->customer->setId( "$order_id" );
		
		if ( strlen( $order->get_meta( '_billing_cpf' ) ) === 14 ) {

			$authorization->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ );

			$cpf = str_replace( '.', '', $order->get_meta( '_billing_cpf' ) );
			$cpf = str_replace( '-', '', $cpf );
			$authorization->customer->setDocument( $this->merchant_cnpj );
		} else {
			$authorization->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ  );

			$cnpj = str_replace( '.', '', $order->get_meta( '_billing_cnpj' ) );
			$cnpj = str_replace( '-', '', $cnpj );
			$authorization->customer->setDocument( $this->merchant_cnpj );
		}


		// ! Customer->address

		$authorization->customer->address->setStreet( $order->get_data()['billing'][ $address_1 ] );
		$authorization->customer->address->setNumber( $order->get_meta( '_billing_number' ) );
		$authorization->customer->address->setNeighborhood( $order->get_data()['billing'][ $address_city ] );
		$authorization->customer->address->setCity( $order->get_data()['billing'][ $address_city ] );
		$authorization->customer->address->setState( $order->get_billing_state() );
		$authorization->customer->address->setCountry( $order->get_billing_country() );
		$authorization->customer->address->setZipcode( str_replace( '-', '', $order->get_billing_postcode() ) );
		$authorization->customer->address->setComplement( $order->get_data()['billing'][ $address_2 ] );
	

		// ! Customer->phone
		$authorization->customer->phone->setCountryCode( '55' );
		$authorization->customer->phone->setAreaCode( $customer_phone_area_code );
		$authorization->customer->phone->setNumber( $customer_phone );
		$authorization->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

		// ! Transactions
		$authorization->transactions->setAmount( $amount );
		$authorization->transactions->setPaymentType( AditumPayments\ApiSDK\Enum\PaymentType::CREDIT );
		$authorization->transactions->setInstallmentNumber( 2 ); // Só pode ser maior que 1 se o tipo de transação for crédito.
	
		// ! Transactions->card
		$authorization->transactions->card->setCardNumber( str_replace( ' ', '', $data['aditum_card_number'] ) );
		$authorization->transactions->card->setCVV( $data['aditum_card_cvv'] );
		$authorization->transactions->card->setCardholderName( $data['card_holder_name'] );
		$authorization->transactions->card->setExpirationMonth( $data['aditum_card_expiration_month'] );
		$authorization->transactions->card->setExpirationYear( 20 . $data['aditum_card_year_month'] );

		$res = $gateway->charge( $authorization );

		if ( isset( $res['status'] ) ) {
			if ( AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED === $res['status'] ) {

					// ! Mark as on-hold (we're awaiting the cheque)
					$order->update_status( 'completed', __( 'Pagamento Concluído', 'wc-aditum-card' ) );

					// ! Remove cart
					$woocommerce->cart->empty_cart();

					// ! Return thankyou redirect
					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order )
					); 

			} else {
				return wc_add_notice( $res['charge']->transactions[0]->errorMessage, 'error' );
			}
		} else {
			if ( null !== $res ) {
				return wc_add_notice( $res['httpMsg'], 'error' );
			}
		}
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
