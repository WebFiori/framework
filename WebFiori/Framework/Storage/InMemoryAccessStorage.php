<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Framework\AccessStorage;
use WebFiori\Framework\Role;

/**
 * In-memory access storage. Data is lost when the process ends.
 */
class InMemoryAccessStorage implements AccessStorage {
    private array $roles = [];
    private array $userRoles = [];

    public function loadRoles(): array {
        return array_values($this->roles);
    }

    public function loadUserRoles($userId): array {
        return $this->userRoles[$userId] ?? [];
    }

    public function saveRole(Role $role): void {
        $this->roles[$role->getName()] = $role;
    }

    public function removeRole(string $name): void {
        unset($this->roles[$name]);
    }

    public function assignRoleToUser($userId, string $roleName): void {
        if (!isset($this->userRoles[$userId])) {
            $this->userRoles[$userId] = [];
        }

        if (!in_array($roleName, $this->userRoles[$userId])) {
            $this->userRoles[$userId][] = $roleName;
        }
    }

    public function removeRoleFromUser($userId, string $roleName): void {
        if (isset($this->userRoles[$userId])) {
            $this->userRoles[$userId] = array_values(
                array_filter($this->userRoles[$userId], fn($r) => $r !== $roleName)
            );
        }
    }
}
