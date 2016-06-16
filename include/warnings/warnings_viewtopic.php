<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load the warnings.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/warnings.php';

if (($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_add'] == '1')) && $pun_config['o_warnings_enabled'] == '1')
	$post_actions[] = '<li class="postreport"><span><a href="warnings.php?warn='.$cur_post['poster_id'].'&amp;pid='.$cur_post['id'].'">'.$lang_warnings['Warn'].'</a></span></li>';

?>
