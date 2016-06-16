<?php
/***********************************************************************

  This software is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  This software is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
    exit;

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.0.1');

// Load the admin_users.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_users.php';

// Load the profile.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/profile.php';

// Load the Honeypot + SFS language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/honeypot_sfs_plugin.php'))
	require PUN_ROOT.'lang/'.$pun_user['language'].'/honeypot_sfs_plugin.php';
else
	require PUN_ROOT.'lang/English/honeypot_sfs_plugin.php';

if (isset($_POST['form_sent']))
{
	// Lazy referer check (in case base_url isn't correct)
	if (!preg_match('#/admin_loader\.php#i', $_SERVER['HTTP_REFERER']))
		message($lang_common['Bad referrer']);

	$form = array_map('trim', $_POST['form']);

	while (list($key, $input) = @each($form))
	{
		// Only update values that have changed
		if ((isset($pun_config['o_'.$key])) || ($pun_config['o_'.$key] == NULL))
		{
			if ($pun_config['o_'.$key] != $input)
			{
				if ($input != '' || is_int($input))
					$value = '\''.$db->escape($input).'\'';
				else
					$value = 'NULL';

				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}

	// Regenerate the config cache
	require_once PUN_ROOT.'include/cache.php';
	generate_config_cache();

	redirect('admin_loader.php?plugin=AP_Honeypot_SFS.php', $lang_honeypot_sfs_plugin['Options updated redirect']);
}
else if (isset($_POST['search_users']))
{
	// Display the admin navigation menu
?>
<div class="linkst">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)"><?php echo $lang_admin_common['Go back'] ?></a></div>
	</div>
</div>

<div id="users1" class="blocktable">
	<h2><span><?php echo $lang_admin_common['Users'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_common['Username'] ?></th>
					<th class="tc2" scope="col"><?php echo $lang_common['Email'] ?></th>
					<th class="tc3" scope="col"><?php echo $lang_common['Posts'] ?></th>
					<th class="tc4" scope="col"><?php echo $lang_profile['Website'] ?></th>
					<th class="tc5" scope="col"><?php echo $lang_profile['Signature'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_common['Registered'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php
$result = $db->query('SELECT * FROM '.$db->prefix.'users WHERE id > 1 AND num_posts=0 AND signature IS NOT NULL ORDER BY registered DESC LIMIT 50') or error('Unable to fetch users', __FILE__, __LINE__, $db->error());

// If there are users with URLs in their signatures but 0 posts
if ($db->num_rows($result))
{
	while ($cur_user = $db->fetch_assoc($result))
	{
		echo "\t\t\t\t\t\t".'<tr><td class="tcl"><a href="profile.php?id='.$cur_user['id'].'">'.pun_htmlspecialchars($cur_user['username']).'</a></td><td class="tc2">'.$cur_user['email'].'</td><td class="tc3">'.forum_number_format($cur_user['num_posts']).'</td><td class="tc4" style="word-wrap: break-word">'.pun_htmlspecialchars($cur_user['url']).'</td><td class="tc5" style="word-wrap: break-word">'.pun_htmlspecialchars($cur_user['signature']).'</td><td class="tcr">'.format_time($cur_user['registered'], true).'</td></tr>'."\n";
	}
}
	else
		echo "\t\t\t\t".'<tr><td class="tcl" colspan="6">'.$lang_admin_users['No match'].'</td></tr>'."\n";
?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="linksb">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)"><?php echo $lang_admin_common['Go back'] ?></a></div>
	</div>


<?php
}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);
?>
	<div class="block">
		<h2><span>Honeypot + StopForumSpam - v<?php echo PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_honeypot_sfs_plugin['Description'] ?></p>
			</div>
		</div>
	</div>
	<div class="blockform">
		<h2 class="block2"><span><?php echo $lang_honeypot_sfs_plugin['Options'] ?></span></h2>
		<div class="box">
			<form method="post" action="admin_loader.php?plugin=AP_Honeypot_SFS.php">
				<p class="submittop"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
				<div class="inform">
					<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend><?php echo $lang_honeypot_sfs_plugin['Settings'] ?></legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row"><?php echo $lang_honeypot_sfs_plugin['StopForumSpam check label'] ?></th>
								<td>
									<input type="radio" name="form[stopforumspam_check]" value="1"<?php if ($pun_config['o_stopforumspam_check'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo $lang_admin_common['Yes'] ?></strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[stopforumspam_check]" value="0"<?php if ($pun_config['o_stopforumspam_check'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong><?php echo $lang_admin_common['No'] ?></strong>
									<span><?php echo $lang_honeypot_sfs_plugin['StopForumSpam check help'] ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php echo $lang_honeypot_sfs_plugin['StopForumSpam API label'] ?></th>
								<td>
									<input type="text" name="form[stopforumspam_api]" size="20" maxlength="30" value="<?php echo $pun_config['o_stopforumspam_api'] ?>" />
									<span><?php echo $lang_honeypot_sfs_plugin['StopForumSpam API help'] ?></span>
								</td>
							</tr>
						</table>
						</div>
					</fieldset>
				</div>
			<p class="submitend"><input type="submit" name="save" value="<?php echo $lang_admin_common['Save changes'] ?>" /></p>
			</form>
		</div>
	</div>

	<div class="blockform block2">
		<h2><span><?php echo $lang_honeypot_sfs_plugin['Search users head'] ?></span></h2>
		<div class="box">
			<form method="post" action="admin_loader.php?plugin=AP_Honeypot_SFS.php">
				<div class="inbox">
					<p>
						<?php echo $lang_honeypot_sfs_plugin['Search users info'] ?>
					</p>
				</div>
				<p class="submitend">
					<input type="submit" name="search_users" value="<?php echo $lang_common['Go'] ?>" />
				</p>
			</form>
		</div>
	</div>
<?php
}
?>