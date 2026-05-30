<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\ForeignKey;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Database table for user-role associations.
 */
#[Table(name: 'user_roles')]
#[Column(name: 'user_id', type: DataType::INT, primary: true)]
#[Column(name: 'role_id', type: DataType::INT, primary: true)]
#[ForeignKey(table: RolesTable::class, columns: ['role_id' => 'id'], name: 'ur_role_fk', onUpdate: 'no action', onDelete: 'cascade')]
class UserRolesTable {
}
