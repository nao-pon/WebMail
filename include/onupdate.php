<?php
function xoops_module_update_WebMail ( $module ) {
	$db =& Database::getInstance();

	$table = $db->prefix('webmail_contactbook');
	if (! $db->query('SELECT * FROM ' . $table)) {
		$db->query('RENAME TABLE `'.$db->prefix('contactbook').'` TO `'.$table.'`');
	}

	$table = $db->prefix('webmail_popsettings');
	if (! $db->query('SELECT * FROM ' . $table)) {
		$db->query('RENAME TABLE `'.$db->prefix('popsettings').'` TO `'.$table.'`');
	}

	$table = $db->prefix('webmail_sign');
	if (! $db->query('SELECT * FROM ' . $table)) {
		$db->query('RENAME TABLE `'.$db->prefix('wmail_sign').'` TO `'.$table.'`');
	}

	$table = $db->prefix('webmail_userpref');
	if (! $db->query('SELECT * FROM ' . $table)) {
		$query = <<<EOD
CREATE TABLE `{$table}` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `key` varchar(255) binary NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `uid` (`uid`),
  KEY `key` (`key`)
) TYPE=MyISAM;
EOD;
		$db->query($query);
	}

	return true;

}