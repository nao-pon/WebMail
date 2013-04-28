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

/*
        this class encapsulates the PHP mail() function.
        implements CC, Bcc, Priority headers

@version        1.3

- added ReplyTo( $address ) method
- added Receipt() method - to add a mail receipt
- added optionnal charset parameter to Body() method. this should fix charset problem on some mail clients

@example
        include ("modules/WebMail/libmail.php");
        $m= new Mail; // create the mail
        $m->From( "leo@isp.com" );
        $m->To( "destination@somewhere.fr" );
        $m->Subject( "the subject of the mail" );
        $message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
	$m->Body( $message);        // set the body
        $m->Cc( "someone@somewhere.fr");
        $m->Bcc( "someoneelse@somewhere.fr");
        $m->Priority(4) ;        // set the priority to Low
        $m->Attach( "/home/leo/toto.gif", "image/gif" ) ;        // attach a file of type image/gif
        $m->Send();        // send the mail
        echo "the mail below has been sent:<br /><pre>", $m->Get(), "</pre>";

LASTMOD
        Fri Oct  6 15:46:12 UTC 2000

@author        Leo West - lwest@free.fr

*/

class w_Mail {
        /*
        list of To addresses
        @var        array
        */
        var $sendto = array();
        /*
        @var        array
        */
        var $acc = array();
        /*
        @var        array
        */
        var $abcc = array();
        /*
        paths of attached files
        @var array
        */
        var $aattach = array();
        /*
        list of message headers
        @var array
        */
        var $xheaders = array();
        /*
        message priorities referential
        @var array
        */
        var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
        /*
        character set of message
        @var string
        */
        var $charset = "iso-2022-jp";
        var $ctencoding = "7bit";
        var $receipt = 0;
        var $sender = "";
/*
        Mail contructor
*/

function w_Mail() {
	$this->autoCheck( true );
	$this->boundary= "--" . md5( uniqid("myboundary") );
}

/*
activate or desactivate the email addresses validator
ex: autoCheck( true ) turn the validator on
by default autoCheck feature is on
@param boolean        $bool set to true to turn on the auto validation
@access public
*/

function autoCheck( $bool ) {
        if( $bool )
                $this->checkAddress = true;
        else
                $this->checkAddress = false;
}

/*
Define the subject line of the email
@param string $subject any monoline string
*/

function Subject( $subject ) {
        $this->xheaders['Subject'] = strtr( $subject, "\r\n" , "  " );
        //$this->xheaders['Subject'] = $subject;
}

/*
set the sender of the mail
@param string $from should be an email address
*/

function From( $from ) {
        if( ! is_string($from) ) {
                echo "Class Mail: error, From is not a string";
                exit;
        }
        $this->xheaders['From'] = $from;
}

/*
set the Reply-to header
@param string $email should be an email address
*/

function ReplyTo( $address ) {
        if( ! is_string($address) )
                return false;
        $this->xheaders["Reply-To"] = $address;
}

/*
add a receipt to the mail ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined)
when the receiver opens the message.
@warning this functionality is *not* a standard, thus only some mail clients are compliants.
*/

function Receipt() {
        $this->receipt = 1;
}

/*
set the mail recipient
@param string $to email address, accept both a single address or an array of addresses
*/

function To( $to ) {
        // TODO : test validit?sur to
        if( is_array( $to ) )
                $this->sendto= $to;
        else
                $this->sendto[] = $to;
        if( $this->checkAddress == true )
        $this->CheckAdresses( $this->sendto );
}

/*                Cc()
 *                set the CC headers ( carbon copy )
 *                $cc : email address(es), accept both array and string
 */

function Cc( $cc ) {
	if (!$cc == "") {
        if( is_array($cc) )
                $this->acc= $cc;
        else
        	$this->acc[]= $cc;
        if( $this->checkAddress == true )
            $this->CheckAdresses( $this->acc );
	}
}

/*                Bcc()
 *                set the Bcc headers ( blank carbon copy ).
 *                $bcc : email address(es), accept both array and string
 */

function Bcc( $bcc ) {
	if (!$bcc == "") {

        if( is_array($bcc) ) {
                $this->abcc = $bcc;
        } else {
                $this->abcc[]= $bcc;
        }
        if( $this->checkAddress == true )
                $this->CheckAdresses( $this->abcc );
	}
}

/*                Body( text [, charset] )
 *                set the body (message) of the mail
 *                define the charset if the message contains extended characters (accents)
 *                default to us-ascii
 *                $mail->Body( "m? en fran?is avec des accents", "iso-8859-1" );
 */

function Body( $body, $charset="" ) {
        $this->body = $body;
        if( $charset != "" ) {
                $this->charset = strtolower($charset);
                if( $this->charset != "us-ascii" )
                        $this->ctencoding = "8bit";
        }
}

/*                Organization( $org )
 *                set the Organization header
 */

function Organization( $org ) {
        if( trim( $org != "" )  )
                $this->xheaders['Organization'] = $org;
}

/*                Priority( $priority )
 *                set the mail priority
 *                $priority : integer taken between 1 (highest) and 5 ( lowest )
 *                ex: $mail->Priority(1) ; => Highest
 */

function Priority( $priority ) {
        if( ! intval( $priority ) )
                return false;
        if( ! isset( $this->priorities[$priority-1]) )
                return false;
        $this->xheaders["X-Priority"] = $this->priorities[$priority-1];
	return true;
}

/*
 Attach a file to the mail
 @param string $filename : path of the file to attach
 @param string $filetype : MIME-type of the file. default to 'application/x-unknown-content-type'
 @param string $disposition : instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
 */

function Attach( $uid, $attdir, $filename, $filetype = "", $disposition = "attachment" ) {
        // TODO : si filetype="", alors chercher dans un tablo de MT connus / extension du fichier
	//echo "$filename<p>";
	//echo "$filetype<p>";
	//echo "atd:$attdir<p>";

	$this->aattach = split(",", $filename);
	$this->actype =  split(",", $filetype);
	for ($i = 0; $i < count($this->aattach); $i++) {
		$this->aattach[$i] = $attdir."/".$uid."_".$this->aattach[$i]."_d_u_m_";
    	$this->adispo[$i] =  $disposition;
    	if($this->actype[$i] == "") $this->actype[$i] = "application/x-unknown-content-type";
	}
}

/*
Build the email message
@access protected
*/

function BuildMail() {

        // build the headers
	$this->headers = "";
//        $this->xheaders['To'] = implode( ", ", $this->sendto );
        if( count($this->acc) > 0 )
                $this->xheaders['CC'] = implode( ", ", $this->acc );
        if( count($this->abcc) > 0 )
                $this->xheaders['BCC'] = implode( ", ", $this->abcc );

        //error mail to admin by nao-pon
        //$this->xheaders["Return-Path"] = $xoopsConfig['adminmail'];
        //$this->xheaders["Reply-To"] = $this->xheaders['From'];

        if( $this->receipt ) {
                if( isset($this->xheaders["Reply-To"] ) )
                        $this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
                else
                        $this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
        }
        if( $this->charset != "" ) {
                global $contenttype;
                $this->xheaders["Mime-Version"] = "1.0";
                $this->xheaders["Content-Type"] = "$contenttype; charset=$this->charset";
                $this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
        }

        $this->xheaders["X-Mailer"] = "RLSP Mailer";
        // include attached files
        if( count( $this->aattach ) > 0 ) {
                $this->_build_attachement();
        } else {
                $this->fullBody = $this->body;
        }
        foreach($this->xheaders as $hdr => $value) {
        //reset($this->xheaders);
        //while( list( $hdr,$value ) = each( $this->xheaders )  ) {
        	if ( $hdr != "Subject" ) $this->headers .= "$hdr: $value\r\n";
        }
        $this->headers = rtrim($this->headers);
}

/*
fornat and send the mail
@access public
*/

function Send() {
        $this->BuildMail();
        $this->strTo = implode( ", ", $this->sendto );
        // envoie du mail
		//$res = @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers );

        if ($this->sender != "" && PHP_VERSION >= "4.0")
        {
            $old_from = ini_get("sendmail_from");
            ini_set("sendmail_from", $this->sender);
        }
        if (ini_get("safe_mode") != "1" && $this->sender != "" && PHP_VERSION >= "4.0.5")
        {
            // The fifth parameter to mail is only available in PHP >= 4.0.5
            $params = sprintf("-oi -f %s", $this->sender);
            $res = @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers, $params);
        }
        else
        {
            $res = @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers );
        }

        if (isset($old_from))
            ini_set("sendmail_from", $old_from);


}

