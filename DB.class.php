<?php
/*
Copyright (c) 2017 mastro-elfo

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * Utility tool to perform queries to MySQL database in PHP with `mysqli`.
 * See also:
 * * [Project on Github](https://github.com/mastro-elfo/MYDB)
 * * [PHP mysqli class](http://php.net/manual/en/class.mysqli.php)
 * * [MySQL](https://dev.mysql.com/)
 * 
 * @version 1.3.0
 * @license https://github.com/mastro-elfo/MYDB/blob/master/LICENSE
 * @author mastro-elfo
 * @copyright Copyright (c) 2017 mastro-elfo
 * @todo Use `affected_rows` for `::replace` query?
 * @todo Maybe add `affected_rows` as public attribute?
 * @todo Maybe add `insert_id` as public attribute?
 * @todo Adjust return value in `::upsert`?
 */
class MYDB {
	
	/**
	 * @var string The last executed query
	 * @since 1.0
	 */
	public $last_query;
	
	/**
	 * @var string Database server
	 * @since 1.0
	 */
	protected $server = '';
	
	/**
	 * @var string Database user
	 * @since 1.0
	 */
	protected $user = '';
	
	/**
	 * @var string Database password
	 * @since 1.0
	 */
	protected $password = '';
	
	/**
	 * @var string Database name
	 * @since 1.0
	 */
	protected $database = '';
	
	/**
	 * @var mysqli Database connection class object
	 * @see http://php.net/manual/en/class.mysqli.php
	 * @since 1.0
	 */
	protected $db = null;
	
	/**
	 * By default calls `set_charset('utf8')` (http://php.net/manual/en/mysqli.set-charset.php)
	 * 
	 * @param $server string
	 * @param $user string
	 * @param $password string
	 * @param $database string
	 * @since 1.0
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
	 * Sends a generic query. Also saves the query string to `$this->last_query`.
	 *
	 * @param string $query The query string
	 * @return mixed Returns `false` on failure. For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return a mysqli_result object. For other successful queries will return `true`.
	 * @see http://php.net/manual/en/mysqli.query.php
	 * @since 1.0
	 */
	public function query($query) {
		$this->last_query = $query;
		return $this->db->query($query);
	}
	
	/**
	 * Creates and sends an 'INSERT INTO' query
	 *
	 * @param string $table Table name
	 * @param array $data [field_name => field_value, ...]
	 * @return number The last insert id on success, 0 on failure
	 * @since 1.0
	 */
	public function insert($table, $data) {
		$this->query('INSERT INTO `'.$table.'` ('.join(', ', array_map(function($key){return '`'.$key.'`';}, array_keys($data))).') VALUES ('.join(', ', array_map(function($value){return '\''.$this->db->real_escape_string($value).'\'';}, $data)).')');
		return $this->db->insert_id;
	}
	
	/**
	 * Creates and sends a 'REPLACE INTO' query
	 *
	 * @param string $table Table name
	 * @param array $data [field_name => field_value, ...]
	 * @return number The last insert id on success, 0 on failure
	 * @since 1.2
	 */
	public function replace($table, $data){
		$this->query('REPLACE INTO `'.$table.'` ('.join(', ', array_map(function($key){return '`'.$key.'`';}, array_keys($data))).') VALUES ('.join(', ', array_map(function($value){return '\''.$this->db->real_escape_string($value).'\'';}, $data)).')');
		return $this->db->insert_id;
	}
	
	/**
	 * Creates and sends an 'UPDATE' query
	 *
	 * @param string $table Table name
	 * @param array $data [field_name => field_value, ...]
	 * @param array $where WHERE clause, joined with AND
	 * @return number of affected rows
	 * @since 1.0
	 */
	public function update($table, $data, $where = null) {
		$this->query('UPDATE `'.$table.'` SET '.join(', ', array_map(function($key, $value){return '`'.$key.'`=\''.$this->db->real_escape_string($value).'\'';}, array_keys($data), $data)).' '.($where?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):''));
		return $this->db->affected_rows;
	}
	
	/**
	 * Create and sends an 'INSERT INTO' query. On duplicate key sends an 'UPDATE' query.
	 *
	 * @param string $table Table name
	 * @param array $data [field_name => field_value, ...]
	 * @param array $where WHERE clause, joined with AND
	 * @return mixed
	 * @since 1.0
	 */
	public function upsert($table, $data, $where) {
		$this->query('INSERT INTO `'.$table.'` ('.join(', ', array_map(function($key){return '`'.$key.'`';}, array_merge(array_keys($data), array_keys($where)))).') VALUES ('.join(', ', array_map(function($value){return '\''.$value.'\'';}, array_merge($data, $where))).') ON DUPLICATE KEY '.('UPDATE '.join(',', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($data), $data))));
		return $this->db->insert_id;
	}
	
	/**
	 * Create and sends a 'SELECT' query.
	 * 
	 * @param string $table Table name
	 * @param mixed $fields '*'|[field_name, ...]
	 * @param array $where [field_name => field_value, ...]
	 * @param array $orderby [field_name => 'ASC|DESC', ...]
	 * @param mixed $limit [0-9]+|[0-9]+,[0-9]
	 * @return mixed For successful queries return a `mysqli_result` object. Returns `false` on failure.
	 * @since 1.0
	 */
	public function select($table, $fields, $where = null, $orderby = null, $limit = null) {
		return $this->query('SELECT '.($fields=='*'?'*':join(', ', array_map(function($key){return '`'.$key.'`';}, $fields))).' FROM `'.$table.'`'.($where!==null?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):'').($orderby!==null?' ORDER BY '.join(', ', array_map(function($key, $value){return '`'.$key.'` '.$value;}, array_keys($orderby), $orderby)):'').($limit!==null?' LIMIT '.$limit:''));
	}
	
	/**
	 * Creates and sends a 'DELETE' query.
	 *
	 * @param string $table Table name
	 * @param array $where [field_name => field_value, ...]
	 * @return number of affected rows
	 * @since 1.0
	 */
	public function delete($table, $where = null) {
		$this->query('DELETE FROM `'.$table.'`'.($where!==null?' WHERE '.join(' AND ', array_map(function($key, $value){return '`'.$key.'`=\''.$value.'\'';}, array_keys($where), $where)):''));
		return $this->db->affected_rows;
	}
}

?>