<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
error_reporting(E_ALL);

if (!defined('PUN'))
	exit;

// Send no-cache headers
header('Strict-Transport-Security: max-age=31536000');
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compatibility

/*
if (!isset($_COOKIE["RHQPrankShutDown3"])) {
	header('Location: http://rockhardquarry.net/final_note.php');
}
*/

/*
$expiry = 3600 * 4; // A week

header('Expires: '.gmdate('D, d M Y H:i:s'.(time() + $expiry)).' GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-control: private, must-revalidate, max-age=' . $expiry);
header('Pragma: private');
*/

// Send the Content-type header in case the web server is setup to send something else
header('Content-type: text/html; charset=utf-8');

// Load the template
if (defined('PUN_ADMIN_CONSOLE'))
	$tpl_file = 'admin.tpl';
else if (defined('PUN_HELP'))
	$tpl_file = 'help.tpl';
else
	$tpl_file = 'main.tpl';

if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/'.$tpl_file)) {
	$tpl_file = PUN_ROOT.'style/'.$pun_user['style'].'/'.$tpl_file;
	$tpl_inc_dir = PUN_ROOT.'style/'.$pun_user['style'].'/';
} else {
	$tpl_file = PUN_ROOT.'include/template/'.$tpl_file;
	$tpl_inc_dir = PUN_ROOT.'include/user/';
}

$tpl_main = file_get_contents($tpl_file);

// START SUBST - <pun_include "*">
preg_match_all('%<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">%i', $tpl_main, $pun_includes, PREG_SET_ORDER);

foreach ($pun_includes as $cur_include) {
	ob_start();

	// Allow for overriding user includes, too.
	if (file_exists($tpl_inc_dir.$cur_include[1].'.'.$cur_include[2]))
		require $tpl_inc_dir.$cur_include[1].'.'.$cur_include[2];
	else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2]))
		require PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
	else
		error(sprintf($lang_common['Pun include error'], htmlspecialchars($cur_include[0]), basename($tpl_file)));

	$tpl_temp = ob_get_contents();
	$tpl_main = str_replace($cur_include[0], $tpl_temp, $tpl_main);
	ob_end_clean();
}
// END SUBST - <pun_include "*">


// START SUBST - <pun_language>
$tpl_main = str_replace('<pun_language>', $lang_common['lang_identifier'], $tpl_main);
// END SUBST - <pun_language>


// START SUBST - <pun_content_direction>
$tpl_main = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_main);
// END SUBST - <pun_content_direction>


// START SUBST - <pun_head>
ob_start();

// Define $p if its not set to avoid a PHP notice
$p = isset($p) ? $p : null;

// Is this a page that we want search index spiders to index?
if (!defined('PUN_ALLOW_INDEX'))
	echo '<meta name="ROBOTS" content="NOINDEX, FOLLOW" />'."\n";
?>

<title><?php echo generate_page_title($page_title, $p) ?></title>

<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />
<link rel="stylesheet" type="text/css" href="style/misc/global_rhq.css" />
<!--
<style> 
body {
    height: 600px;
    background-color: red;
    position: relative;
   
    -webkit-animation-name: example; /* Chrome, Safari, Opera */
    -webkit-animation-duration: 10s; /* Chrome, Safari, Opera */
    -webkit-animation-iteration-count: infinite; /* Chrome, Safari, Opera */
    animation-name: example;
    animation-duration: 10s;
    animation-iteration-count: infinite;
    text-shadow: 3px 3px 3px #ccc;
}

/* Chrome, Safari, Opera */
@-webkit-keyframes example {
    0%   {background-color:red; left:0px; top:0px;}
    25%  {background-color:yellow; left:5px; top:50px;}
    50%  {background-color:blue; left:0px; top:0px;}
    75%  {background-color:green; left:-5px; top:50px;}
    100% {background-color:red; left:0px; top:0px;}
}

/* Standard syntax */
@keyframes example {
    0%   {background-color:red; left:0px; top:0px;}
    25%  {background-color:yellow; left:200px; top:0px;}
    50%  {background-color:blue; left:200px; top:200px;}
    75%  {background-color:green; left:0px; top:200px;}
    100% {background-color:red; left:0px; top:0px;}
}

