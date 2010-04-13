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

global $xoopsDB, $xoopsUser;
// 非ログインユーザーはログイン画面へ
if (!is_object($xoopsUser))
{
	redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
	exit();
}
define("XOOPS_MODULE_WEBMAIL_LOADED",1);

include("cache/config.php");
include_once("gettype.php");
$userid = $xoopsUser->uid();

$msg=$java_script="";

if (ini_get('file_uploads') && $attachments && ! empty($_FILES)) {
	// nao-pon
	if (! empty($_FILES)) {
		$userfile_name = $_FILES['userfile']['name'];
		$userfile_name = urldecode($userfile_name);
		$userfile_name = mb_convert_encoding($userfile_name, _CHARSET, "auto");
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			@copy($_FILES['userfile']['tmp_name'], $attachmentdir."/".$userid."_".$userfile_name."_d_u_m_");
			@unlink($userfile);
			$M_Type = m_get_type($userfile_name);
			$filename = htmlspecialchars($_FILES['userfile']['name'], ENT_QUOTES);
			$java_script ="<script>window.opener.attachfiles(\"".$filename."\",\"".$M_Type."\");</script>";
			$msg = str_replace('$1', $filename, _MD_WEBMAIL_ATTACHE_ADDED).'<br /><br />';
		}
	}
	if (! $msg && strtolower($_SERVER["REQUEST_METHOD"])=="post") $msg = _MD_WEBMAIL_ERR_NOFILE."<br /><br />";
}

$sitename = htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES);
echo "<html>\n"
    ."<title>{$sitename}[Web Mailer]: "._MD_WEBMAIL_ATTACHE_FILE."</title>\n"
    ."<body text=\"#63627f\">\n"
    .$java_script."\n"
    ."<form action=\"mailattach.php\" method=\"post\" ENCTYPE=\"multipart/form-data\" name=\"attchform\">\n"
    ."<center>\n"
    .$msg
    ."<b>{$sitename}[Web Mailer]: "._MD_WEBMAIL_ATTACHE_FILE."</b><br /><br />\n"
    . _MD_WEBMAIL_FILE . ": <input type=\"file\" name=\"userfile\" size=\"30\"><br /><input type=\"submit\" value=\""._MD_WEBMAIL_ATTACHE_ADD."\">\n"
    ."</form>\n"
    ."<br /><br /><form><input type=\"button\" value=\""._MD_WEBMAIL_CLOSE_WINDOW."\" onClick=\"window.close();\"></form>\n"
    ."</body>\n"
    ."</html>";

