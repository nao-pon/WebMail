<?php
function catch_clr($dir){
global $userid,$tempfile_time;
	if (!$tempfile_time) $tempfile_time=30;//def 30m
	if ($dir) {
		$handle = opendir("$dir"); 
		while(false !== ($dir_tmp = readdir($handle)))  { 
			if ($dir_tmp != "." && $dir_tmp != "..") { 
				if (time() - filemtime("$dir/$dir_tmp") > ($tempfile_time * 60)){
					//echo "$dir/$dir_tmp is Old !<br>";
					unlink("$dir/$dir_tmp");
				}
			}
		}
		closedir($handle);
	}
}
?>
