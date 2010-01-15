<?php
function b_system_login_show(){
	global $xoopsUser, $xoopsConfig, $HTTP_COOKIE_VARS;
	if (!$xoopsUser) {
		$block = array();
		$block['title'] = _MB_SYSTEM_LOGIN;
    		$block['content'] = "<div align='center'><p><form action='".XOOPS_URL."/user.php' method='post'>";
    		$block['content'] .= _MB_SYSTEM_NICK."<br />";
    		$block['content'] .= "<input type='text' name='uname' size='12' maxlength='25'";
		if(isset($HTTP_COOKIE_VARS[$xoopsConfig['usercookie']])){
			$block['content'] .= " value='".$HTTP_COOKIE_VARS[$xoopsConfig['usercookie']]."'";
		}
		$block['content'] .= " /><br />";
    		$block['content'] .= _MB_SYSTEM_PASS."<br />";
    		$block['content'] .= "<input type='password' name='pass' size='12' maxlength='20' /><br />";
    		$block['content'] .= "<input type='hidden' name='op' value='login' />";
    		$block['content'] .= "<input type='submit' value='"._MB_SYSTEM_LOGIN."' /></form></p>";
		$block['content'] .= "<a href='".XOOPS_URL."/user.php#lost'>"._MB_SYSTEM_LPASS."</a><br /><br />";
    		$block['content'] .= _MB_SYSTEM_DHAAY."<br />";
    		$block['content'] .= " <a href='".XOOPS_URL."/register.php'>"._MB_SYSTEM_RNOW."</a>";
    		$block['content'] .= "</div>";
    		return $block;
    	}
	return FALSE;
}

function b_system_main_show(){
	global $xoopsDB;
	$block = array();
	$block['title'] = _MB_SYSTEM_MMENU;
	include(XOOPS_ROOT_PATH."/modules/system/cache/mainmenu.php");
	$block['content'] = $mainmenu;
	return $block;
}

function b_system_WM_SEARCH_show(){
	$block = array();
	$block['title'] = _MB_SYSTEM_WM_SEARCH;
	$block['content'] = "<div align='center'><br /><form action='".XOOPS_URL."/search.php' method='post'>\n";
    $block['content'] .= "<input type='text' name='query' size='14' />\n";
	$block['content'] .= "<input type='hidden' name='action' value='results' />\n";
	$block['content'] .= "<br /><input type='submit' value='"._MB_SYSTEM_WM_SEARCH."' />\n";
	$block['content'] .= "</form>\n";
	$block['content'] .= "<a href='".XOOPS_URL."/search.php'>"._MB_SYSTEM_ADVS."</a></div>";
	return $block;
}

function b_system_user_show(){
	global $xoopsDB, $xoopsUser;
	if($xoopsUser) {
		$block = array();
		$block['title'] = sprintf(_MB_SYSTEM_MENU4,$xoopsUser->uname());
		$block['content'] = "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/user.php'>"._MB_SYSTEM_VACNT."</a><br />";
		$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/user.php?op=logout'>"._MB_SYSTEM_LOUT."</a><br />";
		$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/WebMail/index.php'>WebMail</a><br /><br />";
		// Code for Messages
        	list($total_messages) = $xoopsDB->fetchRow($xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("priv_msgs")." WHERE to_userid = ".$xoopsUser->getVar("uid").""));
        	list($new_messages) = $xoopsDB->fetchRow($xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("priv_msgs")." WHERE to_userid = ".$xoopsUser->getVar("uid")." AND read_msg=0"));
        	if ($total_messages > 0) {
			if ($new_messages > 0) {
				$block['content'] .= "<a href=".XOOPS_URL."/viewpmsg.php>";
				$block['content'] .= sprintf(_MB_SYSTEM_NMSGS,$new_messages );
				$block['content'] .= "</a><br />";
			}else{
				$block['content'] .= _MB_SYSTEM_NNMSG." <br /> ";
			}
			$block['content'] .= "<a href=".XOOPS_URL."/viewpmsg.php>";
			$block['content'] .= sprintf(_MB_SYSTEM_TMSGS,$total_messages);
			$block['content'] .= "</a>";
		} else {
			$block['content'] .= "<a href=".XOOPS_URL."/viewpmsg.php>"._MB_SYSTEM_YDMSG."</a>";
		} 
		if ( $xoopsUser->isAdmin() ) {
			$block['content'] .= "<br /><br /><strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/admin.php'>"._MB_SYSTEM_ADMENU."</a>";
		}
		// Code for Messages
		return $block;
	}
	return FALSE;
}

