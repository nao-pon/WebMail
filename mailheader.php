<?php

/*************************************************************************/
 #  Mailbox 0.9.2a   by Sivaprasad R.L (http://netlogger.net)             #
 #  eMailBox 0.9.3   by Don Grabowski  (http://ecomjunk.com)              #
 #          --  A pop3 client addon for phpnuked websites --              #
 #                                                                        #
 # This program is distributed in the hope that it will be useful,        #
 # but WITHOUT ANY WARRANTY; without even the implied warranty of         #
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          #
 # GNU General Public License for more details.                           #
 #                                                                        #
 # You should have received a copy of the GNU General Public License      #
 # along with this program; if not, write to the Free Software            #
 # Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.              #
 #                                                                        #
 #             Copyright (C) by Sivaprasad R.L                            #
 #            Script completed by Ecomjunk.com 2001                       #
/*************************************************************************/

// ------------------------------------------------------------------------ //
// Portet to XOOPS                                                          //
// Modified 21.11.2002                                                      //
// by Jochen Gererstorfer                                                   //
// http://gererstorfer.net                                                  //
// webmaster@gererstorfer.net                                               //
// ------------------------------------------------------------------------ //

if (!defined("XOOPS_MODULE_WEBMAIL_LOADED")) exit;

//mb_string ini set by nao-pon
ini_set("output_buffering","Off");
ini_set("default_charset",_CHARSET);
ini_set("mbstring.language","Japanese");
ini_set("mbstring.encoding_translation","off");
ini_set("mbstring.http_input","Auto");
ini_set("mbstring.http_output",_CHARSET);
ini_set("mbstring.internal_encoding",_CHARSET);
ini_set("mbstring.substitute_character"," ");

error_reporting(E_ERROR);

global $xoopsDB, $xoopsUser;
$userid = $xoopsUser->uid();
$username = $xoopsUser->uname();
$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";

if(!$result=$xoopsDB->query($query)){
	echo "ERROR";
}

$id = (empty($_GET['id']))? "" : (int)$_GET['id'];
if (!$id) {$id = (empty($_POST['id']))? "" : (int)$_POST['id'];}

//OpenTable();
echo "<table align=\"center\" width=\"100%\"><tr><td class='bg2'><b><font class=\"title\"><center>"._MD_WEBMAIL_MAINMENU."</center></font></b></td></tr></table>"
    .""
    ."<table align=\"center\" width=\"100%\"><tr><td width=\"15%\" align=\"center\">";

$mailing = "images/mailbox.gif";
echo "<a href='index.php?action=list'><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_MAILBOX."\" title=\""._MD_WEBMAIL_MAILBOX."\"></a></td>";

if ($email_send == 1) {
    $mailing = "images/compose.gif";
    echo "<td align=\"center\" width=\"15%\">"
	."<a href='compose.php".($id? "?id=$id" : '')."'><img src=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_COMPOSE."\" title=\""._MD_WEBMAIL_COMPOSE."\"></a></td>";
}

$mailing = "images/settings.gif";
echo "<td width=\"15%\" align=\"center\">"
    ."<a href='settings.php'><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_SETTINGS."\" title=\""._MD_WEBMAIL_SETTINGS."\"></a></td>";

$mailing = "images/contact.gif";
echo "<td align=\"center\" width=\"15%\">"
    ."<a href='contactbook.php'><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_ADDRESSBOOK."\" title=\""._MD_WEBMAIL_ADDRESSBOOK."\"></a></td>";

$mailing = "images/search.gif";
echo "<td width=\"15%\" align=\"center\">"
    ."<a href='contactbook.php?op=search'><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_SEARCHCONTACT."\" title=\""._MD_WEBMAIL_SEARCHCONTACT."\"></a></td>"
    ."<td width=\"15%\" align=\"center\">";

$mailing = "images/logout.gif";
//if (is_user($user) AND is_active("Your_Account")) {
//    echo "<a href=\"modules.php?name=Your_Account\"><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_EXIT."\" title=\""._MD_WEBMAIL_EXIT."\"></a></td>";
//} else {
    echo "<a href='../../index.php'><IMG SRC=".$mailing." border=\"0\" alt=\""._MD_WEBMAIL_EXIT."\" title=\""._MD_WEBMAIL_EXIT."\"></a></td>";
//}
echo "<tr>"
    ."<td align=\"center\">"._MD_WEBMAIL_MAILBOX."</td>";
if ($email_send == 1) {
    echo "<td align=\"center\">"._MD_WEBMAIL_COMPOSE."</td>";
}
echo "<td align=\"center\">"._MD_WEBMAIL_SETTINGS."</td>"
    ."<td align=\"center\">"._MD_WEBMAIL_ADDRESSBOOK."</td>"
    ."<td align=\"center\">"._MD_WEBMAIL_SEARCHCONTACT."</td>"
    ."<td align=\"center\">"._MD_WEBMAIL_EXIT."</td></tr>"
    ."</table>";

echo "<div align='center'>
	<form action='inbox.php' method='post'>
	<select name='id'>
";
$i=1;
while ($row = $xoopsDB->fetchArray($result) ) {
	if (($id == $row[id]) || (!$id && $i == 1)) {
		echo "<option value='$row[id]' selected>$row[account]</option>";
	} else {
		echo "<option value='$row[id]'>$row[account]</option>";
	}
	$i++;
}
echo "</select><input type='submit' value='"._MD_WEBMAIL_VIEW_INBOX."'></select></form></div>";
//CloseTable();
echo "<br />";

