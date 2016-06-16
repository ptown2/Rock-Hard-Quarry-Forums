<?php
// Tell header.php to use the help template
// because we don't want to edit header.php lets use the standard header template
define('PUN_HELP', 1);

define('PUN_ROOT', dirname(__FILE__).'/');
require PUN_ROOT.'include/common.php';

if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);

// Load the help.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/modern_bbcode.php';

define('PUN_ACTIVE_PAGE', 'index');
$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Smilies');

require PUN_ROOT.'header.php';
?>

<script type="text/javascript">
<!--
	function insert_text(open, close)
	{
		var docOpener = window.opener.document;

		msgfield = (docOpener.all) ? docOpener.all.req_message : docOpener.forms['post']['req_message'];

		// IE support	
		if (docOpener.selection && docOpener.selection.createRange)
		{
			msgfield.focus();
			sel = docOpener.selection.createRange();
			sel.text = open + sel.text + close;
			msgfield.focus();
		}

		// Moz support
		else if (msgfield.selectionStart || msgfield.selectionStart == '0')
		{
			var startPos = msgfield.selectionStart;
			var endPos = msgfield.selectionEnd;

			msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
			msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
			msgfield.focus();
		}

		// Fallback support for other browsers
		else
		{
			msgfield.value += open + close;
			msgfield.focus();
		}

		//window.close();
		return;
	}
-->
</script>

<div id="smileyblock" class="blocktable" style="width: 100%;">
	<h2><span><?php echo $lang_modern_bbcode['Smilies table'] ?></span></h2>
	<div id="smileybox" class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_modern_bbcode['Smiley text'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_modern_bbcode['Smiley image'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php
			// Display the smiley set
			require PUN_ROOT.'include/parser.php';
			echo "\n";

			foreach ($smilies as $smiley_text => $smiley_img) {
?>
 				<tr>

					<td class="tcl"><?php echo $smiley_text ?></td>
					<td class="tcr"><a href="javascript:insert_text('<?php echo $smiley_text ?> ', '');"><img src="img/smilies/<?php echo $smiley_img ?>" alt="" /></a></td>
				</tr>
<?php
			}
?>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php

require PUN_ROOT.'footer.php';