/*
 *                return the whole e-mail , headers + message
 *                can be used for displaying the message in plain text or logging it
 */

function Get() {
        $this->BuildMail();
        $mail = "mail(To): " . $this->strTo . "\r\n";
        $mail .= "mail(Header):\r\n";
        $mail .= $this->headers . "\r\n";
        $mail .= "mail(Body):\r\n";
        $mail .= $this->fullBody;
        return $mail;
}

/*
check an email address validity
@access public
@param string $address : email address to check
@return true if email adress is ok
*/

function ValidEmail($address) {
        if( preg_match("/.*<(.+)>/", $address, $regs ) ) {
                $address = $regs[1];
        }
        if(preg_match("/^[^@  ]+@([a-zA-Z0-9\-]+\.)+([a-zA-Z0-9\-]{2}|net|com|gov|mil|org|edu|int)$/", $address))
                return true;
        else
                return false;
}

/*
check validity of email addresses
@param        array $aad -
@return if unvalid, output an error message and exit, this may -should- be customized
*/

function CheckAdresses( $aad ) {
        for($i=0;$i< count( $aad); $i++ ) {
                if( ! $this->ValidEmail( $aad[$i]) ) {
                        echo "Class Mail, method Mail : invalid address $aad[$i]";
                        exit;
                }
        }
}

