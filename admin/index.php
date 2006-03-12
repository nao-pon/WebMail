<?php

//  ------------------------------------------------------------------------ //
//  Modified 07.10.2002                                                      //
//  by Jochen Gererstorfer                                                   //
//  http://gererstorfer.net                                                  //
//  webmaster@gererstorfer.net                                               //
//  ------------------------------------------------------------------------ //
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

include("admin_header.php");

if (!xoops_refcheck()) redirect_header(XOOPS_URL."/",1,"Access Denied.");

$config_file = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname()."/cache/config.php";
if (file_exists($config_file)) include($config_file);

// DB Upgread J1.5
//$query = "select * FROM ".$xoopsDB->prefix("wmail_sign")." where uid = $userid";
$query = "select * FROM ".$xoopsDB->prefix("wmail_sign")." LIMIT 1;";
if(!$result=$xoopsDB->query($query)){
	//echo "ERROR:must be upgread!";
	//exit;
	$query="CREATE TABLE ".$xoopsDB->prefix("wmail_sign")." ( id int(11) NOT NULL auto_increment, uid int(11) default '0', signname varchar(255) default NULL, signature text, PRIMARY KEY  (id), KEY uid (uid) ) TYPE=MyISAM;";
	if(!$result=$xoopsDB->queryF($query)){
		echo "ERROR: 'wmail_sign' is already processing settled.<br/>";
		echo $query;
	}

}

// init
foreach(array('op','show_rightS','footermsgtxtS','email_sendS','email_addrS','mail_maxS','attachmentsS','attachmentdirS','attachments_viewS','download_dirS','tempfile_timeS','numaccountsS','singleaccountS','singleaccountnameS','defaultpopserverS','filter_forwardS','filter_subjectS','filter_subjectS','html_tag_colorS','html_tag_colorS','html_scr_colorS') as $key)
{
	$_POST[$key] = (empty($_POST[$key]))? "" : $_POST[$key];
}
$op = $_POST['op'];
$show_rightS = $_POST['show_rightS'];
$footermsgtxtS = $_POST['footermsgtxtS'];
$email_sendS = $_POST['email_sendS'];
$email_addrS = $_POST['email_addrS'];
$mail_maxS = $_POST['mail_maxS'];
$attachmentsS = $_POST['attachmentsS'];
$attachmentdirS = $_POST['attachmentdirS'];
$attachments_viewS = $_POST['attachments_viewS'];
$download_dirS = $_POST['download_dirS'];
$tempfile_timeS = $_POST['tempfile_timeS'];
$numaccountsS = $_POST['numaccountsS'];
$singleaccountS = $_POST['singleaccountS'];
$singleaccountnameS = $_POST['singleaccountnameS'];
$defaultpopserverS = $_POST['defaultpopserverS'];
$filter_forwardS = $_POST['filter_forwardS'];
$filter_subjectS = $_POST['filter_subjectS'];
$html_tag_colorS = $_POST['html_tag_colorS'];
$html_scr_colorS = $_POST['html_scr_colorS'];


