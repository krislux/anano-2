<?php

use Anano\Database\Migrations\MigrationInterface;
use Anano\Database\Migrations\Table;
use Anano\Database\Migrations\Serializers\MySql;
use Anano\Database\Database;

class CreateUsers implements MigrationInterface
{
    public $table = 'users';
    public $disabled = false;
    
    public function up()
    {
        Table::create($this->table, new MySql, new Database, function($table)
        {
            $table->primary('id');
            $table->string('username', 64);
            $table->string('password', 64);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Table::drop($this->table, new MySql, new Database);
    }
}