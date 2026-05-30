<?php

namespace WebFiori\Framework;

/**
 * Represents a single permission (action) that can be assigned to a role.
 */
class Permission extends Privilege {
    private ?int $dbId = null;
    private ?string $description = null;

    /**
     * Creates new permission.
     *
     * @param string $name The unique identifier of the permission (e.g., 'ADD_USER').
     * @param string|null $description Optional description.
     */
    public function __construct(string $name = 'PERMISSION', ?string $description = null) {
        parent::__construct($name, $name);
        $this->description = $description;
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
