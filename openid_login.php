<?php

$openid = isset($_GET['openid']) ? pun_trim($_GET['openid']) : null;

// Kill the OpenID Session just for Security Measures
session_start();
	$_SESSION['username'] = null;
	$_SESSION['email'] = null;
	$_SESSION['openid'] = null;
	$_SESSION['key'] = null;
session_destroy();

// Verify what OpenID System it is...
if (isset($openid) && !empty($openid)) {
	$openide = new LightOpenID($pun_config['o_base_url']);

	if ($openid == 'steam') {
		if (!$openide->mode) {
			$openide->identity = 'http://steamcommunity.com/openid';
			header('Location: ' . $openide->authUrl());
		} else {
			if ($openide->validate()) {
				$id = $openide->identity;

				$steam64 = str_replace('http://steamcommunity.com/openid/id/', '', $id);

				$api_key = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
				$json_object = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.$api_key.'&steamids='.$steam64);
				$steam_info = json_decode($json_object);

				session_start();
				$_SESSION['openid'] = $openid;
				$_SESSION['username'] = $steam_info->response->players[0]->personaname;
				$_SESSION['key'] = $steam64;
			}
		}
	} elseif ($openid == 'google') {
		if (!$openide->mode) {
			$openide->identity = 'https://www.google.com/accounts/o8/id';
			$openide->required = array(
				'namePerson/first',
				'contact/email',
			);

			header('Location: ' . $openide->authUrl());
		} else {
			if ($openide->validate()) {
				$id = $openide->identity;
				$ide = $openide->getAttributes();

				session_start();
				$_SESSION['openid'] = $openid;
				$_SESSION['username'] = $ide['namePerson/first'];
				$_SESSION['email'] = $ide['contact/email'];
				$_SESSION['key'] = pun_hash($ide['contact/email']);
			}
		}
	}
}