function b_system_waiting_show(){
	global $xoopsDB, $xoopsUser;
	$block = array();
	$block['title'] = _MB_SYSTEM_WCNT;
	$block['content'] = "";
	if (XoopsModule::moduleExists("news")) {
		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("stories")." WHERE published=0");
		if ( $result ) {
    			list($num) = $xoopsDB->fetchRow($result);
    			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/news/admin/index.php?op=newarticle'>"._MB_SYSTEM_SUBMS."</a>: $num<br />\n";
		}
	}
	if (XoopsModule::moduleExists("mylinks")) {
		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_links")." WHERE status=0");
		if ( $result ) {
			list($num) = $xoopsDB->fetchRow($result);
    			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mylinks/admin/index.php?op=listNewLinks'>"._MB_SYSTEM_WLNKS."</a>: $num<br />\n";
		}
    		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_broken")."");
		if ( $result ) {
    			list($totalbrokenlinks) = $xoopsDB->fetchRow($result);
    			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mylinks/admin/index.php?op=listBrokenLinks'>"._MB_SYSTEM_BLNK."</a>: $totalbrokenlinks<br />\n";
		}
    		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mylinks_mod")."");
		if ( $result ) {
    			list($totalmodrequests) = $xoopsDB->fetchRow($result);
			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mylinks/admin/index.php?op=listModReq'>"._MB_SYSTEM_MLNKS."</a>: $totalmodrequests<br />\n";
		}
	}
	if (XoopsModule::moduleExists("mydownloads")) {
		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_downloads")." WHERE status=0");
		if ( $result ) {
			list($num) = $xoopsDB->fetchRow($result);
    			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mydownloads/admin/index.php?op=listNewDownloads'>"._MB_SYSTEM_WDLS."</a>: $num<br />\n";
		}
    		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_broken")."");
		if ( $result ) {
    			list($totalbrokenfiles) = $xoopsDB->fetchRow($result);
    			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mydownloads/admin/index.php?op=listBrokenDownloads'>"._MB_SYSTEM_BFLS."</a>: $totalbrokenfiles<br />\n";
		}
    		$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("mydownloads_mod")."");
		if ( $result ) {
    			list($totalmodrequests) = $xoopsDB->fetchRow($result);
			$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/mydownloads/admin/index.php?op=listModReq'>"._MB_SYSTEM_MFLS."</a>: $totalmodrequests<br />\n";
		}
	}

		if (XoopsModule::moduleExists("tutorials")) {
    	$result = $xoopsDB->query("SELECT COUNT(*) FROM ".$xoopsDB->prefix("tutorials")." WHERE status=0");
    	if ( $result ) {
        	list($num) = $xoopsDB->fetchRow($result);
        	$block['content'] .= "<strong><big>&middot;</big></strong>&nbsp;<a href='".XOOPS_URL."/modules/tutorials/admin/index.php'>"._MB_SYSTEM_WTLS."</a>: $num<br />\n";
    	        }
	}


	return $block;
}

