<?php

namespace WebFiori\Framework;

use WebFiori\Framework\Storage\InMemoryAccessStorage;

/**
 * Core RBAC/ABAC manager. Manages roles, permissions, policies, and authorization checks.
 */
class AccessManager {
    private array $roles = [];
    private array $policies = [];
    private array $userRoles = [];
    private AccessStorage $storage;

    /**
     * Creates new instance.
     *
     * @param AccessStorage|null $storage Storage backend. Defaults to InMemoryAccessStorage.
     */
    public function __construct(?AccessStorage $storage = null) {
        $this->storage = $storage ?? new InMemoryAccessStorage();
    }
    /**
     * Create or get a role by name.
     *
     * @param string $name The role name.
     * @param array $permissions Optional permissions to add.
     *
     * @return Role
     */
    public function role(string $name, array $permissions = []): Role {
        if (!isset($this->roles[$name])) {
            $this->roles[$name] = new Role($name);
        }

        foreach ($permissions as $p) {
            $this->roles[$name]->addPermission($p);
        }

        return $this->roles[$name];
    }
    /**
     * Get a role by name.
     *
     * @param string $name The role name.
     *
     * @return Role|null
     */
    public function getRole(string $name): ?Role {
        return $this->roles[$name] ?? null;
    }
    /**
     * Returns all registered roles.
     *
     * @return Role[]
     */
    public function getRoles(): array {
        return $this->roles;
    }
    /**
     * Remove a role.
     *
     * @param string $name The role name.
     */
    public function removeRole(string $name): void {
        unset($this->roles[$name]);
    }
    /**
     * Register a policy (ABAC condition) for a permission.
     *
     * Accepts either a callable or a class with evaluate() method (duck typing).
     *
     * @param string $permission The permission this policy applies to.
     * @param callable|object $condition A callable or policy object with evaluate().
     */
    public function policy(string $permission, callable|object $condition): void {
        $this->policies[$permission] = $condition;
    }
    /**
     * Register a policy object. Permission is inferred from getPermission() method.
     *
     * @param object $policyObj An object with getPermission() and evaluate() methods.
     */
    public function registerPolicy(object $policyObj): void {
        if (method_exists($policyObj, 'getPermission') && method_exists($policyObj, 'evaluate')) {
            $this->policies[$policyObj->getPermission()] = $policyObj;
        }
    }
    /**
     * Get the policy for a permission.
     *
     * @param string $permission The permission name.
     *
     * @return callable|object|null
     */
    public function getPolicy(string $permission) {
        return $this->policies[$permission] ?? null;
    }
    /**
     * Assign a role to a user.
     *
     * @param int|string $userId The user identifier.
     * @param string $roleName The role name.
     */
    public function assignRoleToUser($userId, string $roleName): void {
        if (!isset($this->userRoles[$userId])) {
            $this->userRoles[$userId] = [];
        }

        if (!in_array($roleName, $this->userRoles[$userId])) {
            $this->userRoles[$userId][] = $roleName;
        }
    }
    /**
     * Remove a role from a user.
     *
     * @param int|string $userId The user identifier.
     * @param string $roleName The role name.
     */
    public function removeRoleFromUser($userId, string $roleName): void {
        if (isset($this->userRoles[$userId])) {
            $this->userRoles[$userId] = array_values(
                array_filter($this->userRoles[$userId], fn($r) => $r !== $roleName)
            );
        }
    }
    /**
     * Get role names assigned to a user.
     *
     * @param int|string $userId The user identifier.
     *
     * @return string[]
     */
    public function getUserRoles($userId): array {
        return $this->userRoles[$userId] ?? [];
    }
    /**
     * Check if a user has a permission.
     *
     * 1. Find user's roles
     * 2. Check if any role has the permission (walks inheritance)
     * 3. If a policy exists, run it
     * 4. Return true only if RBAC passes AND policy passes
     *
     * @param object|int|string $user The user (object with getId()) or user ID.
     * @param string $permission The permission to check.
     * @param object|null $resource Optional resource for ABAC policy evaluation.
     *
     * @return bool
     */
    public function can($user, string $permission, ?object $resource = null): bool {
        $userId = is_object($user) && method_exists($user, 'getId') ? $user->getId() : $user;
        $roles = $this->getUserRoles($userId);

        if (empty($roles) && is_object($user) && method_exists($user, 'getRoles')) {
            $roles = $user->getRoles();
        }

        $hasPermission = false;

        foreach ($roles as $roleName) {
            $role = $this->getRole($roleName);

            if ($role !== null && $role->hasPermission($permission, $this)) {
                $hasPermission = true;

                break;
            }
        }

        if (!$hasPermission) {
            return false;
        }

        if (isset($this->policies[$permission])) {
            $policy = $this->policies[$permission];

            if (is_callable($policy)) {
                return $policy($user, $resource);
            }

            if (is_object($policy) && method_exists($policy, 'evaluate')) {
                return $policy->evaluate($user, $resource);
            }
        }

        return true;
    }
    /**
     * Returns the storage backend.
     */
    public function getStorage(): AccessStorage {
        return $this->storage;
    }
    /**
     * Sets the storage backend.
     */
    public function setStorage(AccessStorage $storage): void {
        $this->storage = $storage;
    }
    /**
     * Load roles from storage into memory.
     */
    public function loadFromStorage(): void {
        $this->roles = [];

        foreach ($this->storage->loadRoles() as $role) {
            $this->roles[$role->getName()] = $role;
        }
    }
    /**
     * Load a user's roles from storage into memory.
     *
     * @param int|string $userId The user identifier.
     */
    public function loadUserRolesFromStorage($userId): void {
        $this->userRoles[$userId] = $this->storage->loadUserRoles($userId);
    }
    /**
     * Save all roles to storage.
     */
    public function saveToStorage(): void {
        foreach ($this->roles as $role) {
            $this->storage->saveRole($role);
        }
    }
    /**
     * Reset all roles, policies, and user assignments.
     */
    public function reset(): void {
        $this->roles = [];
        $this->policies = [];
        $this->userRoles = [];
    }
}
