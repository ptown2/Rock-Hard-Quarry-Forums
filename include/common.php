<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN_ROOT'))
	exit('The constant PUN_ROOT must be defined and point to a valid FluxBB installation root directory.');

// Define the version and database revision that this code was written for
define('FORUM_VERSION', '1.4.12');

define('FORUM_DB_REVISION', 15);
define('FORUM_SI_REVISION', 2);
define('FORUM_PARSER_REVISION', 2);

// Block prefetch requests
if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
{
	header('HTTP/1.1 403 Prefetching Forbidden');

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	exit;
}

// Attempt to load the configuration file config.php
if (file_exists(PUN_ROOT.'config.php'))
	require PUN_ROOT.'config.php';

// If we have the 1.3-legacy constant defined, define the proper 1.4 constant so we don't get an incorrect "need to install" message
if (defined('FORUM'))
	define('PUN', FORUM);

// Load the functions script
require PUN_ROOT.'include/functions.php';

// Load UTF-8 functions
require PUN_ROOT.'include/utf8/utf8.php';

// Strip out "bad" UTF-8 characters
forum_remove_bad_characters();

// Reverse the effect of register_globals
forum_unregister_globals();

// If PUN isn't defined, config.php is missing or corrupt
if (!defined('PUN'))
{
	header('Location: install.php');
	exit;
}

// Record the start time (will be used to calculate the generation time for the page)
$pun_start = get_microtime();

// Make sure PHP reports all errors except E_NOTICE. FluxBB supports E_ALL, but a lot of scripts it may interact with, do not
error_reporting(E_ALL ^ E_NOTICE);

// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

// Strip slashes from GET/POST/COOKIE/REQUEST/FILES (if magic_quotes_gpc is enabled)
if (!defined('FORUM_DISABLE_STRIPSLASHES') && get_magic_quotes_gpc())
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
	$_REQUEST = stripslashes_array($_REQUEST);
	if (is_array($_FILES))
	{
		// Don't strip valid slashes from tmp_name path on Windows
		foreach ($_FILES AS $key => $value)
			$_FILES[$key]['tmp_name'] = str_replace('\\', '\\\\', $value['tmp_name']);
		$_FILES = stripslashes_array($_FILES);
	}
}

// If a cookie name is not specified in config.php, we use the default (pun_cookie)
if (empty($cookie_name))
	$cookie_name = 'pun_cookie';

// If the cache directory is not specified, we use the default setting
if (!defined('FORUM_CACHE_DIR'))
	define('FORUM_CACHE_DIR', PUN_ROOT.'cache/');

// Define a few commonly used constants
define('PUN_UNVERIFIED', 0);
define('PUN_ADMIN', 1);
define('PUN_MOD', 2);
define('PUN_GUEST', 3);
define('PUN_MEMBER', 4);
define('PUN_GLMOD', 5);
define('PUN_DONATOR', 7);

// Load DB abstraction layer and connect
require PUN_ROOT.'include/dblayer/common_db.php';

// Start a transaction
$db->start_transaction();

// Load cached config
if (file_exists(FORUM_CACHE_DIR.'cache_config.php'))
	include FORUM_CACHE_DIR.'cache_config.php';

