<?php

namespace Anano\Database\Migrations\Serializers;

use Anano\Database\Migrations\Table;

interface SerializerInterface
{
    public function serialize(Table $table);
    public function truncate($name);
    public function drop($name);
}