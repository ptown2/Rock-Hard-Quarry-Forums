<?php
// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;
	
// Load the smilies language files
require PUN_ROOT.'include/parser.php';

require PUN_ROOT.'lang/'.$pun_user['language'].'/smilies.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);

$action = isset($_GET['action']) ? pun_trim($_GET['action']) : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// If you're saving the game
if ($action == 'add') {
	if (isset($_POST['form_sent'])) {
		confirm_referrer('admin_loader.php');

		// Clean up everything that requires entries and such...
		$name = pun_trim($_POST['req_name']);
		$description = pun_linebreaks(pun_trim($_POST['req_description']));
		$category = isset($_POST['req_category']) ? pun_trim($_POST['req_category']) : 1;
		$filetype = isset($_POST['req_filetype']) ? pun_trim($_POST['req_filetype']) : 'flash';
		$width = isset($_POST['width']) ? pun_trim($_POST['width']) : 800;
		$height = isset($_POST['height']) ? pun_trim($_POST['height']) : 600;
		$ratio = isset($_POST['ratio']) ? pun_trim($_POST['ratio']) : 250;

		// check if the files has been posted...
		if (!isset($_FILES['req_file']))
			message('You did not uploaded the game file. Try again later.');
		elseif (!isset($_FILES['req_image']))
			message('You did not uploaded an image file. Try again later.');

		$uploaded_game = $_FILES['req_file'];
		$uploaded_image = $_FILES['req_image'];

		$rawname_game = $uploaded_game;
		$filename_game = null;
		$filename_image = null;

		// Make sure the upload went smooth for the game file
		if (isset($uploaded_game['error']))
		{
			switch ($uploaded_game['error'])
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
				case 2:	// UPLOAD_ERR_FORM_SIZE
					message($lang_smiley['Too large ini']);
					break;

				case 3:	// UPLOAD_ERR_PARTIAL
					message($lang_smiley['Partial upload']);
					break;

				case 4:	// UPLOAD_ERR_NO_FILE
					message($lang_smiley['No file']);
					break;

				case 6:	// UPLOAD_ERR_NO_TMP_DIR
					message($lang_smiley['No tmp directory']);
					break;

				default:
					// No error occured, but was something actually uploaded?
					if ($uploaded_game['size'] == 0)
						message($lang_smiley['No file']);
					break;
			}
		}

		// Make sure the upload went smooth for the game file
		if (isset($uploaded_image['error']))
		{
			switch ($uploaded_image['error'])
			{
				case 1:	// UPLOAD_ERR_INI_SIZE
				case 2:	// UPLOAD_ERR_FORM_SIZE
					message($lang_smiley['Too large ini']);
					break;

				case 3:	// UPLOAD_ERR_PARTIAL
					message($lang_smiley['Partial upload']);
					break;

				case 4:	// UPLOAD_ERR_NO_FILE
					message($lang_smiley['No file']);
					break;

				case 6:	// UPLOAD_ERR_NO_TMP_DIR
					message($lang_smiley['No tmp directory']);
					break;

				default:
					// No error occured, but was something actually uploaded?
					if ($uploaded_image['size'] == 0)
						message($lang_smiley['No file']);
					break;
			}
		}

		if (is_uploaded_file($uploaded_game['tmp_name']) && is_uploaded_file($uploaded_image['tmp_name']))
		{
			$filename_game = substr($uploaded_game['name'], 0, strpos($uploaded_game['name'], '.'));
			$filename_image = substr($uploaded_image['name'], 0, strpos($uploaded_image['name'], '.'));

			// Check types
			$allowed_types_games = array('application/x-shockwave-flash');
			$allowed_types_image = array('image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/x-png');

			if (!in_array($uploaded_image['type'], $allowed_types_image))
				message($lang_smiley['Bad type']);

			// Determine type
			$extension_image = null;
			if ($uploaded_image['type'] == 'image/jpeg' || $uploaded_image['type'] == 'image/pjpeg' || $uploaded_image['type'] == 'image/jpg')
				$extension_image = array('.jpg', '.png');
			else
				$extension_image = array('.png', '.jpg');

			// Move the files to the games directory.
			if (!@move_uploaded_file($uploaded_game['tmp_name'], PUN_ROOT.'games/'.$filename_game.'.tmp'))
				message($lang_smiley['Move failed']);

			if (!@move_uploaded_file($uploaded_image['tmp_name'], PUN_ROOT.'games/img/'.$filename_image.'.tmp'))
				message($lang_smiley['Move failed']);

			// Delete any tmp, old images or whatever and put the new one in place
			@unlink(PUN_ROOT.'games/img/'.$filename.$extension_image[0]);
			@unlink(PUN_ROOT.'games/img/'.$filename.$extension_image[1]);

			@rename(PUN_ROOT.'games/'.$filename_game.'.tmp', PUN_ROOT.'games/'.$rawname_game);
			@rename(PUN_ROOT.'games/img/'.$filename_image.'.tmp', PUN_ROOT.'games/img/'.$filename_game.$extension_image[0]);

			@chmod(PUN_ROOT.'games/'.$rawname_game, 0644);
			@chmod(PUN_ROOT.'games/img/'.$filename_game.$extension_image[0], 0644);
		}
		else
			message($lang_smiley['Unknown failure']);

		$db->query('INSERT INTO '.$db->prefix.'game_list (name, filename, filetype, description, width, height, category, rock_rate) VALUES("'.$name.'", "'.$db->escape($rawname_game).'", "'.$db->escape($filetype).'", "'.$db->escape($description).'", "'.$width.'", "'.$height.'", "'.$category.'", "'.$ratio.'")') or error('Unable to add game', __FILE__, __LINE__, $db->error());

		// Regenerate the games cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';

		generate_games_cache();

		redirect('admin_loader.php?plugin='.$plugin, 'Successfully added a new game!');
	}

	// Display the admin navigation menu
	$required_fields = array('req_name', 'req_description', 'req_category', 'req_file', 'req_filetype', 'req_image');
	$focus_element = $required_fields;
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], 'Adding a New Game');
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Add a New Game</span></h2>
		<div class="box">
			<form method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=<?php echo $plugin ?>&action=add">
				<input type="hidden" name="form_sent" value="1" />
				<div class="inform">
					<fieldset>
						<legend>General Game Settings</legend>
						<div class="infldset">
							<strong>Name:</strong><br />
							<input type="text" name="req_name" size="50" tabindex="1" />
							<br /><br />
							<strong>Description:</strong><br />
							<textarea name="req_description" tabindex="2" rows="6" cols="95"></textarea><br />
							<span>Warning: This text may require/contain BBCode without Smilies.</span>
							<br /><br />
							<strong>Category:</strong><br />
							<select name="req_category" tabindex="3">
							<?php
								foreach ($category_game_types as $name => $value):
								if (!is_numeric($name)):
							?>
								<option value="<?php echo $value; ?>"><?php echo $name; ?></option>
							<?php endif; endforeach; ?>
							</select>
							<br /><br />
							<strong>Game File:</strong><br />
							<input type="file" name="req_file" tabindex="4" size="50" accept="application/x-shockwave-flash" />
							<br /><br />
							<strong>Game File Type:</strong><br />
							<select name="req_filetype" tabindex="5">
								<?php
									foreach ($game_type_trans as $value => $name):
									if (!is_numeric($name)):
								?>
									<option value="<?php echo $value; ?>" <?php if ($value == $game_info['filetype']) echo 'selected';?>><?php echo $name; ?></option>
								<?php endif; endforeach; ?>
							</select>
							<br /><br />
							<strong>Game Image:</strong><br />
							<input type="file" name="req_image" tabindex="6" size="50" accept="image/*" />
						</div>
					</fieldset>
					<br /><br />
					<fieldset>
						<legend>Other Game Properties</legend>
						<div class="infldset">
							<strong>Width / Height:</strong><br />
							<p><input type="text" name="width" size="10" tabindex="7" />px - <input type="text" name="height" size="10" tabindex="8" />px</p>
							<br />
							<strong>Rocks to Score Ratio:</strong><br />
							1 : <input type="text" name="ratio" size="10" tabindex="9" />
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="upload" value="Add the Game" tabindex="10" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
	</div>
