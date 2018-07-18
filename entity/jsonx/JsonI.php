<?php
/**
 * An interface for the objects that can be added to an instance of <b>Json</b>.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 * @see Json
 * 
 */
interface JsonI {
    /**
     * This function must be implemented by any class that will be added as an 
     * attribute to any <b>Json</b> instance.
     * @return Json An instance of <b>Json</b>
     * @since 1.0
     */
    public function toJSON();
}
