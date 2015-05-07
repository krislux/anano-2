<?php

namespace Anano\Database\ORM;

class QueryBuilder extends \Anano\Database\Database
{
    private $query = array();
    private $flags = array();
    
    
    /**
     * Build query for select
     */
    
    private function buildSelect()
    {
        $parts = array();
        $placeholders = array();
        
        if (empty($this->query['select']))
            $this->query['select'][] = '*';
        
        // If instance of model, use $table_name as default
        if (empty($this->query['table']) && !empty($this->table_name))
            $this->query['table'] = $this->table_name;
        
        if (empty($this->query['table']))
            throw new \ErrorException('No table set.');
        
        $parts[] = "SELECT " . implode(', ', $this->query['select']);
        
        if (!empty($this->query['table']))
            $parts[] = "FROM " . self::tick_words( $this->query['table'] );
        
        if (!empty($this->query['join']))
        {
            $temp = array();
            foreach ($this->query['join'] as $join)
            {
                $dir = '';
                if ($join[4])
                    $dir = strtoupper($join[4]) . ' ';
                $temp[] = $dir . 'JOIN ' .  self::tick_words($join[0]) . ' ON ' . self::tick_words($join[1]) . $join[2] . self::tick_words($join[3]);
            }
            $parts[] = implode(' ', $temp);
        }
        
        if (!empty($this->query['where']))
        {
            $temp = array();
            foreach ($this->query['where'] as $where)
            {
                if ($where[2] === null)
                {
                    $temp[] = self::tick_words($where[0]) . ' IS NULL';
                }
                elseif ($where[2] === false)
                {
                    $temp[] = self::tick_words($where[0]) . $where[1];
                }
                else
                {
                    $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                    $placeholders[] = $where[2];
                }
            }
            $parts[] = 'WHERE ' . implode(' AND ', $temp);
        }
        
        if (!empty($this->query['orWhere']))
        {
            $temp = array();
            foreach ($this->query['orWhere'] as $where)
            {
                $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                $placeholders[] = $where[2];
            }
            if (empty($this->query['where']))
                $parts[] = 'WHERE ' . implode(' OR ', $temp);
            else
                $parts[] = 'OR ' . implode(' OR ', $temp);
        }
        
        if (!empty($this->query['groupBy']))
        {
            $parts[] = 'GROUP BY ' . self::tick_words(implode(', ', $this->query['groupBy']));
        }
        
        if (!empty($this->query['orderBy']))
        {
            $temp = array();
            foreach ($this->query['orderBy'] as $order)
            {
                $col = preg_replace('/[^\w\.]/', '', $order[0]);
                $dir = strtoupper($order[1]);
                
                if (!in_array($dir, array('ASC', 'DESC')))
                    throw new \ErrorException("Incorrect direction value '$dir'");
                
                $temp[] = self::tick_words($col) . ' ' . $dir;
            }
            $parts[] = 'ORDER BY ' . implode(', ', $temp);
        }
        
        if (!empty($this->query['limit']))
            $parts[] = 'LIMIT ' . $this->query['limit'];
        
        if (!empty($this->query['offset']))
            $parts[] = 'OFFSET ' . $this->query['offset'];
        
        return array(
            'query' => implode(' ', $parts),
            'placeholders' => $placeholders
        );
    }
    
    
    /**
     * Build query for update
     */
    
