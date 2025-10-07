<?php
namespace App\Entity;

/**
 * Description of WithException
 *
 * @author Ibrahim
 */
class WithException {
    public function __construct() {
        throw new Exception('Only for testing.');
    }
}
