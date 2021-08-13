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

	$logger = wc_get_logger();

	$input = file_get_contents('php://input');

	if(empty($input)){ 
		$input = $_POST;
	}

	$logger->info( $input, array( 'source' => 'failed-orders' ) );
	
	$key =  isset($_GET['key']) ? $_GET['key']: null;
	if( $key == WEBHOOK_KEY && $key !== null)
	{	

				$input = json_decode(file_get_contents('php://input'), true);

				$order_id = isset( $input['ChargeId'] ) ? $input['ChargeId']: null;
				$order = wc_get_order( $order_id );

				if( isset( $order ) ){

					if( 1 == $input['ChargeStatus'] )
					{
						
						$order->payment_complete();
						wc_reduce_stock_levels( $order_id );

					}else if( 2 == $input['ChargeStatus'] ){

						$order->update_status( 'pending', __( 'Pagamento pre-autorizado.', 'wc-aditum-card' ) );
						
					}else{ 

						$order->update_status( 'cancelled', __( 'Pagamento cancelado.', 'wc-aditum-card' ) );

					}
				}else{
					// LOG THE FAILED ORDER TO CUSTOM "failed-orders" LOG
				    $logger->info( 'O pedido com o ID: '.$input['ChargeId'].' Não foi encontrado ', array( 'source' => 'failed-orders' ) );
				}
	}

	exit();

}

add_action( 'wp_enqueue_scripts', 'aditum_enqueue_dependencies' );
/**
 * Enqueue a script with jQuery as a dependency.
 */
function aditum_enqueue_dependencies() {
	//wp_enqueue_style( 'aditum-style', plugins_url() . '/aditum-payment-gateway/assets/css/bootstrap.min.css' );
	wp_enqueue_style( 'aditum-style', plugins_url() . '/aditum-payment-gateway/assets/css/style.css', [], time() );
	wp_enqueue_script( 'jquerymask', plugins_url() . '/aditum-payment-gateway/assets/js/jquery.mask.js', array( 'jquery' ), time(), false );
	wp_enqueue_script( 'main-scripts', plugins_url() . '/aditum-payment-gateway/assets/js/app.js', array(), time(), false );
	wp_add_inline_script( 'main-scripts', "window.antifraude_id = '".get_option('aditum_antifraude_id')."'" );
	wp_add_inline_script( 'main-scripts', "window.antifraude_type = '".get_option('aditum_antifraude_type')."'" );
	wp_enqueue_script( 'antifraude', plugins_url() . '/aditum-payment-gateway/assets/js/antifraud.js', array('main-scripts'), time(), false );
	
}


add_filter('woocommerce_checkout_fields', 'custom_billing_fields', 1000, 1);
/**
 * Set billing_neighborhood to required
 */
function custom_billing_fields( $fields ) {
    $fields['billing']['billing_neighborhood']['required'] = true;

    return $fields;
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

		$installment_options = ['' => 'Selecione a quantidade de parcelas'];
		$card = new WC_Aditum_Card_Pay_Gateway();
		$installment_count = $card->max_installments ? $card->max_installments : 20;
		
		
		for($i = 1; $i <= $installment_count;$i++){
			$installment_total = $total/$i;
			$installment_plural = ($i > 1 ? 'Parcelas' : 'Parcela');
			if($installment_total < $card->min_installments_amount) {
				continue;
			}
			$installment_options[$i] = $i.' '.$installment_plural.' de R$'.number_format($installment_total, 2, ',', '.');
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
				'label'    => __( 'CPF', 'woocommerce' ),
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
			'aditum_card_expiration_month',
			array(
				'type'     => 'text',
				'label'    => __( 'Data de validade', 'woocommerce' ),
				'class'    => array( 'form-row form-row-first' ),
				'required' => true,
				'placeholder' => 'MM',
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_year_month',
			array(
				'type'     => 'text',
				'label'    => __( 'Ano Expiração', 'woocommerce' ),
				'class'    => array( 'form-row form-row-last' ),
				'required' => true,
				'placeholder' => 'YY',
			),
			''
		);

		woocommerce_form_field(
			'aditum_card_cvv',
			array(
				'type'     => 'text',
				'label'    => __( 'Número de Verificação do Cartão', 'woocommerce' ),
				'class'    => array( 'form-row form-row-wide' ),
				'input_class'   => array('card_cvv'),
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
			'label'         => '<a href="'.plugin_dir_url(__FILE__).'assets/Termos-de-Uso-Portal-Aditum-V3-20210512.pdf" target="_blank" rel="noopener">TERMOS & CONDIÇÕES</a>', // Label and Link
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
			'label'         => '<a href="'.plugin_dir_url(__FILE__).'assets/Termos-de-Uso-Portal-Aditum-V3-20210512.pdf" target="_blank" rel="noopener">TERMOS & CONDIÇÕES</a>', // Label and Link
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

add_action( 'woocommerce_after_order_notes', function(){
	echo '<input type="hidden" class="input-hidden" name="antifraud_token" id="antifraud_token" />';
} );

add_filter( 'woocommerce_settings_tabs_array', function($settings_tabs){
	$settings_tabs['settings_tab_aditum_antifraude'] = __( 'Antifraude', 'woocommerce-settings-tab-aditum-antifraude' );
    return $settings_tabs;
}, 50 );

function get_aditum_antifraude_settings() {
	$settings = array(
        'section_title' => array(
            'name'     => __( 'Antifraude', 'woocommerce-settings-tab-aditum-antifraude' ),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'wc_settings_tab_aditum_antifraude_section_title'
        ),
		'aditum_antifraude_type' => array(
			'title'   => __( 'Tipo de Antifraude:', 'wc-aditum' ),
			'type'    => 'select',
			'options' => ['konduto' => 'Konduto', 'clearsale' => 'Clear Sale'],
			'id'   => 'aditum_antifraude_type'
		),
		'aditum_antifraude_id'   => array(
			'title'       => __( 'Token:', 'wc-aditum' ),
			'type'        => 'text',
			'description' => __( 'Token.', 'wc-aditum' ),
			'desc_tip'    => true,
			'id'   => 'aditum_antifraude_id'
		),
        'section_end' => array(
             'type' => 'sectionend',
             'id' => 'wc_settings_settings_tab_aditum_antifraude_section_end'
        )
	);
	return apply_filters( 'wc_settings_settings_tab_aditum_antifraude_settings', $settings );
}

add_action( 'woocommerce_settings_tabs_settings_tab_aditum_antifraude', function(){
	woocommerce_admin_fields(get_aditum_antifraude_settings());
});

add_action( 'woocommerce_update_options_settings_tab_aditum_antifraude', function () {
    woocommerce_update_options( get_aditum_antifraude_settings() );
} );
