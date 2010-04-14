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

$userid = $xoopsUser->uid();

$id = (int)$_GET['id'];

if ($email_send == 1) {
$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";
if(!$result=$xoopsDB->query($query)){
	echo "ERROR";
}
if ($xoopsDB->getRowsNum($result) == 0 OR $result == "") {
    Header("Location: index.php");
}

include ("mailheader.php");
//$body = stripslashes($body);
//$to = stripslashes($to);
//$subject = stripslashes($subject);
$to = ($_GET['to'])? $_GET['to'] : $_POST['to'];
$subject = $_POST['subject'];
$body = $_POST['body'];
$op = $_POST['op'];
$id = $_POST['id'];


OpenTable();
 echo "<div align='center'><b>"._MD_WEBMAIL_COMPOSEEMAIL."</b></div>";
CloseTable();
echo "<br />";

if(isset($op)) {
	//nao-pon
	$subject = trim(preg_replace("/^re: ?/i","",$subject));
	$subject = trim(preg_replace("/^fwd: ?/i","",$subject));
    //
    if($op == "reply") {
		$subject = "Re: ".$subject;
	    if (strpos($body, "<br />") !== false) {
			$bodytext = explode("<br />",$body);
		        foreach($bodytext as $bt) {
			    $content .= "> ".$bt;
	        }
	    } else {
	        $bodytext = explode("\n",$body);
	        foreach($bodytext as $bt) {
	            $content .= "> ".$bt."\n";
	        }
	    }
	    $content = "\n\n\n".$content;
    } else if($op == "forward"){
		$subject = "Fwd: ".$subject;
		    if (strpos($body, "<br />") !== false) {
			$bodytext = explode("<br />",$body);
	        foreach($bodytext as $bt) {
				$content .= $bt;
	        }
	    } else {
	        $bodytext = explode("\n",$body);
	        foreach($bodytext as $bt) {
	            $content .= $bt."\n";
	        }
	    }
	    $content = "\n\n\n".$content;
	}
}

OpenTable();

if (ini_get(file_uploads) AND $attachments == 1) {
    echo "<script language=\"javascript\">\n"
	."function open_w(file) {\n"
	."    newwin = window.open(file,'Attachments','width=450, height=250, scrollbars=no, toolbar=no');\n"
	."}\n"
	."\n"
	."function attachfiles(files,types) {\n"
	."    var elm=document.getElementById(\"Atts\");\n"
	."    if (elm.firstChild.nodeValue=='"._MD_WEBMAIL_NONE."'){\n"
	."      elm.firstChild.nodeValue =files;\n"
	."      document.emailform.attachment.value=files;\n"
	."      document.emailform.attchtype.value =types;\n"
	."    } else {\n"
	."      elm.firstChild.nodeValue=elm.firstChild.nodeValue+' , '+files;\n"
	."      document.emailform.attachment.value=document.emailform.attachment.value+','+files;\n"
	."      document.emailform.attchtype.value = document.emailform.attchtype.value+','+types;\n"
	."    }\n"
	."}\n"
	."function attach_clr() {\n"
	."    var elm=document.getElementById(\"Atts\");\n"
	."    elm.firstChild.nodeValue =\""._MD_WEBMAIL_NONE."\";\n"
	."    document.emailform.attachment.value=\"\";\n"
	."    document.emailform.attchtype.value =\"\";\n"
	."}\n"
	."</script>\n";
}

// JavaScript for Signature by nao-pon
$query = "select * FROM ".$xoopsDB->prefix("wmail_sign")." where uid = $userid";
	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}
	echo "<script language=\"javascript\">
	function webmail_sign_ins() {";
	$i=1;
	while ($row = $xoopsDB->fetchArray($result)) {
		if ($row['signname']){
			$row['signature'] = str_replace("\r\n","\n",$row['signature']);
			$row['signature'] = str_replace("\r","\n",$row['signature']);
			$row['signature'] = str_replace("\n","\\n",$row['signature']);
			echo "if (document.emailform.webmail_signature.value == '".$i."') document.emailform.message.value=document.emailform.message.value + \"\\n\" + \"".$row['signature']."\";\n";
		}
		$i++;
	}
	echo "}</script>";

	//Make From: nao-pon
	$email = $xoopsUser->email();
	$name = $xoopsUser->name();
	$uname = $xoopsUser->uname();
	if($name == "") {
	    $name = $uname;
	}
	$froms = "$name <$email>";

$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";
    	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}

echo "<b>"._MD_WEBMAIL_SENDANEMAIL."</b>"._MD_WEBMAIL_ADD_BCC."<br /><br />"
    ."<form method=\"post\" action='nlmail.php' name=\"emailform\">"
    ."<table align=\"center\" width=\"98%\">";

