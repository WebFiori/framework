<?php
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
}
