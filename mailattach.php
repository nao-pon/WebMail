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

include("cache/config.php");
include("../../mainfile.php");
include("gettype.php");
	// nao-pon
	global $xoopsUser,$xoopsConfig;
	$userid = $xoopsUser->uid();

//echo $userfile."<br />";
//echo $userfile_name."<br />";

$msg=$java_script="";

//if (isset($userfile) AND $userfile != "none") {
	if (ini_get(file_uploads) AND $attachments == 1) {
		// nao-pon
		$userfile_name = $_FILES['userfile']['name'];
		$userfile_name = urldecode($userfile_name);
		$userfile_name = mb_convert_encoding($userfile_name, "EUC-JP", "auto");
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			@copy($_FILES['userfile']['tmp_name'], $attachmentdir."/".$userid."_".$userfile_name."_d_u_m_");
			@unlink($userfile);
			$M_Type = m_get_type($userfile_name);
			$java_script ="<script>window.opener.attachfiles(\"".$_FILES['userfile']['name']."\",\"".$M_Type."\");</script>";
			$msg = "ファイル\"".$_FILES['userfile']['name']."\"を追加しました。<br /><br />";
		} else {
    		if (strtolower($_SERVER["REQUEST_METHOD"])=="post") $msg = "エラー：添付ファイルを指定してください。<br /><br />";
		}
		//@copy($userfile, $attachmentdir."/".$userid."_".$userfile_name."_d_u_m_");
		//nao-pon edited
		//nao-pon
	}
//}

$sitename = htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES);
echo "<html>\n"
    ."<title>{$sitename}[Web Mailer]: ファイル添付</title>\n"
    ."<body text=\"#63627f\">\n"
    .$java_script."\n"
    ."<form action=\"mailattach.php\" method=\"post\" ENCTYPE=\"multipart/form-data\" name=\"attchform\">\n"
    ."<center>\n"
    .$msg
    ."<b>{$sitename}[Web Mailer]: ファイル添付</b><br /><br />\n"
    ."ファイル: <input type=\"file\" name=\"userfile\" size=\"30\"><br /><input type=\"submit\" value=\"添付ファイル追加\">\n"
    ."</form>\n"
    ."<br /><br /><form><input type=\"button\" value=\"このウィンドウを閉じる\" onClick=\"window.close();\"></form>\n"
    ."</body>\n"
    ."</html>";


// nao-pon edited
?>
