<?php
	// Define the Donation System
	define('PPS_MODE', 0);


	// PayPal settings
	$paypal_email = 'xxxxxxxxxxxxxx.com';
	$return_url = $pun_config['o_base_url']. '/donate.php?action=success';
	$cancel_url = $pun_config['o_base_url']. '/donate.php?action=cancel';
	$notify_url = $pun_config['o_base_url']. '/ipn.php';

	$item_name = 'RHQ Donations';
	$item_amount = 10;
	$item_number = "RHQ1001";

	$group_ids = array('1', '2', '3', '5', '6', '7');


	// Handle CSRF for PayPal Requests
	if (stripos($_SERVER["REQUEST_URI"], '/paypal') === false) {
		$config['csrf_protection'] = true;
	} else {
		$config['csrf_protection'] = false;
	}


	// Handle PayPal URL
	$paypal_url = (PPS_MODE == 0) ? "www.paypal.com" : "www.sandbox.paypal.com";
?>