.text {
    text-shadow: 3px 3px 3px #ccc;
    /* text-shadow must have an initial value for the animation to work. To animate from a "shadowless" state, set the shadow to 0 offset and 0 blur (e.g. 0 0 0 #000;) */
}
</style>
-->
<?php
if (defined('PUN_ADMIN_CONSOLE'))
{
	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/base_admin.css'))
		echo '<link rel="stylesheet" type="text/css" href="style/'.$pun_user['style'].'/base_admin.css" />'."\n";
	else
		echo '<link rel="stylesheet" type="text/css" href="style/imports/base_admin.css" />'."\n";
}

if (isset($page_head))
	echo implode("\n", $page_head)."\n";
?>

<script type="text/javascript" src="include/global_rhq.js"></script>
<script type="text/javascript" src='https://www.google.com/recaptcha/api.js'></script>
<!-- <script src="http://code.jquery.com/jquery-2.1.3.min.js"></script> -->

<!--
<script type="text/javascript" src="jquery.animate-textshadow.min.js"></script>
<script>
function colorChange() {
$( 'span' ).delay(2000).animate({textShadow: "#aaa 6px 6px 6px"});
$( 'span' ).delay(4000).animate({textShadow: "#ccc 3px 3px 3px"});
$( 'div' ).delay(2000).animate({textShadow: "#aaa 6px 6px 6px"});
$( 'div' ).delay(4000).animate({textShadow: "#ccc 3px 3px 3px"});
}

$(document).ready(function(){
var colorLooper = setInterval(function() {
colorChange();
}, 6000);
});
</script>
-->

<?php if (date( 'F' ) == 'December') { ?>
<script type="text/javascript" src="http://yui.yahooapis.com/combo?2.6.0/build/yahoo-dom-event/yahoo-dom-event.js&2.6.0/build/animation/animation-min.js"></script>
<script type="text/javascript" src="include/christmas/lights/soundmanager2-nodebug-jsmin.js"></script>
<script type="text/javascript" src="include/christmas/snowstorm-min.js"></script>
<script type="text/javascript" src="include/christmas/lights/christmaslights.js"></script>
<script type="text/javascript">
var urlBase = 'include/christmas/lights/';
soundManager.url = 'include/christmas/lights/';
</script>
<?php } ?>

<?php
$modern_bbcode_enabled = false;
if (in_array(basename($_SERVER['PHP_SELF']), array('viewtopic.php', 'post.php', 'edit.php', 'message_send.php')))
{
	$modern_bbcode_enabled = ($pun_config['p_message_bbcode'] == '1') ? true : false;

	if ($modern_bbcode_enabled)
		echo '<script type="text/javascript" src="include/modern_bbcode.js"></script>';
}

