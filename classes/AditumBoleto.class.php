<?php
/**
 * Aditum Gateway Payment Boleto Class
 * Description: Boleto Class
 *
 * @package Aditum/Payments
 */

/**
 * Class Init WooCommerce Gateway
 */
class WC_Aditum_Boleto_Pay_Gateway extends WC_Payment_Gateway {

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
	 * Ambient Initial Status
	 *
	 * @var string
	 */
	public $fine_start = '';

	/**
	 * Ambient Initial Status
	 *
	 * @var string
	 */
	public $fine_value = '';

	/**
	 * Ambient Initial Status
	 *
	 * @var string
	 */
	public $fine_percentual = '';

	/**
	 * Ambient Expiry Date
	 *
	 * @var string
	 */
	public $expiry_date = '';


	/**
	 * Function Plugin constructor
	 */
	public function __construct() {
		$this->id                 = 'aditum_boleto';
		$this->icon               = apply_filters( 'woocommerce_aditum_boleto_icon', plugins_url() . '/../plugins/aditum-payment-gateway/assets/icon.png' );
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

		$this->merchant_key   = $this->get_option( 'aditum_boleto_merchantKey' );
		$this->merchant_cnpj  = $this->get_option( 'aditum_boleto_cnpj' );
		$this->environment    = $this->get_option( 'aditum_boleto_environment' );
		$this->initial_status = $this->get_option( 'aditum_boleto_initial_status' );
		$this->deadline       = $this->get_option( 'aditum_boleto_deadline_boleto' );

		// Fines
		$this->fine_start       	 = $this->get_option( 'aditum_boleto_fine_start' );
		$this->fine_value      		 = $this->get_option( 'aditum_boleto_fine_value' );
		$this->fine_percentual       = $this->get_option( 'aditum_boleto_fine_percentual' );

		$this->expiry_date = $this->get_option( 'aditum_boleto_order_expiry' );


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
		// var_dump(WC()->countries->get_address_fields( $country = '', $type = '_billing'));
		// exit();
		$inputs_address = array();
		$wc_address     = WC()->countries->get_address_fields( null, $type = 'billing_' );
		foreach ( $wc_address as $key => $address ) {
			$inputs_address[ $key ] = $key;
		}
		$this->form_fields = apply_filters(
			'woo_aditum_boleto_pay_fields',
			array(
				'enabled'                       => array(
					'title'   => __( 'Habilitar/Desabilitar:', 'wc-aditum-boleto' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum-boleto' ),
					'default' => 'no',
				),
				'aditum_boleto_environment'     => array(
					'title'   => __( 'Ambiente do Gateway:', 'wc-aditum-boleto' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum-boleto' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum-boleto' ),
					),
				),
				'title'                         => array(
					'title'       => __( 'Título do Gateway:', 'wc-aditum-boleto' ),
					'type'        => 'text',
					'description' => __( 'Adicione um novo título ao aditum Boleto Gateway, os clientes vão visualizar ese título no checkout.', 'wc-aditum-boleto' ),
					'default'     => __( 'Aditum Boleto Gateway', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'description'                   => array(
					'title'       => __( 'Descrição do Gateway:', 'wc-aditum-boleto' ),
					'type'        => 'textarea',
					'description' => __( 'Adicione uma nova descrição para o aditum Boleto Gateway.', 'wc-aditum-boleto' ),
					'default'     => __( 'Porfavor envie o comprovante do seu pagamento para a loja processar o seu pedido..', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'instructions'                  => array(
					'title'       => __( 'Instruções Após o Pedido:', 'wc-aditum-boleto' ),
					'type'        => 'textarea',
					'description' => __( 'As instruções iram aparecer na página de Obrigado & Email após o pedido ser feito.', 'wc-aditum-boleto' ),
					'default'     => __( '', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_order_expiry' => array(
					'title'       => __( 'Tempo de expiração do Pedido:', 'wc-aditum_card' ),
					'type'        => 'number',
					'description' => __( 'Depois de quanto tempo o pedido pendente de pagamento deve ser cancelado, define em dias.', 'wc-aditum_card' ),
					'default'     => __( '3', 'wc-aditum_card' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_deadline_boleto' => array(
					'title'       => __( 'Tempo de expiração do boleto (Dias):', 'wc-aditum-boleto' ),
					'type'        => 'number',
					'description' => __( 'Tempo de expiração do boleto.', 'wc-aditum-boleto' ),
					'default'     => __( '2', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_fine_start' => array(
					'title'       => __( 'Dias para multa:', 'wc-aditum-boleto' ),
					'type'        => 'number',
					'description' => __( 'Dias para começar a contar a multa.', 'wc-aditum-boleto' ),
					'default'     => __( '2', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_fine_value' => array(
					'title'       => __( 'Valor fixo da multa:', 'wc-aditum-boleto' ),
					'type'        => 'number',
					'description' => __( 'Valor fixo da multa.', 'wc-aditum-boleto' ),
					'default'     => __( '300', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_fine_percentual' => array(
					'title'       => __( 'Valor percentual da multa:', 'wc-aditum-boleto' ),
					'type'        => 'number',
					'description' => __( 'valor percentual sobre o valor original da multa.', 'wc-aditum-boleto' ),
					'default'     => __( '10', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_initial_status'  => array(
					'title'       => __( 'Status do Pedido criado:', 'wc-aditum-boleto' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_cnpj'            => array(
					'title'       => __( 'CNPJ:', 'wc-aditum-boleto' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum-boleto' ),
					'default'     => __( '', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_merchantKey'     => array(
					'title'       => __( 'Merchant Token:', 'wc-aditum-boleto' ),
					'type'        => 'text',
					'description' => __( 'Insira o Merchant Key cadastrado no Aditum.', 'wc-aditum-boleto' ),
					'default'     => __( '', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'def_endereco_rua'              => array(
					'title'   => __( 'Definições do Endereço - Rua:', 'wc-aditum-boleto' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_numero'           => array(
					'title'   => __( 'Definições do Endereço - Número:', 'wc-aditum-boleto' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_comp'             => array(
					'title'   => __( 'Definições do Endereço - Complemento:', 'wc-aditum-boleto' ),
					'type'    => 'select',
					'options' => $inputs_address,
				),
				'def_endereco_bairro'           => array(
					'title'   => __( 'Definições do Endereço - Bairro:', 'wc-aditum-boleto' ),
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

		$address_1    = get_post_meta($order_id, '_'.$this->get_option( 'def_endereco_rua' ), true);
		$address_2    = get_post_meta($order_id, '_'.$this->get_option( 'def_endereco_comp' ), true);
		$address_number =  get_post_meta($order_id, '_'.$this->get_option( 'def_endereco_numero' ), true);
		$address_neightboorhood = get_post_meta($order_id, '_'.$this->get_option( 'def_endereco_bairro' ), true);

		$amount = str_replace( '.', '', $order->get_total() );

		AditumPayments\ApiSDK\Configuration::initialize();

		if ( 'sandbox' === $this->environment ) {
			AditumPayments\ApiSDK\Configuration::setUrl( AditumPayments\ApiSDK\Configuration::DEV_URL );
		}

		AditumPayments\ApiSDK\Configuration::setCnpj( $this->merchant_cnpj );
		AditumPayments\ApiSDK\Configuration::setMerchantToken( $this->merchant_key );
		AditumPayments\ApiSDK\Configuration::setlog( false );
		AditumPayments\ApiSDK\Configuration::login();

		$customer_phone_area_code = substr( $order->get_billing_phone(), 0, 2 );
		$customer_phone           = substr( $order->get_billing_phone(), 2 );

		$gateway = new AditumPayments\ApiSDK\Gateway();
		$boleto  = new AditumPayments\ApiSDK\Domains\Boleto();

		$boleto->setDeadline( $credentials->deadline );
        $boleto->setSessionId($_POST['antifraud_token']);

		// ! Customer
		$boleto->customer->setId( "$order_id" );
		$boleto->customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$boleto->customer->setEmail( $order->get_billing_email() );
		if ( strlen( $order->get_meta( '_billing_cpf' ) ) === 14 ) {

			$boleto->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ );

			$cpf = str_replace( '.', '', $order->get_meta( '_billing_cpf' ) );
			$cpf = str_replace( '-', '', $cpf );
			$boleto->customer->setDocument( $this->merchant_cnpj );
		} else {
			$boleto->customer->setDocumentType( AditumPayments\ApiSDK\Enum\DocumentType::CNPJ );

			$cnpj = str_replace( '.', '', $order->get_meta( '_billing_cnpj' ) );
			$cnpj = str_replace( '-', '', $cnpj );
			$boleto->customer->setDocument( $this->merchant_cnpj );
		}

		// ! Customer->address
		$boleto->customer->address->setStreet( $address_1 );
		$boleto->customer->address->setNumber( $address_number );
		$boleto->customer->address->setNeighborhood( $address_number );
		$boleto->customer->address->setCity( $order->get_billing_city() );
		$boleto->customer->address->setState( $order->get_billing_state() );
		$boleto->customer->address->setCountry( $order->get_billing_country() );
		$boleto->customer->address->setZipcode( $order->get_billing_postcode() );
		$boleto->customer->address->setComplement( $address_2 );

		// ! Customer->phone
		$boleto->customer->phone->setCountryCode( '55' );
		$boleto->customer->phone->setAreaCode( $customer_phone_area_code );
		$boleto->customer->phone->setNumber( $customer_phone );
		$boleto->customer->phone->setType( AditumPayments\ApiSDK\Enum\PhoneType::MOBILE );

		// ! Transactions
		$boleto->transactions->setAmount( $amount );
		$boleto->transactions->setInstructions( 'Crédito de teste' );

		// Transactions->fine (opcional)

		if(!empty($this->get_option('aditum_boleto_fine_start'))){
			$boleto->transactions->fine->setStartDate($this->fine_start);
			$boleto->transactions->fine->setAmount($this->fine_value);
			$boleto->transactions->fine->setInterest($this->fine_percentual);
		}

		$res = $gateway->charge( $boleto );
		if ( isset( $res['status'] ) ) {
			if ( AditumPayments\ApiSDK\Enum\ChargeStatus::PRE_AUTHORIZED === $res['status'] ) {

				// ! Insert params to metadata
				$order->update_meta_data(
					'_params_aditum_boleto',
					array(
						'order_id'                       => $order_id,
						'boleto_chargeId'                => $res['charge']->id,
						'boleto_chargeStatus'            => $res['charge']->chargeStatus,
						'boleto_transaction_id'          => $res['charge']->transactions[0]->transactionId,
						'boleto_transaction_barcode'     => $res['charge']->transactions[0]->barcode,
						'boleto_transaction_digitalLine' => $res['charge']->transactions[0]->digitalLine,
						'boleto_transaction_amount'      => $res['charge']->transactions[0]->amount,
						'boleto_transaction_transactionStatus' => $res['charge']->transactions[0]->transactionStatus,
						'boleto_transaction_bankSlipUrl' => $res['charge']->transactions[0]->bankSlipUrl,
						'boleto_transaction_deadline'    => $res['charge']->transactions[0]->deadline,
						'boleto_environment'			 => $this->environment
					)
				);

				$order->save();

				$order->update_status( $this->initial_status, __( 'Aguardando o pagamento do boleto', 'wc-aditum-boleto' ) );

				// ! Remove cart
				$woocommerce->cart->empty_cart();

				// ! Return thankyou redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}
		} else {
			if ( $res != null ) {
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
