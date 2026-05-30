<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Database\Database;
use WebFiori\Framework\AccessStorage;
use WebFiori\Framework\Role;

/**
 * Database-backed access storage using webfiori/database.
 */
class DatabaseAccessStorage implements AccessStorage {
    private Database $db;

    /**
     * Creates new instance.
     *
     * @param Database $db The database instance with access tables registered.
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->db->addTableFromClass(RolesTable::class);
        $this->db->addTableFromClass(PermissionsTable::class);
        $this->db->addTableFromClass(RolePermissionsTable::class);
        $this->db->addTableFromClass(UserRolesTable::class);
    }

    public function loadRoles(): array {
        $rolesResult = $this->db->table('roles')->select()->execute();
        $roles = [];

        foreach ($rolesResult->getRows() as $row) {
            $role = new Role($row['name']);
            $role->setDbId($row['id']);

            if (isset($row['description'])) {
                $role->setDescription($row['description']);
            }

            // Load parent
            if ($row['parent_role_id'] !== null) {
                $parentResult = $this->db->table('roles')->select()
                    ->where('id', $row['parent_role_id'])->execute();

                if ($parentResult->getRowsCount() > 0) {
                    $role->inherits($parentResult->getRows()[0]['name']);
                }
            }

            // Load permissions for this role
            $permsResult = $this->db->table('role_permissions')->select()
                ->where('role-id', $row['id'])->execute();

            foreach ($permsResult->getRows() as $rp) {
                $permResult = $this->db->table('permissions')->select()
                    ->where('id', $rp['permission_id'])->execute();

                if ($permResult->getRowsCount() > 0) {
                    $role->addPermission($permResult->getRows()[0]['name']);
                }
            }

            $roles[] = $role;
        }

        return $roles;
    }

    public function loadUserRoles($userId): array {
        $result = $this->db->table('user_roles')->select()
            ->where('user-id', $userId)->execute();
        $roleNames = [];

        foreach ($result->getRows() as $row) {
            $roleResult = $this->db->table('roles')->select()
                ->where('id', $row['role_id'])->execute();

            if ($roleResult->getRowsCount() > 0) {
                $roleNames[] = $roleResult->getRows()[0]['name'];
            }
        }

        return $roleNames;
    }

    public function saveRole(Role $role): void {
        // Check if role exists
        $existing = $this->db->table('roles')->select()
            ->where('name', $role->getName())->execute();

        $parentId = null;

        if ($role->getParentRoleName() !== null) {
            $parentResult = $this->db->table('roles')->select()
                ->where('name', $role->getParentRoleName())->execute();

            if ($parentResult->getRowsCount() > 0) {
                $parentId = $parentResult->getRows()[0]['id'];
            }
        }

        if ($existing->getRowsCount() === 0) {
            $this->db->table('roles')->insert([
                'name' => $role->getName(),
                'parent-role-id' => $parentId,
                'description' => $role->getDescription(),
            ])->execute();
        } else {
            $this->db->table('roles')->update([
                'parent-role-id' => $parentId,
                'description' => $role->getDescription(),
            ])->where('name', $role->getName())->execute();
        }

        // Get role ID
        $roleRow = $this->db->table('roles')->select()
            ->where('name', $role->getName())->execute()->getRows()[0];
        $roleId = $roleRow['id'];

        // Sync permissions
        $this->db->table('role_permissions')->delete()
            ->where('role-id', $roleId)->execute();

        foreach ($role->privileges() as $priv) {
            $permName = $priv->getID();

            // Ensure permission exists
            $permExists = $this->db->table('permissions')->select()
                ->where('name', $permName)->execute();

            if ($permExists->getRowsCount() === 0) {
                $this->db->table('permissions')->insert([
                    'name' => $permName,
                ])->execute();
            }

            $permRow = $this->db->table('permissions')->select()
                ->where('name', $permName)->execute()->getRows()[0];

            $this->db->table('role_permissions')->insert([
                'role-id' => $roleId,
                'permission-id' => $permRow['id'],
            ])->execute();
        }
    }

    public function removeRole(string $name): void {
        $this->db->table('roles')->delete()
            ->where('name', $name)->execute();
    }

    public function assignRoleToUser($userId, string $roleName): void {
        $roleResult = $this->db->table('roles')->select()
            ->where('name', $roleName)->execute();

        if ($roleResult->getRowsCount() > 0) {
            $roleId = $roleResult->getRows()[0]['id'];

            // Check if already assigned
            $existing = $this->db->table('user_roles')->select()
                ->where('user-id', $userId)
                ->andWhere('role-id', $roleId)->execute();

            if ($existing->getRowsCount() === 0) {
                $this->db->table('user_roles')->insert([
                    'user-id' => $userId,
                    'role-id' => $roleId,
                ])->execute();
            }
        }
    }

    public function removeRoleFromUser($userId, string $roleName): void {
        $roleResult = $this->db->table('roles')->select()
            ->where('name', $roleName)->execute();

        if ($roleResult->getRowsCount() > 0) {
            $roleId = $roleResult->getRows()[0]['id'];
            $this->db->table('user_roles')->delete()
                ->where('user-id', $userId)
                ->andWhere('role-id', $roleId)->execute();
        }
    }
    /**
     * Returns the database instance.
     *
     * @return Database
     */
    public function getDatabase(): Database {
        return $this->db;
    }
}
