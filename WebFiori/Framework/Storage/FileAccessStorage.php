<?php

namespace WebFiori\Framework\Storage;

use WebFiori\Framework\AccessStorage;
use WebFiori\Framework\Role;
use WebFiori\Framework\Permission;

/**
 * File-based access storage. Persists roles and user-role assignments as JSON.
 */
class FileAccessStorage implements AccessStorage {
    private string $filePath;

    /**
     * Creates new instance.
     *
     * @param string $filePath Path to the JSON file for storage.
     */
    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    public function loadRoles(): array {
        $data = $this->readFile();
        $roles = [];

        foreach ($data['roles'] ?? [] as $name => $info) {
            $role = new Role($name);

            if (isset($info['parent'])) {
                $role->inherits($info['parent']);
            }

            if (isset($info['description'])) {
                $role->setDescription($info['description']);
            }

            foreach ($info['permissions'] ?? [] as $perm) {
                $role->addPermission($perm);
            }
            $roles[] = $role;
        }

        return $roles;
    }

    public function loadUserRoles($userId): array {
        $data = $this->readFile();

        return $data['userRoles'][$userId] ?? [];
    }

    public function saveRole(Role $role): void {
        $data = $this->readFile();
        $permissions = [];

        foreach ($role->privileges() as $priv) {
            $permissions[] = $priv->getID();
        }

        $data['roles'][$role->getName()] = [
            'permissions' => $permissions,
            'parent' => $role->getParentRoleName(),
            'description' => $role->getDescription(),
        ];
        $this->writeFile($data);
    }

    public function removeRole(string $name): void {
        $data = $this->readFile();
        unset($data['roles'][$name]);
        $this->writeFile($data);
    }

    public function assignRoleToUser($userId, string $roleName): void {
        $data = $this->readFile();

        if (!isset($data['userRoles'][$userId])) {
            $data['userRoles'][$userId] = [];
        }

        if (!in_array($roleName, $data['userRoles'][$userId])) {
            $data['userRoles'][$userId][] = $roleName;
        }
        $this->writeFile($data);
    }

    public function removeRoleFromUser($userId, string $roleName): void {
        $data = $this->readFile();

        if (isset($data['userRoles'][$userId])) {
            $data['userRoles'][$userId] = array_values(
                array_filter($data['userRoles'][$userId], fn($r) => $r !== $roleName)
            );
        }
        $this->writeFile($data);
    }
    /**
     * Returns the file path.
     *
     * @return string
     */
    public function getFilePath(): string {
        return $this->filePath;
    }

    private function readFile(): array {
        if (!file_exists($this->filePath)) {
            return ['roles' => [], 'userRoles' => []];
        }

        $content = file_get_contents($this->filePath);

        return json_decode($content, true) ?? ['roles' => [], 'userRoles' => []];
    }

    private function writeFile(array $data): void {
        $dir = dirname($this->filePath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }
}
