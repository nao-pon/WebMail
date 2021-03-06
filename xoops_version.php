<?php
$modversion['name'] = _MI_WEBMAIL2_NAME;
$modversion['dirname'] = "WebMail";
include(XOOPS_ROOT_PATH."/modules/".$modversion['dirname']."/version.php");
$modversion['version'] = "$webmail_jver";
$modversion['description'] = "POP3 Client";
$modversion['author'] = "Jochen Gererstorfer<br />( http://gererstorfer.net/ )";
$modversion['credits'] = "Das Gererstorfer Net <a href='http://gererstorfer.net/'>http://gererstorfer.net/</a><br />And<br />Japanese(J1-1.3) edit by:nao-pon <a href='http://hypweb.net/'>http://hypweb.net/</a>";
$modversion['help'] = "";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 0;
$modversion['image'] = "images/webmail_slogo.gif";

// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = "contactbook";
$modversion['tables'][1] = "popsettings";
$modversion['tables'][2] = "wmail_sign";

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Menu
$modversion['hasMain'] = 1;


$modversion['onUpdate'] = 'include/onupdate.php' ;