<?php
/**
 *
 * Replaces 'Joined' with 'Registered for' membership timespan in viewtopic miniprofile. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, rxu, https://www.phpbbguru.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(

	'D_SECONDS'  => array(
		1	=> '%d second',
		2	=> '%d seconds',
	),
	'D_MINUTES'  => array(
		1	=> '%d minute',
		2	=> '%d minutes',
	),
	'D_HOURS'    => array(
		1	=> '%d hour',
		2	=> '%d hours',
	),
	'D_MDAY'     => array(
		1	=> '%d day',
		2	=> '%d days',
	),
	'D_MON'      => array(
		1	=> '%d month',
		2	=> '%d months',
	),
	'D_YEAR'     => array(
		1	=> '%d year',
		2	=> '%d years',
	),

	'REGISTEREDFOR'	=> 'Registered for',
));