if ($email_addr == '1'){
	echo "<tr><td align=\"right\" nowrap>"._MD_WEBMAIL_MAIL_FROM.":</td><td width=100%><select name=\"from\">";
	echo "<option value='$froms' selected>".htmlspecialchars($froms)."</option>";
	$i=1;
	while ($row = $xoopsDB->fetchArray($result)) {
		if ($row[smail]){
			if ($row[sname]) {
				$froms = "$row[sname] <".trim($row[smail]).">";
			} else {
				$froms = trim($row[smail]);
			}
			if ($id == $row[id]) {
				echo "<option value='$froms' selected>".htmlspecialchars($froms)."</option>";
			} else {
				echo "<option value='$froms'>".htmlspecialchars($froms)."</option>";
			}
		}
		$i++;
	}
	echo "</select></td></tr>";
} else {
	echo "<tr><td align=\"right\" nowrap>"._MD_WEBMAIL_MAIL_FROM.":</td><td width=100%>".htmlspecialchars($froms)."</td></tr>";
}

echo "<tr><td align=\"right\" nowrap>"._MD_WEBMAIL_TO.":</td><td width=100%><input type=text name=\"to\" size=47 value='$to'></td></tr>"
    ."<tr><td>&nbsp;</td><td><font class=\"tiny\">"._MD_WEBMAIL_SEPARATEEMAILS."<br />"._MD_WEBMAIL_MAIL_SEND_MAX.$mail_max._MD_WEBMAIL_MAIL_SEND_MAX2."</font></td></tr>"
    ."<tr><td nowrap>"._MD_WEBMAIL_MAIL_SUBJECT.":</td><td><input type=text name=\"subject\" size=60 value='$subject'></td></tr>"
    ."<tr><td align=\"right\"><i>Cc:</i></td><td><input type=text name=\"cc\" size=36>&nbsp;&nbsp;<i>Bcc:</i> <input type=text name=\"bcc\" size=36></td></tr>"
    ."<tr><td align=\"right\" nowrap>"._MD_WEBMAIL_PRIORITY.":</td><td><select name=\"prior\">"
    ."<option value=\"1\">"._MD_WEBMAIL_HIGH."</option>"
    ."<option value=\"3\" selected>"._MD_WEBMAIL_NORMAL."</option>"
    ."<option value=\"4\">"._MD_WEBMAIL_LOW."</option>"
    ."</select>";

$query = "select * FROM ".$xoopsDB->prefix("wmail_sign")." where uid = $userid";
	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}

	if($xoopsDB->getRowsNum($result) > 0) {
		echo "&nbsp;&nbsp;"._MD_WEBMAIL_SIGN.":&nbsp;<select name=\"webmail_signature\">";
		$i=1;
		while ($row = $xoopsDB->fetchArray($result)) {
			if ($row['signname']){
				echo "<option value='$i'>".htmlspecialchars($row['signname'])."</option>";
			}
			$i++;
		}
		echo "</select><input type=\"button\" value=\""._MD_WEBMAIL_INSERT."\" onClick=\"webmail_sign_ins()\" />";
	}

echo "</td>"
    ."</tr>"
    ."<tr><td align=\"right\" nowrap>"._MD_WEBMAIL_MAIL_MESSAGE.":</td>"
    ."<td>"
    ."<textarea class=\"norich\" name=\"message\" rows=\"15\" cols=\"80\" wrap=\"virtual\">$content</textarea>"
    ."</td></tr>";

if ($attachments == 1) {
    $attach_clear = "&nbsp;&nbsp;<input type=\"button\" value=\""._MD_WEBMAIL_CLEARATT."\" onClick=\"attach_clr();\">";
    echo "<tr><td colspan=2>";
    OpenTable();
    echo ""._MD_WEBMAIL_ATTACHMENTS.": <span style=\"background-color:#ffffcc\" id=\"Atts\">"._MD_WEBMAIL_NONE."</span> &nbsp;<br /><br /><a href=\"javascript: open_w('mailattach.php')\">"._MD_WEBMAIL_CLICKTOATTACH."</a>"
    ."<noscript>"._MD_WEBMAIL_NOSCRIPT."</noscript><br />";
    CloseTable();
} else {
	$attach_clear = '';
}

echo "<tr><td colspan=\"2\">"
    ."<input type=\"submit\" name=\"send\" value=\""._MD_WEBMAIL_MAIL_SENDMESSAGE."\">&nbsp;&nbsp;<input type=\"reset\" value=\""._MD_WEBMAIL_MAIL_CLEARALL."\" onClick=\"attach_clr();\">"
    .$attach_clear
    ."</td></tr>"
    ."</table>"
    ."</center>"
    ."<input type=hidden name=\"attachment\">"
    ."<input type=hidden name=\"attchtype\">"
    ."</form>";

CloseTable();
include(XOOPS_ROOT_PATH."/footer.php");
} else {
    Header("Location: index.php");
    exit();
}

