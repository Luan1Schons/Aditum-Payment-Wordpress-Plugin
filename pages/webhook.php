<?php
/**
 * Aditum Gateway Payment WebHook
 * Description: Webhoo Boleton
 *
 * @package Aditum/Payments
 */

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	if ( isset( $_GET['key'] ) && $_GET['key'] == WEBHOOK_KEY ) {

        $transaction = json_decode(file_get_contents('php://input'));

	}
}