if (!defined('PUN_CONFIG_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_config_cache();
	require FORUM_CACHE_DIR.'cache_config.php';
}

// Verify that we are running the proper database schema revision
if (!isset($pun_config['o_database_revision']) || $pun_config['o_database_revision'] < FORUM_DB_REVISION ||
	!isset($pun_config['o_searchindex_revision']) || $pun_config['o_searchindex_revision'] < FORUM_SI_REVISION ||
	!isset($pun_config['o_parser_revision']) || $pun_config['o_parser_revision'] < FORUM_PARSER_REVISION ||
	version_compare($pun_config['o_cur_version'], FORUM_VERSION, '<'))
{
	header('Location: db_update.php');
	exit;
}

// Enable output buffering
if (!defined('PUN_DISABLE_BUFFERING'))
{
	// Should we use gzip output compression?
	if ($pun_config['o_gzip'] && extension_loaded('zlib'))
		ob_start('ob_gzhandler');
	else
		ob_start();
}

// Define standard date/time formats
$forum_time_formats = array($pun_config['o_time_format'], 'H:i:s', 'H:i', 'g:i:s a', 'g:i a');
$forum_date_formats = array($pun_config['o_date_format'], 'Y-m-d', 'Y-d-m', 'd-m-Y', 'm-d-Y', 'M j Y', 'jS M Y');

// Check/update/set cookie and fetch user info
$pun_user = array();
check_cookie($pun_user);

// Attempt to load the common language file
if (file_exists(PUN_ROOT.'lang/'.$pun_user['language'].'/common.php'))
	include PUN_ROOT.'lang/'.$pun_user['language'].'/common.php';
else
	error('There is no valid language pack \''.pun_htmlspecialchars($pun_user['language']).'\' installed. Please reinstall a language of that name');

// Check if we are to display a maintenance message
if ($pun_config['o_maintenance'] && $pun_user['g_id'] > PUN_ADMIN && !defined('PUN_TURN_OFF_MAINT'))
	maintenance_message();

// Load cached bans
if (file_exists(FORUM_CACHE_DIR.'cache_bans.php'))
	include FORUM_CACHE_DIR.'cache_bans.php';

if (!defined('PUN_BANS_LOADED'))
{
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';

	generate_bans_cache();
	require FORUM_CACHE_DIR.'cache_bans.php';
}

// Check if current user is banned
check_bans();

// Update online list
update_users_online();

// Google Recaptcha v2
define('CAPTCHA_SITEKEY', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('CAPTCHA_SECRET', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

// Check to see if we logged in without a cookie being set
if ($pun_user['is_guest'] && isset($_GET['login']))
	message($lang_common['No cookie']);

// The maximum size of a post, in bytes, since the field is now MEDIUMTEXT this allows ~16MB but lets cap at 1MB...
if (!defined('PUN_MAX_POSTSIZE'))
	define('PUN_MAX_POSTSIZE', 1048576);

if (!defined('PUN_SEARCH_MIN_WORD'))
	define('PUN_SEARCH_MIN_WORD', 3);
if (!defined('PUN_SEARCH_MAX_WORD'))
	define('PUN_SEARCH_MAX_WORD', 20);

if (!defined('FORUM_MAX_COOKIE_SIZE'))
	define('FORUM_MAX_COOKIE_SIZE', 4048);

//
// Arcade Definitions
//

// Define some files for the Games
$game_file_trans = array(
	// Extension to Folder
	'swf'		=> 'flash',
	'unity3d'	=> 'unity',
	'jar'		=> 'java',

	// MIME Type Names to Folder
	'application/x-shockwave-flash' => 'flash',

	// Folder to Extension
	'flash'	=> 'swf',
);

$game_type_trans = array(
	'flash' => 'SWF Flash Player',
	'unity' => 'Unity Web Player',
	'shock' => 'Macromedia Shockwave',
);

$category_game_types = array(
	'Arcade'	=> 1,
	'Action'	=> 2,
	'Adventure'	=> 3,
	'Puzzle'	=> 4,
	'Skill'		=> 5,
	'Casino/Card'	=> 6,
	'Racing'	=> 7,
	'Sports'	=> 8,

	1		=> 'Arcade',
	2		=> 'Action',
	3		=> 'Adventure',
	4		=> 'Puzzle',
	5		=> 'Skill',
	6		=> 'Casino/Card',
	7		=> 'Racing',
	8		=> 'Sports',
);

//
// BanType IDs
//

$ban_type_trans = array(
	1 => 'NormBan',
	2 => 'SuspBan',
	3 => 'HellBan',
);

//
// Award Values
//

$awards = array(
	'a_thoughtful'	=> '<img src="img/awards/thoughtful.png" title="Thoughtful Poster - Never shitposted, never will. A good poster will always be!" alt="Thoughtful" />',
	'a_loyal'	=> '<img src="img/awards/loyal.png" title="Loyal - Defended the community, the good way." alt="Loyal" width="16" height="16" />',
	'a_creative'	=> '<img src="img/awards/creative.png" title="Creative - Such talent, god speed!" alt="Creative" width="16" height="16" />',
	'a_friendly'	=> '<img src="img/awards/friendly.png" title="Friendly - So friendly, you could cuddle with it." alt="Friendly" width="16" height="16" />',
	'a_reporter'	=> '<img src="img/awards/reporter.png" title="Reporter - The RHQ Reporting hero that everyone needs!" alt="Reporter" width="16" height="16" />',
	'a_cutie'	=> '<img src="img/awards/rainbow.png" title="Cutie - If ptown didn\'t gave this to you then you\'re ugly!" alt="Cutie" width="16" height="16" />',
	'a_anime'	=> '<img src="img/awards/anime.png" title="Sempai Club - But I\'m the real sempai..." alt="Sempai Club" width="16" height="16" />',
	'a_special'	=> '<img src="img/awards/special.png" title="Special - SPECIAL!!!" alt="Special" width="16" height="16" />',
	'a_forumban'	=> '<img src="img/awards/hsnban.png" title="SND Banned - If only you didn\'t shit it enough." alt="SND Banned" width="16" height="16" />',
	'a_shitpost'	=> '<img src="img/awards/shitposter.png" title="Shitposter - Aren\'t ya a cool dude, eh?" alt="Shitposter" width="16" height="16" />',
	'a_abuse'	=> '<img src="img/awards/abuse.png" title="Admin Abused - I survived an admin attack!" alt="Admin Abused" width="16" height="16" />',
	'a_prime'	=> '<img src="img/awards/prime.png" title="Prime Number - Having your userid as a prime number. Wow, seems special?" alt="Prime Number" width="16" height="16" />',
);

$post_counts = array(
	10000		=> '<img src="img/awards/10000posts.png" title="Fanatic - Reached 10,000 posts! I need to get a life..." alt="Fanatic" />',
	5000		=> '<img src="img/awards/5000posts.png" title="Gosen - Reached 5,000! Shitposting to the power of 2!" alt="Gosen" width="16" height="16" />',
	2500		=> '<img src="img/awards/2500posts.png" title="Super VIP - Reached 2,500 posts! Need to contribute more!" alt="Super VIP" width="16" height="16" />',
	1000		=> '<img src="img/awards/1000posts.png" title="VIP -  Reached 1,000 posts! I\'m such a special person!" alt="VIP" width="16" height="16" />',
	500		=> '<img src="img/awards/500posts.png" title="Super Veteran - Reached 500 posts. Half-way to the rocks!" alt="Super Veteran" width="16" height="16" />',
	250		=> '<img src="img/awards/250posts.png" title="Veteran - Reached 250 posts. Getting there!" alt="Veteran" width="16" height="16" />',
	100		=> '<img src="img/awards/100posts.png" title="Super Contributor - Reached 100 posts. I swear I\'m not shitposting..." alt="Super Contributor" width="16" height="16" />',
	50		=> '<img src="img/awards/50posts.png" title="Contributor - Reached 50 posts. Woo!" alt="Contributor" width="16" height="16"/>',
);

$rock_holdage = array(
	//9223372036854775806		=> '<img src="img/awards/pt2mun.png" title="The Ptownaire - Reached the ultimate integer value! Cheater..." alt="The Ptownaire" />',
	10000000	=> '<img src="img/awards/10Mmun.png" title="Rock Street Wizard - Reached 10 Million Rocks! The strategy is to waste all of your money." alt="Rock Street Wizard" />',
	5000000		=> '<img src="img/awards/5Mmun.png" title="Rock Gosen - Reached 5 Million Rocks! If only I didn\'t played that much Smashing Sweets" alt="Rock Gosen" />',
	1000000		=> '<img src="img/awards/1Mmun.png" title="Rock Richie - Reached 1 Million Rocks! That must be worth 1 USD!" alt="Rock Richie" />',
	500000		=> '<img src="img/awards/500kmun.png" title="Halfie - Reached 500k Rocks!" alt="Halfie" />',
	150000		=> '<img src="img/awards/150kmun.png" title="Rock Gatherer - Reached 150k Rocks!" alt="Rock Gatherer" />',
);

//
// Social Info
//

$social_info = array(
	'steam'		=> 'http://steamcommunity.com/id/%s',
	'youtube'	=> 'http://youtube.com/%s',
	'facebook'	=> 'http://facebook.com/%s',
	'twitter'	=> 'http://twitter.com/%s',
	'aim'		=> 'aim:%s',
	'skype'		=> 'skype:%s',
	'yahoo'		=> 'mailto:%s@yahoo.com',
);