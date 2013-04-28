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

include("../../mainfile.php");

global $xoopsDB, $xoopsUser;
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

ob_start();

include ("mailheader.php");
include ("class.rc4crypt.php");

$type = $_POST['type'];
$id = (int)$_POST['id'];
$mode = ($_POST['mode'])? $_POST['mode'] : $_GET['mode'];
$signname = addslashes($_POST['signname']);
$signature = addslashes($_POST['signature']);
$account = addslashes($_POST['account']);
$popserver = addslashes($_POST['popserver']);
$uname = addslashes($_POST['uname']);
$passwd = addslashes($_POST['passwd']);
$port = (int)$_POST['port'];
$apop = (int)$_POST['apop'];
$numshow = (int)$_POST['numshow'];
$sname = addslashes($_POST['sname']);
$smail = addslashes($_POST['smail']);
$submit = $_POST['submit'];

//$mode = $_GET['mode'];
//if (!$mode) $mode=$_POST['mode'];

if ($mode == "sign") {
	$userid = $xoopsUser->uid();

	if(!empty($signname)) {
	    if($submit == ""._MD_WEBMAIL_DELETE."") {
			$query = "Delete from ".$xoopsDB->prefix('webmail_sign')." where id='$id'";
			$msg = _MD_WEBMAIL_MSG_DELETED;
	    } elseif ($type == "signnew") {
			$query = "Insert into ".$xoopsDB->prefix('webmail_sign')." (uid,signname,signature) values ('$userid','$signname','$signature')";
			$msg = _MD_WEBMAIL_MSG_SAVED;
	    } else {
			$query = "Update ".$xoopsDB->prefix('webmail_sign')." set signname = '$signname', signature = '$signature' where id='$id'";
			$msg = _MD_WEBMAIL_MSG_SAVED;
	    }
	    if (! $xoopsDB->query($query)) {
	    	$msg = _MD_WEBMAIL_ERR_SQL;
	    }
		ob_end_clean();
		redirect_header(XOOPS_URL . '/modules/WebMail/settings.php?mode=sign', 1, $msg);
		exit();
	}

	ob_end_flush();

	OpenTable();
	echo "<div align'center'><a href=\"settings.php\">"._MD_WEBMAIL_MAILBOXESSETTINGS."</a> | <b>"._MD_WEBMAIL_SIGNSETTINGS."</b> | <a href=\"settings.php?mode=userpref\">"._MD_WEBMAIL_USERPREFSETTINGS."</a></div>";
	CloseTable();
	echo "<br />";


	$query = "select * FROM ".$xoopsDB->prefix('webmail_sign')." where uid = $userid";
	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}

	if($xoopsDB->getRowsNum($result) > 0) {
		while($row = $xoopsDB->fetchArray($result) ) {
			$id = $row['id'];
			$signname = $row['signname'];
			$signature = $row['signature'];
			showSign($id,$userid,$signname,$signature);
		}
	}

	showSignNew();

} else if ($mode === 'userpref') {
	$names = array('spam_header');
	$name = isset($_POST['name'])? $_POST['name'] : (isset($_GET['name'])? $_GET['name'] : '');
	if ($name && ! in_array($name, $names)) {
		exit('Bad query.');
	}

	$userid = $xoopsUser->uid();

	$value = (isset($_POST['userpref_value']))? $_POST['userpref_value'] : '';

	if ($value) {
		$table = $xoopsDB->prefix('webmail_userpref');
		$query = 'DELETE FROM `'.$table.'` WHERE uid=\''.$userid.'\' AND name=\''.$name.'\'';

		$msg = '';
		if ($xoopsDB->query($query)) {
			$value = addslashes($value);
			$query = "INSERT INTO `$table` (uid,name,value) values ('$userid','$name','$value')";
			if ($xoopsDB->query($query)) {
				$msg = _MD_WEBMAIL_MSG_SAVED;
			}
		}
		if (! $msg) {
			$msg = _MD_WEBMAIL_ERR_SQL;
		}

		//ob_end_clean();
		redirect_header(XOOPS_URL . '/modules/WebMail/settings.php?mode=userpref&name='.$name, 1, $msg);
		exit();
	}


	OpenTable();
	echo "<div align'center'><a href=\"settings.php\">"._MD_WEBMAIL_MAILBOXESSETTINGS."</a> | <a href=\"settings.php?mode=sign\">"._MD_WEBMAIL_SIGNSETTINGS."</a> | <b>"._MD_WEBMAIL_USERPREFSETTINGS."</b></div>";
	CloseTable();
	echo '<br />';

	foreach($names as $name) {
		$query = "SELECT `value` FROM ".$xoopsDB->prefix('webmail_userpref')." WHERE uid='$userid' AND name='$name' LIMIT 1";

		if ($result = $xoopsDB->query($query)) {
			if ($xoopsDB->getRowsNum($result) > 0) {
				list($value) = $xoopsDB->fetchRow($result);
			}
		}
		showUserpref($name, $value);
	}

} else {

	if(!empty($popserver)) {
	    $userid = $xoopsUser->uid();
	    $rc4 = new rc4crypt();
	    if (!$apop == 1) $apop = 0;
	    $spasswd = $rc4->endecrypt($uname,$passwd,"en");
	    if($leavemsg == "Y") $delete = "N"; else $delete = "Y";
	    if($submit == ""._MD_WEBMAIL_DELETE."") {
			$query = "Delete from ".$xoopsDB->prefix('webmail_popsettings')." where id='$id'";
			$msg = _MD_WEBMAIL_MSG_DELETED;
	    } elseif ($type == "new") {
			$query = "Insert into ".$xoopsDB->prefix('webmail_popsettings')." (account,uid,popserver,uname,passwd,port,numshow,deletefromserver,apop,sname,smail) values ('$account','$userid','$popserver','$uname','$spasswd',$port,$numshow,'$delete',$apop,'$sname','$smail')";
	    	$msg = _MD_WEBMAIL_MSG_SAVED;
	    } else {
			$query = "Update ".$xoopsDB->prefix('webmail_popsettings')." set account='$account', popserver = '$popserver', uname = '$uname', passwd = '$spasswd', port = $port, numshow = $numshow, deletefromserver = '$delete' , apop = $apop , sname = '$sname' , smail = '$smail' where id='$id'";
	    	$msg = _MD_WEBMAIL_MSG_SAVED;
	    }
	    if (! $xoopsDB->query($query)) {
	    	$msg = _MD_WEBMAIL_ERR_SQL;
	    }
		ob_end_clean();
		redirect_header(XOOPS_URL . '/modules/WebMail/settings.php', 1, $msg);
		exit();
	}

	ob_end_flush();

	OpenTable();
	echo "<div align'center'><b>"._MD_WEBMAIL_MAILBOXESSETTINGS."</b> | <a href=\"settings.php?mode=sign\">"._MD_WEBMAIL_SIGNSETTINGS."</a> | <a href=\"settings.php?mode=userpref\">"._MD_WEBMAIL_USERPREFSETTINGS."</a></div>";
	CloseTable();
	echo "<br />";

	$port = 110;
	$show = 20;
	$checkbox = "";
	$acc_count = 0;
	$showflag=true;
	$userid = $xoopsUser->uid();
	$apop = 0;
	$query = "select * FROM ".$xoopsDB->prefix('webmail_popsettings')." where uid = $userid";
	if(!$result=$xoopsDB->query($query)){
		echo "ERROR";
	}
	if($xoopsDB->getRowsNum($result) > 0) {
	    $acc_count = $xoopsDB->getRowsNum($result);
	    $rc = new rc4crypt();
	    while($row = $xoopsDB->fetchArray($result) ) {
			$id = $row[id];
			$account = $row[account];
			$popserver = $row[popserver];
			$port = $row[port];
			$uname = $row[uname];
			$apop = $row[apop];
			$sname = $row[sname];
			$smail = $row[smail];
			$passwd = $rc->endecrypt($uname,$row[passwd],"de");
			$delete = $row[deletefromserver];
			$show = $row[numshow];
			if($delete == "Y") $checkbox = "checked";
			showSettings($account,$popserver, $uname,$passwd, $port,$show,$checkbox,$id,$apop,$sname,$smail);
			if ($popserver == $defaultpopserver) $showflag = false;
	    }
	}
	if (($defaultpopserver != "") && $showflag) {
	    showSingle($defaultpopserver, $singleaccountname);
	}

	if ($singleaccount == 0 && ($numaccounts == -1) || ($acc_count < $numaccounts)) {
	    showNew();
	}
}