    private function buildUpdate(array $fields)
    {
        $parts = array();
        $placeholders = array();
        
        // If instance of model, use $table_name as default
        if (empty($this->query['table']) && !empty($this->table_name))
            $this->query['table'] = $this->table_name;
        
        if (empty($this->query['table']))
            throw new \ErrorException('No table set.');
        
        $parts[] = 'UPDATE';
        
        if (!empty($this->query['table']))
            $parts[] = self::tick_words($this->query['table']);
        
        if (!empty($fields))
        {
            $temp = array();
            foreach ($fields as $key => $val)
            {
                $temp[] = self::tick_words($key) . '=?';
                $placeholders[] = $val;
            }
            $parts[] = 'SET ' . implode(', ', $temp);
        }
        
        $temp = array();
        foreach ($this->query['where'] as $where)
        {
            if ($where[2] === null)
            {
                $temp[] = self::tick_words($where[0]) . ' IS NULL';
            }
            elseif ($where[2] === false)
            {
                $temp[] = self::tick_words($where[0]) . $where[1];
            }
            else
            {
                $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                $placeholders[] = $where[2];
            }
        }
        $parts[] = 'WHERE ' . implode(' AND ', $temp);
        
        if (!empty($this->query['orWhere']))
        {
            $temp = array();
            foreach ($this->query['orWhere'] as $where)
            {
                $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                $placeholders[] = $where[2];
            }
            if (empty($this->query['where']))
                $parts[] = 'WHERE ' . implode(' OR ', $temp);
            else
                $parts[] = 'OR ' . implode(' OR ', $temp);
        }
        
        return array(
            'query' => implode(' ', $parts),
            'placeholders' => $placeholders
        );
    }
    
    
    /**
     * Build query for insert/replace
     */
    
    private function buildInsert(array $fields, $replace=false)
    {
        $parts = array();
        $placeholders = array();
        
        // If instance of model, use $table_name as default
        if (empty($this->query['table']) && !empty($this->table_name))
            $this->query['table'] = $this->table_name;
        
        if (empty($this->query['table']))
            throw new \ErrorException('No table set.');
        
        if ($replace)
            $parts[] = 'REPLACE INTO';
        else
            $parts[] = 'INSERT INTO';
        
        if (!empty($this->query['table']))
            $parts[] = self::tick_words( $this->query['table'] );
        
        $temp = array();
        foreach ($fields as $key => $val)
        {
            $temp[0][] = $key;
            $temp[1][] = '?';
            $placeholders[] = $val;
        }
        
        $parts[] = '('. implode(', ', self::tick_array($temp[0])) .') VALUES ('. implode(', ', $temp[1]) .')';
        
        return array(
            'query' => implode(' ', $parts),
            'placeholders' => $placeholders
        );
    }
    
    /**
     * Build query for delete
     */
    
    private function buildDelete()
    {
        $parts = array();
        $placeholders = array();
        
        // If instance of model, use $table_name as default
        if (empty($this->query['table']) && !empty($this->table_name))
            $this->query['table'] = $this->table_name;
        
        if (empty($this->query['table']))
            throw new \ErrorException('No table set.');
        
        $parts[] = 'DELETE FROM ';
        
        if (!empty($this->query['table']))
            $parts[] = self::tick_words( $this->query['table'] );
        
        if (!empty($this->query['where']))
        {
            $temp = array();
            foreach ($this->query['where'] as $where)
            {
                $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                $placeholders[] = $where[2];
            }
            $parts[] = 'WHERE ' . implode(' AND ', $temp);
        }
        
        if (!empty($this->query['orWhere']))
        {
            $temp = array();
            foreach ($this->query['orWhere'] as $where)
            {
                $temp[] = self::tick_words($where[0]) . $where[1] . '?';
                $placeholders[] = $where[2];
            }
            if (empty($this->query['where']))
                $parts[] = 'WHERE ' . implode(' OR ', $temp);
            else
                $parts[] = 'OR ' . implode(' OR ', $temp);
        }
        
        return array(
            'query' => implode(' ', $parts),
            'placeholders' => $placeholders
        );
    }
    
    public function result()
    {
        $build = $this->buildSelect();
        $this->clearQuery();
        return $this->paramQuery(
            $build['query'],
            $build['placeholders']
        );
    }
    
    public function get()
    {
        $ar = array();
        foreach ($this->result() as $result)
        {
            $ar[] = static::make($result);
        }
        return $ar;
    }
    
