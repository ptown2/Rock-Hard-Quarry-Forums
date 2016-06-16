<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);
$forum_id = isset($forum_id) ? $forum_id : 0;

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><span><?php echo $lang_common['Jump to'] ?><br /></span>
					<select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="General RHQ">
							<option value="1"<?php echo ($forum_id == 1) ? ' selected="selected"' : '' ?>>Rock Hard News</option>
							<option value="3"<?php echo ($forum_id == 3) ? ' selected="selected"' : '' ?>>General Talk</option>
							<option value="21"<?php echo ($forum_id == 21) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Newbie Section</option>
							<option value="35"<?php echo ($forum_id == 35) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;RHQ Stock Market</option>
							<option value="18"<?php echo ($forum_id == 18) ? ' selected="selected"' : '' ?>>Sensationalist News and Debates</option>
						</optgroup>
						<optgroup label="Entertainment Plaza">
							<option value="13"<?php echo ($forum_id == 13) ? ' selected="selected"' : '' ?>>Creation World</option>
							<option value="24"<?php echo ($forum_id == 24) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Requests</option>
							<option value="10"<?php echo ($forum_id == 10) ? ' selected="selected"' : '' ?>>Media Group</option>
							<option value="17"<?php echo ($forum_id == 17) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Moobie Night</option>
							<option value="14"<?php echo ($forum_id == 14) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Music Store</option>
							<option value="8"<?php echo ($forum_id == 8) ? ' selected="selected"' : '' ?>>Vidya Gaymes</option>
							<option value="36"<?php echo ($forum_id == 36) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Vidya News</option>
						</optgroup>
						<optgroup label="Miscellaneous">
							<option value="7"<?php echo ($forum_id == 7) ? ' selected="selected"' : '' ?>>Suggestions</option>
							<option value="16"<?php echo ($forum_id == 16) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Approved</option>
							<option value="26"<?php echo ($forum_id == 26) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Denied</option>
							<option value="5"<?php echo ($forum_id == 5) ? ' selected="selected"' : '' ?>>Off-Topic Zone</option>
							<option value="19"<?php echo ($forum_id == 19) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Forum Games</option>
							<option value="25"<?php echo ($forum_id == 25) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Blogger</option>
							<option value="6"<?php echo ($forum_id == 6) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;&nbsp;Shithole Central</option>
						</optgroup>
						<optgroup label="Bottomless Rock Bottom">
							<option value="15"<?php echo ($forum_id == 15) ? ' selected="selected"' : '' ?>>Bright Threads of RHQ</option>
							<option value="20"<?php echo ($forum_id == 20) ? ' selected="selected"' : '' ?>>Rock Hard Obituary</option>
						</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
