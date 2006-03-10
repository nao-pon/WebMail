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

//mb_string ini set by nao-pon
ini_set("output_buffering","Off");
ini_set("default_charset","EUC-JP");
ini_set("mbstring.language","Japanese");
ini_set("mbstring.encoding_translation","On");
ini_set("mbstring.http_input","Auto");
ini_set("mbstring.http_output","EUC-JP");
ini_set("mbstring.internal_encoding","EUC-JP");
ini_set("mbstring.substitute_character","none");

//HTML View On or Off by nao-pon
$html_view = (!empty($_GET['ht']))? $_GET['ht'] : "";

$id = $_GET['id'];
$msgid = $_GET['msgid'];


function unhtmlentities ($string)
{
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);
	return strtr ($string, $trans_tbl);
	//return mb_convert_encoding(strtr ($string, $trans_tbl), "EUC-JP", "AUTO");
}

include("../../mainfile.php");
require_once("cache/config.php");
        if($show_right==true)
	        $xoopsOption['show_rblock'] =1;
        else
                $xoopsOption['show_rblock'] =0;
	include($xoopsConfig['root_path']."header.php");

global $xoopsDB, $xoopsUser;
$userid = $xoopsUser->uid();
$username = $xoopsUser->uname();

parse_str(base64_decode($pop3_cookie));
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
if (!$html_scr_color) $html_scr_color="red";


if(!isset($id)) {
    echo "Error: Invalid Parameter<br>";
    include($xoopsConfig['root_path']."footer.php");
    exit();
}

$query = "Select * from ".$xoopsDB->prefix("popsettings")." where id = $id";
if(($res = $xoopsDB->query($query,$options[0],0)) && ($xoopsDB->getRowsNum($res) > 0)) {
    $row = $xoopsDB->fetchArray($res);
    $uid = $row[uid];
    if ($uid != $userid) {
	echo "<center><h2>Error: Permission denied</center>";
	include($xoopsConfig['root_path']."footer.php");
	exit();
    }
    $server = $row[popserver];
    $port = $row[port];
    $apop = $row[apop];
    $username = $row[uname];
    $rc4 = new rc4crypt();
    $password = $rc4->endecrypt($username,$row[passwd],"de");
} else {
    echo "Error: POP Server not set properly<br>";
    include($xoopsConfig['root_path']."footer.php");
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
$d = new DecodeMessage;
$d->InitMessage($full);
$from_address = chop(mb_convert_encoding(mb_decode_mimeheader($d->Headers("From")), "EUC-JP", "auto"));
$reply_address = chop(mb_convert_encoding(mb_decode_mimeheader($d->Headers("Reply-To")), "EUC-JP", "auto"));
if (!$reply_address) $reply_address = $from_address;
$to_address = chop(mb_convert_encoding(mb_decode_mimeheader($d->Headers("To")), "EUC-JP", "auto"));
$subject = mb_convert_encoding(mb_decode_mimeheader($d->Headers("Subject")), "EUC-JP", "auto");
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
	echo "<td align='left' width='15%'><a href='?id=$id&msgid=$msgid_pre'><b>&lt; Pre</b></a></td>";
 } else {
	echo "<td align='left' width='15%'></td>";
 }
	echo "<td align='center'><a href='./inbox.php?id=".$id."'><b>"._EMAILINBOX." &nbsp;(".$account.")</b></a></td>";
	if ($msgid_next <= $mailsum) {
		echo "<td align='right' width='15%'><a href='?id=$id&msgid=$msgid_next'><b>Next &gt;</b></a></td>";
	} else {
		echo "<td align='right' width='15%'></td>";
	}
 
 echo "</tr></table>";
CloseTable();
echo "<br>";

OpenTable();
    echo "<script language=\"javascript\">\n"
	."function open_w(file) {\n"
	."    newwin = window.open(file,'Attachments','width=800, height=600, toolbar=no');\n"
	."}\n"
	."</script>\n";

