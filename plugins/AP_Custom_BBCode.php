<?php

/**
 * Copyright (C) 2010-2011 Sam Rush (ratburntro44@yahoo.com)
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Load language file
if (file_exists(PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_custom_bbcode.php'))
	require PUN_ROOT.'lang/'.$admin_language.'/admin_plugin_custom_bbcode.php';
else
	require PUN_ROOT.'lang/English/admin_plugin_custom_bbcode.php';

// Tell admin_loader.php that this is indeed a plugin and that it is loaded
define('PUN_PLUGIN_LOADED', 1);
define('PLUGIN_VERSION', '1.0.0');
define('PLUGIN_URL', pun_htmlspecialchars(get_base_url(true).'/admin_loader.php?plugin='.$_GET['plugin']));

// If the "Show text" button was clicked
if (isset($_POST['show_text'])) {
	$last = $db->query('SELECT id FROM '.$db->prefix.'bbcode ORDER BY id DESC LIMIT 1') or error('Unable to fetch bbcodes', __FILE__, __LINE__, $db->error());
	$last = $db->fetch_assoc($last);
	$last = $last['id'];
	//$last = $db->query("SELECT type FROM ".$db->prefix."bbcode WHERE id=1");
	//$last = db->fetch

	$i = 1;
	while($i<=$last)
	{
		$bbname = $db->escape($_POST['bbname'.$i]);
		$bbtype = intval($_POST['bbtype'.$i]);
		$bbout = $db->escape($_POST['bbout'.$i]);

		$db->query("UPDATE ".$db->prefix."bbcode SET name='$bbname', type=$bbtype, output='$bbout' WHERE id=$i");
		$i++;
	}

	$bbname = $db->escape($_POST['bbnewname']);
	$bbtype = intval($_POST['bbnewtype']);
	$bbout = $db->escape($_POST['bbnewout']);

	if($bbname!='New') {
		$last++;
		$db->query("INSERT INTO ".$db->prefix."bbcode (id, name, type, output) VALUES ($last, '$bbname', $bbtype, '$bbout') ");
		$db->query("UPDATE ".$db->prefix."bbcode SET type=$last WHERE id=1");
	}

	function do_custom_bbcode () {
		global $db;

		$custom = $db->query("SELECT * FROM ".$db->prefix."bbcode");

		while($row=$db->fetch_assoc($custom)) {
			if($row['type']==1) {
				$pattern[] = '%\['.$row['name'].'\](.*?)\[/'.$row['name'].'\]%ms';
				$replace[] = $row['output'];
			}
				else if($row['type']==2)
			{
				$pattern[] = '%\['.$row['name'].'=([^\[]+?)\](.*?)\[/'.$row['name'].'\]%';
				$replace[] = $row['output'];
			}
		}

		$bbcache = fopen(dirname(dirname(__FILE__)).'/cache/cache_bbcode.php','w');
		fwrite($bbcache, "<?php\n\n\$pun_bbcode = array (\n");

		$bbcodes = $db->query("SELECT * FROM ".$db->prefix."bbcode ORDER BY id");

		$i = 1;
		while($row=$db->fetch_assoc($bbcodes)) {
			fwrite($bbcache, '\''.$i.'\' => \''.$row['name'].'\',');
			$i++;
		}

		fwrite($bbcache, ");\n\n");
		fwrite($bbcache, "\$pun_bbcode2 = array (\n");
		$bbcodes2 = $db->query("SELECT * FROM ".$db->prefix."bbcode ORDER BY id");

		$i = 1;
		while($row=$db->fetch_assoc($bbcodes2)) {
			fwrite($bbcache, '\''.$i.'\' => \''.$row['output'].'\',');
			$i++;
		}

		fwrite($bbcache, ");\n\n");
		fwrite($bbcache, "\$pun_bbcode3 = array (\n");
		$bbcodes3 = $db->query("SELECT * FROM ".$db->prefix."bbcode ORDER BY id");

		$i = 1;
		while($row=$db->fetch_assoc($bbcodes3)) {
			fwrite($bbcache, '\''.$i.'\' => \''.$row['type'].'\',');
			$i++;
		}

		fwrite($bbcache, ");\n\n?>");
		fclose($bbcache);
	}

	do_custom_bbcode();
	redirect(PLUGIN_URL, $lang_abbc['Plugin redirect']);
}
else
{
	// Display the admin navigation menu
	generate_admin_menu($plugin);

	$cur_index = 1;

?>
	<div class="plugin blockform">
		<h2><span><?php echo $lang_abbc['name']; ?> <?php echo PLUGIN_VERSION; ?></span></h2>
		<div class="box">
			<div class="inbox">
				<p><?php echo $lang_abbc['welcome1']; ?></p>
				<p><?php echo $lang_abbc['welcome2']; ?></p>
			</div>
		</div>
		
		<h2 class="block2"><span>BBCode Tags</span></h2>
		
		<div class="box">
		<p><?php echo $lang_abbc['instruct1']; ?></p>
		<p><?php echo $lang_abbc['instruct2']; ?></p>
		<p><?php echo $lang_abbc['instruct3']; ?></p>
		<p><?php echo $lang_abbc['instruct4']; ?></p>
		<p><?php echo $lang_abbc['instruct5']; ?></p>
			<form id="bbtags" method="post" action="<?php echo PLUGIN_URL; ?>">
			<div class="inform">
			<fieldset>
			<legend><?php echo $lang_abbc['legend']; ?></legend>
			<div class="infldset">
			<table>
			<tr>
			<td width="30%"><?php echo $lang_abbc['tagname']; ?></td>
			<td width="30%"><?php echo $lang_abbc['tagtype']; ?></td>
			<td width="40%"><?php echo $lang_abbc['tagoutp']; ?></td>
			</tr>
			<?php
$bbcodes = $db->query("SELECT * FROM ".$db->prefix."bbcode WHERE id!=1");
while($row=$db->fetch_assoc($bbcodes))
{
echo '<tr>';
echo '<td><input type="text" name="bbname'.$row['id'].'" value="'.$row['name'].'" /></td>';
echo '<td><input type="text" name="bbtype'.$row['id'].'" value="'.$row['type'].'" /></td>';
echo '<td><textarea name="bbout'.$row['id'].'" rows="5" cols="55">'.$row['output'].'</textarea></td>';
echo '</tr>';
}

			?>
			<tr>
			<td><input type="text" name="bbnewname" value="New" /></td>
			<td><input type="text" name="bbnewtype" value="" /></td>
			<td><textarea name="bbnewout" rows="5" cols="55"></textarea></td>
			</tr>
			</table>
			</div>
			</fieldset>
			</div>
			<input type="Hidden" name="show_text" value="show_text" /><br />
			<p class="submitend"><input type="Submit" value="<?php echo $lang_abbc['save']; ?>" /></p>
			</form>
				</div>

		<?php
		}