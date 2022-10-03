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
namespace webfiori\framework\session;

/**
 * An interface which is used to tell if an entity can represents session
 * user or not.
 * 
 * @author Ibrahim
 */
interface SessionUser {
    /**
     * Returns an integer which represents the unique identifier of the user.
     * 
     * @return int The unique identifier of the user.
     */
    public function getId() : int;
    /**
     * Checks if a user has privilege or not given its ID.
     * 
     * @param string $privilegeId The ID of the privilege.
     * 
     * @return bool The method should be implemented in a way that it returns
     * true if the user has specified privilege. False if not.
     * 
     */
    public function hasPrivilege(string $pr) : bool;
}
