<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Database table for storing roles.
 */
#[Table(name: 'roles')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true)]
#[Column(name: 'name', type: DataType::NVARCHAR, size: 128, unique: true)]
#[Column(name: 'parent_role_id', type: DataType::INT, nullable: true)]
#[Column(name: 'description', type: DataType::NVARCHAR, size: 256, nullable: true)]
class RolesTable {
}
