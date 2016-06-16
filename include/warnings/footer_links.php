<?php
if ($footer_style == 'warnings')
{

?>
			<dl id="searchlinks" class="conl">
				<dt><strong>Warning links</strong></dt>
<?php
echo "\t\t\t\t\t\t".'<dd><a href="warnings.php">'.$lang_warnings['Show warning types'].'</a></dd>'."\n";

if ($pun_user['is_admmod'])
	echo "\t\t\t\t\t\t".'<dd><a href="warnings.php?action=show_recent">'.$lang_warnings['Show recent warnings'].'</a></dd>'."\n";
?>
			</dl>
<?php
}