include(XOOPS_ROOT_PATH."/footer.php");
exit();

function showSettings($account,$popserver, $uname,$passwd, $port,$show,$checkbox,$id,$apop,$sname,$smail) {
    global $bgcolor1, $bgcolor2, $bgcolor3, $module_name, $singleaccount, $defaultpopserver ,$email_addr;
    OpenTable();
    echo "<form method=\"post\" action='settings.php' name=\"formpost\">"
	    ."<input type=\"hidden\" name=\"id\" value=\"$id\">"
        ."<input type=\"hidden\" name=\"type\" value=\"$account\">"
        ."<input type=\"hidden\" name=\"account\" value=\"$account\">"
        ."<table width=\"80%\" align=\"center\" border=\"0\">"
        ."<tr class='bg2'><td bgcolor=\"$bgcolor2\" colspan=\"2\"><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>$account</b></td></tr>";

	echo "<tr><td align=left>"._MD_WEBMAIL_ACCOUNTNAME.":</td><td><input type=text name=account value=\"$account\" size=40 maxlength=\"50\"></td></tr>";

    if ($singleaccount == 1 AND $defaultpopserver != "") {
	echo "<tr><td align=\"left\">"._MD_WEBMAIL_POPSERVER.":</td><td><input type=\"hidden\" name=\"popserver\" value=\"$popserver\">$popserver</td></tr>";
    } else {
	echo "<tr><td align=\"left\">"._MD_WEBMAIL_POPSERVER.":</td><td><input type=\"text\" name=\"popserver\" value=\"$popserver\" size=\"40\"></td></tr>";
    }

	if ($apop == 1) {$apop_check = " CHECKED";} else {$apop_check = "";}

	echo "<tr><td align=\"left\">"._MD_WEBMAIL_USERNAME.":</td><td><input type=\"text\" name=\"uname\" size=\"20\" value=\"$uname\"></td></tr>"
        ."<tr><td align=\"left\">"._MD_WEBMAIL_PASSWORD.":</td><td><input type=\"password\" name=\"passwd\" size=\"20\" value=\"$passwd\"></td></tr>"
        ."<tr><td>&nbsp;</td><td><font class=\"tiny\"><i>"._MD_WEBMAIL_PASSWORDSECURE."</i></font></td></tr>"
        ."<tr><td align=\"left\">"._MD_WEBMAIL_PORT.":</td><td><input type=\"text\" name=\"port\" size=\"6\" maxlength=\"5\" value=\"$port\"> </td></tr>"
        ."<tr><td align=\"left\">"._MD_WEBMAIL_APOP.":</td><td><input type=\"checkbox\" name=\"apop\" value=\"1\"$apop_check>"._MD_WEBMAIL_APOP_HINT."</td></tr>"
        ."<tr><td align=\"left\">"._MD_WEBMAIL_MESSAGESPERPAGE.":</td><td><input type=\"text\" name=\"numshow\" size=\"3\" maxlength=\"2\" value=\"$show\" value=\"10\"></td></tr>";

	if ($email_addr == '1'){
		echo "<tr><td align=\"left\">"._MD_WEBMAIL_SENDNAME.":</td><td><input type=\"text\" name=\"sname\" value=\"$sname\" size=\"40\"></td></tr>"
		."<tr><td align=\"left\">"._MD_WEBMAIL_SENDEMAIL.":</td><td><input type=\"text\" name=\"smail\" value=\"$smail\" size=\"40\"></td></tr>"
		."<tr><td align=\"left\" colspan=\"2\">"._MD_WEBMAIL_SENDHINT."</td></tr>";
	}

    echo "<tr><td colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\""._MD_WEBMAIL_SAVE."\">&nbsp;&nbsp;<input type=\"submit\" name=\"submit\" value=\""._MD_WEBMAIL_DELETE."\"></td></tr>"
        ."</table></form>";
    CloseTable();
    echo "<br />";
}

