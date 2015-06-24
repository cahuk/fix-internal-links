<?php

/**
 * MySQL database handler
 */

class Db
{
    private $dbserver		= 'localhost';
    private $dbuser			= 'user';
    private $dbpassword		= 'password';
    private $dbname			= 'db_name';

	private $_dbConnectId	= 0;
	private $_connection = false;
	private $_queryResult;
	private $_numQueries		= 0;
	
	public $showSql = false;
	
	protected static $_instance = null;
	
	function __construct()
	{
	    $this->query('SET NAMES utf8');
	}
	
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }  	
	
	function factory ()
	{
	    if (!$this->_connection) {
            if (!$this->_dbConnectId)
                $this->_dbConnectId = @mysql_connect($this->dbserver, $this->dbuser, $this->dbpassword)
                    or die(	"MySQL connect failed: " . mysql_error());
		
            $dbselect = mysql_select_db($this->dbname);
            if( !$dbselect )
                mysql_close($this->db_connect_id);
			
            if ($this->_dbConnectId and $dbselect) { 
                $this->_connection = true;
                return $this->_dbConnectId;
            } else {
                die( "MySQL connect failed: " . mysql_error());	    	
            }
	    } else {
	        return $this->_dbConnectId;
	    }
	}
	
	function query($sql = "")
	{
		unset($this->queryResult);
		if (!$this->_connection) {
		    $this->factory();
		}
		if ($sql != "") {
		    if ($this->showSql) {
		        echo $sql; die();
		    }
			$this->_numQueries++;
			try {
			    $this->_queryResult = mysql_query($sql, $this->_dbConnectId);
			    if (!$this->_queryResult) throw new Exception(mysql_error());
			} catch (Exception $e) {
			    echo $e;
			}
			
		}
		
		if ($this->_queryResult) 
			return $this->_queryResult;
		else 
			return false;
	}
	
	function fetchAll($sql = '')
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }
	    $res = array();
	    if ($result) {
	       while ($r = mysql_fetch_assoc($result)) {
	           $res[] = $r;
           }
	       if (!empty($res)) {
	           return $res;
            } else {
	           return array();
	        }
	    } else {
	        return array();
	    }
	}
	
	function fetchOne($sql = '')	
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }

	    if ($result) {
	       $res = mysql_fetch_array($result);
	       if ($res) {
	           return $res[0];
	       } else {
	           return false;
	       }
	    } else {
	        return false;
	    }
	}
	
	function fetchRow($sql = '')
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }
	    if ($result) {
	       $res = mysql_fetch_assoc($result);
	       if ($res) {
	           return $res;
	       } else {
	           return false;
	       }
	    } else {
	        return false;
	    }
	}
	
	function fetchPairs($sql = '')
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }
	    if ($result) {
	        while ($res = @mysql_fetch_array($result)) {
	        	$r[$res[0]] = $res[1];
	        }
	       if (isset($r)) {
	           return $r;
	       } else {
	           return array();
	       }
	    } else {
	        return array();
	    }
	}

	function fetchCol($sql = '', $column = '')
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }
	    if ($result) {
	        while ($res = @mysql_fetch_array($result)) {
	            if ($column) {
	                $r[] = $res[$column];
	            } else {
	                $r[] = $res[0];
	            }
	        	
	        }
	       if (isset($r)) {
	           return $r;
	       } else {
	           return array();
	       }
	    } else {
	        return array();
	    }
	    
	}
	
	function delete($table, $where = '')
	{
	    $sql = "DELETE FROM $table" . (($where) ? " WHERE $where" : '');
	    return $this->query($sql);
	}
	
	function insert($table, $data)
	{
	    $str1 = '';
	    $str2 = '';
	    foreach ($data as $field => $value) {
	        if ($str1) {
	            $str1 .= ', ';
	        }
	        if ($str2) {
	            $str2 .= ', ';
	        }
	        $str1 .= '`' . $field . '`';
	        $str2 .= "'" . $value . "'";
	    }
	    $sql = 'INSERT INTO ' . $table . ' (' . $str1 . ') VALUES (' . $str2 . ')';
	    $res = $this->query($sql);
	    if ($res) return $this->getInsertedId();
	    else return $res;
	}
	
	function getInsertedId()
	{
	    if (!$this->_connection) return false;
	    return mysql_insert_id($this->_dbConnectId);
	}
	
	function update($table, $data, $where = '')
	{
	    $str1 = '';
	    foreach ($data as $field => $value) {
	        if ($str1) {
	            $str1 .= ', ';
	        }
	        $str1 .= "`$field`='$value'";
	    }
	    $sql = "UPDATE $table SET $str1" . (($where) ? " WHERE $where" : '');
	    return $this->query($sql);
	}

    function replace($table, $data)
    {
        $str1 = '';
        $str2 = '';
        foreach ($data as $field => $value) {
            if ($str1) {
                $str1 .= ', ';
            }
            if ($str2) {
                $str2 .= ', ';
            }
            $str1 .= '`' . $field . '`';
            $str2 .= "'" . $value . "'";
        }
        $sql = 'REPLACE INTO ' . $table . ' (' . $str1 . ') VALUES (' . $str2 . ')';
        return $this->query($sql);
    }

	function getNumRows($sql)
	{
	    if ($sql == '') {
	        $result = $this->_queryResult;
	    } else {
	        $result = $this->query($sql);
	    }
	    return mysql_num_rows($result);	    
	}

	public function showSql($show = true)
	{
	    $this->showSql = $show;
	    return $this;
	}	
} // end class
