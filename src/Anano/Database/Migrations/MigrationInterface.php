<?php

namespace Anano\Database\Migrations;

interface MigrationInterface
{
    public function up();
    
    public function down();
}