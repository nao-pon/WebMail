<?php
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

define("_AM_DBUPDATED","データベースは更新されました。");
define("_AM_GENERALCONF","一般設定");
define("_AM_CANCEL","キャンセル");
define("_AM_SAVECHANGE","保存");
define("_AM_YES","はい");
define("_AM_NO","いいえ");

define("_AM_SHOW_RIGHT","右のサイドブロックを表示しますか？");

define("_AM_FOOTERMSGTXT","すべての送信メッセージに付加するフッター:");
define("_AM_EMAIL_SEND","ユーザにメール送信を許可しますか？");
define("_AM_ATTACHMENTS","ユーザにメール送信時にファイル添付を許可しますか？");
define("_AM_ATTACHMENTDIR","添付ファイルの一時保存先ディレクトリ:");
define("_AM_ATTACHMENTS_VIEW","ユーザが受信メッセージの添付ファイルを見たり保存したりすることができるようにしますか？");
define("_AM_DOWNLOAD_DIR","受信メールの添付ファイル一時保存先ディレクトリ:");
define("_AM_NUMACCOUNTS","メールアカウントの最大登録数( -1 で無制限):");
define("_AM_SINGLEACCOUNT","このサービスを単一のメールサーバで運営されますか？");
define("_AM_SINGLEACCOUNTNAME","上記が「はい」の場合のメールサービスの名称：");
define("_AM_DEFAULTPOPSERVER","サービスを行うPOP3 サーバ名:<br />(単一のメールサーバで運営する場合は必ず入力してください。)");
define("_AM_FILTER_FORWARD","メールの返信・転送時に本文にヘッダ情報を付加しますか？");

// add nao-pon
define("_AM_TEMPFILE_TIME","一時ファイルを保持する時間(単位：分)");
define("_AM_MAIL_SEND_MAX","一回の送信で指定できる宛先件数(To: Cc: Bcc: を ,(カンマ)で区切って指定できる件数)");
define("_AM_FILTER_SUBJECT","一覧表示で削除対象にチェックを自動で入れるSubjectの文字列:<br />・改行で区切って入力してください。(大文字・小文字を識別しません。)<br /><b>おすすめの設定</b>※正規表現も使えます。<br />ADV:<br />(!|！)\s*広\s*告\s*(!|！)<br />(!|！)\s*連\s*絡\s*方\s*法\s*(無|な)(\s*し)?\s*(!|！)<br />(未|末)\s*(承\s*諾\s*|承\s*認\s*)広\s*告<br />(韻\s*壱|畠\s*左|舛\s*左)");
define("_AM_DBUP_B","データベーステーブルのアップデート実行");
define("_AM_DBUP_M","※WebMail 1.02(J1.2)以前からアップデートした場合に一度だけ実行してください。");
define("_AM_EMAIL_ADDRESS","メール送信を許可する場合、アカウント別のメールアドレスでの送信を許可しますか？<br />この設定が「いいえ」の場合は、ユーザー登録されたメールアドレスでのみ送信されます。");
define("_AM_HTML_TAG_COLOR","HTMLメールのソース表示時のタグの色<br />16進表示(例 #0000ff ) または、色名(例 blue) で指定。");
define("_AM_HTML_SCR_COLOR","HTMLメール表示時のスクリプト部分の色<br />16進表示(例 #ff0000 ) または、色名(例 red) で指定。");

