<?php 
/*
 * 通用DB类
 *
 *
 */

class DB {
	//连接
	public static $connection;
	public static $fetch_style = PDO::FETCH_ASSOC;
	protected $options = array(
		PDO::ATTR_CASE => PDO::CASE_LOWER,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
		PDO::ATTR_STRINGIFY_FETCHES => false,
		PDO::ATTR_EMULATE_PREPARES => false,
	);
	
	
	public $pdo;
	public $config;
	
	//实例
	public static function instance() {
		if(!isset(self::$connection)) {
			self::$connection = new DB(Config::get('global.database'));
		}
		return self::$connection;
	}
	
	//查询表数据
	public static function select($tablename, $fieldsarr, $wheresqlarr, $orderarr = null, $limit = 0, $offset = 0) {
		$db = DB::instance();
		$list = array();
		$comma = $fields ='';
		if(empty($fieldsarr)) {
			$fields = '*';
		} elseif(is_array($fieldsarr)) {
			foreach ($fieldsarr as $key => $value) {
				$fields .= $comma.'`'.$value.'`';
				$comma = ', ';
			}
			
		} else {
			$fields = $fieldsarr;
		}
		$comma = $where ='';
		$bindings = array();
		if(empty($wheresqlarr)) {
			$where = '1';
		} elseif(is_array($wheresqlarr)) {
			foreach ($wheresqlarr as $key => $value) { //只有数组才使用binding
				$argument_length = count($value);
				if($argument_length == 1) {
					continue;
				} else if($argument_length == 2) {
					$where .= $comma.'`'.$value[0].'`'.' = :'.$value[0].'';
					$bindings[$value[0]] = $value[1];
				} else if($argument_length == 3) {
					$where .= $comma.'`'.$value[0].'`'.' '.$value[1].' :'.$value[0].'';
					$bindings[$value[0]] = $value[2];
				}
				$comma = ' AND ';
			}
		} else {
			$where = $wheresqlarr;
		}
		$comma = $orderby ='';
		if(empty($orderarr)) {
			$orderby = '';
		} elseif(is_array($orderarr)) {
			foreach ($orderarr as $key => $value) {
				$orderby .= $comma.'`'.$key.'` '.$value;
				$comma = ', ';
			}
		} else {
			$orderby = $orderarr;
		}
		if(!empty($orderby)) $orderby = 'ORDER BY '.$orderby;
		$limits = '';
		if($limit>=1) $limits = ' LIMIT '.$offset.','.$limit;
		$result = $db->execute('SELECT '.$fields.' FROM '.$db->tname($tablename)." WHERE $where $orderby $limits",$bindings);
		if($limit == 1) {
			$list = $result[0]->fetch(self::$fetch_style);
		} else {
			$list = $result[0]->fetchAll(self::$fetch_style);
			
		}
		return $list;
	}
	
	//插入数据
	public static function insert($tablename, $insertsqlarr, $returnid=0, $replace = false, $silent=0) {
		$db = DB::instance();
		$insertkeysql = $insertvaluesql = $comma = '';
		$bindings = array();
		foreach ($insertsqlarr as $insert_key => $insert_value) {
			$insertkeysql .= $comma.'`'.$insert_key.'`';
			$insertvaluesql .= $comma.':'.$insert_key.'';
			$comma = ', ';
			$bindings[$insert_key] = $insert_value;
		}
		$method = $replace?'REPLACE':'INSERT';
		$sql = $method.' INTO '.$db->tname($tablename).' ('.$insertkeysql.') VALUES ('.$insertvaluesql.')'.($silent?'SILENT':'');
		$db->execute($sql, $bindings);
		if($returnid && !$replace) {
			return $db->pdo->lastInsertId();
		} else {
			return true;
		}
	}
	
