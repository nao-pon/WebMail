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
//  ------------------------------------------------------------------------ //
//  Edited 01.18.2003                                                        //
//  by nao-pon                                                               //
//  http://hypweb.net/                                                       //
//  nao-pon@hypweb.net                                                       //
//  * It corresponds to Japanese special environment.                        //
//  * JIS+MINE encoding about a header.                                      //
//  * Body text is change into JIS code.                                     //
//  * Japanese message creation.                                             //
//  * URL & Mail Automatic link.                                             //
//  ------------------------------------------------------------------------ //

include("../../mainfile.php");

// 非ログインユーザーはログイン画面へ
if (!is_object($xoopsUser))
{
	redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
	exit();
}
define("XOOPS_MODULE_WEBMAIL_LOADED",1);

include("cache/config.php");

$xoopsOption['show_rblock'] = ($show_right==true)? 1 : 0 ;

include(XOOPS_ROOT_PATH."/header.php");

//error_reporting(E_ALL);

$userid = $xoopsUser->uid();
$username = $xoopsUser->uname();

ob_start();

//parse_str(base64_decode($pop3_cookie));
require ("pop3.php");
require ("decodemessage.php");
include ("mailheader.php");
include ("class.rc4crypt.php");

$_GET = array_merge($_GET,$_POST);
$id = (isset($_GET['id']))? (int)$_GET['id'] : null;
$start = (isset($_GET['start']))? (int)$_GET['start'] : null;
$op = (isset($_GET['op']))? $_GET['op'] : null;
$msgid = (isset($_GET['msgid']))? $_GET['msgid'] : null;

if (! $id) {
	redirect_header(XOOPS_URL . '/modules/WebMail/', 1);
}

getServer($id);
@ set_time_limit(120);
$pop3=new POP3($server,$username,$password,$port,$apop);
if (!$pop3->Open()) echo "<center>Not Connectable! Please check your POP3 settings.</center><br />";

if($op == "delete") {
    if(! is_array($msgid)) {
    	$msgid = array($msgid);
    }
	foreach($msgid as $mid) {
		$mid = intval($mid);
		if ($mid) {
	    	$pop3->DeleteMessage($mid);
	    	--$start;
		}
	}
    $pop3->Close();

	$qs = '';
	if ($start > 0) {
		$qs = '&amp;start=' . $start;
	}

	ob_end_clean();
	redirect_header(XOOPS_URL . '/modules/WebMail/inbox.php?id='.$id.$qs, 1, _MD_WEBMAIL_MSG_DELETED);
	exit();
}

ob_end_flush();

$query = "select account from ".$xoopsDB->prefix("popsettings")." where id='$id' AND uid='$userid'";
$result=$xoopsDB->query($query, 0, 1);
$row = $xoopsDB->fetchArray($result);
$account = $row[account];

$s = $pop3->Stats();
$mailsum = $s["message"];

