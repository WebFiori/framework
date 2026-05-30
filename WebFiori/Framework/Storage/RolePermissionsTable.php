<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Database\Attributes\Column;
use WebFiori\Database\Attributes\ForeignKey;
use WebFiori\Database\Attributes\Table;
use WebFiori\Database\DataType;

/**
 * Database table for role-permission associations.
 */
#[Table(name: 'role_permissions')]
#[Column(name: 'role_id', type: DataType::INT, primary: true)]
#[Column(name: 'permission_id', type: DataType::INT, primary: true)]
#[ForeignKey(table: RolesTable::class, columns: ['role_id' => 'id'], name: 'rp_role_fk', onUpdate: 'no action', onDelete: 'cascade')]
#[ForeignKey(table: PermissionsTable::class, columns: ['permission_id' => 'id'], name: 'rp_perm_fk', onUpdate: 'no action', onDelete: 'cascade')]
class RolePermissionsTable {
}
