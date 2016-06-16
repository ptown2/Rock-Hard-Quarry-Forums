<?php
/***********************************************************************

  Copyright (C) 2010 Mpok (mpok@fluxbb.fr)
  based on code Copyright (C) 2005 Vincent Garnier (vin100@forx.fr)
  License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher

************************************************************************/

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

//
// Generate smiley cache PHP array
//
function generate_smiley_cache()
{
	global $db;

	$smiley_text = array();
	$smiley_img = array();

	$result = $db->query('SELECT image, text FROM '.$db->prefix.'smilies ORDER BY disp_position') or error('Unable to retrieve smilies', __FILE__, __LINE__, $db->error());
	while ($db_smilies = $db->fetch_assoc($result))
	{
		$smiley_text[] = $db_smilies['text'];
		$smiley_img[] = $db_smilies['image'];
	}

	$smiley_text = array_map('pun_htmlspecialchars', $smiley_text);
	$smiley_text = array_map('addslashes', $smiley_text);

	$str = '';
	$str .= '<?php'."\n";
	$str .= '$smilies = array('."\n";

	foreach ($smiley_text as $id => $t)
		$str .= "'".$t."' => '".$smiley_img[$id]."',"."\n";

	$str .= ');'."\n";
	$str .= '?>'."\n";

	$fh = @fopen(FORUM_CACHE_DIR.'cache_smilies.php', 'wb');
	if (!$fh)
		error('Unable to write the smilies cache. Please check permissions of the \'cache\' directory.', __FILE__, __LINE__);

	fwrite($fh, $str);
	fclose($fh);
}
