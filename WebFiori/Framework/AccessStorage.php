<?php

namespace WebFiori\Framework;

/**
 * Interface for persisting and loading roles, permissions, and user-role assignments.
 */
interface AccessStorage {
    /**
     * Load all roles with their permissions.
     *
     * @return Role[]
     */
    public function loadRoles(): array;
    /**
     * Load role names assigned to a specific user.
     *
     * @param int|string $userId
     *
     * @return string[] Array of role names.
     */
    public function loadUserRoles($userId): array;
    /**
     * Persist a role (create or update).
     *
     * @param Role $role The role to save.
     */
    public function saveRole(Role $role): void;
    /**
     * Remove a role by name.
     *
     * @param string $name The role name.
     */
    public function removeRole(string $name): void;
    /**
     * Assign a role to a user.
     *
     * @param int|string $userId The user identifier.
     * @param string $roleName The role name.
     */
    public function assignRoleToUser($userId, string $roleName): void;
    /**
     * Remove a role from a user.
     *
     * @param int|string $userId The user identifier.
     * @param string $roleName The role name.
     */
    public function removeRoleFromUser($userId, string $roleName): void;
}
