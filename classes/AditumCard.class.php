<?php
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
		$this->deadline       = $this->get_option( 'aditum_boleto_deadline_boleto' );

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
		$wc_address     = WC()->countries->get_address_fields( $country = '', $type = '_billing' );
		foreach ( $wc_address as $key => $address ) {
			$inputs_address[ $key ] = $key;
		}
		$this->form_fields = apply_filters(
			'woo_aditum_card_pay_fields',
			array(
				'enabled'                     => array(
					'title'   => __( 'Habilitar/Desabilitar', 'wc-aditum_card' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum_card' ),
					'default' => 'no',
				),
				'aditum_card_environment'     => array(
					'title'   => __( 'Ambiente do Gateway', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum_card' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum_card' ),
					),
				),
				'title'                       => array(
					'title'       => __( 'Título do Gateway', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Adicione um novo título ao aditum Boleto Gateway, os clientes vão visualizar ese título no checkout.', 'wc-aditum_card' ),
					'default'     => __( 'Aditum Boleto Gateway', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'description'                 => array(
					'title'       => __( 'Descrição do Gateway:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'Adicione uma nova descrição para o aditum Boleto Gateway.', 'wc-aditum_card' ),
					'default'     => __( 'Porfavor envie o comprovante do seu pagamento para a loja processar o seu pedido..', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'instructions'                => array(
					'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum_card' ),
					'type'        => 'textarea',
					'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum_card' ),
					'default'     => __( '', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_deadline_boleto' => array(
					'title'       => __( 'Tempo de expiração do boleto (Dias)', 'wc-aditum_card' ),
					'type'        => 'number',
					'description' => __( 'Tempo de expiração do boleto.', 'wc-aditum_card' ),
					'default'     => __( '2', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_initial_status'  => array(
					'title'       => __( 'Status do Pedido criado', 'wc-aditum_card' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum_card' ),
					'default'     => __( '2', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_cnpj'            => array(
					'title'       => __( 'CNPJ Do aditum:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum_card' ),
					'default'     => __( '', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_card_merchantKey'     => array(
					'title'       => __( 'Merchant Key Do aditum:', 'wc-aditum_card' ),
					'type'        => 'text',
					'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum_card' ),
					'default'     => __( '', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'def_endereco_rua'            => array(
					'title'   => __( 'Definições do Endereço - Rua:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_numero'         => array(
					'title'   => __( 'Definições do Endereço - Número:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_comp'           => array(
					'title'   => __( 'Definições do Endereço - Complemento:', 'wc-aditum_card' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_bairro'         => array(
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

	/**
	 * Process_payment method.
	 *
	 * @param int $order_id Id of order.
	 */
	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );

		AditumPayments\ApiSDK\Configuration::initialize();
		AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
		AditumPayments\ApiSDK\Configuration::setCnpj( $this->merchant_cnpj );
		AditumPayments\ApiSDK\Configuration::setMerchantToken( $this->merchant_key );
		AditumPayments\ApiSDK\Configuration::setlog( true );
		AditumPayments\ApiSDK\Configuration::login();

		$customer_phone_area_code = substr( $order->get_billing_phone(), 0, 2 );
		$customer_phone           = substr( $order->get_billing_phone(), 2 );

		$gateway       = new AditumPayments\ApiSDK\Gateway();
		$authorization = new AditumPayments\ApiSDK\Domains\Authorization();

		// ! Customer
		$authorization->customer->setName( 'ceres' );
		$authorization->customer->setEmail( 'ceres@aditum.co' );

		// ! Customer->address
		$authorization->customer->address->setStreet( $this->get_option( 'def_endereco_rua' ) );
		$authorization->customer->address->setNumber( $this->get_option( 'def_endereco_numero' ) );
		$authorization->customer->address->setNeighborhood( $this->get_option( 'def_endereco_bairro' ) );
		$authorization->customer->address->setCity( $order->get_billing_city() );
		$authorization->customer->address->setState( $order->get_billing_state() );
		$authorization->customer->address->setCountry( $order->get_billing_country() );
		$authorization->customer->address->setZipcode( $order->get_billing_postcode() );
		$authorization->customer->address->setComplement( '' );

		// ! Customer->phone
		$authorization->customer->phone->setCountryCode( '55' );
		$authorization->customer->phone->setAreaCode( $customer_phone_area_code );
		$authorization->customer->phone->setNumber( $customer_phone );
		$authorization->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

		// ! Transactions
		$authorization->transactions->setAmount( 100 );
		$authorization->transactions->setPaymentType( AditumPayments\ApiSDK\Enum\PaymentType::CREDIT );
		$authorization->transactions->setInstallmentNumber( 2 ); // Só pode ser maior que 1 se o tipo de transação for crédito.
		$authorization->transactions->getAcquirer( AditumPayments\ApiSDK\Enum\AcquirerCode::SIMULADOR ); // Valor padrão AditumPayments\ApiSDK\AcquirerCode::ADITUM_ECOM

		// ! Transactions->card
		$authorization->transactions->card->setCardNumber( '5463373320417272' );
		$authorization->transactions->card->setCVV( '879' );
		$authorization->transactions->card->setCardholderName( 'CERES ROHANA' );
		$authorization->transactions->card->setExpirationMonth( 10 );
		$authorization->transactions->card->setExpirationYear( 2022 );

		$res = $gateway->charge( $authorization );

		echo "\n\nResposta:\n";
		print_r( json_encode( $res ) );

		if ( isset( $res['status'] ) ) {
			if ( $res['status'] == AditumPayments\ApiSDK\Enum\ChargeStatus::AUTHORIZED ) {
				echo "\n\nAprovado!\n";
			}
		} else {
			if ( $res != null ) {
				echo 'httStatus: ' . $res['httpStatus']
				. "\n httpMsg: " . $res['httpMsg']
				. "\n";
			}
		}
		// ! Mark as on-hold (we're awaiting the cheque)
		$order->update_status( 'on-hold', __( 'Aguardando o pagamento do boleto', 'wc-aditum-card' ) );

		// ! Remove cart
		$woocommerce->cart->empty_cart();

		// ! Return thankyou redirect
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
