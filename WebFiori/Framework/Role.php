<?php

namespace WebFiori\Framework;

/**
 * Represents a role that contains permissions and can inherit from a parent role.
 */
class Role extends PrivilegesGroup {
    private ?int $dbId = null;
    private ?string $parentRoleName = null;
    private ?string $description = null;

    /**
     * Creates new role.
     *
     * @param string $name The unique name of the role.
     */
    public function __construct(string $name = 'ROLE') {
        parent::__construct($name, $name);
    }
    /**
     * Set this role to inherit all permissions from another role.
     *
     * @param string $parentRoleName The name of the parent role.
     *
     * @return self
     */
    public function inherits(string $parentRoleName): self {
        $this->parentRoleName = $parentRoleName;

        return $this;
    }
    /**
     * Returns the parent role name.
     *
     * @return string|null
     */
    public function getParentRoleName(): ?string {
        return $this->parentRoleName;
    }
    /**
     * Check if this role has a permission, including inherited permissions.
     *
     * @param string $permissionId The permission to check.
     * @param AccessManager $manager The manager to resolve parent roles.
     *
     * @return bool
     */
    public function hasPermission(string $permissionId, AccessManager $manager): bool {
        $perm = new Permission($permissionId);

        if (parent::hasPrivilege($perm)) {
            return true;
        }

        $wildcard = new Permission('*');

        if (parent::hasPrivilege($wildcard)) {
            return true;
        }

        if ($this->parentRoleName !== null) {
            $parent = $manager->getRole($this->parentRoleName);

            if ($parent !== null) {
                return $parent->hasPermission($permissionId, $manager);
            }
        }

        return false;
    }
    /**
     * Add a permission to this role.
     *
     * @param string $permissionId The permission identifier.
     *
     * @return self
     */
    public function addPermission(string $permissionId): self {
        $this->addPrivilege(new Permission($permissionId));

        return $this;
    }
    /**
     * Returns the database ID.
     */
    public function getDbId(): ?int {
        return $this->dbId;
    }
    /**
     * Sets the database ID.
     */
    public function setDbId(int $id): void {
        $this->dbId = $id;
    }
    /**
     * Returns the description.
     */
    public function getDescription(): ?string {
        return $this->description;
    }
    /**
     * Sets the description.
     */
    public function setDescription(?string $desc): void {
        $this->description = $desc;
    }
}
