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
//session_start();

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

//mb_string ini set by nao-pon
ini_set("output_buffering","Off");
ini_set("default_charset",_CHARSET);
ini_set("mbstring.language","Japanese");
ini_set("mbstring.encoding_translation","Off");
ini_set("mbstring.http_input","Auto");
ini_set("mbstring.http_output",_CHARSET);
ini_set("mbstring.internal_encoding",_CHARSET);
ini_set("mbstring.substitute_character"," ");

$keys = array('from','to','cc','bcc','prior','subject','message','attachment','atachtype');
foreach ($keys as $key)
{
	$$key = (isset($_POST[$key]))? $_POST[$key] : '';
}

if ($email_send == 1) {
	// nao-pon
	$tos = array();
	$ccs = array();
	$bccs = array();
	if ($to) $tos = split(",",$to);
	if ($cc) $ccs = split(",",$cc);
	if ($bcc) $bccs = split(",",$bcc);
	$mail_sum = count($tos)+count($ccs)+count($bccs);
	//$mail_max = 5;//test
	if ($to){
		$tmps = array();
		foreach ($tos as $tmp){
			$tmp =trim($tmp);
			$pos = strrpos($tmp, '<');
			$name = $email = '';
			if ($pos != 0){
				$name = mb_encode_mimeheader(trim(substr($tmp, 0, $pos - 1)),"ISO-2022-JP","B").' ';
				$email = substr($tmp, $pos);
				$tmps[] = $name.$email;
			}else{
				$tmps[] = $tmp;
			}
		}
		$to = implode(",",$tmps);
	}
	if ($cc){
		$tmps = array();
		foreach ($ccs as $tmp){
			$tmp =trim($tmp);
			$pos = strrpos($tmp, '<');
			$name = $email = '';
			if ($pos != 0){
				$name = mb_encode_mimeheader(trim(substr($tmp, 0, $pos - 1)),"ISO-2022-JP","B").' ';
				$email = substr($tmp, $pos);
				$tmps[] = $name.$email;
			}else{
				$tmps[] = $tmp;
			}
		}
		$cc = implode(",",$tmps);
	}
	if ($bcc){
		$tmps = array();
		foreach ($bccs as $tmp){
			$tmp =trim($tmp);
			$pos = strrpos($tmp, '<');
			$name = $email = '';
			if ($pos != 0){
				$name = mb_encode_mimeheader(trim(substr($tmp, 0, $pos - 1)),"ISO-2022-JP","B").' ';
				$email = substr($tmp, $pos);
				$tmps[] = $name.$email;
			}else{
				$tmps[] = $tmp;
			}
		}
		$bcc = implode(",",$tmps);
	}

	include ("mailheader.php");

	if ($mail_sum < $mail_max + 1){

		include ("libmail.php");
		$userid = $xoopsUser->uid();
		srand ((double) microtime() * 1000000);
		$messageid = rand();
		if (!$from) {
			$email_f = $xoopsUser->email();
			$name = $xoopsUser->name();
			$uname = $xoopsUser->uname();
			if($name == "") {
			    $name = $uname;
			}
			$name = mb_encode_mimeheader(trim($name),"ISO-2022-JP","B");
			$from = "$name <$email_f>";
		} else {
			$pos = strrpos($from, '<');
			$name = $email_f = '';
			if ($pos != 0){
				$name = mb_encode_mimeheader(trim(substr($from, 0, $pos - 1)),"ISO-2022-JP","B").' ';
				$email_f = substr($from, $pos);
				$from = $name."".$email_f;
				$email_f = str_replace("<","",$email_f);
				$email_f = str_replace(">","",$email_f);
			} else {
				$email_f = $from;
			}
		}

		if ($bcc) {
			$bcc .= ",".$email_f;
		} else {
			$bcc = $email_f;
		}

		$txtfooter = "\n\n___________________________________________________________________________\n";
		$txtfooter .= "$footermsgtxt";
		$message = stripslashes($message);
		$content = $message.$txtfooter;

		$content = str_replace("\r\n","\n",$content);
		$content = str_replace("\r","\n",$content);

		$contenttype = "text/plain";
		$acknowledge = "N";
		$status = "NONE";

		if($attachment != "") {
		    //$attachment = $attachmentdir.$attachment;
		    //$attachment = "tmp/".$attachment;
		}


		//nao-pon
		//$pos = strrpos($to, '<');
		//$name = $email = '';
		//if ($pos != 0){
		//	$name = mb_encode_mimeheader(trim(substr($to, 0, $pos - 1)),"ISO-2022-JP","B").' ';
		//	$email = substr($to, $pos);
		//	$to = $name."".$email;
		//}
		//


		$m= new w_Mail;
		$m->autoCheck(false);
		$m->From($from);
		$m->from = $email_f;
		$m->To($to);
		//$m->To(mb_encode_mimeheader(mb_convert_kana($to,"KV")));
		//$m->Subject(stripslashes(mb_encode_mimeheader(mb_convert_kana($subject,"KV"),"ISO-2022-JP","B")));
		$m->Subject(mb_encode_mimeheader(mb_convert_kana($subject,"KV"),"ISO-2022-JP","B"));
		$m->Body(mb_convert_encoding(mb_convert_kana($content,"KV"), "JIS", _CHARSET));
		//$m->Subject($subject);
		//$m->Body($content);
		$m->Cc($cc);
		$m->Bcc($bcc);
		$m->Priority($prior) ;
		$m->ReplyTo($from);

		if($attachment != "")  {
		    $m->Attach($userid,$attachmentdir,$attachment,$attchtype);
		}

		$m->Send();
		$ret_message = _MD_WEBMAIL_MESSAGESENT;
	} else {
		$ret_message = _MD_WEBMAIL_MAIL_OVER;
	}
	OpenTable();
	echo "<center><b>".$ret_message."</b></center>";
	//for debug//echo "the mail below has been sent:<br /><pre>", $m->Get(), "</pre>";

	CloseTable();

	//$aattach = split(",", $attachment);
	//for ($i = 0; $i < count($aattach); $i++) {
	//	unlink($attachmentdir."/".$userid."_".$aattach[$i]."_d_u_m_");
	//}
	$handle = opendir("$attachmentdir");
	while(false !== ($dir_tmp = readdir($handle)))  {
		if ($dir_tmp != "." && $dir_tmp != "..") {
			if (preg_match("/^".$userid."_.*/",$dir_tmp)) {
				unlink("$attachmentdir/$dir_tmp");
			}
		}
	}
	closedir($handle);

	include(XOOPS_ROOT_PATH."/footer.php");

} else {
    Header("Location: index.php");
    exit();
}

