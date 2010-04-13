<?php
// $Id$
include("../../mainfile.php");

if (!is_object($xoopsUser))
{
	exit();
}

require_once("cache/config.php");
include("gettype.php");

$_GET["fn"] = str_replace("\0","",$_GET["fn"]);
$_GET["dfn"] = str_replace("\0","",$_GET["dfn"]);

if (preg_match("#\.\./#",$_GET["fn"])) exit;

$dlfilename = urldecode($_GET["dfn"]);
$filename = urldecode($_GET["fn"]);
$filetype = m_get_type($dlfilename);
$workdir = $download_dir;

$size=filesize($workdir."/".$filename);

if (strstr($HTTP_SERVER_VARS["HTTP_USER_AGENT"], "MSIE")) {      // For IE

        //$dlfilename = WfsConvert::filenameForWin($dlfilename);
		$dlfilename = mb_convert_encoding($dlfilename, "SJIS", "auto");

        header("Content-Type: ".$filetype);
        header("Content-Length: $size");

        header("Cache-control: private");
        //header("Content-Disposition: inline; filename=$dlfilename");
        header("Content-Disposition: attachment; filename=\"$dlfilename\"");
}
else {  // For Other browsers
        header("Content-Type: ".$filetype);
        header("Content-Length: $size");
        //if (preg_match("/[^a-zA-Z0-9_\-\.]/",$dlfilename)) $dlfilename=$fileid.".".$file->getExt();
        //header("Content-Disposition: inline; filename=\"$dlfilename\"");
        header("Content-Disposition: attachment; filename=\"$dlfilename\"");

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
}

readfile($workdir."/".$filename);

