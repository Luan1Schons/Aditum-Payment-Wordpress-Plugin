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

add_action( 'woocommerce_api_aditum', 'webhook' );

/**
 * Webhook function
 */
function webhook() { 

	$key = $_GET['key'];
	if( $key == WEBHOOK_KEY && !empty( $_GET['order_id'] ) ){
		$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
		$order = wc_get_order( $order_id );
		$order->payment_complete();
		wc_reduce_stock_levels($order_id);
	}

}

add_action( 'wp_enqueue_scripts', 'aditum_enqueue_dependencies' );
/**
 * Enqueue a script with jQuery as a dependency.
 */
function aditum_enqueue_dependencies() {
	//wp_enqueue_style( 'aditum-style', plugins_url() . '/aditum-payment-gateway/assets/css/bootstrap.min.css' );
	wp_enqueue_style( 'aditum-style', plugins_url() . '/aditum-payment-gateway/assets/css/style.css' );
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

	remove_menu_page('webhook_boleto_aditum');

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

// add the action 
add_action( 'woocommerce_view_order', 'action_woocommerce_view_order', 10, 2 ); 
// define the woocommerce_view_order callback 
function action_woocommerce_view_order( $order_id) { 

    $order = new WC_Order( $order_id );

	if ( $order->get_payment_method() === 'aditum_boleto' ) {
		$boleto = new WC_Aditum_Boleto_Pay_Gateway();

		$date_order_expiry = date('Y-m-d H:i:s', strtotime($order->get_date_created(). "+ $boleto->expiry_date days"));

		if(strtotime($date_order_expiry) < date('Y-m-d H:i:s')){
			echo '<div class="woocommerce-error"><b>Pedido Expirado</b> Não é possível mais visualizar este pedido.	</div>';
		}
	}else if ( $order->get_payment_method() === 'aditum_card' ) {
		$card = new WC_Aditum_Card_Pay_Gateway();
		
		$date_order_expiry = date('Y-m-d H:i:s', strtotime($order->get_date_created(). "+ $card->expiry_date days"));

		if(strtotime($date_order_expiry) < date('Y-m-d H:i:s')){
			echo '<div class="woocommerce-error"><b>Pedido Expirado</b> Não é possível mais visualizar este pedido.	</div>';
		}

	}
}; 
         

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
			echo '<div style="text-align: center">';
			if($boleto_data['boleto_environment'] === 'sandbox')
			{
				echo '<a href="https://payment-dev.aditum.com.br' . $boleto_data['boleto_transaction_bankSlipUrl'] . '" class="button button-primary download-boleto" >Clique aqui para baixar o boleto</a>';
			}else{
				echo '<a href="https://payment.aditum.com.br' . $boleto_data['boleto_transaction_bankSlipUrl'] . '" class="button button-primary download-boleto">Clique aqui para baixar o boleto</a>';
			}
			echo '</div>';
		}
	}else if ( $order->get_payment_method() === 'aditum_card' )
	{
		$card_data = $order->get_meta( '_params_aditum_card' );

		if ( ! empty( $card_data ) ) {
			if($card_data['card_transaction_transactionStatus'] === 'PreAuthorized'){
				echo '<div class="woocommerce-info"><b>Pagamento Pré-Autorizado</b> recebemos o seu pedido mas o seu pagamento ainda não foi totalmente aprovado, assim que a compra for totalmente aprovada te notificaremos por e-mail.	</div>';
			}else if($card_data['card_transaction_transactionStatus'] === 'Captured'){
				echo '<div class="woocommerce-message"><b>Pagamento Feito!</b> recebemos o seu pagamento com sucesso.</div>';
			}
		}
	}
}

add_action( 'woocommerce_checkout_process', 'bt_add_checkout_checkbox_warning' );
/**
 * Alert if checkbox not checked
 */ 
function bt_add_checkout_checkbox_warning($order_id) {
	$order = new WC_Order( $order_id );

    if ( ! (int) isset( $_POST['aditum_checkbox'] ) ) {
        wc_add_notice( __( 'Porfavor aceite os termos & condições.' ), 'error' );
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
		//global $woocommerce;

		//$cart_total_price = wc_prices_include_tax() ? WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() : WC()->cart->get_cart_contents_total();
		
		$total = WC()->cart->get_total(null);

		$installment_options = [];
		$card = new WC_Aditum_Card_Pay_Gateway();
		$installment_count = $card->max_installment;
		
		
		for($i = 1; $i <= $installment_count;$i++){
			$installment_total = $total/$i;
			$installment_plural = ($i > 1 ? 'Parcelas' : 'Parcela');
			$installment_options[$i] = $i.' '.$installment_plural.' de R$'.number_format($installment_total, 2);
		}
		

		ob_start(); // ! Start buffering

		echo '<div  class="aditum-card-fields" style="padding:10px 0;">';

		woocommerce_form_field(
			'card_holder_name',
			array(
				'type'     => 'text',
				'label'    => __( 'Nome do Titular do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'card_holder_document',
			array(
				'type'     => 'text',
				'label'    => __( 'CPF/CNPJ do Titular', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_number',
			array(
				'type'     => 'text',
				'label'    => __( 'Número do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'required' => true,
			),
			''
		);

		echo '<span id="card-brand"></span>';

		woocommerce_form_field(
			'aditum_card_cvv',
			array(
				'type'     => 'text',
				'label'    => __( 'Código de segurança CVV', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'input_class'   => array('card_cvv'),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_expiration_month',
			array(
				'type'     => 'number',
				'label'    => __( 'Mês Expiração', 'woocommerce' ),
				'class'    => array( 'form-row form-row-first' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_year_month',
			array(
				'type'     => 'number',
				'label'    => __( 'Ano Expiração', 'woocommerce' ),
				'class'    => array( 'form-row form-row-last' ),
				'required' => true,
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_installment',
			array(
				'type'     => 'select',
				'options'  => $installment_options,
				'label'    => __( 'Quantidade de parcelas', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide installment_aditum_card' ),
				'required' => true,
			),
			''
		);


		woocommerce_form_field( 'aditum_checkbox', array( // CSS ID
			'type'          => 'checkbox',
			'class'         => array('form-row form-row-wide mycheckbox'), // CSS Class
			'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required'      => true, // Mandatory or Optional
			'label'         => '<a href="https://drive.google.com/file/d/1bWsmDz9AMe9pNETRCbGk-zl2AOWNpt3a/view?usp=sharing" target="_blank" rel="noopener">TERMOS & CONDIÇÕES</a>', // Label and Link
		 ));    

		 echo '</div>';

		$description .= ob_get_clean(); // ! Append buffered content
		}else if( 'aditum_boleto' === $payment_id ){

		ob_start(); // ! Start buffering

		echo '<div  class="aditum-boleto-fields" style="padding:10px 0;">';

		woocommerce_form_field( 'aditum_checkbox', array( // CSS ID
			'type'          => 'checkbox',
			'class'         => array('form-row mycheckbox'), // CSS Class
			'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required'      => true, // Mandatory or Optional
			'label'         => '<a href="https://drive.google.com/file/d/1bWsmDz9AMe9pNETRCbGk-zl2AOWNpt3a/view?usp=sharing" target="_blank" rel="noopener">TERMOS & CONDIÇÕES</a>', // Label and Link
		 ));    

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
