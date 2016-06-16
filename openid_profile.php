<?php
// Check the OpenID but for Steam
if (($openid == 'steam' && $pun_user['id'] == $id) && empty($pun_user['steam64'])) {
	$openide = new LightOpenID( $pun_config['o_base_url'] );

	if (!$openide->mode) {
		$openide->identity = 'http://steamcommunity.com/openid';
		header('Location: ' . $openide->authUrl());
	} else {
		if ($openide->validate()) {
			$stid = $openide->identity;
			$steam64 = str_replace('http://steamcommunity.com/openid/id/', '', $stid);

			check_openid($openid, $steam64);

			if (empty($errors)) {
				$db->query('UPDATE '.$db->prefix.'users SET steam64=\''. $steam64 .'\' WHERE id='.$pun_user['id']) or error('Unable to link RHQ Account to your Steam Account', __FILE__, __LINE__, $db->error());
				redirect('profile.php?id='.$id, 'Account successfully linked with Steam Account.');
			} else {
				message($errors[0]);
			}
		}
	}
}

// Check the OpenID but for Google
if (($openid == 'google' && $pun_user['id'] == $id) && empty($pun_user['google_id'])) {
	$openide = new LightOpenID($pun_config['o_base_url']);

	if (!$openide->mode) {
		$openide->identity = 'https://www.google.com/accounts/o8/id';
		$openide->required = array(
			'contact/email',
		);

		header('Location: ' . $openide->authUrl());
	} else {
		if ($openide->validate()) {
			$id = $openide->identity;
			$ide = $openide->getAttributes();
			$emailhash = pun_hash($ide['contact/email']);

			check_openid($openid, $emailhash);

			if (empty($errors)) {
				$db->query('UPDATE '.$db->prefix.'users SET google_id=\''. $emailhash .'\' WHERE id='.$pun_user['id']) or error('Unable to link RHQ Account to your Google Account', __FILE__, __LINE__, $db->error());
				redirect('profile.php?id='.$id, 'Account successfully linked with Google Account.');
			} else {
				message($errors[0]);
			}
		}
	}
}
?>