if ($mailsum) {
	if (is_null($start) || $mailsum < $start) {
		$start = $upperlimit = $mailsum;
	} else {
		$upperlimit = $start;
	}
	$lowerlimit = $upperlimit - $numshow;
	if ($lowerlimit < 0) $lowerlimit = 0;
	$showstart =  $mailsum - $upperlimit + 1;
	$showend = $mailsum - $lowerlimit;
	echo "<form action=inbox.php method=post>
	    <input type=hidden name=id value=$id>
	    <input type=hidden name=start value=$start>
	    <input type=hidden name=op value='delete'>";
	OpenTable();
	echo "<center><b>$account: "._MD_WEBMAIL_EMAILINBOX."</b></center><br /><br />";
	echo "<table border=\"0\" width=100%>"
	    ."<tr class='bg2'>"
	    ."<td width=\"4%\" bgcolor=\"$bgcolor2\">&nbsp;</td>"
	    ."<td width=\"25%\" bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_FROM."</b></td>"
	    ."<td width=\"51%\" bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_SUBJECT."</b></font></td>"
	    ."<td width=\"6%\" bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_SIZE."</b></font></td>"
	    ."<td width=\"14%\" bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_DATE."</b></font></td>"
	    ."</tr>";

	//nao-pon
	$ad_filters = split("[\n]+",trim($filter_subject));

	$readline = 15;

	for ($i=$upperlimit;$i>$lowerlimit;$i--) {
	    $list = $pop3->ListMessage($i,$readline);

	    $sender = ($list["sender"]["name"]) ? $list["sender"]["name"] : $list["sender"]["email"];

		//nao-pon
		$sender = mb_decode_mimeheader($sender);
		$sender = htmlspecialchars(trim($sender));

		if ($list["sender"]["name"] && ($list["sender"]["name"] != $list["sender"]["email"])) {
			$sender2 = mb_decode_mimeheader($list["sender"]["name"])."\n".mb_decode_mimeheader($list["sender"]["email"]);
		} else {
			$sender2 = mb_decode_mimeheader($list["sender"]["email"]);
		}
		$sender2 = htmlspecialchars(trim($sender2));
		$sender2 = str_replace("\n","&#13;&#10;",$sender2);
		$sender2 = str_replace(" ","&nbsp;",$sender2);

		$subject = $list["subject"];
		$subject = mb_decode_mimeheader($subject);

		//nao-pon
		$body = mb_convert_encoding($list["body"], _CHARSET, "auto");
		$bodys = split("\n",$body);
		$body = "";
		foreach ($bodys as $tmp) {
			$tmp = mb_strimwidth($tmp,0,50,"...");
			if ($tmp) $body .= $tmp."\n";
		}
		$body = htmlspecialchars(trim($body));
		$body = str_replace(" ","&nbsp;",$body);
		$body = str_replace("\n"," ",$body);

		if (function_exists('mberegi')) {
			foreach($ad_filters as $ad_filter){
				if (($ad_filter) && mberegi($ad_filter, $subject)) $is_ad = true;
			}
		} else {
			foreach($ad_filters as $ad_filter){
				if (($ad_filter) && eregi($ad_filter, $subject)) $is_ad = true;
			}
		}
		//

		if ($is_ad) {
			echo "<tr><td bgcolor=\"$bgcolor1\" height=\"24\" align=\"center\"><input type=\"checkbox\" name=\"msgid[]\" value=\"$i\" CHECKED></td>";
		} else {
			echo "<tr><td bgcolor=\"$bgcolor1\" height=\"24\" align=\"center\"><input type=\"checkbox\" name=\"msgid[]\" value=\"$i\"></td>";
		}
		$is_ad = false;

	    if ($attachments_view == 0) {
		if ($list["has_attachment"]) {
	    	    $att_exists = "&amp;attach_nv=1";
		} else {
		    $att_exists = "";
		}
	    }


	    echo "<td bgcolor=\"$bgcolor1\" height=\"24\"><a href='readmail.php?id=$id&msgid=$i$att_exists' title=$sender2>";
	    echo htmlspecialchars(mb_strimwidth($sender, 0, 25, "..."));
	//	echo htmlspecialchars(substr($sender,0,30));
	    echo "</a>";
	//	echo (mbstrlen($sender) > 15) ? "..." : "";
	    echo "</a></font></td>";
	    echo "<td bgcolor=\"$bgcolor1\"><a href='readmail.php?id=$id&msgid=$i$att_exists' title=\"$body\">";
		$subject = htmlspecialchars($subject);
	    echo rtrim($subject) ? $subject : ""._MD_WEBMAIL_NOSUBJECT."";
	    echo "</td><td bgcolor=\"$bgcolor1\">";
	    echo round($list["size"]/1024)."Kb";
	    echo $list["has_attachment"] ? "<img src='images/clip.gif' border=\"0\">" : "";
	    echo $list["is_html"] ? "<a href='readmail.php?id=$id&msgid=$i$att_exists&ht=1'><img src='images/html.gif' border=\"0\" alt="._MD_WEBMAIL_HTML_VIEW."></a>" : "";
	    echo "</td><td bgcolor=\"$bgcolor1\">";
	    echo htmlspecialchars($list["date"]);
	    echo "</font></td></tr>";
	}
	echo "</table>";
	navbuttons();
	echo "</form>";
	CloseTable();
} else {
	OpenTable();
	echo "<center><b>$account: " . _MD_WEBMAIL_EMAILINBOX . '<br /><br />';
	echo '<b>' . _MD_WEBMAIL_MSG_NO_MAIL . '</b></center>';
	CloseTable();
}
$pop3->Close();

include(XOOPS_ROOT_PATH."/footer.php");

function getServer($id) {
    global $xoopsDB, $xoopsUser, $user, $server, $port, $username, $password, $numshow, $apop;
    if(!isset($id)) {
	echo "Error: Invalid Parameter<br />";
	include(XOOPS_ROOT_PATH."/footer.php");
	exit();
    }
    $query = "Select * from ".$xoopsDB->prefix("popsettings")." where id = $id";
    if(($res = $xoopsDB->query($query)) && ($xoopsDB->getRowsNum($res) > 0)) {
	$row = $xoopsDB->fetchArray($res);
	$uid = $row[uid];
        $userid = $xoopsUser->uid();
	if($uid != $userid) {
	    echo "<center><h2>Error: Permission Denied</center>";
	    exit();
	}
	$server = $row[popserver];
	$port = $row[port];
	$apop = $row[apop];
	$username = $row[uname];
	$rc4 = new rc4crypt();
	$password = $rc4->endecrypt($username,$row[passwd],"de");
	$numshow = $row[numshow];
    } else {
	echo "Error: POP Server not set properly<br />";
	exit();
    }
}

function navbuttons() {
    global $xoopsDB, $xoopsUser, $id, $showstart, $showend, $mailsum, $upperlimit, $lowerlimit, $numshow, $module_name;
    echo "<br />"
        ."<table border=\"0\" width=\"100%\">"
        ."<tr><td width=\"15%\">"
	."<input type=\"submit\" value=\""._MD_WEBMAIL_DELETESELECTED."\"></td></tr></table>"
	."<table border=\"0\" width=\"100%\" align=\"center\"><tr>"
        ."<td width=\"70%\" align=\"center\">"._MD_WEBMAIL_SHOWING." ($showstart - $showend) "._MD_WEBMAIL_OF." $mailsum "._MD_WEBMAIL_EMAILS."</td>";
    if ($upperlimit != $mailsum) {
	$ul = $upperlimit + $numshow;
        if ($ul > $mailsum) $ul = $mailsum;
	echo "<td width=\"7%\"><a href='inbox.php?id=$id&start=$ul'>"._MD_WEBMAIL_PREVIOUS."</a></td>";
    }
    if ($lowerlimit > 0) {
	echo "<td width=\"7%\"><a href='inbox.php?id=$id&start=$lowerlimit'>"._MD_WEBMAIL_NEXT."</a></td>";
    }
    echo "</tr></table>";
}

