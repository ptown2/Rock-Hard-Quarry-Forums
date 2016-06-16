<?php

/**
 *    Mod title: Bumpsage
 *         File: /include/mods/bumpsage_post.php
 *  Description: Included from /post.php
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load the mod_bumpsage.php language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/mod_bumpsage.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/mod_bumpsage.php';
else
	require PUN_ROOT.'lang/English/mod_bumpsage.php';

$sage = false;

// If in one of those case, bye bye
if(!isset($lang_bumpsage) || !empty($errors) || isset($_POST['preview']))
    return 1;

// If user can sage
if (stripos($message, $lang_bumpsage['sage'])===0)
{
    if(!$pun_user['g_sage_replies'])
    {
        $errors[] = sprintf($lang_bumpsage['not allowed'], pun_htmlspecialchars($lang_bumpsage['sage']));
    }
    else
    {
        $message = $lang_bumpsage['sage result'].substr($message, strlen($lang_bumpsage['sage']));
        $sage = true;
    }
}