if (isset($required_fields)) {
?>
<script type="text/javascript">
	/* <![CDATA[ */
	function process_form(the_form)
	{
		var required_fields = {
	<?php
		// Output a JavaScript object with localised field names
		$tpl_temp = count($required_fields);
		foreach ($required_fields as $elem_orig => $elem_trans)
		{
			echo "\t\t\"".$elem_orig.'": "'.addslashes(str_replace('&#160;', ' ', $elem_trans));
			if (--$tpl_temp) echo "\",\n";
			else echo "\"\n\t};\n";
		}
	?>
		if (document.all || document.getElementById)
		{
			for (var i = 0; i < the_form.length; ++i)
			{
				var elem = the_form.elements[i];
				if (elem.name && required_fields[elem.name] && !elem.value && elem.type && (/^(?:text(?:area)?|password|file)$/i.test(elem.type)))
				{
					alert('"' + required_fields[elem.name] + '" <?php echo $lang_common['required field'] ?>');
					elem.focus();
					return false;
				}
			}
		}
		return true;
	}
	/* ]]> */
</script>
<?php
}

// JavaScript tricks for IE6 and older
echo '<!--[if lte IE 6]><script type="text/javascript" src="style/imports/minmax.js"></script><![endif]-->'."\n";

$tpl_temp = trim(ob_get_contents());
$tpl_main = str_replace('<pun_head>', $tpl_temp, $tpl_main);
ob_end_clean();
// END SUBST - <pun_head>


$htmlvar = '<div id="randumb"></div><div id="lights"></div>';
// START SUBST - <body>
if (isset($focus_element))
{
	$tpl_main = str_replace('<body onload="', $htmlvar. "\n" .'<body onload="document.getElementById(\''.$focus_element[0].'\').elements[\''.$focus_element[1].'\'].focus(); ToolTip.init();', $tpl_main);
	$tpl_main = str_replace('<body>', $htmlvar. "\n" .'<body onload="document.getElementById(\''.$focus_element[0].'\').elements[\''.$focus_element[1].'\'].focus(); ToolTip.init();">', $tpl_main);
}

if ($modern_bbcode_enabled)
{
	$tpl_main = str_replace('<body onload="', $htmlvar. "\n" .'<body onClick="documentClickHandler(event.target);" onload="fixOperaWidth(); ToolTip.init();', $tpl_main);
	$tpl_main = str_replace('<body>', $htmlvar. "\n" .'<body onClick="documentClickHandler(event.target);" onload="fixOperaWidth(); ToolTip.init();">', $tpl_main);
}

$tpl_main = str_replace('<body>', $htmlvar. "\n" .'<body>', $tpl_main);
// END SUBST - <body>

// START SUBST - <pun_page>
$tpl_main = str_replace('<pun_page>', htmlspecialchars(basename($_SERVER['PHP_SELF'], '.php')), $tpl_main);
// END SUBST - <pun_page>

// START SUBST - <pun_headimg>
$taglines = array(
	'Fridey Nights',
	'Spooky Saturday!',
	'"Welcome to RHQ! Leave your soul at the door."',
	'Buy ptown an iPad 6',
	'get rhq gold today!',
	':^)',
	'Smegmas',
	'Creditdawg ddosed the forums!!!',
	'"Dark Side of the Moon"',
	'EYYY PTOWN!',
	'Now with more League of Legends!',
	'Now with 20% more shitposting',
	'"I blame ptown for exposing me to this!"',
	'"I blame Rocks for exposing me to this!"',
	'You don\'t even have to come!',
	'Hellbanned :)',
	'Smashing Sweet Series!',
	'my pussy tasted like pepsi-cola',
	'flappy '.strtolower($pun_user['username']),
);


/*$taglines = array(
	'Merry Kwanza!',
	'Happy Christmas to you!',
	'"This Santa sucks my dick" -Goomba',
	'"Jingle my Balls" -Moja',
	'Sleigh Bells roasting over an open fire!',
	'Ho, Ho, Ho',
);
*/

$holiday_month = array(
	'October'		=> 'halloween',
	'December'		=> 'christmas',
);

$holiday_monthday = array(
	'September10'	=> 'dbz',
);

$holiday_day = array(
	'Fri'			=> 'frideynight',
	'Sat'			=> 'halloween',
);

$holiday_srcs = array(
	'valentines' => array(
		'',
	),

	'halloween' => array(
		'veryscary.png',
		'skeletonmoves.gif',
		'4spooky2.gif',
		'4spooky.gif',
		'scarydancing.gif',
		'2spookie.png',
	),

	'christmas' => array(
		'santa.png',
	),

	'frideynight' => array(
		'frideynight.jpg',
	),

	'dbz' => array(
		'dbzkinect.gif',
		'vegetadance.gif',
		'uhhnoname.gif',
	),
);

if ( !isset($cur_holiday) && isset($holiday_monthday[ date( 'Fj' ) ]) ) {
	$cur_holiday = $holiday_monthday[ date( 'Fj' ) ];
}

if ( !isset($cur_holiday) && isset($holiday_month[ date( 'F' ) ]) ) {
	$cur_holiday = $holiday_month[ date( 'F' ) ];
}

if ( !isset($cur_holiday) && isset($holiday_day[ date( 'D' ) ]) ) {
	$cur_holiday = $holiday_day[ date( 'D' ) ];
}

if ( isset($cur_holiday) && ( $cur_holiday != 'none' ) ) {
	if ( $holiday_srcs[ $cur_holiday ] ) {
		$cur_src_img = array_rand( $holiday_srcs[ $cur_holiday ], 1 );

		$tpl_main = str_replace('<pun_headimg>', '<span style="margin: 4px 0px 0px 2px; height: 160px; float: left;"><img height="160" src="img/'. $cur_holiday .'/'. $holiday_srcs[ $cur_holiday ][ $cur_src_img ] .'"></span><span style="margin: 4px 2px 0px 0px; height: 160px; float: right;"><img class="flipleft" height="160" src="img/'. $cur_holiday .'/'. $holiday_srcs[ $cur_holiday ][ $cur_src_img ] .'"></span>', $tpl_main);
	}
}
// END SUBST - <pun_headimg>

// START SUBST - <pun_title>
$tpl_main = str_replace('<pun_title>', '<center><a href="index.php"><img src="img/rhqlogo2.png"></a><div class="tagline">'. $taglines[ array_rand( $taglines, 1 ) ] .'</div></center></br><h1><a href="index.php">'.pun_htmlspecialchars($pun_config['o_board_title']).'</a></h1>', $tpl_main);
// END SUBST - <pun_title>


// START SUBST - <pun_desc>
$tpl_main = str_replace('<pun_desc>', '<div id="brddesc">'.$pun_config['o_board_desc'].'</div>', $tpl_main);
// END SUBST - <pun_desc>


// START SUBST - <pun_navlinks>
$links = array();

// Index should always be displayed
$links[] = '<li id="navindex"'.((PUN_ACTIVE_PAGE == 'index') ? ' class="isactive"' : '').'><a href="index.php">'.$lang_common['Index'].'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_view_users'] == '1')
	$links[] = '<li id="navuserlist"'.((PUN_ACTIVE_PAGE == 'userlist') ? ' class="isactive"' : '').'><a href="userlist.php">'.$lang_common['User list'].'</a></li>';

