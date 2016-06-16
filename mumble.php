<?php
	define('PUN_ROOT', dirname(__FILE__).'/');

	require PUN_ROOT.'include/common.php';

	define('PUN_ACTIVE_PAGE', 'index');
	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Mumble Viewer');

	require PUN_ROOT.'header.php';
	require PUN_ROOT.'mumble/mumbleChannelViewer.php';
?>

<link rel="stylesheet" href="mumble/mumbleChannelViewer.css" type="text/css" />

<div id="rules" class="blockform">
	<div id="announce" class="block">
		<div class="hd"><h2><span>How to get in the Mumble Server</span></h2></div>
		<div class="box">
			<div id="announce-block" class="inbox">
				<div class="usercontent" style="font-size: 16px;">
				
				</div>
			</div>
		</div>
	</div>

	<div class="hd"><h2><span>RHQ Mumble Viewer</span></h2></div>
	<div class="inbox" style="background-color: white;">
		<div id="mumbleViewer">
			<?php
				//$dataUrl = 'http://api.commandchannel.com/cvp.json?email=Grubz5@gmail.com&apiKey=3AE832AF-9B8B-4607-AC1F-92A97D91248D';
				//echo MumbleChannelViewer::render( $dataUrl, 'json' );
			?>
		</div>
	</div>
</div>

<?php require PUN_ROOT.'footer.php'; ?>