<?php
set_time_limit(120);
function showmnextpage($message,$nexturl=false){

	$html ='';

	$html .='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
					<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
					<script type="text/JavaScript">
						function redirect(url) {
							window.location.replace(url);
						}
					</script>
					<style>
						.infobox {
							clear: both;
							margin-bottom: 10px;
							padding: 30px;
							text-align: center;
							border-top: 4px solid #DEEFFA;
							border-bottom: 4px solid #DEEEFA;
							background: #F2F9FD;
							zoom: 1;
						}
						h4 {
							margin-bottom: 10px;
							color: #09C;
							font-size: 14px;
							font-weight: 700;
						}
						a {
						color: #666;
							text-decoration: none;
						}
						a:hover {
							text-decoration: underline;
						}
					</style>
				</head>
				<body>';
		$html .='<div class="infobox"><h4>'.$message.'</h4>';
		if ($nexturl) {
			$html .='<img src="ajax_loader.gif" class="marginbot">';
			$html .='<p class="marginbot"><a href="'.$nexturl.'">如果您的浏览器没有自动跳转，请点击这里</a></p>';
			$html .='<script type="text/JavaScript">jQuery(function() { setTimeout("redirect(\''.$nexturl.'\');", 0)});</script>';
		}
		$html .='</div>';

	$html .='</body></html>';

	echo $html;
	exit;
}

function get_avatar_path($uid){

	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);

	$path = $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).'_avatar_$size$.jpg';
	nmkdir($path);
	return  $path;
}

function mv_avatar($uid,$olduid) {
	$retrue = TRUE;
	$path  = get_avatar_path($uid);
	$oldpath  = get_avatar_path($olduid);
	foreach (array('big', 'middle', 'small') as  $value) {
		$retrue  = $retrue && rename(AVATARPATH_OLD.str_replace('$size$', $value, $oldpath),AVATARPATH.str_replace('$size$', $value, $path));
	}
	return $retrue;
}


function mv_attach($aid,$tableid) {
	$attach= DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_'.$tableid)." WHERE `aid`='$aid'" );

	nmkdir(ATTACHPATH.$attach['attachment']);
	if (file_exists(ATTACHPATH_OLD.$attach['attachment'].'.thumb.jpg')) {
		@rename(ATTACHPATH_OLD.$attach['attachment'].'.thumb.jpg',ATTACHPATH.$attach['attachment'].'.thumb.jpg');
	}
	return @rename(ATTACHPATH_OLD.$attach['attachment'],ATTACHPATH.$attach['attachment']);
}

function nmkdir($path, $mode = 0777){

	$path = str_replace ( '\\', '/',  dirname($path) );
	$dir = '';
	$dirarray = explode ( '/', $path );
	foreach ( $dirarray as $str ) {
		$dir .= $str . '/';
		if (! file_exists ( $dir )) {
			mkdir ( $dir, $mode );
		}
	}
		
	@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);

}


?>