<?php
} elseif ($action == 'edit') {
	if (!isset($id))
		message('Invalid game ID.');
		
	if (isset($_POST['form_sent'])) {
		confirm_referrer('admin_loader.php');

		// Clean up everything that requires entries and such...
		$name = pun_trim($_POST['req_name']);
		$filetype = isset($_POST['req_filetype']) ? pun_trim($_POST['req_filetype']) : 'flash';
		$description = pun_linebreaks(pun_trim($_POST['req_description']));
		$category = isset($_POST['req_category']) ? pun_trim($_POST['req_category']) : 1;
		$width = isset($_POST['width']) ? pun_trim($_POST['width']) : 800;
		$height = isset($_POST['height']) ? pun_trim($_POST['height']) : 600;
		$ratio = isset($_POST['ratio']) ? pun_trim($_POST['ratio']) : 250;

		$db->query('UPDATE '.$db->prefix.'game_list SET name=\''.$db->escape($name).'\', filetype=\''.$db->escape($filetype).'\', description=\''.$db->escape($description).'\', width='.$width.', height='.$height.', category='.$category.', rock_rate='.$ratio.' WHERE id='.$id) or error('Unable to edit game', __FILE__, __LINE__, $db->error());

		// Regenerate the games cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';

		generate_games_cache();

		redirect('admin_loader.php?plugin='.$plugin, 'Successfully edited a game!');
	}

	//pull out game info
	$result = $db->query('SELECT * FROM '.$db->prefix.'game_list WHERE id='.$id.'') or error('Unable to fetch game information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		message($lang_common['Bad request']);

	$game_info = $db->fetch_assoc($result);

	// Display the admin navigation menu
	$required_fields = array('req_name', 'req_description', 'req_category', 'req_file', 'req_filetype', 'req_image');
	$focus_element = $required_fields;
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], 'Editing a Game');
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2><span>Edit a Game</span></h2>
		<div class="box">
			<form method="post" enctype="multipart/form-data" action="admin_loader.php?plugin=<?php echo $plugin ?>&action=edit&id=<?php echo $id; ?>">
				<input type="hidden" name="form_sent" value="1" />
				<div class="inform">
					<fieldset>
						<legend>General Game Settings</legend>
						<div class="infldset">
							<strong>Name:</strong><br />
							<input type="text" name="req_name" size="50" tabindex="1" value="<?php echo pun_htmlspecialchars($game_info['name']); ?>" />
							<br /><br />
							<strong>Description:</strong><br />
							<textarea name="req_description" tabindex="2" rows="6" cols="95"><?php echo $game_info['description']; ?></textarea><br />
							<span>Warning: This text may require/contain BBCode without Smilies.</span>
							<br /><br />
							<strong>Category:</strong><br />
							<select name="req_category" tabindex="3">
							<?php
								foreach ($category_game_types as $name => $value):
								if (!is_numeric($name)):
							?>
								<option value="<?php echo $value; ?>" <?php if ($value == $game_info['category']) echo 'selected';?>><?php echo $name; ?></option>
							<?php endif; endforeach; ?>
							</select>
							<br /><br />
							<strong>Game File:</strong><br />
							<input type="file" name="req_file" tabindex="4" size="50" accept="application/x-shockwave-flash" />
							<br /><br />
							<strong>Game File Type:</strong><br />
							<select name="req_filetype" tabindex="5">
								<?php
									foreach ($game_type_trans as $value => $name):
									if (!is_numeric($name)):
								?>
									<option value="<?php echo $value; ?>" <?php if ($value == $game_info['filetype']) echo 'selected';?>><?php echo $name; ?></option>
								<?php endif; endforeach; ?>
							</select>
							<br /><br />
							<strong>Game Image:</strong><br />
							<input type="file" name="req_image" tabindex="6" size="50" accept="image/*" />
						</div>
					</fieldset>
					<br /><br />
					<fieldset>
						<legend>Other Game Properties</legend>
						<div class="infldset">
							<strong>Width / Height:</strong><br/>
							<p><input type="text" name="width" size="10" tabindex="7" value="<?php echo $game_info['width']; ?>" />px - <input type="text" name="height" size="10" tabindex="8" value="<?php echo $game_info['height']; ?>" />px</p>
							<br />
							<strong>Rocks to Score Ratio:</strong><br />
							1 : <input type="text" name="ratio" size="10" tabindex="9" value="<?php echo $game_info['rock_rate']; ?>" />
						</div>
					</fieldset>
				</div>
				<p class="buttons"><input type="submit" name="upload" value="Edit the Game" tabindex="10" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
			</form>
		</div>
	</div>
