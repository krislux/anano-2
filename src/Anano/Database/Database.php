<?php

namespace Anano\Database;

use Anano\Config;
use PDO;
use PDOException;

class Database implements DatabaseInterface
{
    protected $db;
    private $connName;
    
    private static $query_log = array();
    
    public function __construct($connName=null)
    {
        $this->connName = $connName;
    }
    
    /**
     * Set up the database connection.
     */
    
    public function init()
    {
        if (!$this->connName) $this->connName = Config::get('database.default');
        
        extract(Config::get('database.connections.'. $this->connName));
        
        $connstr = "$driver:host=$host;dbname=$database;charset=$charset";
        try
        {
            $this->db = new PDO($connstr, $username, $password,
                array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => $persistent));
        }
        catch (PDOException $e)
        {
            // Connection aborted. Strange error that may be caused by persistent connection.
            if ($e->getCode() == 10053)
            {
                // Attempt same connection without persistent as a fallback.
                $this->db = new PDO($connstr, $username, $password,
                    array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => false));
            }
            else
                throw $e;
        }
    }
    
    /**
     * Get an array of strings containing all queries executed so far with parameters resolved.
     * @return  array
     */
    
    public static function getQueryLog()
    {
        return self::$query_log;
    }
    
    /**
     * Execute a normal inline query.
     * @param   string  $sql    SQL string to execute
     * @param   int     $fetch  The PDO fetch style. E.g. PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     * @return  array
     */
    
    public function query($sql, $fetch=PDO::FETCH_ASSOC)
    {
        $rv = false;
        
        if ($this->db === null)
            $this->init();
        
        self::$query_log[] = $sql;
        
        
        try
        {
            $stmt = $this->db->query($sql);
            $rv = $stmt->fetchAll($fetch);
        }
        catch (PDOException $e)
        {
            if ($e->errorInfo[1] == 2053)   // no result set, likely because of non-select query.
            {
                $rv = true;
            }
            else
            {
                // @todo    PDOExceptions return strings as getCode() but cannot accept them in the constructor. Find better solution.
                throw new PDOException( $e->getMessage() . ". Query: " . $sql );
            }
        }
        
        $stmt->closeCursor();
        return $rv;
    }
    
    /**
     * Execute a parameterized statement.
     * @param   string  $sql        SQL string with ? for parameters
     * @param   array   $params     Array of parameter values, length must match number of question marks in $sql.
     * @param   int     $fetch      The PDO fetch style. E.g. PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_BOTH
     * @return  array
     */
    
    public function paramQuery($sql, $params, $fetch=PDO::FETCH_ASSOC)
    {
        $rv = false;
        
        if ($this->db === null)
            $this->init();
        
        self::$query_log[] = $this->resolveParams($sql, $params);
        
        try
        {
            $stmt = $this->db->prepare($sql);
            
            $cnt = count($params);
            for ($i = 0; $i < $cnt; $i++)
            {
                if ($params[$i] === null)
                    $type = PDO::PARAM_NULL;
                else if (preg_match('/[\d]+/', $params[$i]))
                    $type = PDO::PARAM_INT;
                else
                    $type = PDO::PARAM_STR;
                
                $stmt->bindValue($i+1, $params[$i], $type);
            }
            if ($stmt->execute())
            {
                try
                {
                    $rv = $stmt->fetchAll($fetch);
                }
                catch (PDOException $e)
                {
                    if ($e->errorInfo[1] == 2053)   // no result set, likely because of non-select query.
                        $rv = true;
                }
            }
            
            $stmt->closeCursor();
            return $rv;
        }
        catch (PDOException $e)
        {
            if ($e->getCode() == 42000 && Config::get('app.debug'))
                die($e->getMessage() . "<p>$sql</p>");
            else throw $e;
        }
    }
    
    /**
     * Convert a parameterized object to a plain sql string, mainly for query log.
     */
    
    protected function resolveParams($sql, array $params)
    {
        $sql = preg_replace_callback('/\?/', function($matches) use ($params) {
            static $i = 0;
            $rv = $params[$i++];
            if (preg_match('/[\d]+/', $rv))
                return $rv;
            else
                return "\"$rv\"";
        }, $sql);
        
        return $sql;
    }
    
    protected function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    
    protected function escape($str)
    {
        return $this->db->quote($str);
    }
    
    public function affectedRows()
    {
        return $this->db->rowCount();
    }
}