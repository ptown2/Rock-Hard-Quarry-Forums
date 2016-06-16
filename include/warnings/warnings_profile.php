<?php
// Load the warnings.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/warnings.php';

// Does the user have active warnings?
$result = $db->query('SELECT 1 FROM '.$db->prefix.'warnings WHERE user_id='.$id.' AND (date_expire > '.time().' OR date_expire=0)') or error('Unable to fetch active warnings info', __FILE__, __LINE__, $db->error());
$has_active = ($db->num_rows($result)) ? 1 : 0;

if ($has_active)
{
	// Total points of active warnings
	$result = $db->query('SELECT SUM(points) FROM '.$db->prefix.'warnings WHERE user_id='.$id.' AND (date_expire > '.time().' OR date_expire=0)') or error('Unable to count active warnings', __FILE__, __LINE__, $db->error());
	$points_active = $db->result($result);
	$points_active = intval($points_active);

	$warning_level = '<strong>'.$lang_warnings['Warning level'].': '.$points_active.'</strong>';
}
else
{
	$points_active = 0;

	$warning_level = $lang_warnings['Warning level'].': '.$points_active;
}

if (($pun_user['g_id'] == PUN_ADMIN || ($pun_user['is_admmod'] && $pun_config['o_warnings_mod_add'] == '1')) && $pun_config['o_warnings_enabled'] == '1')
{
	echo "\t\t\t\t\t\t\t".'<p>'.$warning_level.' - <a href="warnings.php?view='.$id.'">'.$lang_warnings['Show all warnings'].'</a> - <a href="warnings.php?warn='.$id.'">'.$lang_warnings['Warn user'].'</a></p>';
}
else if (($pun_config['o_warnings_see_status'] == 'all' || $pun_user['is_admmod'] || ($pun_config['o_warnings_see_status'] == 'warned' && $has_active)) && $pun_config['o_warnings_enabled'] == '1')
{
	echo "\t\t\t\t\t\t\t".'<p>'.$warning_level.' - <a href="warnings.php?view='.$id.'">'.$lang_warnings['Show all warnings'].'</a></p>';
}

?>