    public function first()
    {
        $set = $this->get();
        if (is_array($set))
            return current($set);
        return $set;
    }
    
    public function count()
    {
        $set = $this->select('COUNT(*)')->first();
        return $set->field(0);
    }
    
    public function update(array $fields)
    {
        $build = $this->buildUpdate($fields);
        $this->clearQuery();
        return $this->paramQuery(
            $build['query'],
            $build['placeholders']
        );
    }
    
    public function insert(array $fields)
    {
        $build = $this->buildInsert($fields);
        $this->clearQuery();
        return $this->paramQuery(
            $build['query'],
            $build['placeholders']
        );
    }
    
    public function replace($fields)
    {
        $build = $this->buildInsert($fields, true);
        $this->clearQuery();
        return $this->paramQuery(
            $build['query'],
            $build['placeholders']
        );
    }
    
    public function destroy()
    {
        $build = $this->buildDelete();
        $this->clearQuery();
        return $this->paramQuery(
            $build['query'],
            $build['placeholders']
        );
    }
    
    public function describe()
    {
        return $this->query("DESCRIBE `{$this->table_name}`");
    }
    
    protected static function tick_words($str)
    {
        $str = trim($str);
        if (empty($str))
            return $str;
        $str = str_replace('.', '`.`', $str);
        $str = '`' . preg_replace_callback('/[ \t,.]+(as)[ \t,.]+/i', function($matches) {
            return '` '. strtoupper($matches[1]) .' `';
        }, $str) . '`';
        return $str;
    }
    
    protected static function tick_array(array $ar)
    {
        foreach ($ar as &$item)
        {
            if (!empty($item))
                $item = "`$item`";
        }
        return $ar;
    }
    
    private function clearQuery()
    {
        $this->query = array();
    }
    
    /**
     * Builder methods, select.
     */
    
    public function select()
    {
        $args = func_get_args();
        foreach ($args as $arg)
            $this->query['select'][] = $arg;
        return $this;
    }
    
    public function from($table)
    {
        $this->query['table'] = $table;
        return $this;
    }
    
    public function table($table)
    {
        $this->query['table'] = $table;
        return $this;
    }
    
    public function join($table, $var1, $mode, $var2, $dir=null)
    {
        $this->query['join'][] = array($table, $var1, $mode, $var2, $dir);
        return $this;
    }
    public function leftJoin($table, $var1, $mode, $var2)
    {
        return $this->join($table, $var1, $mode, $var2, 'left');
    }
    public function rightJoin($table, $var1, $mode, $var2)
    {
        return $this->join($table, $var1, $mode, $var2, 'right');
    }
    public function innerJoin($table, $var1, $mode, $var2)
    {
        return $this->join($table, $var1, $mode, $var2, 'inner');
    }
    
    public function where($var1, $mode, $var2)
    {
        $this->query['where'][] = array($var1, $mode, $var2);
        return $this;
    }
    
    public function orWhere($var1, $mode, $var2)
    {
        $this->query['orWhere'][] = array($var1, $mode, $var2);
        return $this;
    }
    
    public function whereNull($var1)
    {
        $this->query['where'][] = array($var1, ' IS NULL', false);
        return $this;
    }
    
    public function whereNotNull($var1)
    {
        $this->query['where'][] = array($var1, ' IS NOT NULL', false);
        return $this;
    }
    
    public function groupBy()
    {
        $args = func_get_args();
        foreach ($args as $arg)
            $this->query['groupBy'][] = $arg;
        return $this;
    }
    
    public function orderBy($field, $dir='asc')
    {
        $this->query['orderBy'][] = array($field, $dir);
        return $this;
    }
    
    public function limit($num)
    {
        $this->query['limit'] = (int)$num;
        return $this;
    }
    
    public function offset($num)
    {
        $this->query['offset'] = (int)$num;
        return $this;
    }
}