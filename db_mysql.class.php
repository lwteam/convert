<?php
/*
+----------------------------------------------
| [Bigqi.com] ---->
| Item Name	: DBbase MYSQL Class
+----------------------------------------------
| File	: db_mysql.class.php Tue Nov 20 15:15:46 CST 2007
| Author: Haierspi ...
+----------------------------------------------
*/

class BQDB {
	var $link;
	var $dbname;
	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $out = TRUE) {
		if ($pconnect) {
			if (! $this->link = @mysql_pconnect ( $dbhost, $dbuser, $dbpw )) {
				$out && $this->out ( 'Can not connect to MySQL server' );
			}
		} else {
			if (! $this->link = @mysql_connect ( $dbhost, $dbuser, $dbpw, 1 )) {
				$out && $this->out ( 'Can not connect to MySQL server' );
			}
		}
		
		if ($this->version () > '4.1') {
			global $setting;
			
			if (! $setting ['db_charset'] && in_array ( strtolower ( $setting ['charset'] ), array ('gbk', 'big5', 'utf-8' ) )) {
				$setting ['db_charset'] = str_replace ( '-', '', $setting ['charset'] );
			}
			
			if ($setting ['db_charset']) {
				@mysql_query ( "SET character_set_connection={$setting['db_charset']}, character_set_results={$setting['db_charset']}, character_set_client=binary", $this->link );
			}
			
			if ($this->version () > '5.0.1') {
				@mysql_query ( "SET sql_mode=''", $this->link );
			}
		}
		
		if ($dbname) {
			$this->dbname = $dbname;
			@mysql_select_db ( $dbname, $this->link );
		}
	
	}
	
	function select_db($dbname) {
		return mysql_select_db ( $dbname, $this->link );
	}
	function db_exists($dbname) {
		$query = $this->query("show databases");
		while($list = $this->fetch_array($query)) {
			if($list['Database'] == $dbname){
				return True;
			}
		}
		return False;
	}
	function table_exists($tablename,$dbname='') {
		if($dbname){
			if(!$this->db_exists($dbname)){
				return False;
			}
			$this->select_db($dbname);
		}
		$query = $this->query("show tables");
		while($list = $this->fetch_array($query)) {
			$key_array = array_keys($list);
			$key = $key_array[0];
			if($list[$key] == $tablename){
				$this->select_db($this->dbname);
				return True;
			}
		}
		$this->select_db($this->dbname);
		return False;
	}	
	function query($sql, $type = '') {
		$func = $type == 'UNBUFFERED' && @function_exists ( 'mysql_unbuffered_query' ) ? 'mysql_unbuffered_query' : 'mysql_query';
		if (! ($query = $func ( $sql, $this->link ))) {
			if (in_array ( $this->errno (), array (2006, 2013 ) ) && substr ( $type, 0, 5 ) != 'RESEND') {
				$this->close ();
				require BIGQI_ROOT . '.data/config.inc.php';
				$this->connect ( $setting ['db_host'], $setting ['db_user'], $setting ['db_pw'], $setting ['db_name'], $setting ['db_pconnect'] );
				$this->query ( $sql, 'RESEND' . $type );
			} elseif ($type != 'OUTNONE' && substr ( $type, 5 ) != 'OUTNONE') {
				$this->out ( 'MySQL Query Error', $sql );
			}
		}
		return $query;
	}
	function un_query($sql) {
		$query = $this->query ( $sql, 'UNBUFFERED' );
		return $query;
	}
	function close() {
		return mysql_close ( $this->link );
	}
	
	/* MySql结果集操作定义部分 */
	
	//取得结果集数组中1条
	function fetch_array1($sql, $result_type = MYSQL_ASSOC) {
		$query = $this->query ( $sql );
		return mysql_fetch_array ( $query, $result_type );
	}
	//取得结果集数组
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array ( $query, $result_type );
	}
	function fetch_row($query) {
		$query = mysql_fetch_row ( $query );
		return $query;
	}
	//以对象方式返回结果集
	function fetch_fields($query) {
		return mysql_fetch_field ( $query );
	}
	function result($query, $row) {
		$query = @mysql_result ( $query, $row );
		return $query;
	}
	function free_result($query) {
		return mysql_free_result ( $query );
	}
	function data_seek($query, $pit) {
		@mysql_data_seek ( $query, $pit );
	}
	/* MySql结果集操作定义部分 */
	
	//返回结果集中字段的数目
	function num_fields($query) {
		return mysql_num_fields ( $query );
	}
	
	//取得数据结果集的行数
	function num_rows($query) {
		$query = mysql_num_rows ( $query );
		return $query;
	}
	
	//查询影响行数
	function affected_rows() {
		return mysql_affected_rows ( $this->link );
	}
	
	function insert_id() {
		return ($id = mysql_insert_id ( $this->link )) >= 0 ? $id : $this->result ( $this->query ( "SELECT last_insert_id()" ), 0 );
	}
	
	function version() {
		return mysql_get_server_info ( $this->link );
	}
	
	function error() {
		return (($this->link) ? mysql_error ( $this->link ) : mysql_error ());
	}
	
	function errno() {
		return intval ( ($this->link) ? mysql_errno ( $this->link ) : mysql_errno () );
	}
	
	function out($message = '', $sql = '') {
		global $setting;
		require_once BIGQI_ROOT . 'include/db_mysql_error.inc.php';
	}

	function insert($table, $inary) {

		if (!is_array($inary)){
			return '';
		}
		$in_key = array_keys($inary);
		foreach ($inary as $key => $val){
			 $sql_in_key[] = "`$key`";
			 if($field && $field[$key]){
				$sql_in_val[] = "$val";
			 }else{
				$sql_in_val[] = "'$val'";
			 }
		}
		$sql_in_key= implode(', ', $sql_in_key);
		$sql_in_val= implode(', ', $sql_in_val);
			
		$this->query('INSERT INTO `'.$table.'` ('.$sql_in_key.') VALUES ('.$sql_in_val.');');
	}

	function update($table, $inary,$field = array(),$where='') {
		if (!is_array($inary)){
			return '';
		}
		if($where){
			$wheresql = ' WHERE '.$where;
		}else{
			$wheresql = $where;
		}
		foreach ($inary as $key=>$val){
			if($field && $field[$key]){
				$sql_in_array[] = "`$key` = $val";
			}else{
				$sql_in_array[] = "`$key` = '$val'";
			}
		}
		$sql_in= implode(', ', $sql_in_array);
		$this->query('UPDATE `'.$table.'` SET '.$sql_in.$wheresql);
	}

}


