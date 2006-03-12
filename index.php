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
$webmail_var = "1.02 (J1.5)";
$webmail_credits = "
Das Gererstorfer Net 
<a href='http://gererstorfer.net/'>
http://gererstorfer.net/
</a><br />
Japanese(J1-1.3) edit by:nao-pon 
<a href='http://hypweb.net/'>
http://hypweb.net/</a>";

include("../../mainfile.php");
include_once("cache/config.php");

if($show_right==true)
{
	$xoopsOption['show_rblock'] =1;
}
else
{
	$xoopsOption['show_rblock'] =0;
}

include(XOOPS_ROOT_PATH."/header.php");

global $xoopsDB, $xoopsUser;
// 非ログインユーザーはログイン画面へ
if (!is_object($xoopsUser))
{
	redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
	exit();
}
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
    $welcome_msg = _MAILWELCOME1;
} elseif ($numaccounts == 1) {
    $welcome_msg = _MAILWELCOME2;
}

$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";
    	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}

if (!function_exists('mb_convert_encoding')) {
    OpenTable();
    echo "<table width=\"95%\" border=\"0\" align=\"center\"><tr><td>"
	."<b>"._MAILWELCOME3." $sitename!</b>"
        ."<br><br>Warning: Since mbstring of this server's PHP is not enable, WebMail does not operate."
        ."</td></tr></table>";
    CloseTable();
    include(XOOPS_ROOT_PATH."/footer.php");
    return;
} 

if ($xoopsDB->getRowsNum($result) < 1) {
    OpenTable();
    echo "<table width=\"95%\" border=\"0\" align=\"center\"><tr><td>"
	."<b>"._MAILWELCOME3." $sitename!</b><br><br>"
        .""._CLICKONSETTINGS."<br><br>$welcome_msg"
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
	echo "<center><b>"._MAILBOXESFOR." $username</b></center>";
	echo "<br><table border=\"1\" align=\"center\" width=\"80%\">"
	    ."<tr class='bg2'><td bgcolor=\"$bgcolor2\" width=\"33%\">&nbsp;<b>"._ACCOUNT."</b></td><td bgcolor=\"$bgcolor2\" width=\"33%\" align=\"center\">&nbsp;<b>"._EMAILS."</b></td><td bgcolor=\"$bgcolor2\" width=\"33%\" align=\"center\">&nbsp;<b>"._TOTALSIZE."</b></td></tr>";
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
	    ."<center>"._SELECTACCOUNT."</center>";

	CloseTable();
}
echo "<div align='right'>".$xoopsModule->name()." Var. ".$webmail_var."<br />".$webmail_credits."</div>";
include(XOOPS_ROOT_PATH."/footer.php");
?>