function showNew() {
 //   global $bgcolor1, $bgcolor2, $bgcolor3, $module_name;
	global $email_addr;
    OpenTable();
    echo "<form method=post action='settings.php' name=formpost>
        <table width=80% align=center>
        <tr class='bg2'><td bgcolor='' colspan=2>&nbsp;<b>New Mail Account</b></td></tr>
        <tr><td align=left>"._MD_WEBMAIL_ACCOUNTNAME.":</td><td><input type=text name=account value=\"\" size=40 maxlength=\"50\"></td></tr>
        <tr><td align=left>"._MD_WEBMAIL_POPSERVER.":</td><td><input type=text name=popserver value=\"\" size=40></td></tr>
        <tr><td align=left>"._MD_WEBMAIL_USERNAME.":</td><td><input type=text name=uname size=20 value=\"\" autocomplete=\"off\"> </td></tr>
        <tr><td align=left>"._MD_WEBMAIL_PASSWORD.":</td><td><input type=password name=passwd size=20 value=\"\" autocomplete=\"off\"></td></tr>
        <tr><td align=left>"._MD_WEBMAIL_PORT.":</td><td><input type=text name=port size=6 maxlength=\"5\" value=\"110\"></td></tr>
        <tr><td align=left>"._MD_WEBMAIL_APOP.":</td><td><input type=\"checkbox\" name=\"apop\" value=\"1\">"._MD_WEBMAIL_APOP_HINT."</td></tr>
        <tr><td align=left>"._MD_WEBMAIL_MESSAGESPERPAGE.":</td><td><input type=text name=numshow size=3 maxlength=\"2\" value=\"10\"></td></tr>";
	if ($email_addr == '1'){
		echo "<tr><td align=\"left\">"._MD_WEBMAIL_SENDNAME.":</td><td><input type=\"text\" name=\"sname\" value=\"$sname\" size=\"40\"></td></tr>
		<tr><td align=\"left\">"._MD_WEBMAIL_SENDEMAIL.":</td><td><input type=\"text\" name=\"smail\" value=\"$smail\" size=\"40\"></td></tr>
		<tr><td align=\"left\" colspan=\"2\">"._MD_WEBMAIL_SENDHINT."</td></tr>";
	}
    echo "<input type=hidden name=type value=\"new\">
        <tr><td colspan=2><input type=submit name=submit value=\""._MD_WEBMAIL_ADDNEW."\"></td></tr></table></form>";
    CloseTable();
}