function lenovomember($uid){
	global $membeructables,$memberfields,$db;
	$insert = $insertlen = array();
	$member = $db->fetch_array1("SELECT m.*,mp.field1,mp.field2  FROM convert_lefen.`pre_common_member` m 
		LEFT JOIN convert_lefen.`pre_common_member_profile` mp USING(uid) WHERE m.`uid`='$uid'" );

		
	if ($member) {
		foreach ($memberfields as  $value) {
			$insert[$value] = $member[$value];
		}

		foreach ($membeructables as  $value) {
			$db->query("insert into $value select * from convert_lefen.{$value} where uid ='$uid'");
		}
	}

	$insertlen['uid'] = $member['uid'];
	$insertlen['lenovoid'] = $member['field1'];
	$db->insert('common_member', $insert);
	if ($member['field1']) {
		var_dump( $insertlen );
		$db->insert('common_member_lenovoid', $insertlen);
	}
	
}
function lephonemember($uid){
	global $membeructables,$memberfields,$db;

	//SHOW TABLE STATUS from convert_lefen where name='pre_ucenter_members';
	$tabstatus =  $db->fetch_array1("SHOW TABLE STATUS where name='pre_ucenter_members';");
	$newuid= $tabstatus['Auto_increment'];

	foreach ($membeructables as  $value) {
		$levalue = str_replace('ucenter_', 'uc_', $value);
		$member = $db->fetch_array1("SELECT * FROM convert_lephone.$levalue WHERE `uid`='$uid'" );
		$member['uid'] = $newuid;
		if ($member['username']) {
			$member['username'] = $member['username'].'@lephone';
		}
		$db->insert($value, $member);
	}

	$insert = $insertlen = array();
	$member = $db->fetch_array1("SELECT m.* FROM convert_lephone.`pre_common_member` m WHERE m.`uid`='$uid'" );
	foreach ($memberfields as  $value) {
		if ($value == 'uid') {
			$insert[$value] = $newuid;
		}elseif ($value == 'username') {
			$insert['username'] = $member['username'].'@lephone';
		}else{
			$insert[$value] = $member[$value];
		}
	}
	$insertlen['uid'] = $newuid;
	$insertlen['lephoneid'] = $uid;
	$x = $db->insert('pre_common_member', $insert);
	echo'<pre>';
	var_dump( $x  );
	echo'</pre>';exit;
		
	$x = $db->insert('pre_common_member_lephoneid', $insertlen);
}

function get_avatar($uid, $size = 'big', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return  $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}

?>