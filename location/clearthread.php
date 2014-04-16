<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();


$fids = array(649,730,668,329,269,335,648,383,669,715,264,400,677,678,728,729,714,690,734,706,705,686,683,699,691,724,713,711,733,737,637,660,674,362,293,639,386,385,662,670,675,687);


DB::query("DELETE FROM ".DB::table('common_member')." WHERE `fid` IN ("join(',',$fids)")");
exit('OK');
?>