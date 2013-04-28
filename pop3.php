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

class POP3 {
	var $hostname;
	var $user;
	var $password;
	var $apop = "";
	//var $port=110;
	var $port = 0;
	var $DEBUG = 0;
	var $exit = false;
	var $has_error = false;

	var $dateformat = DATE_RFC2822;
	var $userTZ = 0;
	var $weekdays = array();

	/* Private variables - DO NOT ACCESS */

	var $connection = 0;
	var $greeting = "";
	var $state = "DISCONNECTED";
	var $must_update = 0;

	function POP3($hostname, $user, $password, $port = 110, $apop = 0) {
		$this->hostname = $hostname;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;
		$this->apop = $apop;
		$this->userTZ = date('Z');
	}

	function AddError($error) {
		$this->has_error = true;
		echo "<center>\n";
		echo "<b>Error:</b> $error\n";
		echo "</center>\n";
		$this->CloseConnection();
		if ($this->exit) exit;
	}

	function POP3Command($command, & $result) {
		if ($this->DEBUG)
			echo "<b>Sending Command: </b>".$command."<br />";
		flush();
		@ fputs($this->connection, "$command\r\n");
		$result = @ fgets($this->connection, 1024);

		if (preg_match("#^(\+OK)#i", $result))
			: if ($this->DEBUG)
				echo "<b>Result OK: </b><br />";
		flush();
		return true;
		else
			: $this->AddError($result);
		endif;
	}
	function OpenConnection() {
		if ($this->DEBUG)
			echo "<b>Openning Connection to: </b>".$this->hostname."<br />";
		flush();
		if ($this->hostname == "")
			$this->AddError("You must specified a valid hostname");
		$errno = 0;
		$errstr = "";
		$this->connection = fsockopen($this->hostname, $this->port, $errno, $errstr, 10);
		if ($this->DEBUG)
			echo "<b>Connection opened </b><br />";
		flush();
		if (!($this->connection))
			: return false;
		//if ($errno == 0)
		//         $this->AddError("Invalid Mail Server Name or Server Connection Error");
		//$this->AddError($errno." ".$errstr);
		endif;
		return true;
	}

	function CloseConnection() {
		if ($this->connection != 0)
			: fclose($this->connection);
		$this->connection = 0;
		endif;
	}

	function Open() {
		if ($this->state != "DISCONNECTED")
			$this->AddError("1 a connection is already opened");
		if ($this->OpenConnection()) {
			$this->greeting = @ fgets($this->connection, 1024);
			if (GetType($this->greeting) != "string" OR strtok($this->greeting, " ") != "+OK") {
				$this->CloseConnection();
				$this->AddError("2 POP3 server greeting was not found");
			}
		} else {
			return false;
		}
		//nao-pon for enabled apop
		//$this->greeting=strtok("\r\n");
		if (preg_match("/<(.+)>/", $this->greeting, $reg)) {
			$this->greeting = "<$reg[1]>";
		}
		//this is bug? nao-pon rewrote.
		//$this->must_update=0;
		$this->must_update = 1;
		//
		$this->state = "AUTHORIZATION";
		$this->Login();
		return true;
	}

	/* Close method - this method must be called at least if there are any
	    messages to be deleted */

	function Close() {
		if ($this->state == "DISCONNECTED")
			$this->AddError("no connection was opened");
		if ($this->must_update) {
			$this->POP3Command("QUIT");
			//nao-pon
			fgets($this->connection, 1024);
			//
		}
		$this->CloseConnection();
		$this->state = "DISCONNECTED";
		return true;
	}

	/* Login method - pass the user name and password of POP account.  Set
	    $apop to 1 or 0 wether you want to login using APOP method or not.  */

	function Login() {
		if ($this->state != "AUTHORIZATION")
			$this->AddError("connection is not in AUTHORIZATION state");
		if ($this->apop)
			: $this->POP3Command("APOP $this->user ".md5($this->greeting.$this->password));
		else
			: $this->POP3Command("USER $this->user");
		$this->POP3Command("PASS $this->password");
		endif;
		$this->state = "TRANSACTION";
	}

	/* Statistics method - pass references to variables to hold the number of
	messages in the mail box and the size that they take in bytes.  */

	function Stats($msg = "") {
		$result = "";
		if ($this->state != "TRANSACTION")
			$this->AddError("connection is not in TRANSACTION state");
		if ($msg == "")
			: $this->POP3Command("STAT", $result);
		else
			: $this->POP3Command("LIST $msg", $result);
		endif;
		$p = explode(" ", $result);
		$stat["message"] = $p[1];
		$stat["size"] = $p[2];
		return $stat;
	}

	function GetHeaders($message = 1) {
		$this->POP3Command("TOP $message 0");
		for ($headers = "";;) {
			$line = fgets($this->connection, 1024);
			if (trim($line) == "." OR feof($this->connection)) {
				break;
			}
			$headers .= $line;
		}
		return $headers;
	}

	function GetMessageID($message = "") {
		$result = "";
		if ($message)
			: $this->POP3Command("UIDL $message", $result);
		$id = explode(" ", $result);
		return ereg_replace("[<>]", "", $id[2]);
		else
			: $this->POP3Command("UIDL");
		while (!feof($this->connection))
			: $line = fgets($this->connection, 1024);
		if (trim($line) == ".") {
			break;
		}
		$part = explode(" ", $line);
		$part[1] = ereg_replace("[<>]", "", $part[1]);
		$id[$part[0]] = $part[1];
		endwhile;
		return $id;
		endif;

	}