function showSingle($defaultpopserver, $singleaccountname) {
//    global $bgcolor1, $bgcolor2, $bgcolor3, $module_name;
    OpenTable();
    echo "<br />
          <form method=post action='settings.php' name=formpost>
          <input type=hidden name=type value=\"new\">
	      <input type=hidden name=port value=110>
	      <input type=hidden name=account value=\"$singleaccountname\">
          <input type=hidden name=popserver value=\"$defaultpopserver\">
          <table width=80% align=center>
          <tr><td bgcolor='' colspan=2>&nbsp;<b>$singleaccountname</b></td><td>&nbsp</td></tr>
          <tr><td align=left>"._MD_WEBMAIL_USERNAME.":</td><td><input type=text name=uname size=20 value=\"\"></td></tr>
          <tr><td align=left>"._MD_WEBMAIL_PASSWORD.":</td><td><input type=password name=passwd size=20 value=\"\"></td></tr>
          <tr><td align=left>"._MD_WEBMAIL_MESSAGESPERPAGE.":</td><td><input type=text name=numshow size=3 maxlength=\"2\" value=\"10\"></td></tr>
          <tr><td colspan=2><input type=submit name=submit value=\""._ADD."\"></td></tr></table></form>";
    CloseTable();
}

function showSign($id,$uid,$signname,$signature){
    OpenTable();
    echo "<br />
		<form method=post action='settings.php'>
		<input type=hidden name=type value=\"signupdate\">
		<input type=hidden name=id value=\"$id\">
		<input type=hidden name=mode value=\"sign\">
		<table width=80% align=center>
		<tr><td bgcolor=\"$bgcolor2\" colspan=\"2\" class='bg2'><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>$signname</b></td></tr>
		<tr><td align=left>"._MD_WEBMAIL_SIGNNAME.":</td><td><input type=text name=signname size=20 value=\"$signname\"></td></tr>
		<tr><td align=left>"._MD_WEBMAIL_SIGN.":</td><td><textarea class=\"norich\" name=\"signature\" cols=\"60\" rows=\"3\">$signature</textarea></td></tr>
		<tr><td colspan=2><input type=\"submit\" name=\"submit\" value=\""._MD_WEBMAIL_SAVE."\">&nbsp;&nbsp;<input type=\"submit\" name=\"submit\" value=\""._MD_WEBMAIL_DELETE."\"></td></tr></table></form>";
    CloseTable();
}

function showSignNew(){
    OpenTable();
    echo "<br /><form method=post action='settings.php'>
		<input type=hidden name=type value=\"signnew\">
		<input type=hidden name=mode value=\"sign\">
		<table width=80% align=center>
		<tr><td bgcolor=\"$bgcolor2\" colspan=\"2\" class='bg2'><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>New Mail Signature</b></td></tr>
		<tr><td align=left>"._MD_WEBMAIL_SIGNNAME.":</td><td><input type=text name=signname size=20 value=\"\"></td></tr>
		<tr><td align=left>"._MD_WEBMAIL_SIGN.":</td><td><textarea class=\"norich\" name=\"signature\" cols=\"60\" rows=\"3\"></textarea></td></tr>
		<tr><td colspan=2><input type=submit name=submit value=\""._MD_WEBMAIL_ADDNEW."\"></td></tr></table></form>";
    CloseTable();
}

function showUserpref($name, $value) {
    OpenTable();
    echo "<br />
		<form method=post action='settings.php'>
		<input type=hidden name=mode value=\"userpref\">
		<input type=hidden name=name value=\"{$name}\">
		<table width=80% align=center>
		<tr><td bgcolor=\"$bgcolor2\" colspan=\"2\" class='bg2'><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>".constant('_MD_WEBMAIL_'.strtoupper($name).'_TITLE')."</b></td></tr>
		<tr><td align=left>".constant('_MD_WEBMAIL_'.strtoupper($name)).":</td><td><textarea class=\"norich\" name=\"userpref_value\" cols=\"60\" rows=\"3\">".htmlspecialchars($value)."</textarea></td></tr>
		<tr><td colspan=2><input type=\"submit\" name=\"submit\" value=\""._MD_WEBMAIL_SAVE."\"></td></tr></table></form>";
    CloseTable();
}
