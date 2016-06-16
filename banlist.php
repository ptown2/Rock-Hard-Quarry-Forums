<?php
	define('PUN_ROOT', dirname(__FILE__).'/');
	require PUN_ROOT.'include/common.php';

	global $db, $pun_config, $lang_common, $pun_user, $pun_bans;

	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Suspension List');

	require PUN_ROOT.'header.php';

	$ban_count = 0;
	foreach ($pun_bans as $cur_ban) {
		$ban_count++;
	}

	if ($ban_count > 0) {
?>
<div id="rules" class="blockform">
	<div class="hd"><h2><span>Active Suspension List</span></h2></div>
	<div class="box">
		<table align="center" width="95%">
			<tr style="font-weight: bold; font-size: 16px;">
				<th style="width: 15%;">Banned User:</th>
				<th style="width: 35%;">Reason:</th>
				<th style="width: 15%;">Ban Type:</th>
				<th style="width: 20%;">Expires in:</th>
				<th style="width: 15%;">Admin:</th>
			</tr>
	
	<?php
	$i = 1;
	$javascript = '';

	foreach ($pun_bans as $cur_ban) {
			$expire = '<span style="color: red;">Never</span>';

			if ($cur_ban['expire'] != '') {
				$expire = 'Loading...';
				$javascript .= ' var ban_exp'.$i.' = 0; setInterval( function() { if ( ('.($cur_ban['expire'] - time()).' - ban_exp'.$i.') <= 0) { document.getElementById(\'ban_expire'.$i.'\').innerHTML = \'Expired\' } else { document.getElementById(\'ban_expire'.$i.'\').innerHTML = remaining.getString(' .($cur_ban['expire'] - time()). ' - ban_exp'.$i.', null, false) + \' remaining\'; ++ban_exp'.$i.'; } }, 1000);' ."\n\t\t\t";
			}
	?>

			<tr style="font-weight: bold; font-size: 16px;">
				<td><?php echo $cur_ban['username']; ?></td>
				<td><?php echo $cur_ban['message']; ?></td>
				<td><?php echo $lang_common[$ban_type_trans[$cur_ban['ban_type']]]; ?></td>
				<td id="<?php echo 'ban_expire'.$i; ?>"><?php echo $expire; ?></td>
				<td><a href="profile.php?id=<?php echo $cur_ban['ban_creator']; ?>"><?php echo $cur_ban['ban_creator_user']; ?></a></td>
			</tr>

	<?php

		$i++;
	}

	if ( $javascript != '' ) {
		echo "\n\t\t".'<script>'. $javascript .'</script>';
	}
	?>
			</table>
		</div>
	</div>
</div>
<?php } else { ?>
<div style="font-size: 72px; color: red; text-align: center; margin: 12px;">NO SUSPENSIONS LISTED!</div>
<?php
}
require PUN_ROOT.'footer.php'; ?>