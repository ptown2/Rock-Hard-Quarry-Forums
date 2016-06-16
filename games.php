<?php
	define('PUN_ROOT', dirname(__FILE__).'/');

	require PUN_ROOT.'include/common.php';
	require PUN_ROOT.'include/parser.php';
	require PUN_ROOT.'cache/cache_games.php';

	global $db, $pun_config, $lang_common, $pun_user;

	// Variables
	$id = isset($_GET['id']) ? intval($_GET['id']) : null;
	$mode = isset($_GET['mode']) ? pun_trim($_GET['mode']) : null;

	// Score Submission
	if (isset($mode) && $mode == 'score') {
		if ($pun_user['is_guest'])
			message('You must log-in in order to play Official RHQ Games to earn rocks.');

		confirm_referrer(array('games.php', 'newscore.php'));

		$score = isset($_POST['score']) ? pun_trim($_POST['score']) : 0;
		$ratio = isset($_POST['ratio']) ? pun_trim($_POST['ratio']) : 500;
		$post_score = ceil($score / $ratio);

		$db->query('UPDATE '.$db->prefix.'users SET rocks=rocks+'.$post_score.' WHERE id='.$pun_user['id']) or error('Unable to update user', __FILE__, __LINE__, $db->error());

		redirect('games.php', 'You have earned '.$post_score.' rocks from playing this game!');
	}

	//if (!$pun_user['is_admmod'])
	//	message('In under-maintenance. Please come back soon!');
?>

<?php if (isset($id)) {
	$game_info = null;
	foreach ($pun_games as $game_check) {
		if ($game_check['id'] == $id) {
			$game_info = $game_check;
			break;
		}
	}

	if (!isset($game_info))
		message('Invalid Game Info');

	$type = $game_info['filetype'];
	list($width, $height) = getimagesize('games/'.$game_info['filename']);

	session_start();
		$_SESSION['game_name'] = $game_info['filename'];
		$_SESSION['game_ratio'] = $game_info['rock_rate'];

	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $game_info['name']);

	require PUN_ROOT.'header.php';
?>
	<div id="rules" class="blockform">
		<div class="hd"><h2><span><?php echo $game_info['name']; ?></span></h2></div>
		<div class="box" style="text-align: center; padding: 24px 0px 24px 0px;">
		<?php if ($type == 'flash') { ?>
			<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="<?php echo $width ?>" height="<?php echo $height ?>" id="<?php echo $game_info['name']; ?>" align="middle">
				<param name="allowScriptAccess" value="sameDomain" />
				<param name="allowFullScreen" value="false" />
				<param name="movie" value="games/<?php echo $game_info['filename']; ?>" /><param name="quality" value="high" /><embed src="games/<?php echo $game_info['filename']; ?>" quality="high" width="<?php echo $width ?>" height="<?php echo $height ?>" name="<?php echo $game_info['name']; ?>" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
			</object>
		<?php } elseif ($type == 'unity') { ?>
			<script type="text/javascript" src="http://webplayer.unity3d.com/download_webplayer-3.x/3.0/uo/UnityObject.js"></script>
			<script type="text/javascript">unityObject.embedUnity("games/<?php echo $game_info['filename']; ?>", "WebPlayer.unity3d", <?php echo $game_info['width']; ?>, <?php echo $game_info['height']; ?>);</script>

			<div id="unityPlayer">
				<div class="missing">
					<a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!">
						<img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" />
					</a>
				</div>
			</div>
		<?php } ?>
		</div>
	</div>
<?php } else { 
	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Game Selection');

	require PUN_ROOT.'header.php';
?>
	<div id="rules" class="blockform">
		<div class="hd"><h2><span>Game List</span></h2></div>
		<div class="box" style="text-align: center; padding: 24px 0px 24px 0px;">
			<?php
				foreach ($pun_games as $game_info) {
				?>
					<div style="height: 100px; text-align: left; padding: 12px 25% 12px 25%;">
						<span style="float: left; height: 100px; border: 1px black solid;">
							<img src="<?php echo generate_game_image($game_info['filename']); ?>" alt="" width="100px" height="100px" />
						</span>

						<div style="font-size: 24px; margin-left: 116px;">
							<p><a href="games.php?id=<?php echo $game_info['id']; ?>"><?php echo $game_info['name'] ?></a></p>
						</div>
						<div style="font-size: 14px; margin-left: 116px;">
							<?php echo parse_message($game_info['description'], true); ?>
						</div>
					</div>
				<?php
				}
			?>
		</div>
	</div>
<?php
}

require PUN_ROOT.'footer.php';
?>