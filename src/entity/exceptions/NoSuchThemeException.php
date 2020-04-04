<?php

namespace webfiori\entity\exceptions;

use Exception;
/**
 * An exception which is thrown to indicate that a theme was 
 * not found when trying to load it.
 *
 * @author Ibrahim
 * @version 1.0
 */
class NoSuchThemeException extends Exception{
    /**
     * Creates new instance of the class.
     * @param string $message Exception message.
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}
