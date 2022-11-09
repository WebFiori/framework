<?php
namespace app\entity;
/**
 * Description of WithException
 *
 * @author i.binalshikh
 */
class WithException {
    public function __construct() {
        throw new Exception('Only for testing.');
    }
}
