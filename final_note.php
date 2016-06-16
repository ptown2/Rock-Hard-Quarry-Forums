<?php
	define('PUN_ROOT', dirname(__FILE__).'/');
	require PUN_ROOT.'include/common.php';

	global $db, $pun_config, $lang_common, $pun_user;

	$action = $_GET['a'];
	
	if ($action == "backup") {
		forum_setcookie("RHQPrankShutDown", "You're a dumb cunt.", time() + 1209600);
		header('Location: http://rockhardquarry.net/viewtopic.php?id=983');
	}

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	// Deal with newlines, tabs and multiple spaces
	$pattern = array("\t", '  ', '  ');
	$replace = array('&#160; &#160; ', '&#160; ', ' &#160;');
	$message = str_replace($pattern, $replace, $pun_config['o_maintenance_message']);

	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/maintenance.tpl'))
	{
		$tpl_file = PUN_ROOT.'style/'.$pun_user['style'].'/maintenance.tpl';
		$tpl_inc_dir = PUN_ROOT.'style/'.$pun_user['style'].'/';
	}
	else
	{
		$tpl_file = PUN_ROOT.'include/template/maintenance.tpl';
		$tpl_inc_dir = PUN_ROOT.'include/user/';
	}

	$tpl_maint = file_get_contents($tpl_file);

	// START SUBST - <pun_include "*">
	preg_match_all('%<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">%i', $tpl_maint, $pun_includes, PREG_SET_ORDER);

	foreach ($pun_includes as $cur_include)
	{
		ob_start();

		// Allow for overriding user includes, too.
		if (file_exists($tpl_inc_dir.$cur_include[1].'.'.$cur_include[2]))
			require $tpl_inc_dir.$cur_include[1].'.'.$cur_include[2];
		else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2]))
			require PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
		else
			error(sprintf($lang_common['Pun include error'], htmlspecialchars($cur_include[0]), basename($tpl_file)));

		$tpl_temp = ob_get_contents();
		$tpl_maint = str_replace($cur_include[0], $tpl_temp, $tpl_maint);
		ob_end_clean();
	}
	// END SUBST - <pun_include "*">

	// START SUBST - <pun_language>
	$tpl_maint = str_replace('<pun_language>', $lang_common['lang_identifier'], $tpl_maint);
	// END SUBST - <pun_language>

	// START SUBST - <pun_content_direction>
	$tpl_maint = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_maint);
	// END SUBST - <pun_content_direction>

	// START SUBST - <pun_head>
	ob_start();

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), "Final Days of RHQ");

?>
<title><?php echo generate_page_title($page_title) ?></title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />
<?php
	$tpl_temp = trim(ob_get_contents());
	$tpl_maint = str_replace('<pun_head>', $tpl_temp, $tpl_maint);
	ob_end_clean();
	// END SUBST - <pun_head>

	// START SUBST - <pun_maint_main>
	ob_start();
?>
<center><img src="img/rhqlogo2.png"></center>
<br />
<div class="block">
	<h2>Final Notes/Words for Rock Hard Quarry Forums</h2>
	<div class="box">
		<div class="inbox">
			<p>
			We're sorry to announce but Rock Hard Quarry will close down tomorrow (April 2, 2014) as I got a DMCA Claim and Personal Note from Clockwork (Alex Quach) to permanently shutdown the forums and erase all data and products involved under the name of RHQ. But I declined on the whole removing data as I personally promised to all RHQers I would release it.
			<br /><br />
			I'm honestly sorry as I shouldn't have done that; reviving the forums once more. But the time has finally come to an end.
			<br /><br />
			If you want to have a carbon copy of the forums as a keep-sake, <a href="final_note.php?a=backup">click here to download it</a>.
			</p>
		</div>
	</div>
</div>

<iframe width="0" height="0" src="//www.youtube.com/embed/f2VmWUdxW6k?list=PLcVhok3OuJMqbP0nsy80b5P0b-ztB0Wru&shuffle=0&autoplay=1" frameborder="0" autoplay="1" allowfullscreen></iframe>
<?php
	$tpl_temp = trim(ob_get_contents());
	$tpl_maint = str_replace('<pun_maint_main>', $tpl_temp, $tpl_maint);
	ob_end_clean();
	// END SUBST - <pun_maint_main>

	// End the transaction
	$db->end_transaction();

	// Close the db connection (and free up any result data)
	$db->close();

	exit($tpl_maint);
?>