function b_system_info_show($options){
	global $xoopsConfig, $xoopsUser, $xoopsDB;
	$myts =& MyTextSanitizer::getInstance();
	$block = array();
	$block['title'] = _MB_SYSTEM_INFO;
	$block['content'] = "<div style='text-align: center;'>";
	if ( isset($options[3]) && $options[3] == 1 ) {
		$result = $xoopsDB->query("SELECT u.uid, u.uname, u.email, u.user_viewemail, g.name AS groupname FROM ".$xoopsDB->prefix("groups_users_link")." l LEFT JOIN ".$xoopsDB->prefix("users")." u ON l.uid=u.uid LEFT JOIN ".$xoopsDB->prefix("groups")." g ON l.groupid=g.groupid WHERE g.type='Admin' ORDER BY l.groupid");
        	if ($xoopsDB->getRowsNum($result) > 0) {
                	$prev_cation = "";
                	$block['content'] .= "<table width='92%' border='0' cellspacing='1' cellpadding='0' class='bg2'><tr><td>\n";
                	$block['content'] .= "<table width='100%' border='0' cellspacing='1' cellpadding='8' class='bg1'><tr><td>\n";
			$prev_caption = "";
                	while  ($userinfo = $xoopsDB->fetchArray($result)) {
                        	if ($prev_caption != $userinfo['groupname']) {
                                	$prev_caption = $userinfo['groupname'];
                                	$block['content'] .= "<tr><td colspan=\"2\">";
                                	$block['content'] .= "<small>";
                                	$block['content'] .= "<b>".$myts->makeTboxData4Show($prev_caption)."</b>";
                                	$block['content'] .= "</small>";
                                	$block['content'] .= "</td></tr>";
                        	}
				$userinfo['uname'] = $myts->makeTboxData4Show($userinfo['uname']);
                        	$block['content'] .= "<tr><td width=80%>";
                        	$block['content'] .= "<small>";
                        	$block['content'] .= "<a href=\"".XOOPS_URL."/userinfo.php?uid=".$userinfo['uid']."\">".$userinfo['uname']."</a>";
                        	$block['content'] .= "</small>";
                       	 	$block['content'] .= "</td><td width=20% align=right>";
                        	if ($xoopsUser) {
					$block['content'] .= "<a href=\"javascript:openWithSelfMain('".XOOPS_URL."/pmlite.php?send2=1&amp;to_userid=".$userinfo['uid']."','pmlite',360,300);\">";
					$block['content'] .= "<img src=\"".XOOPS_URL."/images/icons/pm_small.gif\" border=\"0\" width=\"27\" height=\"17\" alt=\"";
					$block['content'] .= sprintf(_MB_SYSTEM_SPMTO,$userinfo['uname']);
					$block['content'] .= "\" /></a>\n";
                        	} else {
					if($userinfo['user_viewemail']){
     						$block['content'] .= "<a href=\"mailto:".$userinfo['email']."\"><img src=\"".XOOPS_URL."/images/icons/em_small.gif\" border=\"0\" width=\"16\" height=\"14\" alt=\"";
						$block['content'] .= sprintf(_MB_SYSTEM_SEMTO,$userinfo['uname']);
						$block['content'] .= "\" /></a>\n";
					}else{
						$block['content'] .= "&nbsp;";
					}
                        	}
                        	$block['content'] .= "</td></tr>";
                	}
                	$block['content'] .= "</table></td></tr></table><br />";
		}
	}
	$block['content'] .= "<img src='".XOOPS_URL."/images/".$options[2]."' alt='".$xoopsConfig['sitename']."' border='0' />";
	if ( $xoopsUser ) {
		$block['content'] .= "<br /><a href='javascript:openWithSelfMain(\"".XOOPS_URL."/misc.php?action=showpopups&type=friend&amp;op=sendform&amp;t=".time()."\",\"friend\",".$options[0].",".$options[1].")'>"._MB_SYSTEM_RECO."</a>";
	}
    	$block['content'] .= waspInfo()."</div>";
	return $block;
}

function b_system_info_edit($options){
	$form = ""._MB_SYSTEM_PWWIDTH."&nbsp;";
	$form .= "<input type='text' name='options[]' value='".$options[0]."' />";
	$form .= "<br />"._MB_SYSTEM_PWHEIGHT."&nbsp;";
	$form .= "<input type='text' name='options[]' value='".$options[1]."' />";
	$form .= "<br />".sprintf(_MB_SYSTEM_LOGO,XOOPS_URL."/images/")."&nbsp;";
	$form .= "<input type='text' name='options[]' value='".$options[2]."' />";
	$chk = "";
	$form .= "<br />"._MB_SYSTEM_SADMIN."&nbsp;";
	if ( $options[3] == 1 ) {
		$chk = " checked='checked'";
	}
	$form .= "<input type='radio' name='options[3]' value='1'".$chk." />&nbsp;"._YES."";
	$chk = "";
	if ( $options[3] == 0 ) {
		$chk = " checked=\"checked\"";
	}
	$form .= "&nbsp;<input type='radio' name='options[3]' value='0'".$chk." />"._NO."";
	return $form;
}