/*
check and encode attach file(s) . internal use only
@access private
*/

function _build_attachement() {
		$this->xheaders["Content-Type"] = "multipart/mixed;\r\n\tboundary=\"$this->boundary\"";
        $this->fullBody = "This is a multi-part message in MIME format.\r\n--$this->boundary\r\n";
        $this->fullBody .= "Content-Type: text/plain; charset=$this->charset\r\nContent-Transfer-Encoding: $this->ctencoding\r\n\r\n" . $this->body ."\r\n";
        $sep= chr(13) . chr(10);
        $ata= array();
        $k=0;
        // for each attached file, do...
        for( $i=0; $i < count( $this->aattach); $i++ ) {
                $filename = $this->aattach[$i];
                $basename = basename($filename);
                //nao-pon
                $basename = substr($basename,strpos($basename,"_")+1);
                $basename = preg_replace("/_d_u_m_$/i", "", $basename);
                $basename = mb_encode_mimeheader($basename);
                //
                $ctype = $this->actype[$i];        // content-type
                $disposition = $this->adispo[$i];
                if( ! file_exists( $filename) ) {
                        echo "Class Mail, method attach : file $filename can't be found"; exit;
                }
                $subhdr= "--$this->boundary\r\nContent-Type: $ctype;\r\n name=\"$basename\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: $disposition;\r\n  filename=\"$basename\"\r\n";
                $ata[$k++] = $subhdr;
                // non encoded line length
                $linesz= filesize( $filename)+1;
                $fp= fopen( $filename, 'rb' );
                $ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
                fclose($fp);
        }
        $this->fullBody .= implode($sep, $ata);
        $this->fullBody .= "\r\n--".$this->boundary."--\r\n";
}

} // class Mail