if ($pun_config['o_rules'] == '1' && (!$pun_user['is_guest'] || $pun_user['g_read_board'] == '1' || $pun_config['o_regs_allow'] == '1'))
	$links[] = '<li id="navrules"'.((PUN_ACTIVE_PAGE == 'rules') ? ' class="isactive"' : '').'><a href="misc.php?action=rules">'.$lang_common['Rules'].'</a></li>';

if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	$links[] = '<li id="navsearch"'.((PUN_ACTIVE_PAGE == 'search') ? ' class="isactive"' : '').'><a href="search.php">'.$lang_common['Search'].'</a></li>';

if ($pun_user['is_guest'])
{
	$links[] = '<li id="navregister"'.((PUN_ACTIVE_PAGE == 'register') ? ' class="isactive"' : '').'><a href="register.php">'.$lang_common['Register'].'</a></li>';
	$links[] = '<li id="navlogin"'.((PUN_ACTIVE_PAGE == 'login') ? ' class="isactive"' : '').'><a href="login.php">'.$lang_common['Login'].'</a></li>';
}
else
{
	$links[] = '<li id="navprofile"'.((PUN_ACTIVE_PAGE == 'profile') ? ' class="isactive"' : '').'><a href="profile.php?id='.$pun_user['id'].'">'.$lang_common['Profile'].'</a></li>';

	if ($pun_user['is_admmod'])
		$links[] = '<li id="navadmin"'.((PUN_ACTIVE_PAGE == 'admin') ? ' class="isactive"' : '').'><a href="admin_index.php">'.$lang_common['Admin'].'</a></li>';

	$links[] = '<li id="navlogout"><a href="login.php?action=out&amp;id='.$pun_user['id'].'&amp;csrf_token='.pun_hash($pun_user['id'].pun_hash(get_remote_address())).'">'.$lang_common['Logout'].'</a></li>';
}

// Are there any additional navlinks we should insert into the array before imploding it?
if ($pun_user['g_read_board'] == '1' && $pun_config['o_additional_navlinks'] != '')
{
	if (preg_match_all('%([0-9]+)\s*=\s*(.*?)\n%s', $pun_config['o_additional_navlinks']."\n", $extra_links))
	{
		// Insert any additional links into the $links array (at the correct index)
		$num_links = count($extra_links[1]);
		for ($i = 0; $i < $num_links; ++$i)
			array_splice($links, $extra_links[1][$i], 0, array('<li id="navextra'.($i + 1).'">'.$extra_links[2][$i].'</li>'));
	}
}

require PUN_ROOT.'include/pms/functions_navlinks2.php';

$tpl_temp = '<div id="brdmenu" class="inbox">'."\n\t\t\t".'<ul>'."\n\t\t\t\t".implode("\n\t\t\t\t", $links)."\n\t\t\t".'</ul>'."\n\t\t".'</div>';
$tpl_main = str_replace('<pun_navlinks>', $tpl_temp, $tpl_main);
// END SUBST - <pun_navlinks>


// START SUBST - <pun_status>
$page_statusinfo = $page_topicsearches = array();

if ($pun_user['is_guest'])
	$page_statusinfo = '<p class="conl">'.$lang_common['Not logged in'].'</p>';
