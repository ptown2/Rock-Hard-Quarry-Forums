<?php

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

//
// Format time in seconds to display as hours/days/months/never
//
function format_expiration_time($seconds)
{
	global $lang_warnings;

	$seconds = intval($seconds);

	if ($seconds <= 0)
	{
		//never
		return $lang_warnings['Never'];
	}
	else if ($seconds % (30*24*60*60) == '0')
	{
		//is months
		$expiration_value = $seconds / (30*24*60*60);
		return sprintf($lang_warnings['No of months'], $expiration_value);
	}
	else if ($seconds % (24*60*60) == '0')
	{
		//is days
		$expiration_value = $seconds / (24*60*60);
		return sprintf($lang_warnings['No of days'], $expiration_value);
	}
	else
	{
		//is hours
		$expiration_value = $seconds / (60*60);
		return sprintf($lang_warnings['No of hours'], $expiration_value);
	}
}


//
// Get expiration time (in seconds)
//
function get_expiration_time($value = 0, $unit)
{
	$value = abs(intval($value));

	if ($value == '0')
		$expiration_time = 0;
	else if ($unit == 'hours')
		$expiration_time = $value*60*60;
	else if ($unit == 'days')
		$expiration_time = $value*24*60*60;
	else if ($unit == 'months')
		$expiration_time = $value*30*24*60*60;
	else if ($unit == 'never')
		$expiration_time = 0;
	else 
		$expiration_time = 0;

	return $expiration_time;
}

?>
