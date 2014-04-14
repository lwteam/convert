<?php
set_time_limit(120);
function showmnextpage($message,$nexturl=false,$time = 0){

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
			$html .='<script type="text/JavaScript">jQuery(function() { setTimeout("redirect(\''.$nexturl.'\');", '.$time.')});</script>';
		}
		$html .='</div>';

	$html .='</body></html>';

	echo $html;
	exit;
}

function get_avatar_path($uid,$dir){

	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$path = $dir.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).'_avatar_$size$.jpg';
	return str_replace ( '\\', '/',  $path );
}

function mv_avatar($uid,$olduid) {
	$retrue = TRUE;
	$path  = get_avatar_path($uid,AVATARPATH);
	$oldpath  = get_avatar_path($olduid,AVATARPATH_OLD);
	nmkdir($path);
	foreach (array('big', 'middle', 'small') as  $value) {
		$retrue  = $retrue && @rename(str_replace('$size$', $value, $oldpath),str_replace('$size$', $value, $path));
	}
	return $retrue;
}


function mv_attach($aid,$tableid) {
	$attach= DB::fetch_first("SELECT * FROM ".DB::table('forum_attachment_'.$tableid)." WHERE `aid`='$aid'" );

	$path  = str_replace ( '\\', '/',  ATTACHPATH.$attach['attachment'] );
	$oldpath  = str_replace ( '\\', '/',  ATTACHPATH_OLD.$attach['attachment'] );

	nmkdir($path);
	if (file_exists($oldpath.'.thumb.jpg')) {
		@rename($oldpath.'.thumb.jpg',$path.'.thumb.jpg');
	}
	return rename($oldpath,$path);
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

function loadingdata(){
	global $ProcessNum,$page,$totalnum,$starttime,$_G;

	$UseRate = $ProcessNum*$page/$totalnum;
	$UseTime = $_G['timestamp']-$starttime;
	$NeedTime = floor($UseTime/$UseRate*(1-$UseRate));
	$html = '<p style="font-weight:400;line-height:22px;color:#444;font-size:13px">';
	$html .= '当前执行进度:'.sprintf('%.2f%%',$UseRate*100).' ( '.$ProcessNum*$page." / $totalnum )";
	$html .= '<br />';
	$html .= '执行耗时:'.timediff($_G['timestamp']-$starttime);
	$html .= '<br />';
	$html .= '预计剩余时间:'.timediff($NeedTime);
	$html .= '</p>';
	return $html;
	
}
function timediff($timediff ){

    $days = intval( $timediff / 86400 );
    $remain = $timediff % 86400;
    $hours = intval( $remain / 3600 );
    $remain = $remain % 3600;
    $mins = intval( $remain / 60 );
    $secs = $remain % 60;
    $res = ($days?$days.'天':'').($hours?$hours.'小时':'').($mins?$mins.'分钟':'').($secs?$secs.'秒':'');
    return $res;
}
?>