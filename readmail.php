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

//HTML View On or Off by nao-pon
$html_view = (!empty($_GET['ht']))? (int)$_GET['ht'] : "";

$id = (int)$_GET['id'];
$msgid = (int)$_GET['msgid'];


function unhtmlentities ($string)
{
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);
	return strtr ($string, $trans_tbl);
}

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

$userid = $xoopsUser->uid();
$username = $xoopsUser->uname();

//parse_str(base64_decode($pop3_cookie));
require ("pop3.php");
require ("decodemessage.php");
include ("mailheader.php");
include ("class.rc4crypt.php");

	// nao-pon
	//session_start();
	//session_register("webmail");
	//if(!@opendir($download_dir."/".$userid)) mkdir($download_dir."/".$userid, 0777);
	//@fopen("$download_dir/$userid/index.html","w");
	//session_destroy();

// nao-pon
if (!$html_tag_color) $html_tag_color="blue";
//if (!$html_scr_color) $html_scr_color="red";


if(!isset($id)) {
    echo "Error: Invalid Parameter<br />";
    include(XOOPS_ROOT_PATH."/footer.php");
    exit();
}

$query = "Select * from ".$xoopsDB->prefix("popsettings")." where id = $id";
if(($res = $xoopsDB->query($query,$options[0],0)) && ($xoopsDB->getRowsNum($res) > 0)) {
    $row = $xoopsDB->fetchArray($res);
    $uid = $row[uid];
    if ($uid != $userid) {
	echo "<center><h2>Error: Permission denied</center>";
	include(XOOPS_ROOT_PATH."/footer.php");
	exit();
    }
    $server = $row[popserver];
    $port = $row[port];
    $apop = $row[apop];
    $username = $row[uname];
    $rc4 = new rc4crypt();
    $password = $rc4->endecrypt($username,$row[passwd],"de");
} else {
    echo "Error: POP Server not set properly<br />";
    include(XOOPS_ROOT_PATH."/footer.php");
    exit();
}

$ms = $msgid;
set_time_limit(0);
$pop3=new POP3($server,$username,$password,$port,$apop);
$pop3->Open();
$message = $pop3->GetMessage($ms) ;
$s = $pop3->Stats() ;
$mailsum = $s["message"];
$body = $message["body"];
$header = $message["header"];
$full = $message["full"];
$pop3->Close();
//echo $full;
$d = new DecodeMessage;
$d->InitMessage($full);
$from_address = chop(mb_decode_mimeheader($d->Headers("From")));
$reply_address = chop(mb_decode_mimeheader($d->Headers("Reply-To")));
if (!$reply_address) $reply_address = $from_address;
$to_address = chop(mb_decode_mimeheader($d->Headers("To")));
$subject = mb_decode_mimeheader($d->Headers("Subject"));
$cc = chop($d->Headers("Cc"));
$replyto = chop($d->Headers("Reply-To:"));
$query = "select account from ".$xoopsDB->prefix("popsettings")." where id='$id'";
$result=$xoopsDB->query($query,$options[0],0);
$row = $xoopsDB->fetchArray($result);
$account =  $row[account];
//$content = $d->body;
OpenTable();
 $msgid_pre = $msgid-1;
 $msgid_next = $msgid+1;
 echo "<table width='100%'><tr>";
 if ($msgid_pre > 0) {
	echo "<td align='left' width='15%'><a href='?id=$id&amp;msgid=$msgid_pre'><b>&lt; Pre</b></a></td>";
 } else {
	echo "<td align='left' width='15%'></td>";
 }
	echo "<td align='center'><a href='./inbox.php?id=".$id."'><b>"._MD_WEBMAIL_EMAILINBOX." &nbsp;(".$account.")</b></a></td>";
	if ($msgid_next <= $mailsum) {
		echo "<td align='right' width='15%'><a href='?id=$id&amp;msgid=$msgid_next'><b>Next &gt;</b></a></td>";
	} else {
		echo "<td align='right' width='15%'></td>";
	}

 echo "</tr></table>";
CloseTable();
echo "<br />";

OpenTable();
    echo "<script language=\"javascript\">\n"
	."function open_w(file) {\n"
	."    newwin = window.open(file,'Attachments','width=800, height=600, toolbar=no');\n"
	."}\n"
	."</script>\n";

