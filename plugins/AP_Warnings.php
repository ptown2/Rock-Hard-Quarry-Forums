<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;


// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.0');

// Load the warnings mod functions script
require PUN_ROOT.'include/warnings/warning_functions.php';

// Load the warnings.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/warnings.php';


if (isset($_POST['save']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

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

				$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$key.'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
			}
		}
	}

	// Regenerate the config cache
	require_once PUN_ROOT.'include/cache.php';
	generate_config_cache();

	redirect('admin_loader.php?plugin=AP_Warnings.php', 'Options updated. Redirecting &hellip;');
}

//
// Warning types stuff
//


// Delete a warning type
else if (isset($_GET['del_type']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

	$id = intval($_GET['del_type']);
	if ($id < 1)
		message($lang_common['Bad request']);

	if (isset($_POST['del_type_comply']))	// Delete a warning type
	{
		// Delete the warning type
		$db->query('DELETE FROM '.$db->prefix.'warning_types WHERE id='.$id) or error('Unable to delete warning type', __FILE__, __LINE__, $db->error());

		redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning type deleted. Redirecting &hellip;');
	}
	else	// If the user hasn't confirmed the delete
	{
		$result = $db->query('SELECT title FROM '.$db->prefix.'warning_types WHERE id='.$id) or error('Unable to fetch warning type info', __FILE__, __LINE__, $db->error());
		$warning_type = pun_htmlspecialchars($db->result($result));

		generate_admin_menu('forums');

?>
	<div class="blockform">
		<h2><span>Confirm delete warning type</span></h2>
		<div class="box">
			<form method="post" action="admin_loader.php?plugin=AP_Warnings.php&amp;del_type=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend>Important! Read before deleting</legend>
						<div class="infldset">
							<p>Are you sure that you want to delete the warning type titled "<?php echo $warning_type ?>"?</p>
							<p>WARNING! Deleting a warning type will result in users who have been assigned this warning type not to be able to view the warning title and description (just points).</p>
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="del_type_comply" value="Delete" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

		require PUN_ROOT.'footer.php';
	}
}


// Handle adding a new warning type and editing a warning type
else if (isset($_POST['form_sent_type']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

	if(empty($_POST['warning_title']))
		message('You must enter a title for the warning type.');

	// Determine expiration time
	$expiration_time  = get_expiration_time($_POST['expiration_time'], $_POST['expiration_unit']);

	// Update warning type
	if(isset($_POST['the_id']))
	{
		$result = $db->query('SELECT id, title, description, points, expiration_time FROM '.$db->prefix.'warning_types WHERE id = '.intval($_POST['the_id'])) or error('Unable to fetch warnings info3', __FILE__, __LINE__, $db->error());

		if($db->num_rows($result) == 1)
		{
			$warning_type = $db->fetch_assoc($result);

			$db->query('UPDATE '.$db->prefix.'warning_types SET title = \''.$db->escape($_POST['warning_title']).'\', description =  \''.$db->escape($_POST['warning_description']).'\', points =  \''.intval($_POST['warning_points']).'\', expiration_time =  \''.$expiration_time.'\' WHERE id = '.$warning_type['id']) or error('Unable to update warning3', __FILE__, __LINE__, $db->error());
			$finalid = $warning_type['id'];

			redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning type updated.'.' Redirecting &hellip;');
		}
		else
			message('Unknown failure.');
	}
	else
	// Insert a new warning type
	{
		$db->query('INSERT INTO '.$db->prefix.'warning_types (title, description, points, expiration_time) VALUES(\''.$db->escape($_POST['warning_title']).'\', \''.$db->escape($_POST['warning_description']).'\', \''.intval($_POST['warning_points']).'\', \''.$expiration_time.'\')') or error('Unable to insert warning type', __FILE__, __LINE__, $db->error());
		$finalid = $db->insert_id();

		redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning type added.'.' Redirecting &hellip;');
	}
}


// Show the edit screen of a warning type
else if (isset($_GET['edit_type']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

// Get information of the warning
	$result = $db->query('SELECT id, title, description, points, expiration_time FROM '.$db->prefix.'warning_types WHERE id = '.intval($_GET['edit_type'])) or error('Unable to fetch warnings info5', __FILE__, __LINE__, $db->error());
	if($db->num_rows($result) == 0)
		message('Wrong ID.');

	generate_admin_menu($plugin);	
	$warning_type = $db->fetch_assoc($result);

	// Get expiration time and unit
	$expiration = explode(' ', format_expiration_time($warning_type['expiration_time']));
	if ($expiration[0] == 'Never')
	{
		$expiration[0] = '';
		$expiration[1] = 'Never';
	}

?>

	<div class="blockform">
		<h2><span>Edit warning type</span></h2>
		<div class="box">
			<form id="edit_type" method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=AP_Warnings.php"> 
				<p class="submittop"><input type="submit" name="update" value="Save changes" tabindex="6" /></p>
				<div class="inform">
					<fieldset>
						<legend>Enter warning type details</legend>
						<div class="infldset">
							<input type="hidden" name="form_sent_type" value="1" />
							<input type="hidden" name="the_id" value="<?php echo $warning_type['id']; ?>" />
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Warning title</th>
									<td><input type="text" name="warning_title" size="35" maxlength="120" value="<?php echo pun_htmlspecialchars($warning_type['title']); ?>" tabindex="1" /></td>
								</tr>
								<tr>
									<th scope="row">Points</th>
									<td><input type="text" name="warning_points" size="3" maxlength="3" value="<?php echo pun_htmlspecialchars($warning_type['points']); ?>" tabindex="2" /></td>
								</tr>
								<tr>
									<th scope="row">Expiration</th>
									<td><input type="text" name="expiration_time" size="3" maxlength="3" value="<?php echo pun_htmlspecialchars($expiration[0]); ?>" tabindex="3" />

										<select name="expiration_unit">
											<option value="hours"<?php if ($expiration[1] == 'hours') echo ' selected="selected"' ?>>Hours</option>
											<option value="days"<?php if ($expiration[1] == 'days') echo ' selected="selected"' ?>>Days</option>
											<option value="months"<?php if ($expiration[1] == 'months') echo ' selected="selected"' ?>>Months</option>
											<option value="never"<?php if ($expiration[1] == 'Never') echo ' selected="selected"' ?>>Never</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">Description (HTML)</th>
									<td><textarea name="warning_description" rows="3" cols="50" tabindex="4"><?php echo pun_htmlspecialchars($warning_type['description']) ?></textarea></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>

				<p class="submitend"><input type="submit" name="update" value="Save changes" /></p>
			</form>
		</div>
	</div>
<?php
}


// Show the add screen of a warning types
else if (isset($_POST['add_type']))
{
generate_admin_menu($plugin);
?>

	<div class="blockform">
		<h2><span>Add new warning type</span></h2>
		<div class="box">
			<form id="edit_type" method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=AP_Warnings.php"> 
				<p class="submittop"><input type="submit" name="add" value="Add New" /></p>
				<div class="inform">
					<fieldset>
						<legend>Enter warning type details</legend>
						<div class="infldset">
							<input type="hidden" name="form_sent_type" value="1" />
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Warning title</th>
									<td><input type="text" name="warning_title" size="35" maxlength="120" tabindex="1" /></td>
								</tr>
								<tr>
									<th scope="row">Points</th>
									<td><input type="text" name="warning_points" size="5" maxlength="5" tabindex="2" /></td>
								</tr>
								<tr>
									<th scope="row">Expiration</th>
									<td><input type="text" name="expiration_time" size="5" maxlength="5" value="10" tabindex="3" />

										<select name="expiration_unit">
											<option value="hours">Hours</option>
											<option value="days" selected="selected">Days</option>
											<option value="months">Months</option>
											<option value="never">Never</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">Description (HTML)</th>
									<td><textarea name="warning_description" rows="3" cols="50" tabindex="4"></textarea></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>

				<p class="submitend"><input type="submit" name="add" value="Add New" /></p>
			</form>
		</div>
	</div>

<?php
}

//
// Warning levels stuff
//


// Delete a warning level
else if (isset($_GET['del_level']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

	$id = intval($_GET['del_level']);
	if ($id < 1)
		message($lang_common['Bad request']);

	if (isset($_POST['del_level_comply']))	// Delete a warning level
	{
		// Delete the warning level
		$db->query('DELETE FROM '.$db->prefix.'warning_levels WHERE id='.$id) or error('Unable to delete warning level', __FILE__, __LINE__, $db->error());

		redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning level deleted. Redirecting &hellip;');
	}
	else	// If the user hasn't confirmed the delete
	{
		generate_admin_menu('forums');

?>
	<div class="blockform">
		<h2><span>Confirm delete warning level</span></h2>
		<div class="box">
			<form method="post" action="admin_loader.php?plugin=AP_Warnings.php&amp;del_level=<?php echo $id ?>">
				<div class="inform">
					<fieldset>
						<legend>Important! Read before deleting</legend>
						<div class="infldset">
							<p>Are you sure that you want to delete this warning level?</p>
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="del_level_comply" value="Delete" /> <a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

		require PUN_ROOT.'footer.php';
	}
}


// Handle adding a new warning level and editing a warning level
else if (isset($_POST['form_sent_level']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

	if(empty($_POST['warning_title']))
		message('You must enter a message for the warning level.');

	// Determine expiration time
	$expiration_time  = get_expiration_time($_POST['expiration_time'], $_POST['expiration_unit']);

	// Update warning level
	if(isset($_POST['the_id']))
	{
		$result = $db->query('SELECT id, points, method, message, period FROM '.$db->prefix.'warning_levels WHERE id = '.intval($_POST['the_id'])) or error('Unable to fetch warning level info', __FILE__, __LINE__, $db->error());

		if($db->num_rows($result) == 1)
		{
			$warning_level = $db->fetch_assoc($result);

			$db->query('UPDATE '.$db->prefix.'warning_levels SET points =  \''.intval($_POST['warning_points']).'\', method = \'ban\', message = \''.$db->escape($_POST['warning_title']).'\', period =  \''.$expiration_time.'\' WHERE id = '.$warning_level['id']) or error('Unable to update warning3', __FILE__, __LINE__, $db->error());
			$finalid = $warning_level['id'];

			redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning level updated.'.' Redirecting &hellip;');
		}
		else
			message('Unknown failure.');
	}
	else
	// Insert a new warning level
	{
		$db->query('INSERT INTO '.$db->prefix.'warning_levels (points, method, message, period) VALUES(\''.intval($_POST['warning_points']).'\', \'ban\', \''.$db->escape($_POST['warning_title']).'\', \''.$expiration_time.'\')') or error('Unable to insert warning level', __FILE__, __LINE__, $db->error());
		//$finalid = $db->insert_id();

		redirect('admin_loader.php?plugin=AP_Warnings.php', 'Warning level added.'.' Redirecting &hellip;');
	}
}


// Show the edit screen of a warning level
else if (isset($_GET['edit_level']))
{
	confirm_referrer('admin_loader.php?plugin=AP_Warnings.php');

// Get information of the warning
	$result = $db->query('SELECT id, points, message, period FROM '.$db->prefix.'warning_levels WHERE id = '.intval($_GET['edit_level'])) or error('Unable to fetch warnings info5', __FILE__, __LINE__, $db->error());
	if($db->num_rows($result) == 0)
		message('Wrong ID.');

	generate_admin_menu($plugin);	
	$warning_level = $db->fetch_assoc($result);

	// Get expiration time and unit
	$expiration = explode(' ', format_expiration_time($warning_level['period']));
	if ($expiration[0] == 'Never')
	{
		$expiration[0] = '';
		$expiration[1] = 'Never';
	}

?>

	<div class="blockform">
		<h2><span>Edit warning level</span></h2>
		<div class="box">
			<form id="edit_level" method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=AP_Warnings.php"> 
				<p class="submittop"><input type="submit" name="update" value="Save changes" tabindex="6" /></p>
				<div class="inform">
					<fieldset>
						<legend>Enter warning level details</legend>
						<div class="infldset">
							<input type="hidden" name="form_sent_level" value="1" />
							<input type="hidden" name="the_id" value="<?php echo $warning_level['id']; ?>" />
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Ban message</th>
									<td>
										<input type="text" name="warning_title" size="50" maxlength="255" value="<?php echo pun_htmlspecialchars($warning_level['message']); ?>" tabindex="1" />
										<span>A message that will be displayed to the banned user when he/she visits the forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Points</th>
									<td>
										<input type="text" name="warning_points" size="3" maxlength="3" value="<?php echo pun_htmlspecialchars($warning_level['points']); ?>" tabindex="2" />
										<span>Number of warning points a user must have for this ban to be applied.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Ban period</th>
									<td><input type="text" name="expiration_time" size="3" maxlength="3" value="<?php echo pun_htmlspecialchars($expiration[0]); ?>" tabindex="3" />

										<select name="expiration_unit">
											<option value="hours"<?php if ($expiration[1] == 'hours') echo ' selected="selected"' ?>>Hours</option>
											<option value="days"<?php if ($expiration[1] == 'days') echo ' selected="selected"' ?>>Days</option>
											<option value="months"<?php if ($expiration[1] == 'months') echo ' selected="selected"' ?>>Months</option>
											<option value="never"<?php if ($expiration[1] == 'Never') echo ' selected="selected"' ?>>Permanent</option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>

				<p class="submitend"><input type="submit" name="update" value="Save changes" /></p>
			</form>
		</div>
	</div>
<?php
}


// Show the add screen of a warning levels
else if (isset($_POST['add_level']))
{
generate_admin_menu($plugin);
?>

	<div class="blockform">
		<h2><span>Add new warning levels</span></h2>
		<div class="box">
			<form id="edit_type" method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=AP_Warnings.php"> 
				<p class="submittop"><input type="submit" name="add" value="Add New" /></p>
				<div class="inform">
					<fieldset>
						<legend>Enter warning level details</legend>
						<div class="infldset">
							<input type="hidden" name="form_sent_level" value="1" />
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Ban message</th>
									<td>
										<input type="text" name="warning_title" size="50" maxlength="255" tabindex="1" />
										<span>A message that will be displayed to the banned user when he/she visits the forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Points</th>
									<td>
										<input type="text" name="warning_points" size="5" maxlength="5" tabindex="2" />
										<span>Number of warning points a user must have for this ban to be applied.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Ban duration</th>
									<td><input type="text" name="expiration_time" size="5" maxlength="5" value="10" tabindex="3" />

										<select name="expiration_unit">
											<option value="hours">Hours</option>
											<option value="days" selected="selected">Days</option>
											<option value="months">Months</option>
											<option value="never">Permanent</option>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>

				<p class="submitend"><input type="submit" name="add" value="Add New" /></p>
			</form>
		</div>
	</div>

<?php
}


else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

?>
<script type="text/javascript"> 
function confirmdelete() { 
 return confirm("<?php echo 'Are you sure you want to delete?' ?>");   
}
</script>

	<div class="block">
		<h2><span>Auto Warnings - v<?php echo PLUGIN_VERSION ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p>Here you can modify the warning system settings.</p>
			</div>
		</div>
	</div>

	<div class="blockform">
		<h2 class="block2"><span>Options</span></h2>
		<div class="box">
			<form id="example" method="post" action="<?php  echo  $_SERVER['REQUEST_URI'] ?>">
				<p class="submittop"><input type="submit" name="save" value="Save changes" /></p>
				<div class="inform">
					<fieldset>
						<legend>Settings</legend>
						<div class="infldset">
						<table class="aligntop" cellspacing="0">
							<tr>
								<th scope="row">Enable warnings mod</th>
								<td>
									<input type="radio" name="form[warnings_enabled]" value="1"<?php if ($pun_config['o_warnings_enabled'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[warnings_enabled]" value="0"<?php if ($pun_config['o_warnings_enabled'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
									<span>If no, all warning mod functions will be disabled.</span>
								</td>
							</tr>
							<tr>
								<th scope="row">Custom warnings</th>
								<td>
									<input type="radio" name="form[warnings_custom]" value="1"<?php if ($pun_config['o_warnings_custom'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[warnings_custom]" value="0"<?php if ($pun_config['o_warnings_custom'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
									<span>If enabled, it will be possible to issue custom warnings.</span>
								</td>
							</tr>
							<tr>
								<th scope="row">Users who can see warning status</th>
									<td>
										<select name="form[warnings_see_status]">
											<option value="mods"<?php if ($pun_config['o_warnings_see_status'] == 'mods') echo ' selected="selected"' ?>>Moderators only</option>
											<option value="warned"<?php if ($pun_config['o_warnings_see_status'] == 'warned') echo ' selected="selected"' ?>>Moderators and warned users</option>
											<option value="all"<?php if ($pun_config['o_warnings_see_status'] == 'all') echo ' selected="selected"' ?>>All users</option>
										</select>
										<span>Determines who can see the warning level of users on the forum.</span>
								</td>
							</tr>


						</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Moderators</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Add warnings</th>
									<td>
										<input type="radio" name="form[warnings_mod_add]" value="1"<?php if ($pun_config['o_warnings_mod_add'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[warnings_mod_add]" value="0"<?php if ($pun_config['o_warnings_mod_add'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										<span>Allow moderators to issue new warnings to users.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Delete warnings</th>
									<td>
										<input type="radio" name="form[warnings_mod_remove]" value="1"<?php if ($pun_config['o_warnings_mod_remove'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Yes</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[warnings_mod_remove]" value="0"<?php if ($pun_config['o_warnings_mod_remove'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>No</strong>
										<span>Allow moderators to delete warnings already issued to users.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="save" value="Save changes" /></p>
			</form>
		</div>
	</div>

	<div class="blockform block2">
		<h2 class="block2"><span>Warning types</span></h2>
		<div class="box">
			<form id="list_types" method="post" action="admin_loader.php?plugin=AP_Warnings.php">
				<div class="inform">
					<fieldset>
						<legend>Add/edit/delete warning types</legend>

						<div class="infldset">
							<table cellspacing="0">
<?php				
$result = $db->query('SELECT id, title, description, points, expiration_time FROM '.$db->prefix.'warning_types ORDER BY points, id') or error('Unable to fetch warning types', __FILE__, __LINE__, $db->error());
while ($list_types = $db->fetch_assoc($result))
{
	$expiration = explode(' ', format_expiration_time($list_types['expiration_time']));
	if ($expiration[0] == 'Never')
	{
		$expiration[0] = '';
		$expiration[1] = 'Never';
	}

	
?>
								<tr>
									<th><a href="admin_loader.php?plugin=AP_Warnings.php&amp;edit_type=<?php echo $list_types['id'] ?>">Edit</a> - <a href="admin_loader.php?plugin=AP_Warnings.php&amp;del_type=<?php echo $list_types['id'] ?>">Delete</a></th>
									<td>Points:&nbsp;&nbsp;<?php echo $list_types['points'] ?>
									&nbsp;&nbsp;<strong><?php echo pun_htmlspecialchars($list_types['title']) ?></strong>&nbsp;&nbsp;(Expires after: <?php echo $expiration[0].' '.$expiration[1] ?>)</td>
								</tr>

<?php	
}
?>
							</table>
							
							<div class="fsetsubmit"><input type="submit" name="add_type" value="Add" tabindex="4" /></div>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>



	<div class="blockform block2">
		<h2 class="block2"><span>Warning levels</span></h2>
		<div class="box">
			<form id="list_levels" method="post" action="admin_loader.php?plugin=AP_Warnings.php">
				<div class="inform">
					<fieldset>
						<legend>Add/edit/delete warning levels</legend>

						<div class="infldset">
							<table cellspacing="0">
<?php				
$result = $db->query('SELECT id, points, method, period FROM '.$db->prefix.'warning_levels ORDER BY points, id') or error('Unable to fetch warning levels', __FILE__, __LINE__, $db->error());
while ($list_levels = $db->fetch_assoc($result))
{
	if ($list_levels['period'] == '0')
		$ban_title = 'Ban user permanently';
	else
	{
		$expiration = explode(' ', format_expiration_time($list_levels['period']));
		if ($expiration[0] == 'Never')
		{
			$expiration[0] = '';
			$expiration[1] = 'Never';
		}
		$ban_title = 'Ban user for '.$expiration[0].' '.$expiration[1];
	}
?>
								<tr>
									<th><a href="admin_loader.php?plugin=AP_Warnings.php&amp;edit_level=<?php echo $list_levels['id'] ?>">Edit</a> - <a href="admin_loader.php?plugin=AP_Warnings.php&amp;del_level=<?php echo $list_levels['id'] ?>">Delete</a></th>
									<td>Points:&nbsp;&nbsp;<?php echo $list_levels['points'] ?>
									&nbsp;&nbsp;<strong><?php echo $ban_title ?></strong></td>
								</tr>

<?php	
}
?>
							</table>
							
							<div class="fsetsubmit"><input type="submit" name="add_level" value="Add" tabindex="4" /></div>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>

<?php

}
