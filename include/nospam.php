<?php

define('SPAM_NOT', 0);
define('SPAM_HONEYPOT', 1);
define('SPAM_BLACKLIST', 2);

//
// Check a given IP and email against the stopforumspam API
//
function stopforumspam_check($ip, $email, $username)
{
	global $pun_config;

	if ($pun_config['o_stopforumspam_check'] != '1')
		return false;

	$response = @simplexml_load_file('http://www.stopforumspam.com/api?'.http_build_query(array(
		'ip'		=> $ip,
		'email'		=> $email,
//		'username'	=> $username,	// I'm not sure checking by username is a good idea...
	)));
	if ($response === false)
		return false;

	foreach ($response->appears as $appears)
		if ($appears == 'yes')
			return true;

	return false;
}


//
// Report a spammer to stopforumspam database
//
function stopforumspam_report($ip, $email, $username)
{
	global $pun_config;

	// Do not report if there is no StopForumSpam API key
	if ($pun_config['o_stopforumspam_api'] == '')
		return false;

	// Do not report if the usernamefield is not hidden
	if (!usernamefield_is_hidden())
		return false;

	$context = stream_context_create(array('http' => array(
		'method'	=> 'POST',
		'header'	=> 'Content-type: application/x-www-form-urlencoded',
		'content'	=> http_build_query(array(
			'ip_addr'	=> $ip,
			'email'		=> $email,
			'username'	=> $username,
			'api_key'	=> $pun_config['o_stopforumspam_api'],
		)),
	)));

	return @file_get_contents('http://www.stopforumspam.com/add', false, $context) ? true : false;
}


//
// Check if usernamefield is hidden
// This function is partially based on code from Sumatra: CSS Editor (http://www.bewebmaster.com/228.php).
//
function usernamefield_is_hidden()
{
	global $pun_user;

	if (!file_exists(PUN_ROOT.'style/'.$pun_user['style'].'.css'))
		return false;

	$lines = file(PUN_ROOT.'style/'.$pun_user['style'].'.css');

	$cssstyles = '';

	foreach ($lines as $line_num => $line)
		$cssstyles .= trim($line);

	// Using strtok we remove the brackets around the css styles
	$tok = strtok($cssstyles, "{}");

	// Create another array in which we will store tokenized string
	$sarray = array();

	// Set counter
	$spos = 0;

	// Separate selectors from styles and store those values in $sarray
	while ($tok !== false)
	{
		$sarray[$spos] = $tok;
		$spos++;
		$tok = strtok("{}");
	}

	// To start we need to get the size of $sarray
	$size = count($sarray);

	// Create selectors and styles arrays
	$selectors = array();
	$sstyles = array();

	// Set counters
	$npos = 0;
	$sstl = 0;

	// Separate styles from selectors
	for($i = 0; $i<$size; $i++)
	{
		if ($i % 2 == 0)
		{
			$selectors[$npos] = $sarray[$i];
			$npos++;
		}
		else
		{
			$sstyles[$sstl] = $sarray[$i];
			$sstl++;
		}
	}

	// We are now able to access individual selectors and their styles using array keys

	$field_found = 0;

	foreach ($selectors as $key => $value)
	{
		if (contains('.pun .usernamefield', $value))
			$field_found = intval($key);
	}

	if ($field_found == 0)
		return false;

	if (contains('display: none', $sstyles[$field_found]) || contains('display:none', $sstyles[$field_found]))
		return true;
	else
		return false;
}


//
// Check if a string is contained in another string
//
function contains($str, $content, $ignorecase = true)
{
	if ($ignorecase)
	{
		$str = strtolower($str);
		$content = strtolower($content);
	}

	return (strpos($content, $str) !== false) ? true : false;
}
