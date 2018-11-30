<?php
namespace jsonx;
/**
 * An interface for the objects that can be added to an instance of JsonX.
 * @author Ibrahim <ibinshikh@hotmail.com>
 * @version 1.0
 * @see JsonX
 */
interface JsonI {
    /**
     * This function can be implemented by any class that will be added as an 
     * attribute to any JsonX instance. It is used to customize the generated 
     * JSON string.
     * @return JsonX An instance of JsonX.
     * @since 1.0
     */
    public function toJSON();
}
