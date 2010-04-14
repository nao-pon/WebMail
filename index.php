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
include ("version.php");
$webmail_var = "1.02 ( J{$webmail_jver} )";
$webmail_credits = "
Das Gererstorfer Net
<a href='http://gererstorfer.net/'>
http://gererstorfer.net/
</a><br />
Japanese(J1-{$webmail_jver}) edit by:nao-pon
<a href='http://xoops.hypweb.net/'>
http://xoops.hypweb.net/</a>";

include("../../mainfile.php");

// 非ログインユーザーはログイン画面へ
if (!is_object($xoopsUser))
{
	redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
	exit();
}
define("XOOPS_MODULE_WEBMAIL_LOADED",1);

if (! include("cache/config.php")) {
	redirect_header(XOOPS_URL . '/modules/WebMail/admin/index.php', 0, 'Go to settings.');
	exit();
}

$xoopsOption['show_rblock'] = ($show_right==true)? 1 : 0 ;

include(XOOPS_ROOT_PATH."/header.php");

$userid = $xoopsUser->uid();
$username = $xoopsUser->uname();
$sitename = htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES);

// classes include
include_once ("pop3.php");
include_once ("decodemessage.php");
include_once ("class.rc4crypt.php");


include ("mailheader.php");
// nao-pon
include ("catch_clr.php");
catch_clr($download_dir);
catch_clr($attachmentdir);

$action = (empty($_GET['action']))? "" : $_GET['action'];
//

if ($numaccounts == -1 OR $numaccounts > 1) {
    $welcome_msg = _MD_WEBMAIL_MAILWELCOME1;
} elseif ($numaccounts == 1) {
    $welcome_msg = _MD_WEBMAIL_MAILWELCOME2;
}

$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";
    	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}

if (!function_exists('mb_convert_encoding')) {
    OpenTable();
    echo "<table width=\"95%\" border=\"0\" align=\"center\"><tr><td>"
	."<b>"._MD_WEBMAIL_MAILWELCOME3." $sitename!</b>"
        ."<br><br>Warning: Since mbstring of this server's PHP is not enable, WebMail does not operate."
        ."</td></tr></table>";
    CloseTable();
    include(XOOPS_ROOT_PATH."/footer.php");
    return;
}

if ($xoopsDB->getRowsNum($result) < 1) {
    OpenTable();
    echo "<table width=\"95%\" border=\"0\" align=\"center\"><tr><td>"
	."<b>"._MD_WEBMAIL_MAILWELCOME3." $sitename!</b><br><br>"
        .""._MD_WEBMAIL_CLICKONSETTINGS."<br><br>$welcome_msg"
        ."</td></tr></table>";
    CloseTable();
    include(XOOPS_ROOT_PATH."/footer.php");
    return;
}

echo "<script language=javascript>
    function mailbox(num) {
	formname = 'inbox' + num;
	window.document.forms[formname].submit();
    }
    </script>";
$count = 0;
if ($action == "list"){
	OpenTable();
	echo "<center><b>"._MD_WEBMAIL_MAILBOXESFOR." $username</b></center>";
	echo "<br><table border=\"1\" align=\"center\" width=\"80%\">"
	    ."<tr class='bg2'><td bgcolor=\"$bgcolor2\" width=\"33%\">&nbsp;<b>"._MD_WEBMAIL_ACCOUNT."</b></td><td bgcolor=\"$bgcolor2\" width=\"33%\" align=\"center\">&nbsp;<b>"._MD_WEBMAIL_EMAILS."</b></td><td bgcolor=\"$bgcolor2\" width=\"33%\" align=\"center\">&nbsp;<b>"._MD_WEBMAIL_TOTALSIZE."</b></td></tr>";
	while ($row = $xoopsDB->fetchArray($result) ) {
	    $count++;
	    $server = $row[popserver];
	    $port = $row[port];
	    $apop = $row[apop];
	    $username = $row[uname];
	    $rc4 = new rc4crypt();
	    $password = $rc4->endecrypt($username,$row[passwd],"de");
	    $account = $row[account];
	    $serverid = $row[id];
	    $pop3=new POP3($server,$username,$password,$port,$apop);
	    if ($pop3->Open()){
		    $stats = $pop3->Stats();
		    $mailsum = $stats["message"];
		    $mailmem = round($stats["size"]/1024)." Kbytes";
	    	$pop3->Close();
		} else {
			$mailsum = "Not Connectable!";
		    $mailmem = "N/A";
		}
	    echo "<tr>"
		."<td align=\"left\">&nbsp;"
		."<a href='inbox.php?id=$serverid'>$account</a></td>"
	        ."<td align=\"center\">$mailsum</td>"
	        ."<td align=\"center\">$mailmem</td></tr>";
	}
	echo "</table><br><br>"
	    ."<center>"._MD_WEBMAIL_SELECTACCOUNT."</center>";

	CloseTable();
}
echo "<div align='right'>".$xoopsModule->name()." Var. ".$webmail_var."<br />".$webmail_credits."</div>";
include(XOOPS_ROOT_PATH."/footer.php");
