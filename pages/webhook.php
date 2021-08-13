<?php
/**
 * Aditum Gateway Payment WebHook
 * Description: Webhook Boleto
 *
 * @package Aditum/Payments
 */

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	if ( isset( $_GET['key'] ) && $_GET['key'] === WEBHOOK_KEY ) {

		$transaction = json_decode( file_get_contents( 'php://input' ) );

	}
}
