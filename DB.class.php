<?php

/**
 * Utility tool to perform queries to mysql db in PHP with `mysqli`.
 */
class MYDB {
	
	/**
	 * @var string The last executed query
	 */
	public $last_query;
	
	/**
	 * @var string Database server
	 */
	protected $server = '';
	
	/**
	 * @var string Database user
	 */
	protected $user = '';
	
	/**
	 * @var string Database password
	 */
	protected $password = '';
	
	/**
	 * @var string Database name
	 */
	protected $database = '';
	
	/**
	 * @var mysqli Database connection class object
	 * @see http://php.net/manual/en/class.mysqli.php
	 */
	protected $db = null;
	
	/**
	 * By default calls `set_charset('utf8')` (http://php.net/manual/en/mysqli.set-charset.php).
	 * 
	 * @param $server string
	 * @param $user string
	 * @param $password string
	 * @param $database string
	 */
	public function __construct($server = null, $user = null, $password = null, $database = null) {
		if($server !== null) {
			$this->server = $server;
		}
		if($user !== null) {
			$this->user = $user;
		}
		if($password !== null) {
			$this->password = $password;
		}
		if($database !== null) {
			$this->database = $database;
		}
		
		$this->db = new mysqli($this->server, $this->user, $this->password, $this->database);
		$this->db->set_charset('utf8');
	}
	
	/**
	 * Sends a generic query. Also saves the query string to `$this->last_query`
	 *
	 * @param string $query
	 * @return mixed Returns `false` on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a mysqli_result object. For other successful queries will return `true`.
	 * @see http://php.net/manual/en/mysqli.query.php
	 */
	public function query($query) {
		$this->last_query = $query;
		return $this->db->query($query);
	}
	
	/**
	 * Creates and sends an 'INSERT INTO' query
	 *
	 * @param $table Table name
	 * @param $data [field_name => field_value, ...]
	 * @return number The last insert id on success, 0 on failure
	 */
	public function insert($table, $data) {
		$this->query('INSERT INTO `'.$table.'` ('.join(', ', array_map(function($key){return '`'.$key.'`';}, array_keys($data))).') VALUES ('.join(', ', array_map(function($value){return '\''.$value.'\'';}, $data)).')');
		return $this->db->insert_id;
	}
	
	/**
	 * Creates and sends an 'UPDATE' query
	 *
	 * @param $table
	 * @param $data
	 * @param $where
	 * @return `true` on success, `false` on failure
	 */
	public function update($table, $data, $where = null) {
		return $this->query('UPDATE `'.$table.'` SET '.join(', ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($data), $data)).' '.($where?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):''));
	}
	
	/**
	 * Create and sends an 'INSERT INTO' query. On duplicate key sends an 'UPDATE' query.
	 *
	 * @param $table
	 * @param $data
	 * @param $where
	 * @return mixed 
	 */
	public function upsert($table, $data, $where) {
		$this->query('INSERT INTO `'.$table.'` ('.join(', ', array_map(function($key){return '`'.$key.'`';}, array_merge(array_keys($data), array_keys($where)))).') VALUES ('.join(', ', array_map(function($value){return '\''.$value.'\'';}, array_merge($data, $where))).') ON DUPLICATE KEY '.('UPDATE '.join(',', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($data), $data))));
		return $this->db->insert_id;
	}
	
	/**
	 * Create and sends a 'SELECT' query
	 * 
	 * @param $table string Table name
	 * @param $fields mixed '*'|[field_name, ...]
	 * @param $where array [field_name => field_value, ...]
	 * @param $orderby array [field_name => 'ASC|DESC', ...]
	 * @param $limit mixed [0-9]+|[0-9]+,[0-9]
	 * @return mixed For successful queries return a `mysqli_result` object. Returns `false` on failure.
	 */
	public function select($table, $fields, $where = null, $orderby = null, $limit = null) {
		return $this->db->query('SELECT '.($fields=='*'?'*':join(', ', array_map(function($key){return '`'.$key.'`';}, $fields))).' FROM `'.$table.'`'.($where!==null?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):'').($orderby!==null?' ORDER BY '.join(', ', array_map(function($key, $value){return '`'.$key.'` '.$value;}, array_keys($orderby), $orderby)):'').($limit!==null?' LIMIT '.$limit:''));
	}
	
	/**
	 * Creates and sends a 'DELETE' query.
	 *
	 * @param $table
	 * @param $where
	 * @return `true` on success, `false` on failure
	 */
	public function delete($table, $where = null) {
		return $this->db->query('DELETE FROM `'.$table.'`'.($where!==null?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):''));
	}
}

?>