	function GetMessage($msg = 1) {
		$i = 0;
		$this->POP3Command("RETR $msg");
		for ($m = "";;) {
			$line = fgets($this->connection, 1024);
			if (trim($line) == "." OR feof($this->connection)) {
				break;
			}
			if (chop($line) == "" AND $i < 1) {
				$message["header"] = $m;
				$i ++;
				$m .= $line;
				continue;
			}
			if ($i > 0)
				$messagebody .= $line;
			$m .= $line;
		}
		$message["body"] = $messagebody;
		$message["full"] = $m;
		//echo "pop3-238:debug<br />".nl2br(htmlspecialchars($m));
		return $message;
	}

	function ListMessage($msg, $readline = 15) {
		// Set time out 10s by nao-pon
		socket_set_timeout($this->connection, 10);
		//
		$list = array ();
		$list["has_attachment"] = false;
		//$list["size"] = '';
		$stat = $this->Stats($msg);
		$list["size"] = $stat['size'];
		//$this->POP3Command("RETR $msg");
		$this->POP3Command("TOP $msg $readline");
		$is_header = true;
		$is_subject = false;
		$is_from = false;
		$readlines = 0;
		$list['body'] = '';
		$list['header'] = '';
		while ($line = fgets($this->connection, 4096)) {
			//$line = fgets($this->connection, 4096);
			//$list["size"] += strlen($line);

			if (trim($line) == "." OR feof($this->connection)) {
				break;
			}
			if (trim($line) == "")
				$is_header = false;
			if ($is_header) {
				$list['header'] .= $line;
				if ($is_subject == 1) {
					if (preg_match("/^(.+?: )/", $line, $reg)) {
						$is_subject = false;
					} else {
						$list["subject"] .= $line;
					}
				}
				if ($is_from) {
					if (preg_match("/^(.+?: )/", $line, $reg)) {
						$is_from = false;
					} else {
						$from .= $line;
					}
				}
				if (preg_match("#^Subject: (.*)#i", $line, $reg)) {
					$list["subject"] = $reg[1];
					$is_subject = true;
				}
				if (preg_match("#^Date: (.*)#i", $line, $reg))
					$date = $reg[1];
				if (preg_match("#^From: (.*)#i", $line, $reg)) {
					$from = $reg[1];
					$is_from = true;
				}
			} else {
				if ($readlines < $readline) {
					$line = str_replace("\r\n", "\n", $line);
					$line = str_replace("\r", "\n", $line);
					if (trim($line)) {
						$list["body"] .= $line;
						$readlines ++;
					}
				}
			}
			if (preg_match("#^Content-Disposition: attachment#i", $line) OR preg_match("#^Content-Disposition: inline#i", $line))
				$list["has_attachment"] = true;
			if (preg_match("#^Content-Type: text/html#i", $line))
				$list["is_html"] = true;
		}

		//$list["body"] = str_replace("\r\n","\n",$list["body"]);
		//$list["body"] = str_replace("\r","\n",$list["body"]);
		$list["body"] = trim($list["body"]);

		if ($_time = @ strtotime($date)) {
			$date = date($this->dateformat, $_time + $this->userTZ - date('Z'));
    		if ($this->weekdays) {
    			$date = preg_replace('/\(([0-6])\)/e', '"(".$this->weekdays[$1].")"', $date);
    		}
		}
		//preg_match("#(Sun|Mon|Tue|Wed|Thu|Fri|Sat)?,?\s?(.+) (.+) ([0-9]{1,2})([0-9]{1,2}) (.+):(.+):(.+) (.+)#i", $date, $dreg);
		//$dreg[9] = eregi_replace("(\+0900|\(?jst\)?)", "", $dreg[9]);
		//$list["date"] = $dreg[1]." ".$dreg[2]." ".$dreg[3]." ".$dreg[6].":".$dreg[6]." ".$dreg[9];
		//$list["date"] = $dreg[2]." ".$dreg[3]." ".$dreg[6].":".$dreg[7]." ".$dreg[9];
		$list["date"] = $date;
		$from = eregi_replace("<|>|\[|\]|\(|\)|\"|\'|(mailto:)", "", $from);
		if (preg_match("#(.*)? (.+@.+\\..+)#i", $from))
			: preg_match("#(.*)? (.+@.+\\..+)#i", $from, $reg);
		$list["sender"]["name"] = $reg[1];
		$list["sender"]["email"] = $reg[2];
		else
			: preg_match("#(.+@.+\\..+)#i", $from, $reg);
		$list["sender"]["name"] = $reg[1];
		$list["sender"]["email"] = $reg[1];
		endif;
		return $list;
	}

	function DeleteMessage($message) {
		if ($this->state != "TRANSACTION")
			$this->AddError("connection is not in TRANSACTION state");
		$this->POP3Command("DELE $message");
		$this->must_update = 1;
		return true;
	}

	function ResetDeletedMessages() {
		if ($this->state != "TRANSACTION")
			$this->AddError("connection is not in TRANSACTION state");
		$this->POP3Command("RSET");
		$this->must_update = 0;
		return ("");
	}

	function NOOP() {
		if ($this->state != "TRANSACTION")
			$this->AddError("connection is not in TRANSACTION state");
		$this->POP3Command("NOOP");
		return ("");
	}
};
