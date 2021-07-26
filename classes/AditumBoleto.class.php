<?php
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
	 * Function Plugin constructor
	 */
	public function __construct() {
		$this->id                 = 'aditum_boleto';
		$this->icon               = apply_filters( 'woocommerce_aditum_boleto_icon', plugins_url() . '/../plugins/aditum-boleto-gateway/assets/icon.png' );
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
		$wc_address     = WC()->countries->get_address_fields( $country = '', $type = '_billing_' );
		foreach ( $wc_address as $key => $address ) {
			$inputs_address[ $key ] = $key;
		}
		$this->form_fields = apply_filters(
			'woo_aditum_boleto_pay_fields',
			array(
				'enabled'                       => array(
					'title'   => __( 'Habilitar/Desabilitar', 'wc-aditum-boleto' ),
					'type'    => 'checkbox',
					'label'   => __( 'Habilitar ou desabilitar o Módulo de Pagamento', 'wc-aditum-boleto' ),
					'default' => 'no',
				),
				'aditum_boleto_environment'     => array(
					'title'   => __( 'Ambiente do Gateway', 'wc-aditum-boleto' ),
					'type'    => 'select',
					'options' => array(
						'production' => __( 'Produção', 'wc-aditum-boleto' ),
						'sandbox'    => __( 'Sandbox', 'wc-aditum-boleto' ),
					),
				),
				'title'                         => array(
					'title'       => __( 'Título do Gateway', 'wc-aditum-boleto' ),
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
				'aditum_boleto_deadline_boleto' => array(
					'title'       => __( 'Tempo de expiração do boleto (Dias)', 'wc-aditum-boleto' ),
					'type'        => 'number',
					'description' => __( 'Tempo de expiração do boleto.', 'wc-aditum-boleto' ),
					'default'     => __( '2', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_initial_status'  => array(
					'title'       => __( 'Status do Pedido criado', 'wc-aditum-boleto' ),
					'type'        => 'select',
					'options'     => wc_get_order_statuses(),
					'description' => __( 'Status do pedido criado.', 'wc-aditum-boleto' ),
					'default'     => __( '2', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_cnpj'            => array(
					'title'       => __( 'CNPJ Do aditum:', 'wc-aditum-boleto' ),
					'type'        => 'text',
					'description' => __( 'Insira o CNPJ cadastrado no Aditum.', 'wc-aditum-boleto' ),
					'default'     => __( '', 'wc-aditum-boleto' ),
					'desc_tip'    => true,
				),
				'aditum_boleto_merchantKey'     => array(
					'title'       => __( 'Merchant Key Do aditum:', 'wc-aditum-boleto' ),
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

		$order->update_status( $this->initial_status, __( 'Aguardando o pagamento do boleto', 'wc-aditum-boleto' ) );

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
