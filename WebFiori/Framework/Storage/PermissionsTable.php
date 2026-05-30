<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Database table for storing permissions.
 */
#[Table(name: 'permissions')]
#[Column(name: 'id', type: DataType::INT, primary: true, autoIncrement: true)]
#[Column(name: 'name', type: DataType::NVARCHAR, size: 128, unique: true)]
#[Column(name: 'description', type: DataType::NVARCHAR, size: 256, nullable: true)]
class PermissionsTable {
}
