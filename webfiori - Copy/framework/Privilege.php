<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace webfiori\framework;

use WebFiori\Json\Json;
use WebFiori\Json\JsonI;
/**
 * A class that represents a privilege.
 *
 * @author Ibrahim
 * @version 1.0.1
 */
class Privilege implements JsonI {
    /**
     * The ID of the privilege.
     *
     * @var string
     *
     * @since 1.0
     */
    private $code;
    /**
     * The name of the privilege.
     *
     * @var string
     *
     * @since 1.0
     */
    private $name;
    /**
     * Creates new instance of the class
     *
     * @param string $id The unique identifier of the privilege. Default is
     * 'PR'.
     *
     * @param string $name The name of the privilege. It is provided only
     * in case of displaying privilege in some UI view. Default is empty string.
     *
     * @since 1.0
     */
    public function __construct(string $id = 'PR', string $name = '') {
        if (!$this->setID($id)) {
            $this->setID('PR');
        }

        if (!$this->setName($name)) {
            $this->setName('PR_NAME');
        }
    }
    /**
     * Returns the ID of the privilege.
     *
     * @return string The ID of the privilege. If the ID was not set,
     * the method will return 'PR'.
     *
     * @since 1.0
     */
    public function getID() : string {
        return $this->code;
    }
    /**
     * Returns the name of the privilege.
     *
     * @return string The name of the privilege. If the name was not updated,
     * the method will return 'PR_NAME'.
     *
     * @since 1.0
     */
    public function getName() : string {
        return $this->name;
    }
    /**
     * Sets the ID of the privilege
     *
     * @param string $code The ID of the privilege. Only set if the given string
     * is not empty. In addition, The ID of the privilege can only consist
     * of the following characters: [A-Z], [a-z], [0-9] and underscore.
     *
     * @return boolean If the ID of the privilege is updated, the method will return
     * true. If not updated, it will return false.
     *
     * @since 1.0
     */
    public function setID(string $code): bool {
        $xid = trim($code);
        $len = strlen($xid);

        for ($x = 0 ; $x < $len ; $x++) {
            $ch = $xid[$x];

            if (!($ch == '_' || ($ch >= 'a' && $ch <= 'z') || ($ch >= 'A' && $ch <= 'Z') || ($ch >= '0' && $ch <= '9'))) {
                return false;
            }
        }
        $this->code = $xid;

        return true;
    }
    /**
     * Sets the name of the privilege.
     *
     * @param string $name The name of the privilege. It is only set when
     * the given string is not empty.
     *
     * @return boolean If the privilege name was set, the method will return
     * true. If not set, the method will return false.
     *
     * @since 1.0
     */
    public function setName(string $name): bool {
        $trimmed = trim($name);

        if (strlen($trimmed) > 0) {
            $this->name = $trimmed;

            return true;
        }

        return false;
    }
    /**
     * Returns an object of type Json that contains group info as JSON string.
     *
     * The generated JSON string will have the following format:
     * <p>
     * {<br/>
     * &nbsp;&nbsp;"privilegeId":"",<br/>
     * &nbsp;&nbsp;"name":"",<br/>
     * }
     * </p>
     */
    public function toJSON() : Json {
        $j = new Json();
        $j->add('privilegeId', $this->getID());
        $j->add('name', $this->getName());

        return $j;
    }
}