	//更新数据
	public static function update($tablename, $setsqlarr, $wheresqlarr, $silent=0) {
		$db = DB::instance();
		$setsql = $comma = '';
		$bindings = array();
		foreach ($setsqlarr as $set_key => $set_value) {//fix
			$setsql .= $comma.'`'.$set_key.'`'.' = :'.$set_key.'';
			$comma = ', ';
			$bindings[$set_key] = $set_value;
		}
		$where = $comma = '';
		if(empty($wheresqlarr)) {
			$where = '1';
		} elseif(is_array($wheresqlarr)) {
			foreach ($wheresqlarr as $key => $value) {
				$argument_length = count($value);
				if($argument_length == 1) {
					continue;
				} else if($argument_length == 2) {
					$where .= $comma.'`'.$value[0].'`'.' = :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[1];
				} else if($argument_length == 3) {
					$where .= $comma.'`'.$value[0].'`'.' '.$value[1].' :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[2];
				}
				$comma = ' AND ';
			}
		} else {
			$where = $wheresqlarr;
		}
		$sql = 'UPDATE '.$db->tname($tablename).' SET '.$setsql.' WHERE '.$where.($silent?'SILENT':'');
		list($statement, $result) = $db->execute($sql, $bindings);
		return $statement->rowCount(); //affect row;
	}
	
	//删除
	public static function delete($tablename, $wheresqlarr, $empty = false) {
		$db = DB::instance();
		$where = $comma = '';
		$bindings = array();
		if(empty($wheresqlarr)) {
			if(!$empty) {
				return false;
			} else {
				$where = '1';
			}
		} elseif(is_array($wheresqlarr)) {
			foreach ($wheresqlarr as $key => $value) {
				$argument_length = count($value);
				if($argument_length == 1) {
					continue;
				} else if($argument_length == 2) {
					$where .= $comma.'`'.$value[0].'`'.' = :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[1];
				} else if($argument_length == 3) {
					$where .= $comma.'`'.$value[0].'`'.' '.$value[1].' :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[2];
				}
				$comma = ' AND ';
			}
		} else {
			$where = $wheresqlarr;
		}
		$sql = "DELETE FROM ".$db->tname($tablename)." WHERE $where";
		list($statement, $result) = $db->execute($sql, $bindings);
		return $statement->rowCount(); //affect row;
	}
	
	//取总数
	public static function getcount($tablename, $wherearr = null, $get='COUNT(*)',$as_key = 'total') {
		$db = DB::instance();
		$bindings = array();
		$where = $comma = '';
		if(empty($wherearr)) {
			$where = '1';
		} else if(is_array($wherearr)) {
			foreach ($wherearr as $key => $value) {
				$argument_length = count($value);
				if($argument_length == 1) {
					continue;
				} else if($argument_length == 2) {
					$where .= $comma.'`'.$value[0].'`'.' = :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[1];
				} else if($argument_length == 3) {
					$where .= $comma.'`'.$value[0].'`'.' '.$value[1].' :w__'.$value[0].'';
					$bindings['w__'.$value[0]] = $value[2];
				}
				$comma = ' AND ';
			}
		} else {
			$where = $wherearr;
		}
		$sql = "SELECT {$get} AS `{$as_key}` FROM ".$db->tname($tablename)." WHERE $where LIMIT 1";
		list($statement, $result) = $db->execute($sql, $bindings);
		$list = $statement->fetch(self::$fetch_style);
		return $list[$as_key];
	}

	
	public function __construct($config) {
		$this->config = $config;
		extract($config);
		$dsn = "mysql:host={$host};dbname={$database}";
		if (isset($config['port'])){
			$dsn .= ";port={$config['port']}";
		}
		if (isset($config['unix_socket'])) {
			$dsn .= ";unix_socket={$config['unix_socket']}";
		}
		$this->pdo = new PDO($dsn, $username, $password, $this->options($config));
		if (isset($config['charset'])) {
			$this->pdo->prepare("SET NAMES '{$config['charset']}'")->execute();
		}
		return $this->pdo;
	}
	
	protected function options($config){
		$options = (isset($config['options'])) ? $config['options'] : array();
		return $options + $this->options;
	}
	
	public function tname($name) {
		return $this->config['prefix'].$name;
	}
	//查询
	public function query($sql, $bindings = array()) {
		$db = DB::instance();
		list($statement, $result) = $db->execute($sql, $bindings);
		return $statement->fetchAll(self::$fetch_style);
	}
	
	//执行语句
	public function execute($sql, $bindings = array()) {
		try{
			$statement = $this->pdo->prepare($sql);
			$result = $statement->execute($bindings);
		} catch (Exception $exception) {
			exit($sql);
			$exception = new Exception($sql, $bindings, $exception);
			throw $exception;
		}
		return array($statement, $result);
	}
	
}