<?php
} else {
	if ($action == 'remove') {
		if (!isset($id))
			message('Invalid game ID.');

		$result = $db->query('SELECT * FROM '.$db->prefix.'game_list WHERE id='.$id.'') or error('Unable to fetch game information', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message($lang_common['Bad request']);
		
		$game_info = $db->fetch_assoc($result);

		$filename = substr($game_info['filename'], 0, strpos($game_info['filename'], '.'));

		@unlink(PUN_ROOT.'games/img/'.$filename.'.png');
		@unlink(PUN_ROOT.'games/img/'.$filename.'.jpg');
		@unlink(PUN_ROOT.'games/img/'.$filename.'.jpeg');

		@unlink(PUN_ROOT.'games/'.$game_info['filename']);

		$db->query('DELETE FROM '.$db->prefix.'game_list WHERE id='.$id.'') or error('Unable to delete game', __FILE__, __LINE__, $db->error());

		// Regenerate the games cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';

		generate_games_cache();

		redirect('admin_loader.php?plugin='.$plugin, 'Successfully removed a game!');
	}

	// Display the admin navigation menu
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], 'Manage Games');
	generate_admin_menu($plugin);
?>
	<div class="blockform">
		<h2 class="block2"><span>Game List</span></h2>
		<div class="box">
			<p style="margin: 6px 12px 3px 6px; text-align: right;"><img src="../img/icons/exclamation.png" align="top">&nbsp;<a href="admin_loader.php?plugin=<? echo $plugin; ?>&action=add">Add a New Game</a></p>
			<style>
				#adminconsole th {
					WIDTH: 0em;
					FONT-WEIGHT: bold;
				}

				#adminconsole thead th {
					padding-bottom: 4px;
				}

				.pun th {
					padding: 0px 0px 0px 8px;
				}
			</style>

			<table cellspacing="0" border="1">
				<thead>
					<tr>
						<th class="tc2" scope="col">Image</th>
						<th class="tc2" scope="col">Game Name</th>
						<th class="tc3" scope="col">Description</th>
						<th class="tc2" scope="col">Category</th>
						<th class="tc3" scope="col">Action</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$result = $db->query('SELECT * FROM '.$db->prefix.'game_list ORDER BY id ASC') or error('Unable to get latest games', __FILE__, __LINE__, $db->error());

				if ($db->num_rows($result))
				{
					while($game_info = $db->fetch_assoc($result))
					{
				?>
					<tr>
						<td scope="col"><img src="<?php echo generate_game_image($game_info['filename']); ?>" alt="" width="50px" height="50px" /></td>
						<td scope="col"><?php echo $game_info['name']; ?></td>
						<td scope="col"><?php echo parse_message($game_info['description'], true); ?></td>
						<td scope="col"><?php echo $category_game_types[$game_info['category']]; ?></td>
						<td scope="col">
							<img src="../img/icons/comment_edit.png" align="top">&nbsp;<a href="admin_loader.php?plugin=<? echo $plugin; ?>&action=edit&id=<?php echo $game_info['id']; ?>">Edit this Game</a>
							<br /><br />
							<img src="../img/icons/bin.png" align="top">&nbsp;<a href="admin_loader.php?plugin=<? echo $plugin; ?>&action=remove&id=<?php echo $game_info['id']; ?>">Delete this Game</a>
						</td>
					</tr>
				<?php
					}
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
<?php
}
// Note that the script just ends here. The footer will be included by admin_loader.php