echo "<table border=\"0\" width=\"100%\">
    <tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_FROM.":</b></td>
    <td>".htmlspecialchars($from_address)." <small>[ <a href=\"./contactbook.php?op=addnew&amp;from=".rawurlencode($from_address)."&amp;id={$id}\">"._MD_WEBMAIL_ADD_ADR_BOOK."</a> ]</small></td>
    </tr>
    <tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_TO.":</b></td>
    <td>".htmlspecialchars($to_address)."</td>
    </tr>";

if ($cc != "") {
    echo "<tr>
	<td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>Cc:</b></td>
        <td>".htmlspecialchars($cc)."</td>
        </tr>";
}

echo "<tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_SUBJECT.":</b></td>
    <td>".htmlspecialchars($subject)."</td>
    </tr><tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MD_WEBMAIL_MAIL_DATE.":</b></td>
    <td>".$d->Headers("Date")."</td>
    </tr><tr>
    <td colspan=2>
    <table border=0 width=100% cellspacing=0><tr><td class='bg2' bgcolor=$bgcolor2>
    <table border=0 width=100% cellspacing=5 cellpadding=0><tr><td bgcolor=\"$bgcolor2\">
    <form action='inbox.php' method=\"post\">
    <input type=hidden name=\"id\" value=\"$id\">
    <input type=hidden name=\"op\" value=\"delete\">
    <input type=hidden name=\"msgid\" value=\"$msgid\">
    <input type=submit value=\""._MD_WEBMAIL_DELETE."\">";
    echo "</form>";
if ($email_send == 1) {
	echo "</td><td bgcolor=\"$bgcolor2\" class='bg2'>
	<form action='compose.php' method=\"post\" name=\"f_rep\">
	<input type=hidden name=to value=\"".htmlspecialchars($reply_address)."\">
	<input type=hidden name=subject value=\"".htmlspecialchars($subject)."\">
	<input type=hidden name=body value=\"\">
	<input type=hidden name=op value=\"reply\">
	<input type=hidden name=id value=\"$id\">
	<input type=submit value=\""._MD_WEBMAIL_REPLY."\">
	</form>
	</td><td bgcolor=\"$bgcolor2\" width=\"100%\" class='bg2'>
	<form action='compose.php' method=\"post\" name=\"f_del\">
	<input type=hidden name=\"to\" value=\"\">
	<input type=hidden name=\"subject\" value=\"".htmlspecialchars($subject)."\">
	<input type=hidden name=\"body\" value=\"\">
	<input type=hidden name=\"op\" value=\"forward\">
	<input type=hidden name=id value=\"$id\">
	<input type=submit value=\""._MD_WEBMAIL_FORWARD."\">
	</form>";
}
echo "</td></tr></table></tr></td></table></td></tr><tr><td colspan=2 bgcolor=\"$bgcolor2\" class='bg2'>";

if ($filter_forward == '1'){
	$rep_header = "-----Original Message-----\\nFrom: ".$from_address."\\nSent: ".$d->Headers("Date")."\\nTo: ".$to_address."\\nSubject: ".$subject."\\n\\n";
}

$message = $d->Result();
// nao-pon wrote
$http_URL_regex = '#(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%\#]+)#';
$mail_ADR_regex = '#((mailto:)?([0-9A-Za-z._-]+@[0-9A-Za-z.-]+))#';
//echo "email_send:$email_send";
// edit end.
$rtext = "";

//var_dump($message);

$echo = $html = $htmls = $text = '';

for ($j=0;$j<count($message);$j++) {
    $found_text = false;
    $found_html = false;
    for ($i=0;$i<count($message[$j]);$i++) {
		if (chop($message[$j][$i]["attachments"]) != '') {
			$filename = mb_convert_encoding($message[$j][$i]["attachments"], _CHARSET, "AUTO");
			$filename = urlencode($filename);
			$filename_id = urlencode($message[$j][$i]["attachments_id"]);
			// nao-pon
			$att_txt .= " <a href=\""."download.php?fn=".$filename_id."&amp;dfn=".$filename."\" target='_blank'>".$message[$j][$i]["attachments"]."</a>";
		}
		if (preg_match('#text/plain#i', $message[$j][$i]["body"]["type"])) {
			$found_text = true;
		}
		if (preg_match('#text/html#i', $message[$j][$i]["body"]["type"])) {
			$found_html = true;
		}
    }

    if ($found_html) {
		if (!$html_view) $echo .= '<div style="text-align:center;width:100%;"><a href="readmail.php?id='.$id.'&amp;msgid='.$msgid.'&amp;ht=1">'._MD_WEBMAIL_HTML_VIEW.'</a> [<a href="readmail.php?id='.$id.'&amp;msgid='.$msgid.'&ht=2">'._MD_WEBMAIL_HTML_VIEW_S.'</a>]</div><hr />';
		if ($html_view == 2) $echo .= '<div style="text-align:center;width:100%;"><a href="readmail.php?id='.$id.'&amp;msgid='.$msgid.'&amp;ht=1">'._MD_WEBMAIL_HTML_VIEW.'</a> [<a href="readmail.php?id='.$id.'&amp;msgid='.$msgid.'">'._MD_WEBMAIL_HTML_VIEW_T.'</a>]</div><hr />';
    }

	for ($i=0;$i<count($message[$j]);$i++) {

		if ($found_html && ($html_view || ! $found_text) && preg_match('#text/html#i', $message[$j][$i]["body"]["type"])) {

			$res = $message[$j][$i]["body"]["body"];

			$bg_img_h_tag = $bg_img_w_tag = "";
			if (preg_match("/<body.*?background\s*?=\s*?['\"]?(.*?)['\"]?( |>)/i",$res,$reg)){
				$bg_img_size = GetImageSize($reg[1]);
				if ($bg_img_size[0]) {
					$bg_img_h_tag = "<td class=\"bg2\" width=\"0\"><img src=\"dammy.gif\" width=\"0%\" height=\"$bg_img_size[1]\" /></td>";
					$bg_img_w_tag = "<tr><td class=\"bg2\" height=\"0\" width=\"100%\"><img src=\"dammy.gif\" height=\"0\" width=\"$bg_img_size[0]\" /></td><td width=\"0%\"></td></tr>";
				}
			}

			if ($message[$j][$i]["body"]["charset"]) {
				$enc = mb_detect_encoding($str, $message[$j][$i]["body"]["charset"] . ',AUTO');
			} else {
				$enc = 'AUTO';
			}
			$res = mb_convert_encoding($res, _CHARSET, $enc);

			$res = str_replace(array("\r\n", "\r"), "\n", $res);

			$res = preg_replace("/\n{3,}/", '', $res);
			$res = preg_replace("/<\s+/", "<", $res);
			$res = preg_replace("/\s+>/", ">", $res);

			$res = preg_replace("/<!DOCTYPE[^>]*?>/i", "", $res);
			$res = preg_replace("/<html[^>]*?>/i", "", $res);
			$res = preg_replace("/<\/html>/i", "", $res);
			$res = preg_replace("/<head.+?\/head>/is", "", $res);
			$res = preg_replace("/<body(.+?)\/body>/is", "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td valign='top'$1/td>".$bg_img_h_tag."</tr>".$bg_img_w_tag."</table>", $res);

//			if (defined(XOOPS_TRUST_PATH) && include_once(XOOPS_TRUST_PATH.'/libs/htmlpurifier/library/HTMLPurifier.auto.php')) {
//
//				$config = HTMLPurifier_Config::createDefault();
//				$config->set('Core.Encoding', _CHARSET);
//				$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
//				$purifier = new HTMLPurifier($config);
//				$res = $purifier->purify($res);
//
//			} else {
				//// Remove etc.
				$res = preg_replace('/<(script[^>]*?)>.*?<(\/script)>/isS', '', $res);

				// <a> with JavaScript
				$res = preg_replace('#<a[^>]+?href=(?:"|\')?javascript:[^>]+?>(.+?)</a>#is', '$1', $res);

				//// tag attribute
				$res = str_replace(array("\\'", '\\"'), array("\x07", "\x08"), $res);
				// on*
				$reg = '#(<[^>]+?)\s+(?:on[^=]+?)=(?:\'[^\']*\'|"[^"]*")([^>]*>)#iS';
				while(preg_match($reg, $res)) {
					$res = preg_replace($reg, '$1$2', $res);
				}
				$reg = '#(<[^>]+?)\s+(?:on[^=]+?)=[^ >/]+([^>]*>)#iS';
				while(preg_match($reg, $res)) {
					$res = preg_replace($reg, '$1$2', $res);
				}
				$res = str_replace(array("\x07", "\x08"), array("\\'", '\\"'), $res);
//			}

			$html .= $res;

			$tmp = $res;
			$tmp = htmlspecialchars($tmp);
//			$tmp = preg_replace("/(&lt;\s*?script.*?&gt;)(.*?)(&lt;.*?\/script\s*?&gt)/is", "$1<font color=$html_scr_color>$2</font>$3", $tmp);
			$tmp = preg_replace("/(&lt;.*?&gt;)/s","<font color=$html_tag_color>$1</font>",$tmp);
			$htmls .= nl2br($tmp);

		} else if ($found_text && preg_match('#text/plain#i', $message[$j][$i]["body"]["type"])) {
			// nao-pon worte
			$res = rtrim($message[$j][$i]["body"]["body"]);
			if ($message[$j][$i]["body"]["charset"]) {
				$enc = $message[$j][$i]["body"]["charset"].',AUTO';
			} else {
				$enc = 'AUTO';
			}
			$res = mb_convert_encoding($res,_CHARSET,$enc);
			$text = trim($res);
		}
    }
}

if (! $text && $html) {

    $res = str_replace("\n", ' ', $res);
    $res = preg_replace('#<style.*?/style>#is', '', $html);
    $res = preg_replace("#<br[^>]*?>i#", "\n", $res);
    $res = preg_replace("#</div>#i", "\n", $res);
    $res = preg_replace("#</p>#i", "\n", $res);
    $res = preg_replace("#</table>#i", "\n", $res);
    $res = preg_replace("#<hr[^>]*?>#i", "\n", $res);
    $res = preg_replace("/&nbsp;/i", " ", $res);
	$res = preg_replace("/<(\s*?script.*?)>(.*?)<(.*?\/script\s*?)>/i", "&lt;$1&gt;$2&lt;$3&gt;", $res);
    $res = strip_tags($res);
    $res = unhtmlentities($res);

	$text = $rtext = trim(preg_replace("/(?:[\t ]*\n){2,}/", "\n\n", $res));
} else {
	$text = trim(preg_replace("/(?:[\t ]*\n){2,}/", "\n\n", $text));
	$rtext = $text;
}

$rtext = preg_replace('/^\s+/m', '', $rtext);
$rtext = str_replace("\n", '\n', $rtext);
$rtext = $rep_header.$rtext;

if ($html_view == 2) {
	$echo .= $htmls;
} else if ($html_view) {
	$echo .= $html;
} else {
    $text = str_replace(array('<', '>'), array('< ', ' >'), $text);
    $text = htmlspecialchars($text);
    $text = preg_replace($http_URL_regex,"<a href=\"\\1\" target=\"_blank\">\\1</a>",$text);
	if ($email_send == 1) {
		$text = preg_replace($mail_ADR_regex, "<a href=\"./compose.php?to=\\3\">\\1</a>", $text);
	} else {
		$text = preg_replace($mail_ADR_regex, "<a href=\"mailto:\\3\">\\1</a>", $text);
	}
//	$tmp = preg_replace("/(&lt;\s*?script.*?&gt;)(.*?)(&lt;.*?\/script\s*?&gt)/i", "<font color=$html_tag_color>$1</font><font color=$html_scr_color>$2</font><font color=$html_tag_color>$3</font>", $tmp);
	$echo .= nl2br($text);
}

OpenTable();

echo $echo;

if ($email_send == 1) {
	echo "
<script type=\"text/javascript\">
<!--
  document.f_rep.body.value = \"".htmlspecialchars($rtext)."\";
  document.f_del.body.value = \"".htmlspecialchars($rtext)."\";
// -->
</script>
";
}

CloseTable();

echo "</td></tr></table>";

if ($attachments_view == 1) {
    if($att_txt) {
	echo "<table align=\"center\" border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\" class='bg2'><td nowrap>
    	    <b>&nbsp;"._MD_WEBMAIL_ATTACHMENTS.": </b></td><td width=\"100%\">&nbsp;$att_txt</td></tr>"
    	    ."<tr bgcolor=\"$bgcolor2\" class='bg2'><td colspan=2>"._MD_WEBMAIL_ATTACHCOM."</td><tr>"
    	    ."</table>";
    }
}

if ($attach_nv == 1) {
	echo "<table align=\"center\" border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\" class='bg2'><td align=\"center\">"
	    .""._MD_WEBMAIL_ATTACHSECURITY."</td></tr></table>";
}

CloseTable();
include(XOOPS_ROOT_PATH."/footer.php");

