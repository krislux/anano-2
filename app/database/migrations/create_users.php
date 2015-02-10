<?php

use Anano\Database\Migrations\Migration;
use Anano\Database\Migrations\Table;
use Anano\Database\Migrations\Serializers\MySql;
use Anano\Database\Database;

class CreateUsers extends Migration
{
    public $table = 'users';
    public $disabled = false;
    
    public function up()
    {
        $table = new Table($this->table, new MySql, new Database);
        $table->index('id');
        $table->save();
    }
    
    public function down()
    {
        $table = new Table($this->table, new MySql, new Database);
        $table->drop();
    }
}