switch($op){
   case "mailConfigS":
	global $xoopsConfig;
	
	$filter_subjectS = ereg_replace("\r\n", "\n", $filter_subjectS);
	$footermsgtxtS = ereg_replace("\r\n", "\n", $footermsgtxtS);
	$filter_subjectS = ereg_replace("\r", "\n", $filter_subjectS);
	$footermsgtxtS = ereg_replace("\r", "\n", $footermsgtxtS);
	$filename = "../cache/config.php";
	$file = fopen($filename, "w");
	$content = "";
	$content .= "<?php\n";
	$content .= "
\$show_right = $show_rightS;
\$footermsgtxt = '$footermsgtxtS';
\$email_send = '$email_sendS';
\$email_addr = '$email_addrS';
\$mail_max = '$mail_maxS';
\$attachments = '$attachmentsS';
\$attachmentdir = '$attachmentdirS';
\$attachments_view = '$attachments_viewS';
\$download_dir = '$download_dirS';
\$tempfile_time = '".trim($tempfile_timeS)."';
\$numaccounts = '$numaccountsS';
\$singleaccount = '$singleaccountS';
\$singleaccountname = '$singleaccountnameS';
\$defaultpopserver = '$defaultpopserverS';
\$filter_forward = '$filter_forwardS';
\$filter_subject = '$filter_subjectS';
\$html_tag_color = '$html_tag_colorS';
\$html_scr_color = '$html_scr_colorS';
";
 	$content .= "\n?>";

	fwrite($file, $content);
    	fclose($file);

	redirect_header("index.php",1,_AM_DBUPDATED);
	exit();
        break;
        
	case "db_up" :
		xoops_cp_header();
		$query = "ALTER TABLE `".$xoopsDB->prefix("popsettings")."` ADD `apop` INT (1)";
   		if(!$result=$xoopsDB->queryF($query)){
			echo "ERROR: 'apop' is already processing settled.";
		}
		$query = "ALTER TABLE `".$xoopsDB->prefix("popsettings")."` ADD `sname` VARCHAR (255) , ADD `smail` VARCHAR (255)";
   		if(!$result=$xoopsDB->queryF($query)){
			echo "ERROR: 'sname' and 'smail' are already processing settled.";
		}
		redirect_header(XOOPS_ADMIN_URL,1,_AM_DBUPDATED);
		break;
		
    case "default":
    default:

	// nao-pon Default Data
	if (empty($attachmentdir)) $attachmentdir="tmp";
	if (empty($download_dir)) $download_dir="attachments";
	if (empty($tempfile_time)) $tempfile_time="30";
	if (empty($numaccounts)) $numaccounts="-1";
	if (empty($mail_max)) $mail_max="5";
	if (empty($filter_forward)) $filter_forward="1";
	if (empty($html_tag_color)) $html_tag_color="blue";
	if (empty($html_scr_color)) $html_scr_color="red";
	if (empty($show_right)) $show_right = false;
	if (empty($email_send)) $email_send = 0;
	if (empty($email_addr)) $email_addr = 0;
	if (empty($attachments)) $attachments = 0;
	if (empty($attachments_view)) $attachments_view = 0;
	if (empty($singleaccoun)) $singleaccoun = 0;
	if (empty($singleaccount)) $singleaccount = 0;
	if (empty($footermsgtxt)) $footermsgtxt = "";
	if (empty($singleaccountname)) $singleaccountname = "";
	if (empty($defaultpopserver)) $defaultpopserver = "";
	if (empty($filter_subject)) $filter_subject = "";

	global $xoopsConfig, $xoopsModule;
	xoops_cp_header();
	OpenTable();
	echo "<h4>" . _AM_GENERALCONF . "</h4><br>";
	echo "<form action='index.php' method='post'>";
    	echo "
    	<table width='100%' border='5' cellspacing=1 cellpadding=3 class='bg1'>";
	echo "<tr><td class='nw'>" . _AM_SHOW_RIGHT . "</td><td>";
	if ($show_right=='true') {
		echo "<input type='radio' name='show_rightS' value='true' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='show_rightS' value='false' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='show_rightS' value='true' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='show_rightS' value='false' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_FOOTERMSGTXT . "</td><td>";
        echo "<textarea name='footermsgtxtS' cols=40 rows=8 tabindex=1>$footermsgtxt</textarea>";
	echo "</td></tr>";
	
	echo "<tr><td class='nw'>" . _AM_EMAIL_SEND . "</td><td>";
	if ($email_send=='1') {
		echo "<input type='radio' name='email_sendS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='email_sendS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='email_sendS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='email_sendS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";

	echo "<tr><td class='nw'>" . _AM_EMAIL_ADDRESS . "</td><td>";
	if ($email_addr=='1') {
		echo "<input type='radio' name='email_addrS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='email_addrS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='email_addrS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='email_addrS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";

        echo "<tr><td class='nw'>" . _AM_MAIL_SEND_MAX . "</td><td>";
        echo "<input type='text' name='mail_maxS' value='$mail_max' size=10 tabindex=1>";

	echo "</td></tr>";
	echo "<tr><td class='nw'>" . _AM_ATTACHMENTS . "</td><td>";
        if ($attachments=='1') {
		echo "<input type='radio' name='attachmentsS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='attachmentsS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='attachmentsS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='attachmentsS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
        echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_ATTACHMENTDIR . "</td><td>";
        echo "<input type='text' name=' attachmentdirS' value='$attachmentdir' size=50 tabindex=1>";
	echo "</td></tr>";
	echo "<tr><td class='nw'>" . _AM_ATTACHMENTS_VIEW . "</td><td>";
        if ($attachments_view=='1') {
		echo "<input type='radio' name='attachments_viewS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='attachments_viewS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='attachments_viewS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='attachments_viewS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_DOWNLOAD_DIR . "</td><td>";
        echo "<input type='text' name='download_dirS' value='$download_dir' size=50 tabindex=1>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_TEMPFILE_TIME . "</td><td>";
        echo "<input type='text' name='tempfile_timeS' value='$tempfile_time' size=10 tabindex=1>";
	echo "</td></tr>";
	    echo "<tr><td class='nw'>" . _AM_NUMACCOUNTS . "</td><td>";
        echo "<input type='text' name='numaccountsS' value='$numaccounts' size=5 tabindex=1>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_SINGLEACCOUNT . "</td><td>";
        if ($singleaccount=='1') {
		echo "<input type='radio' name='singleaccountS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='singleaccountS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='singleaccountS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='singleaccountS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_SINGLEACCOUNTNAME . "</td><td>";
        echo "<input type='text' name='singleaccountnameS' value='$singleaccountname' size=30 tabindex=1>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_DEFAULTPOPSERVER . "</td><td>";
        echo "<input type='text' name='defaultpopserverS' value='$defaultpopserver' size=30 tabindex=1>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_FILTER_FORWARD . "</td><td>";
        if ($filter_forward=='1') {
		echo "<input type='radio' name='filter_forwardS' value='1' checked='checked' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='filter_forwardS' value='0' />&nbsp;" ._AM_NO."&nbsp;";
	} else {
		echo "<input type='radio' name='filter_forwardS' value='1' />&nbsp;" ._AM_YES."&nbsp;";
		echo "<input type='radio' name='filter_forwardS' value='0' checked='checked' />&nbsp;" ._AM_NO."&nbsp;";
	}
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_FILTER_SUBJECT . "</td><td>";
        echo "<textarea name='filter_subjectS' cols=40 rows=8 tabindex=1>$filter_subject</textarea>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_HTML_TAG_COLOR . "</td><td>";
        echo "<input type='text' name='html_tag_colorS' value='$html_tag_color' size=10 tabindex=1>";
	echo "</td></tr>";
        echo "<tr><td class='nw'>" . _AM_HTML_SCR_COLOR . "</td><td>";
        echo "<input type='text' name='html_scr_colorS' value='$html_scr_color' size=10 tabindex=1>";
	echo "</td></tr>";

    	echo "</table>";
    	echo "<input type='hidden' name='op' value='mailConfigS' />";
    	echo "<center><br /><input type='submit' value='"._AM_SAVECHANGE."' />";
	echo "&nbsp;<input type='button' value='"._AM_CANCEL."' onclick='javascript:history.go(-1)' /></center>";
    	echo "</form>";
    	CloseTable();
		// for DB update nao-pon
    	OpenTable();
		echo "
		<center>
		<form action='index.php' method='post'>
			<input type='hidden' name='op' value='db_up' />
			<input type='submit' value='"._AM_DBUP_B."' /><br />
			"._AM_DBUP_M."
		</form>
		</center>
    	";
    	CloseTable();
    	
        break;
}

xoops_cp_footer();
?>
