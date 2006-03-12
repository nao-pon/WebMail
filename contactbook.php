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
require_once("cache/config.php");
        if($show_right==true)
	        $xoopsOption['show_rblock'] =1;
        else
                $xoopsOption['show_rblock'] =0;
	include(XOOPS_ROOT_PATH."/header.php");

global $xoopsDB, $xoopsUser;

include ("mailheader.php");

$nav_bar = "[ <a href='contactbook.php?op=listall'>"._LISTALL."</a> | <a href='contactbook.php?op=addnew'>"._ADDNEW."</a> | <a href='contactbook.php?op=search'>"._WM_SEARCH."</a> ]";
$userid = $xoopsUser->uid();

$op = ($_GET['op'])? $_GET['op'] : $_POST['op'];
$cid = ($_GET['cid'])? $_GET['cid'] : $_POST['cid'];
$cb_index = ($_GET['cb_index'])? $_GET['cb_index'] : $_POST['cb_index'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$homephone = $_POST['homephone'];
$workphone = $_POST['workphone'];
$address = $_POST['address'];
$city = $_POST['city'];
$company = $_POST['company'];
$homepage = $_POST['homepage'];
$IM = $_POST['IM'];
$events = $_POST['events'];
$reminders = $_POST['reminders'];
$notes = $_POST['notes'];
$save = $_POST['save'];
$q = $_POST['q'];
$searchfield = $_POST['searchfield'];
$searchdb = $_POST['searchdb'];
$del = $_POST['del'];

$add_new_from = (!empty($_GET['from']))? $_GET['from'] : "";

OpenTable();
 echo "<div align'center'><b>"._ADDRESSBOOK.": ".$nav_bar."</b></div>";
CloseTable();
echo "<br>";

if ($op=="addnew") {
    addnew($add_new_from);
} elseif ($op == "search") {
    search();
} elseif ($op == "view") {
    view();
} elseif ($op == "delete") {
    del();
} elseif ($op == "edit") {
    edit();
} else {
   listall();
}
include(XOOPS_ROOT_PATH."/footer.php");

function listall() {
    global $xoopsDB, $xoopsUser, $userid, $cb_index, $email_send;
    OpenTable();
    $countlimit = 20;
    $query = "select * FROM ".$xoopsDB->prefix("contactbook")." where uid = $userid order by firstname";
        	if(!$result=$xoopsDB->query($query,$options[0],0)){
		echo "ERROR";
	}
    $res = $xoopsDB->query($query,$options[0],0);
    echo "<form name=\"listform\" method=\"post\" action='contactbook.php'>
	<input type=\"hidden\" name=\"op\" value=\"delete\">
	<table width=\"100%\" align=\"center\" border=\"0\"><tr class='bg2' bgcolor=\"$bgcolor2\"><td width=\"3%\" align=\"center\"><b>"._VIEW."</b></td><td width=\"3%\" align=\"center\"><b>"._EDIT."</b></td><td width=\"3%\">&nbsp;</td><td width=\"28%\"><b>"._NAME."</b></td><td width=\"30%\"><b>"._EMAIL."</b></td><td width=\"15%\"><b>"._PHONERES."</b></td><td width=\"15%\"><b>"._PHONEWORK."</b></td></tr>";
    $numrows = $xoopsDB->getRowsNum($res);
    if($numrows == 0) {
	echo "<tr><td colspan=\"7\" align=\"center\">"._NORECORDSFOUND."</td></tr>";
    }
    $color = "$bgcolor1";
    $count = 0;
    if(isset($cb_index)) {
	$skipcount = $cb_index * $countlimit;
        mysql_data_seek($res,$skipcount);
    }
    while($count < $countlimit && $row = $xoopsDB->fetchArray($res) ) {
	$contactid = $row[contactid];
	$firstname = $row[firstname];
	$lastname = $row[lastname];
	$email = $row[email];
	$homephone = $row[homephone];
	$workphone = $row[workphone];
	if ($email_send == 1) {
	    $esend = "compose.php?to=$email";
	} else {
	    $esend = "mailto:$email";
	}
	echo "<tr bgcolor=\"$bgcolor2\"><td align=\"center\"><a href='contactbook.php?op=view&cid=$contactid'><img src='images/view.gif' alt=\""._VIEWPROFILE."\" title=\""._VIEWPROFILE."\" border=\"0\" width=\"16\" height=\"12\"></a></td><td align=\"center\"><a href='contactbook.php?op=edit&cid=$contactid'><img src='images/edit.gif' border=\"0\" alt=\""._EDITCONTACT."\" title=\""._EDITCONTACT."\" width=\"16\" height=\"16\"></a></td><td><input type=\"checkbox\" name=\"del[]\" value=\"$contactid\"></td><td>$lastname $firstname</td><td><a href=\"$esend\">$email</a></td><td>$homephone</td><td>$workphone</td></tr>";
	if($color == "$bgcolor1") {
	    $color = "$bgcolor2";
	} else {
	    $color = "$bgcolor1";
	}
	$count++;
    }
    echo "</table><br><input type=\"submit\" name=\"deleteall\" value=\""._WM_DELETESELECTED."\"></form>";
    echo "<center>";
    if($cb_index > 0) {
	$ind = $cb_index-1;
	echo "<a href='contactbook.php?op=listall&cb_index=$ind'>"._PREVIOUS."</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    $limit = $numrows/$countlimit;
    if($limit > 1) {
	for($i=0; $i < $limit; $i++) {
	    $ind = $i+1;
	    if($cb_index == $i) echo "$ind ";
            else echo "<a href='contactbook.php?op=listall&cb_index=$i'>$ind</a>&nbsp;";
	}
    }
    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
    if (($skipcount + $count) < $numrows) {
	$ind = $cb_index + 1;
	echo "<a href='contactbook.php?op=listall&cb_index=$ind'>"._NEXT."</a></center>";
    }
    CloseTable();
}

function addnew($add_new_from="") {
    global $xoopsDB, $xoopsUser, $userid, $save, $firstname, $lastname, $email, $company, $homeaddress, $homepage, $city, $prefix, $homephone, $workphone, $IM, $events, $reminders, $notes, $imgpath, $dbi, $module_name;
    OpenTable();
    if(isset($save)) {
	$query = "insert into ".$xoopsDB->prefix("contactbook")." (uid,firstname,lastname,email,company,homeaddress,city,homepage,homephone,workphone,IM,events,reminders,notes) values($userid,'$firstname','$lastname','$email','$company','$homeaddress','$city','$homepage','$homephone','$workphone','$IM','$events','$reminders','$notes');";
            	if(!$result=$xoopsDB->query($query,$options[0],0)){
		echo "ERROR";
	        }
	listall();
	} else {
		if (strpos($add_new_from," ") !== FALSE)
		{
			preg_match("/^(.*) ([^ ]+)/",$add_new_from,$match);
			$add_new_name = preg_replace("/(\"|'|\(|<)(.+)(\"|\"|'|\)|>)/","$2",stripslashes(trim($match[1])));
			$add_new_mail = preg_replace("/(\"|'|\(|<)(.+)(\"|'|\)|>)/","$2",stripslashes(trim($match[2])));
			list($add_new_lname,$add_new_name) = array_pad(explode(" ",$add_new_name,2),2,"");
			//list($add_new_name,$add_new_mail) = preg_split("/ (?![^ ]+)/",$add_new_from);
			
		}
		else
		{
			$add_new_lname = $add_new_mail = $add_new_from;
			$add_new_name = "";
		}
	echo "<form name=\"addnew\" method=\"post\" action='contactbook.php'>
	    <b>"._ADDNEWCONTACT."</b><br><br>
	    <table border=\"0\">
	    <tr><td width=\"25%\">"._LASTNAME.":</td><td><input type=\"text\" name=\"lastname\" value=\"".htmlspecialchars($add_new_lname)."\" size=\"40\"></td></tr>
	    <tr><td>"._FIRSTNAME.":</td><td><input type=\"text\" name=\"firstname\" value=\"".htmlspecialchars($add_new_name)."\" size=\"40\"></td></tr>
	    <tr><td>"._EMAIL.":</td><td><input type=\"text\" name=\"email\" value=\"".htmlspecialchars($add_new_mail)."\" size=\"60\"></td></tr>
	    <tr><td>"._PHONERES.":</td><td><input type=\"text\" name=\"homephone\" size=\"30\"></td></tr>
	    <tr><td>"._PHONEWORK.":</td><td><input type=\"text\" name=\"workphone\" size=\"30\"></td></tr>
	    <tr><td>"._ADDRESS.":</td><td><textarea name=\"address\" rows=\"4\" cols=\"25\"></textarea></td></tr>
	    <tr><td>"._CITY.":</td><td><input type=\"text\" name=\"city\"></td></tr>
	    <tr><td>"._COMPANY.":</td><td><input type=\"text\" name=\"company\" size=\"60\"></td></tr>
	    <tr><td>"._HOMEPAGE.":</td><td><input type=\"text\" name=\"homepage\" size=\"60\" value=\"http://\"></td></tr>
	    <tr><td><br><br></td></tr>
	    <tr><td valign=top>"._IMIDS."</td><td>"._IMIDSMSG."<br><textarea name=IM rows=4 cols=25>
Yahoo: 
MSN: 
ICQ: 
AIM: 
	    </textarea></td></tr>
	    <tr><td><br><br></td></tr>
	    <!--
	    <tr><td valign=top>"._RELATEDEVENTS.":</td><td>"._RELATEDEVENTSMSG."<br>
	    <textarea name=events rows=4 cols=40></textarea></td></tr>
	    <tr><td>"._REMINDME.":</td><td><input type=text name=reminders size=3 value=1> "._DAYSBEFORE."</td></tr>
	    <tr><td><br><br></td></tr>
	    -->
	    <tr><td>"._NOTES.":</td><td><textarea name=notes rows=4 cols=40></textarea></td></tr></table>
	    <input type=hidden name=save value='true'>
	    <input type=hidden name=op value='addnew'>
	    <input type=submit name=add value=\""._WM_SUBMIT."\"></form>";
    }
    CloseTable();
}

function search() {
    global $xoopsDB, $xoopsUser, $userid, $q, $searchdb, $searchfield, $cb_index, $bgcolor1, $bgcolor2, $bgcolor3, $imgpath, $prefix, $dbi, $module_name;
    OpenTable();
    echo "<center><b>"._WM_SEARCHCONTACT."</b></center><br>";
    echo "<form method=post action='contactbook.php' name=searchform>
	<input type=hidden name=op value=search>
	<table align=center><tr><Td>"._WM_SEARCH.": </td><td><input type=text name=q value='$q'></td>
	<td> "._IN." </td><td>
	<select name=searchfield>
	<option value='all'>"._WM_ALL."</option>
	<option value='firstname'>"._FIRSTNAME."</option>
	<option value='lastname'>"._LASTNAME."</option>
	<option value='email'>"._EMAIL."</option>
	<option value='homeaddress'>"._ADDRESS."</option>
	<option value='city'>"._CITY."</option>
	<option value='company'>"._COMPANY."</option>
	<option value='notes'>"._NOTES."</option>
	</select>
	<input type=hidden name=searchdb value='"._WM_SEARCH."'>
        </td><td>&nbsp;<input type=submit value='"._WM_SEARCH."'></td></tr></table></form>";
    if($searchdb == ""._WM_SEARCH."") {
	$query = "Select * from ".$xoopsDB->prefix("contactbook")." where uid = $userid and ( ";
	if($searchfield != "all") {
	    $words = explode(" ",$q);
	    foreach($words as $w) {
		$condition = " ($searchfield like '%$w%') ||";
	    }
	    $condition = substr($condition,0,-2) . ")";
	} else {
	    $searchfield = array ("firstname","lastname","email","homeaddress","city","company","notes");
	    foreach($searchfield as $sf) {
		$words = explode(" ",$q);
		foreach($words as $w) {
		    $condition .= " ($sf like '%$w%') ||";
                }
            }
	    $condition = substr($condition,0,-2) . ")";
	}
	$query .= $condition;
	$res = $xoopsDB->query($query,$options[0],0);
	$numrows = $xoopsDB->getRowsNum($res);
	echo "<form method=post action='contactbook.php' name=searchform>
	    <input type=\"hidden\" name=\"op\" value=\"delete\">";
	echo "<Br><center>$numrows "._RESULTSFOUND."</center><br>
	    <table width=\"100%\" align=\"center\" border=\"0\"><tr class='bg2'><td width=\"3%\" align=\"center\"><b>"._VIEW."</b></td><td width=\"3%\" align=\"center\"><b>"._EDIT."</b></td><td width=\"3%\">&nbsp;</td><td width=\"28%\"><b>"._NAME."</b></td><td width=\"30%\"><b>"._EMAIL."</b></td><td width=\"15%\"><b>"._PHONERES."</b></td><td width=\"15%\"><b>"._PHONEWORK."</b></td></tr>";
	$skipcount = 0; $count = 0; $countlimit = 20;
	if(isset($cb_index)) {
	    $skipcount = $cb_index * $countlimit;
	    mysql_data_seek($res,$skipcount);
	}
	while($count < $countlimit && $row = $xoopsDB->fetchArray($res)) {
	    $contactid = $row[contactid];
	    $firstname = $row[firstname];
    	    $lastname = $row[lastname];
    	    $email = $row[email];
    	    $homephone = $row[homephone];
    	    $workphone = $row[workphone];
    	    echo "<tr bgcolor=\"$bgcolor2\"><td align=\"center\"><a href='contactbook.php?op=view&cid=$contactid'><img src='images/view.gif' alt=\""._VIEWPROFILE."\" title=\""._VIEWPROFILE."\" border=\"0\" width=\"16\" height=\"12\"></a></td><td align=\"center\"><a href='contactbook.php?op=edit&cid=$contactid'><img src='images/edit.gif' border=\"0\" alt=\""._EDITCONTACT."\" title=\""._EDITCONTACT."\" width=\"16\" height=\"16\"></a></td><td><input type=\"checkbox\" name=\"del[]\" value=\"$contactid\"></td><td>$firstname, $lastname</td><td><a href='compose.php?to=$email'>$email</a></td><td>$homephone</td><td>$workphone</td></tr>";
    	    if($color== "$bgcolor1") $color = "$bgcolor2"; else $color = "$bgcolor1";
    	    $count++;
	}
	echo "</table><br><input type=\"submit\" name=\"deleteall\" value=\""._WM_DELETESELECTED."\"></form>&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<center>";
	if($cb_index > 0) {
	    $ind = $cb_index-1;
	    echo "<a href='contactbook.php?op=search&index=$ind'>"._PREVIOUS."</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	$limit = $numrows/$countlimit;
	if($limit > 1) {
	    for($i=0; $i < $limit; $i++) {
		$ind = $i+1;
		if($cb_index == $i) echo "$ind ";
		else echo "<a href='contactbook.php?op=search&index=$i'>$ind</a>&nbsp";
	    }
	}
	echo "&nbsp;&nbsp;&nbsp;&nbsp;";
	if(($skipcount + $count) < $numrows) {
	    $ind = $cb_index + 1;
	    echo "<a href='contactbook.php?op=search&index=$ind'>"._NEXT." ?/a></center>";
	}
    }
    CloseTable();
}

function view() {
    global $xoopsDB, $xoopsUser, $userid, $cid, $domain, $imgpath, $bgcolor1, $bgcolor2, $bgcolor3, $prefix, $dbi, $module_name;
    OpenTable();
    $query = "Select * from ".$xoopsDB->prefix("contactbook")." where uid='$userid' and contactid='$cid'";
    $res = $xoopsDB->query($query,$options[0],0);
    if($xoopsDB->getRowsNum($res) == 0) {
	echo "<center>"._NORECORDSFOUND."</center>";
    }
    if($row = $xoopsDB->fetchArray($res)){
    	$contactid = $row[contactid];
	$uid = $row[uid];
	if($uid != $userid) {
	    echo "<center><b>Error : Permission Denied</b></center>";
    	    return;
	}
	$firstname = $row[firstname];
	$lastname = $row[lastname];
	$email = $row[email];
	$homephone = $row[homephone];
        $workphone = $row[workphone];
	$homeaddress = $row[homeaddress];
        $city = $row[city];
	$company = $row[company];
        $homepage = $row[homepage];
	$IM = $row[IM];
        $events = $row[events];
	$reminders = $row[reminders];
        $notes = $row[notes];
    }
    if ($homepage == "" OR $homepage == "http://") {
	$homepage = "";
    } else {
	$homepage = "<a href=\"$homepage\" target=\"new\">$homepage</a>";
    }
    if ($email != "") {
	$email = "<a href='compose.php?to=$email'>$email</a>";
    }
    echo "<center><b>"._VIEWPROFILE."</b></center><br>
	<table width=90% align=center>
	<tr><td width=20%><b>"._LASTNAME.":</b></td><td>$lastname</td></tr>
	<tr><td><b>"._FIRSTNAME.":</b></td><td>$firstname</td></tr>
	<tr><td><b>"._EMAIL.":</b></td><td>$email</td></tr>
	<tr><td><b>"._PHONERES.":</b></td><td>$homephone</td></tr>
	<tr><td><b>"._PHONEWORK.":</b></td><td>$workphone</td></tr>
	<tr><td><b>"._ADDRESS.":</b></td><td>$homeaddress</td></tr>
	<tr><td><b>"._CITY.":</b></td><td>$city</td></tr>
	<tr><td><b>"._COMPANY.":</b></td><td>$company</td></tr>
	<tr><td><b>"._HOMEPAGE.":</b></td><td>$homepage</td></tr>
	<tr><td colspan=2><hr width=100% noshade size=1></td></tr>
	<tr><td valign=top colspan=2><b>"._IMIDS.":</b></td></tr>";
    echo "<tr><td colspan=2><table width=80% align=center>";
    $listim = explode("\n",$IM);
    foreach($listim as $item) {
	$array = explode(":",$item);
	if ($array[1] != "") {
    	    echo "<tr><td><b>$array[0]:</b></td><td width=100%>$array[1]</td></tr>";
	}
    }
    /*
    echo "</table></td></tr>
	<tr><td colspan=2><hr width=100% size=1 noshade></td></tr>
	<tr><td colspan=2 valign=top><b>"._RELATEDEVENTS.":</b></td></tr>";
    echo "<tr><td colspan=2><table width=80% align=center>";
    $listevents = explode("\n",$events);
    foreach($listevents as $ev) {
	$array = explode(":",$ev);
	if ($array[1] != "") {
    	    echo "<tr><td><b>$array[0]:</b></td><td width=100%>$array[1]</td></tr>";
	}
    }
    */
    echo "</table></td></tr>
	<tr><td colspan=2><hr width=100% size=1 noshade></td></tr>
	<tr><td><b>"._NOTES.":</b></td><td>$notes</td></tr></table><br><br>";
    CloseTable();
}

function del() {
    global $xoopsDB, $xoopsUser, $userid, $del, $prefix, $dbi;
    if(is_array($del)) {
	foreach ($del as $d) {
	    $q = "select * from ".$xoopsDB->prefix("contactbook")." where uid='$userid' and contactid='$d'";
            $r = $xoopsDB->query($q,$options[0],0);
            if($xoopsDB->getRowsNum($r) > 0) {
        	$query = "delete from ".$xoopsDB->prefix("contactbook")." where contactid='$d'";
                $res = $xoopsDB->query($query,$options[0],0);
            }
	}
    } else {
        $q = "select * from ".$xoopsDB->prefix("contactbook")." where uid='$userid' and contactid='$del'";
        $r = $xoopsDB->query($q,$options[0],0);
        if($xoopsDB->getRowsNum($r) > 0) {
            $query = "delete from ".$xoopsDB->prefix("contactbook")." where contactid='$del'";
            $res = $xoopsDB->query($query,$options[0],0);
        }
    }
    listall();
}

function edit() {
    global $xoopsDB, $xoopsUser, $dbi, $userid, $cid, $save, $userid, $firstname, $lastname, $email, $company, $homeaddress, $homepage, $city, $homephone, $workphone, $IM, $events, $reminders, $notes, $bgcolor1, $bgcolor2, $bgcolor3, $imgpath, $prefix, $module_name;
    OpenTable();
    if($save == "true") {
	$query = "update ".$xoopsDB->prefix("contactbook")." set firstname='$firstname', lastname='$lastname', email='$email', homephone = '$homephone', workphone ='$workphone', homeaddress= '$homeaddress', city = '$city', company = '$company', homepage= '$homepage',IM = '$IM', events = '$events', reminders = '$reminders',notes = '$notes' where contactid = $cid";
	$res = $xoopsDB->query($query,$options[0],0);
	listall(); 
        CloseTable();
	return;
    }
    $query = "Select * from ".$xoopsDB->prefix("contactbook")." where uid='$userid' and contactid='$cid'";
    $res = $xoopsDB->query($query,$options[0],0);
    if($row = $xoopsDB->fetchArray($res)) {
	$uid = $row[uid];
	if($uid != $userid) {
	    echo "<center><b>Error: Permission Denied</b></center>";
	    return;
	}
	$firstname = $row[firstname];
        $lastname = $row[lastname];
        $email = $row[email];
        $homephone = $row[homephone];
        $workphone = $row[workphone];
        $homeaddress = $row[homeaddress];
        $city = $row[city];
        $company = $row[company];
        $homepage = $row[homepage];
        $IM = $row[IM];
        $events = $row[events];
        $reminders = $row[reminders];
        $notes = $row[notes];
    }
    echo "<form name=editform method=post action='contactbook.php'>
	<b>"._EDITCONTACTS."</b></font><br><br>
	<table border=0 width=90%>
	<tr><td width=25%>"._LASTNAME.":</td><td><input type=text name=lastname value='$lastname'></td></tr>
	<tr><td>"._FIRSTNAME.":</td><td><input type=text name=firstname value='$firstname'></td></tr>
	<tr><td>"._EMAIL.":</td><td><input type=text name=email value='$email'></td></tr>
	<tr><td>"._PHONERES.":</td><td><input type=text name=homephone value='$homephone'></td></tr>
	<tr><td>"._PHONEWORK.":</td><td><input type=text name=workphone value='$workphone'></td></tr>
	<tr><td>"._ADDRESS.":</td><td><textarea name=homeaddress rows=4 cols=25>$homeaddress</textarea></td></tr>
	<tr><td>"._CITY.":</td><td><input type=text name=city value='$city'></td></tr>
	<tr><td>"._COMPANY.":</td><td><input type=text name=company size=40 value='$company'></td></tr>
	<tr><td>"._HOMEPAGE.":</td><td><input type=text name=homepage size=40 value='$homepage'></td></tr>
	<tr><td><br><br></td></tr>
	<tr><td valign=top>"._IMIDS.":</td><td>"._IMIDSMSG."<br><textarea name=IM rows=4 cols=25>$IM</textarea></td></tr>
	<!--
	<tr><td colspan=2><br><br></td></tr>
	<tr><td valign=top>"._RELATEDEVENTS.":</td><td>"._RELATEDEVENTSMSG."<br><textarea name=events rows=4 cols=40>$events</textarea></td></tr>
	<tr><td>"._REMINDME.":</td><td><input type=text name=reminders value='$reminders'size=3 value=1> "._DAYSBEFORE."</td></tr>
	<tr><td><br><br></td></tr>
	-->
	<tr><td>"._NOTES.":</td><td><textarea name=notes rows=4 cols=40>$notes</textarea></td></tr></table>
	<input type=hidden name=save value='true'>
	<input type=hidden name=op value='edit'>
	<input type=hidden name=cid value='$cid'>
	<input type=submit name=add value=\""._WM_SUBMIT."\"></form>";
    CloseTable();
}
include(XOOPS_ROOT_PATH."/footer.php");
?>
