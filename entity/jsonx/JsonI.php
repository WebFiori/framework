<?php
/**
 * An interface for the objects that can be added to an instance of <b>JsonX</b>.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 * @see JsonX
 * 
 */
interface JsonI {
    /**
     * Returns an object of type 'JsonX'.
     * This function must be implemented by any class that will be added as an 
     * attribute to any <b>JsonX</b> instance. It is used to customize the returned 
     * JSON string.
     * @return JsonX An object of type <b>JsonX</b>
     * @since 1.0
     */
    public function toJSON();
}
