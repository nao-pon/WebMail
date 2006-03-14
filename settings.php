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
require_once("cache/config.php");
        if($show_right==true)
	        $xoopsOption['show_rblock'] =1;
        else
                $xoopsOption['show_rblock'] =0;
	include(XOOPS_ROOT_PATH."/header.php");

global $xoopsDB, $xoopsUser;

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
	OpenTable();
	 echo "<div align'center'><a href=\"settings.php\">"._MAILBOXESSETTINGS."</a> | <b>"._WM_SIGNSETTINGS."</b></div>";
	CloseTable();
	echo "<br />";
	$userid = $xoopsUser->uid();
	
	if(isset($signname)) {
	    if($submit == ""._WM_DELETE."") {
			$query = "Delete from ".$xoopsDB->prefix("wmail_sign")." where id='$id'";
	    } elseif ($type == "signnew") {
			$query = "Insert into ".$xoopsDB->prefix("wmail_sign")." (uid,signname,signature) values ('$userid','$signname','$signature')";
	    } else {
			$query = "Update ".$xoopsDB->prefix("wmail_sign")." set signname = '$signname', signature = '$signature' where id='$id'";
	    }
	    $res=$xoopsDB->query($query);
	    if(!$res) {
			echo "error: $query";
	    }
	} 

	$query = "select * FROM ".$xoopsDB->prefix("wmail_sign")." where uid = $userid";
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
	
} else {
	OpenTable();
	 echo "<div align'center'><b>"._MAILBOXESSETTINGS."</b> | <a href=\"settings.php?mode=sign\">"._WM_SIGNSETTINGS."</a></div>";
	CloseTable();
	echo "<br />";

	if(isset($popserver)) {
	    $userid = $xoopsUser->uid();
	    $rc4 = new rc4crypt();
	    if (!$apop == 1) $apop = 0;
	    $spasswd = $rc4->endecrypt($uname,$passwd,"en");
	    if($leavemsg == "Y") $delete = "N"; else $delete = "Y";
	    if($submit == ""._WM_DELETE."") {
			$query = "Delete from ".$xoopsDB->prefix("popsettings")." where id='$id'";
	    } elseif ($type == "new") {
			$query = "Insert into ".$xoopsDB->prefix("popsettings")." (account,uid,popserver,uname,passwd,port,numshow,deletefromserver,apop,sname,smail) values ('$account','$userid','$popserver','$uname','$spasswd',$port,$numshow,'$delete',$apop,'$sname','$smail')";
	    } else {
			$query = "Update ".$xoopsDB->prefix("popsettings")." set account='$account', popserver = '$popserver', uname = '$uname', passwd = '$spasswd', port = $port, numshow = $numshow, deletefromserver = '$delete' , apop = $apop , sname = '$sname' , smail = '$smail' where id='$id'";
	    }
	    $res=$xoopsDB->query($query);
	    if(!$res) {
			echo "error: $query";
	    }
	} 
	$port = 110;
	$show = 20;
	$checkbox = "";
	$acc_count = 0;
	$showflag=true;
	$userid = $xoopsUser->uid();
	$apop = 0;
	$query = "select * FROM ".$xoopsDB->prefix("popsettings")." where uid = $userid";
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

function showSettings($account,$popserver, $uname,$passwd, $port,$show,$checkbox,$id,$apop,$sname,$smail) {
    global $bgcolor1, $bgcolor2, $bgcolor3, $module_name, $singleaccount, $defaultpopserver ,$email_addr;
    OpenTable();
    echo "<table width=\"80%\" align=\"center\" border=\"0\">"
	."<form method=\"post\" action='settings.php' name=\"formpost\">"
	."<input type=\"hidden\" name=\"id\" value=\"$id\">"
        ."<input type=\"hidden\" name=\"type\" value=\"$account\">"
        ."<input type=\"hidden\" name=\"account\" value=\"$account\">"
        ."<tr class='bg2'><td bgcolor=\"$bgcolor2\" colspan=\"2\"><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>$account</b></td></tr>";

	echo "<tr><td align=left>"._ACCOUNTNAME.":</td><td><input type=text name=account value=\"$account\" size=40 maxlength=\"50\"></td></tr>";

    if ($singleaccount == 1 AND $defaultpopserver != "") {
	echo "<tr><td align=\"left\">"._POPSERVER.":</td><td><input type=\"hidden\" name=\"popserver\" value=\"$popserver\">$popserver</td></tr>";
    } else {
	echo "<tr><td align=\"left\">"._POPSERVER.":</td><td><input type=\"text\" name=\"popserver\" value=\"$popserver\" size=\"40\"></td></tr>";
    }

	if ($apop == 1) {$apop_check = " CHECKED";} else {$apop_check = "";}

	echo "<tr><td align=\"left\">"._WM_USERNAME.":</td><td><input type=\"text\" name=\"uname\" size=\"20\" value=\"$uname\"></td></tr>"
        ."<tr><td align=\"left\">"._WM_PASSWORD.":</td><td><input type=\"password\" name=\"passwd\" size=\"20\" value=\"$passwd\"></td></tr>"
        ."<tr><td>&nbsp;</td><td><font class=\"tiny\"><i>"._WM_PASSWORDSECURE."</i></font></td></tr>"
        ."<tr><td align=\"left\">"._PORT.":</td><td><input type=\"text\" name=\"port\" size=\"6\" maxlength=\"5\" value=\"$port\"> </td></tr>"
        ."<tr><td align=\"left\">"._WM_APOP.":</td><td><input type=\"checkbox\" name=\"apop\" value=\"1\"$apop_check>"._WM_APOP_HINT."</td></tr>"
        ."<tr><td align=\"left\">"._MESSAGESPERPAGE.":</td><td><input type=\"text\" name=\"numshow\" size=\"3\" maxlength=\"2\" value=\"$show\" value=\"10\"></td></tr>";
        
	if ($email_addr == '1'){
		echo "<tr><td align=\"left\">"._WM_SENDNAME.":</td><td><input type=\"text\" name=\"sname\" value=\"$sname\" size=\"40\"></td></tr>"
		."<tr><td align=\"left\">"._WM_SENDEMAIL.":</td><td><input type=\"text\" name=\"smail\" value=\"$smail\" size=\"40\"></td></tr>"
		."<tr><td align=\"left\" colspan=\"2\">"._WM_SENDHINT."</td></tr>";
	}
	
    echo "<tr><td colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\""._SAVE."\">&nbsp;&nbsp;<input type=\"submit\" name=\"submit\" value=\""._WM_DELETE."\"></td></tr>"
        ."</table></form>";
    CloseTable();
    echo "<br>";
}

function showNew() {
 //   global $bgcolor1, $bgcolor2, $bgcolor3, $module_name;
	global $email_addr;
    OpenTable();
    echo "<table width=80% align=center>
        <form method=post action='settings.php' name=formpost>
        <tr class='bg2'><td bgcolor='' colspan=2>&nbsp;<b>New Mail Account</b></td></tr>
        <tr><td align=left>"._ACCOUNTNAME.":</td><td><input type=text name=account value=\"\" size=40 maxlength=\"50\"></td></tr>
        <tr><td align=left>"._POPSERVER.":</td><td><input type=text name=popserver value=\"\" size=40></td></tr>
        <tr><td align=left>"._WM_USERNAME.":</td><td><input type=text name=uname size=20 value=\"\"> </td></tr>
        <tr><td align=left>"._WM_PASSWORD.":</td><td><input type=password name=passwd size=20 value=\"\"></td></tr>
        <tr><td align=left>"._PORT.":</td><td><input type=text name=port size=6 maxlength=\"5\" value=\"110\"></td></tr>
        <tr><td align=left>"._WM_APOP.":</td><td><input type=\"checkbox\" name=\"apop\" value=\"1\">"._WM_APOP_HINT."</td></tr>
        <tr><td align=left>"._MESSAGESPERPAGE.":</td><td><input type=text name=numshow size=3 maxlength=\"2\" value=\"10\"></td></tr>";
	if ($email_addr == '1'){
		echo "<tr><td align=\"left\">"._WM_SENDNAME.":</td><td><input type=\"text\" name=\"sname\" value=\"$sname\" size=\"40\"></td></tr>
		<tr><td align=\"left\">"._WM_SENDEMAIL.":</td><td><input type=\"text\" name=\"smail\" value=\"$smail\" size=\"40\"></td></tr>
		<tr><td align=\"left\" colspan=\"2\">"._WM_SENDHINT."</td></tr>";
	}
    echo "<input type=hidden name=type value=\"new\">
        <tr><td colspan=2><input type=submit name=submit value=\""._ADDNEW."\"></form></td></tr></table>";
    CloseTable();
}

function showSingle($defaultpopserver, $singleaccountname) {
//    global $bgcolor1, $bgcolor2, $bgcolor3, $module_name;
    OpenTable();
    echo "<br><table width=80% align=center>
          <form method=post action='settings.php' name=formpost>
          <input type=hidden name=type value=\"new\">
	  <input type=hidden name=port value=110>
	  <input type=hidden name=account value=\"$singleaccountname\">
          <input type=hidden name=popserver value=\"$defaultpopserver\">
          <tr><td bgcolor='' colspan=2>&nbsp;<b>$singleaccountname</b></td><td>&nbsp</td></tr>
          <tr><td align=left>"._WM_USERNAME.":</td><td><input type=text name=uname size=20 value=\"\"></td></tr>
          <tr><td align=left>"._WM_PASSWORD.":</td><td><input type=password name=passwd size=20 value=\"\"></td></tr>
          <tr><td align=left>"._MESSAGESPERPAGE.":</td><td><input type=text name=numshow size=3 maxlength=\"2\" value=\"10\"></td></tr>
          <tr><td colspan=2><input type=submit name=submit value=\""._ADD."\"></form></td></tr></table>";
    CloseTable();
}

function showSign($id,$uid,$signname,$signature){
    OpenTable();
    echo "<br><table width=80% align=center>
		<form method=post action='settings.php'>
		<input type=hidden name=type value=\"signupdate\">
		<input type=hidden name=id value=\"$id\">
		<input type=hidden name=mode value=\"sign\">
		<tr><td bgcolor=\"$bgcolor2\" colspan=\"2\" class='bg2'><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>$signname</b></td></tr>
		<tr><td align=left>"._WM_SIGNNAME.":</td><td><input type=text name=signname size=20 value=\"$signname\"></td></tr>
		<tr><td align=left>"._WM_SIGN.":</td><td><textarea name=\"signature\" cols=\"60\" rows=\"3\">$signature</textarea></td></tr>
		<tr><td colspan=2><input type=\"submit\" name=\"submit\" value=\""._SAVE."\">&nbsp;&nbsp;<input type=\"submit\" name=\"submit\" value=\""._WM_DELETE."\"></td></tr></table></form>";
    CloseTable();
}

function showSignNew(){
    OpenTable();
    echo "<br><table width=80% align=center>
		<form method=post action='settings.php'>
		<input type=hidden name=type value=\"signnew\">
		<input type=hidden name=mode value=\"sign\">
		<tr><td bgcolor=\"$bgcolor2\" colspan=\"2\" class='bg2'><img src='images/arrow.gif' border=\"0\" hspace=\"5\"><b>New Mail Signature</b></td></tr>
		<tr><td align=left>"._WM_SIGNNAME.":</td><td><input type=text name=signname size=20 value=\"\"></td></tr>
		<tr><td align=left>"._WM_SIGN.":</td><td><textarea name=\"signature\" cols=\"60\" rows=\"3\"></textarea></td></tr>
		<tr><td colspan=2><input type=submit name=submit value=\""._ADDNEW."\"></td></tr></table></form>";
    CloseTable();
}
include(XOOPS_ROOT_PATH."/footer.php");
?>
