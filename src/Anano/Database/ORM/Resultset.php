<?php

/**
 * Cursor/container for database results.
 * Can be used directly in foreach()
 */

namespace Anano\Database\ORM;

use Iterator;
use PDO;
use PDOStatement;

class Resultset implements Iterator
{
    private $position = -1;
    private $statement;
    private $fetch_style;

    private $model = null;
    private $last_row = null;

    public function __construct(PDOStatement $statement, $fetch_style=PDO::FETCH_ASSOC)
    {
        $this->statement = $statement;
        $this->fetch_style = $fetch_style;
    }

    public function __destruct()
    {
        $this->statement->closeCursor();
    }

    /**
     * Set the model to cast to when getting rows.
     */
    public function setModel($name)
    {
        $this->model = $name;
    }

    public function all($as_array = false)
    {
        $output = [];
        $max = $this->statement->rowCount();
        for ($i = 0; $i < $max; $i++) {
            $output[] = $this->next($as_array);
        }
        return $output;
    }

    public function next($as_array = false)
    {
        $this->last_row = $this->statement->fetch($this->fetch_style);
        if ($this->model && $as_array == false) {
            $this->last_row = call_user_func(array($this->model, 'make'), $this->last_row );
        }
        
        $this->position++;
        return $this->last_row;
    }

    public function key()
    {
        return $this->position;
    }

    public function current()
    {
        if ($this->last_row !== null) {
            return $this->last_row;
        }
        return $this->next();
    }

    public function valid()
    {
        return $this->position < $this->statement->rowCount();
    }

    public function rewind()
    {
        if ($this->position === -1) {
            return;
        }
        throw new \ErrorException('Resultset cannot be rewound. Cache result or run statement again.');
    }
}