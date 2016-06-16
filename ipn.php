<?php
	define('PUN_ROOT', dirname(__FILE__).'/');
	require PUN_ROOT.'include/common.php';
	require PUN_ROOT.'include/paypal_conf.php';


	// Check if paypal request or response
	if (!isset($_POST["txn_id"]) && !isset($_POST["txn_type"])) {
		die();
	} else {
		$fn = "ipn_log.txt";
		$file = fopen($fn, "a");
		fwrite($file, 'RESPONSE FROM PAYPAL RECEIVED! PAYPAL LOG RECORDING...' ."\n");

		// Read the POST vars from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value);	// IPN fix
			$req .= "&$key=$value";
		}

		// Assign posted variables to local variables
		$data['item_name']			= $_POST['item_name'];
		$data['item_number'] 		= $_POST['item_number'];
		$data['payment_status'] 	= $_POST['payment_status'];
		$data['payment_amount'] 	= $_POST['mc_gross'];
		$data['payment_tax']		= $_POST['mc_fee'];
		$data['payment_total']		= ($_POST['mc_gross'] - $_POST['mc_fee']);
		$data['payment_currency']	= $_POST['mc_currency'];
		$data['txn_id']				= isset($_POST['txn_id']) ? $_POST['txn_id'] : 'INVALID';
		$data['receiver_email'] 	= $_POST['receiver_email'];
		$data['payer_name']			= ($_POST['first_name'] .' '. $_POST['last_name']);
		$data['payer_email'] 		= $_POST['payer_email'];
		$data['custom'] 			= $_POST['custom'];

		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Host: " .$paypal_url. "\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " .strlen($req). "\r\n";
		$header .= "Connection: Close\r\n\r\n";

		fwrite($file, 'TRANSACTION ID IS: ' .$data['txn_id']. "\n");

		$fp = fsockopen('ssl://' .$paypal_url, 443, $errno, $errstr, 30);

		if ($fp) {
			fwrite($file, 'CHECKING HEADER' ."\n");
			fputs($fp, $header . $req);

			$result = $db->query('SELECT group_id, username, email, registration_ip, donated FROM '.$db->prefix.'users WHERE id=\''.$data['custom'].'\'') or fwrite($file, 'FAILED TO FIND USER' ."\n");
			$donated_user = $db->fetch_assoc($result);

			while (!feof($fp)) {
				$res = fgets($fp, 1024);

				if (strcmp($res, "VERIFIED") == 0) {
					$totaldons = $data['payment_amount'];
					if ($donated_user['donated'] > 0)
						$totaldons = $totaldons + $donated_user['donated'];

					//$valid_txnid = check_txnid($data['txn_id']);
					$valid_txnid = true;

					if ($data['payment_status'] == 'Completed') {
						// PAYMENT VALIDATED & VERIFIED!
						if ($valid_txnid) {
							fwrite($file, 'VERIFIED HEADER, PAYMENT STATUS COMPLETE' ."\n");
							$db->query('INSERT INTO '.$db->prefix.'donations (transaction_id, name, email, amount, user_id) VALUES (\''.$db->escape($data['txn_id']).'\', \''.$db->escape($data['payer_name']).'\', \''.$db->escape($data['payer_email']).'\', '.$data['payment_amount'].', '.$data['custom'].')') or fwrite($file, 'FAILED TO IMPLEMENT TRANSACTION' ."\n");

							if (($totaldons >= $item_amount) && !in_array($data['custom'], $group_ids)) {
								$db->query('UPDATE '.$db->prefix.'users SET group_id=\'7\', donated=\''.$totaldons.'\' WHERE id=\''.$data['custom'].'\'') or fwrite($file, 'FAILED TO UPDATE USER' ."\n");
							} else {
								$db->query('UPDATE '.$db->prefix.'users SET donated=\''.$totaldons.'\' WHERE id=\''.$data['custom'].'\'') or fwrite($file, 'FAILED TO UPDATE USER 2' ."\n");
							}
						}
					} else if ($data["payment_status"] == "Refunded") {
						// PAYMENT REFUNDED!
						fwrite($file, 'PAYMENT REFUNDED, INVESTIGATE ISSUE MANUALLY' ."\n");

						$totaldons = $data['payment_amount'];
						if ($donated_user['donated'] > 0)
							$totaldons = $donated_user['donated'] + $totaldons;

						$db->query('UPDATE '.$db->prefix.'users SET group_id=\'4\', donated=\''.$totaldons.'\' WHERE id=\''.$data['custom'].'\'') or fwrite($file, 'FAILED TO UPDATE USER' ."\n");
					} else if ($data["payment_status"] == "Reversed") {
						// PAYMENT REVERSED!
						fwrite($file, 'PAYMENT CHARGEDBACK, INVESTIGATE ISSUE MANUALLY' ."\n");

						$totaldons = $data['payment_amount'];
						if ($donated_user['donated'] > 0)
							$totaldons = $donated_user['donated'] + $totaldons;

						$db->query('UPDATE '.$db->prefix.'users SET group_id=\'4\', donated=\''.$totaldons.'\' WHERE id=\''.$data['custom'].'\'') or fwrite($file, 'FAILED TO UPDATE USER' ."\n");
						$db->query('INSERT INTO '.$db->prefix.'bans (username, ip, email, message, ban_type) VALUES (\''.$donated_user['username'].'\', \''.$donated_user['registration_ip'].'\', \''.$donated_user['email'].'\', \'PayPal Reversal - Banned until you cancel the reversal\', \'1\')') or fwrite($file, 'FAILED TO INSERT REVERSAL BAN' ."\n");

						if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
							require PUN_ROOT.'include/cache.php';

						generate_bans_cache();
						generate_donations_cache();
					}
				} else if (strcmp($res, "INVALID") == 0) {
					fwrite($file, 'PAYMENT FAILED, INVESTIGATE ISSUE MANUALLY' ."\n");
					// PAYMENT INVALID & INVESTIGATE MANUALY!
				}
			}

			fclose($fp);
		}

		fwrite($file, 'RESPONSE FROM PAYPAL ENDED! PAYPAL LOG STOPPED...' ."\n\n");
		fclose($file);
	}
?>