echo "<table border=\"0\" width=\"100%\">
    <tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MAIL_FROM.":</b></td>
    <td>".htmlspecialchars($from_address)." <small>[ <a href=\"./contactbook.php?op=addnew&amp;from=".rawurlencode($from_address)."&amp;id={$id}\">アドレス帳へ追加</a> ]</small></td>
    </tr>
    <tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._TO.":</b></td>
    <td>".htmlspecialchars($to_address)."</td>
    </tr>";

if ($cc != "") {
    echo "<tr>
	<td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>Cc:</b></td>
        <td>".htmlspecialchars($cc)."</td>
        </tr>";
}

echo "<tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MAIL_SUBJECT.":</b></td>
    <td>".htmlspecialchars($subject)."</td>
    </tr><tr>
    <td align=\"left\" class='bg2' bgcolor=\"$bgcolor2\"><b>"._MAIL_DATE.":</b></td>
    <td>".htmlspecialchars($d->Headers("Date")) ."</td>
    </tr><tr>
    <td colspan=2>
    <table border=0 width=100% cellspacing=0><tr><td class='bg2' bgcolor=$bgcolor2>
    <table border=0 width=100% cellspacing=5 cellpadding=0><tr><td bgcolor=\"$bgcolor2\">
    <form action='inbox.php' method=\"post\">
    <input type=hidden name=\"id\" value=\"$id\">
    <input type=hidden name=\"op\" value=\"delete\">
    <input type=hidden name=\"msgid\" value=\"$msgid\">
    <input type=submit value=\""._DELETE."\">";
    echo "</form>";
if ($email_send == 1) {
	echo "</td><td bgcolor=\"$bgcolor2\" class='bg2'>
	<form action='compose.php' method=\"post\" name=\"f_rep\">
	<input type=hidden name=to value=\"".htmlspecialchars($reply_address)."\">
	<input type=hidden name=subject value=\"".htmlspecialchars($subject)."\">
	<input type=hidden name=body value=\"\">
	<input type=hidden name=op value=\"reply\">
	<input type=hidden name=id value=\"$id\">
	<input type=submit value=\""._REPLY."\">
	</form>
	</td><td bgcolor=\"$bgcolor2\" width=\"100%\" class='bg2'>
	<form action='compose.php' method=\"post\" name=\"f_del\">
	<input type=hidden name=\"to\" value=\"\">
	<input type=hidden name=\"subject\" value=\"".htmlspecialchars($subject)."\">
	<input type=hidden name=\"body\" value=\"\">
	<input type=hidden name=\"op\" value=\"forward\">
	<input type=hidden name=id value=\"$id\">
	<input type=submit value=\""._FORWARD."\">
	</form>";
}
echo "</td></tr></table></tr></td></table></td></tr><tr><td colspan=2 bgcolor=\"$bgcolor2\" class='bg2'>";

if ($filter_forward == '1'){
	$rep_header = "-----Original Message-----\\nFrom: ".$from_address."\\nSent: ".$d->Headers("Date")."\\nTo: ".$to_address."\\nSubject: ".$subject."\\n\\n";
}

OpenTable();
$message = $d->Result();
// nao-pon wrote
$http_URL_regex = '(s?https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)';
$mail_ADR_regex = "((mailto:)?([0-9A-Za-z._-]+@[0-9A-Za-z.-]+))";
//echo "email_send:$email_send";
// edit end.
$rtext = "";

for ($j=0;$j<count($message);$j++) {
    for ($i=0;$i<count($message[$j]);$i++) {
		if (chop($message[$j][$i]["attachments"]) != '') {
			$filename = mb_convert_encoding($message[$j][$i]["attachments"], "EUC-JP", "AUTO");
			$filename = urlencode($filename);
			$filename_id = urlencode($message[$j][$i]["attachments_id"]);
			// nao-pon
			$att_txt .= " <a href=\""."download.php?fn=".$filename_id."&dfn=".$filename."\" target='_blank'>".$message[$j][$i]["attachments"]."</a>";
//			$att_txt .= " <a href=\"javascript: open_w('".$d->attachment_path."/".$filename."')\">".$message[$j][$i]["attachments"]."</a>";
			//
		}
    }
	for ($i=0;$i<count($message[$j]);$i++) {
		if (eregi("text/html", $message[$j][$i]["body"]["type"])) {

			if (!$html_view) echo "<table border=0 width=100% cellspacing=0><tr><td class='bg2' align='center'><a href='readmail.php?id=$id&msgid=$msgid&ht=1'>"._WM_HTML_VIEW."</a> [<a href='readmail.php?id=$id&msgid=$msgid&ht=2'>"._WM_HTML_VIEW_S."</a>]</td></tr></table>";
			if ($html_view == 2) echo "<table border=0 width=100% cellspacing=0><tr><td class='bg2' align='center'><a href='readmail.php?id=$id&msgid=$msgid&ht=1'>"._WM_HTML_VIEW."</a> [<a href='readmail.php?id=$id&msgid=$msgid'>"._WM_HTML_VIEW_T."</a>]</td></tr></table>";
			$res = $message[$j][$i]["body"]["body"];
			
			$bg_img_h_tag = $bg_img_w_tag = "";
			if (preg_match("/<body.*?background\s*?=\s*?['\"]?(.*?)['\"]?( |>)/i",$res,$reg)){
				$bg_img_size = GetImageSize($reg[1]);
				if ($bg_img_size[0]) {
					$bg_img_h_tag = "<td class=\"bg2\" width=\"0\"><img src=\"dammy.gif\" width=\"0%\" height=\"$bg_img_size[1]\" /></td>";
					$bg_img_w_tag = "<tr><td class=\"bg2\" height=\"0\" width=\"100%\"><img src=\"dammy.gif\" height=\"0\" width=\"$bg_img_size[0]\" /></td><td width=\"0%\"></td></tr>";
				}
			}
			
			if (eregi("iso-2022-jp",$message[$j][$i]["body"]["charset"])) {
		    	$res = mb_convert_encoding($res,"EUC-JP","JIS");
		    } else {
				$res = mb_convert_encoding($res,"EUC-JP","AUTO");
			}

			$res = str_replace("\r\n", "\n", $res);
			$res = str_replace("\r", "\n", $res);

			if ($html_view == 2) {
				$tmp = $res;
				$tmp = htmlspecialchars($tmp);
				$tmp = str_replace("\n","&br;",$tmp);
				$tmp = preg_replace("/(&lt;\s*?script.*?&gt;)(.*?)(&lt;.*?\/script\s*?&gt)/i", "$1<font color=$html_scr_color>$2</font>$3", $tmp);
				$tmp = preg_replace("/(&lt;.*?&gt;)/","<font color=$html_tag_color>$1</font>",$tmp);
				$tmp = str_replace("&br;","\n",$tmp);
				echo nl2br($tmp);
			}
			
			$res = ereg_replace("\n", " ", $res);
			$res = preg_replace("/<!DOCTYPE.*?>/i", "", $res);
			$res = preg_replace("/<html.*?>/i", "", $res);
			$res = preg_replace("/<\/html>/i", "", $res);
			$res = preg_replace("/<head(.+?\/)head>/i", "", $res);
			$res = preg_replace("/<body(.+?)\/body>/i", "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td valign='top'$1/td>".$bg_img_h_tag."</tr>".$bg_img_w_tag."</table>", $res);
		    
		    if ($html_view == 1) {
				$res = preg_replace("/<(\s*?script.*?)>(.*?)<(.*?\/script\s*?)>/i", "<font color=$html_tag_color>&lt;$1&gt;</font><font color=$html_scr_color>$2</font><font color=$html_tag_color>&lt;$3&gt;</font>", $res);
		    	echo $res;
		    }
		    
		    $res = eregi_replace("<br ?/?>", "\n", $res);
		    $res = eregi_replace("</div>", "\n", $res);
		    $res = eregi_replace("</p>", "\n", $res);
		    $res = eregi_replace("</table>", "\n", $res);
		    $res = preg_replace("/<hr.*?>/i", "\n", $res);
		    $res = eregi_replace("&nbsp;", " ", $res);
			$tmp = preg_replace("/<(\s*?script.*?)>(.*?)<(.*?\/script\s*?)>/i", "&lt;$1&gt;$2&lt;$3&gt;", $res);
		    $res = strip_tags($res);
		    $res = unhtmlentities($res);
		    
		    if (!$html_view) {
			    $tmp = strip_tags($tmp);
			    $tmp = unhtmlentities($tmp);
			    $tmp = preg_replace("/<(\s*?script.*?)>(.*?)<(.*?\/script\s*?)>/i", "&lt;$1&gt;$2&lt;$3&gt;", $tmp);
			    $tmp = ereg_replace($http_URL_regex,"<a href=\"\\1\" target=\"_blank\">\\1</a>",$tmp);
				if ($email_send == 1) {
					$tmp = eregi_replace($mail_ADR_regex, "<a href=\"./compose.php?to=\\3\">\\1</a>", $tmp);
				} else {
					$tmp = eregi_replace($mail_ADR_regex, "<a href=\"mailto:\\3\">\\1</a>", $tmp);
				}
				$tmp = preg_replace("/(&lt;\s*?script.*?&gt;)(.*?)(&lt;.*?\/script\s*?&gt)/i", "<font color=$html_tag_color>$1</font><font color=$html_scr_color>$2</font><font color=$html_tag_color>$3</font>", $tmp);
				echo nl2br($tmp);
			}
			
		} else {
			// nao-pon worte
			$res = rtrim($message[$j][$i]["body"]["body"]);
			//echo mb_detect_encoding($res);
			//echo $message[$j][$i]["body"]["charset"];
			if (eregi("(iso-2022-jp)",$message[$j][$i]["body"]["charset"])) {
		    	$res = mb_convert_encoding($res,"EUC-JP","JIS");
		    } else {
				$res = mb_convert_encoding($res,"EUC-JP","AUTO");
			}
			$tmp = htmlspecialchars($res);
			$tmp = ereg_replace($http_URL_regex,"<a href=\"\\1\" target=\"_blank\">\\1</a>",$tmp);
			if ($email_send == 1) {
				$tmp = eregi_replace($mail_ADR_regex, "<a href=\"./compose.php?to=\\3\">\\1</a>", $tmp);
			} else {
				$tmp = eregi_replace($mail_ADR_regex, "<a href=\"mailto:\\3\">\\1</a>", $tmp);
			}
			//echo nl2br($tmp)."<br>";
			echo nl2br($tmp);
			// nao-pon next line commented. edit end.
			//echo nl2br(htmlspecialchars($message[$j][$i]["body"]["body"]))."<br>";
		}
		//$content = $rtext .= strip_tags($message[$j][$i]["body"]["body"]);
		$rtext .= $res;
    }
}
$rtext = ereg_replace("\r\n", "\n", $rtext);
$rtext = ereg_replace("\r", "\n", $rtext);
$rtext = ereg_replace("\n", "\\n", $rtext);
$rtext = $rep_header.$rtext;

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
    	    <b>&nbsp;"._ATTACHMENTS.": </b></td><td width=\"100%\">&nbsp;$att_txt</td></tr>"
    	    ."<tr bgcolor=\"$bgcolor2\" class='bg2'><td colspan=2>"._ATTACHCOM."</td><tr>"
    	    ."</table>";
    }
}

if ($attach_nv == 1) {
	echo "<table align=\"center\" border=\"0\" width=\"100%\"><tr bgcolor=\"$bgcolor2\" class='bg2'><td align=\"center\">"
	    .""._ATTACHSECURITY."</td></tr></table>";
}

CloseTable();
include($xoopsConfig['root_path']."footer.php");

?>
