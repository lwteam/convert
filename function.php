<?php
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








?>