else
{
	$page_statusinfo[] = '<li><span>'.$lang_common['Logged in as'].' <strong>'.pun_htmlspecialchars($pun_user['username']).'</strong></span></li>';
	$page_statusinfo[] = '<li><span>'.sprintf($lang_common['Last visit'], format_time($pun_user['last_visit'])).'</span></li>';
	$page_statusinfo[] = show_user_awards($pun_user, false);

	require PUN_ROOT.'include/pms/header_new_messages.php';

	if ($pun_user['is_admmod'])
	{
		if ($pun_config['o_report_method'] == '0' || $pun_config['o_report_method'] == '2')
		{
			$result_header = $db->query('SELECT 1 FROM '.$db->prefix.'reports WHERE zapped IS NULL') or error('Unable to fetch reports info', __FILE__, __LINE__, $db->error());

			if ($db->result($result_header))
				$page_statusinfo[] = '<li class="reportlink"><span><strong><a href="admin_reports.php">'.$lang_common['New reports'].'</a></strong></span></li>';
		}

		if ($pun_config['o_maintenance'] == '1')
			$page_statusinfo[] = '<li class="maintenancelink"><span><strong><a href="admin_options.php#maintenance">'.$lang_common['Maintenance mode enabled'].'</a></strong></span></li>';
	}

	if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
	{
		$page_topicsearches[] = '<a href="search.php?action=show_replies" title="'.$lang_common['Show posted topics'].'">'.$lang_common['Posted topics'].'</a>';
		$page_topicsearches[] = '<a href="search.php?action=show_new" title="'.$lang_common['Show new posts'].'">'.$lang_common['New posts header'].'</a>';
	}
}

// Quick searches
if ($pun_user['g_read_board'] == '1' && $pun_user['g_search'] == '1')
{
	$page_topicsearches[] = '<a href="search.php?action=show_recent" title="'.$lang_common['Show active topics'].'">'.$lang_common['Active topics'].'</a>';
	$page_topicsearches[] = '<a href="search.php?action=show_unanswered" title="'.$lang_common['Show unanswered topics'].'">'.$lang_common['Unanswered topics'].'</a>';
}


// Generate all that jazz
$tpl_temp = '<div id="brdwelcome" class="inbox">';

// The status information
if (is_array($page_statusinfo))
{
	$tpl_temp .= "\n\t\t\t".'<ul class="conl">';
	$tpl_temp .= "\n\t\t\t\t".implode("\n\t\t\t\t", $page_statusinfo);
	$tpl_temp .= "\n\t\t\t".'</ul>';
}
else
	$tpl_temp .= "\n\t\t\t".$page_statusinfo;

// Generate quicklinks
if (!empty($page_topicsearches))
{
	$tpl_temp .= "\n\t\t\t".'<ul class="conr">';
	$tpl_temp .= "\n\t\t\t\t".'<li><span>'.$lang_common['Topic searches'].' '.implode(' | ', $page_topicsearches).'</span></li>';
	$tpl_temp .= "\n\t\t\t".'</ul>';
}

$tpl_temp .= "\n\t\t\t".'<div class="clearer"></div>'."\n\t\t".'</div>';

$tpl_main = str_replace('<pun_status>', $tpl_temp, $tpl_main);
// END SUBST - <pun_status>

// START SUBST - <pun_announcement>
if ($pun_user['g_read_board'] == '1') {
	ob_start();

	if ($pun_config['o_announcement'] == '1') {
	?>
		<div id="announce" class="block">
			<div class="hd"><h2><span><?php echo $lang_common['Announcement'] ?></span></h2></div>
			<div class="box">
				<div id="announce-block" class="inbox">
					<div class="usercontent"><?php echo $pun_config['o_announcement_message'] ?></div>
				</div>
			</div>
		</div>
	<?php
	}

if ($pun_user['is_guest']) {
?>
	<div id="announce" class="block">
		<div class="box">
			<div class="inbox" style="font-size:16px;">
				<center><p>Hey guest, why not log in Rock Hard Quarry and take the advantage to post here.<br>You can't take advantage of that staying as a guest! <a href="login.php">Login Now!</a></p></center>
			</div>
		</div>
	</div>
<?php
}
	$tpl_temp = trim(ob_get_contents());
	$tpl_main = str_replace('<pun_announcement>', $tpl_temp, $tpl_main);
	ob_end_clean();
}
else
	$tpl_main = str_replace('<pun_announcement>', '', $tpl_main);
// END SUBST - <pun_announcement>


// START SUBST - <pun_main>
ob_start();


define('PUN_HEADER', 1);