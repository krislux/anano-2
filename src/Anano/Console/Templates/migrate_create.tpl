<?php

use Anano\Database\Migrations\MigrationInterface;
use Anano\Database\Migrations\Table;
use Anano\Database\Migrations\Serializers\MySql;
use Anano\Database\Database;

class Create{name} implements MigrationInterface
{
    public $table = '{lname}';
    public $disabled = false;
    
    public function up()
    {
        Table::create($this->table, new MySql, new Database, function($table)
        {
            $table->primary('id');
        });
    }
    
    public function down()
    {
        Table::drop($this->table, new MySql, new Database);
    }
}