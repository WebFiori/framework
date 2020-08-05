<?php

namespace webfiori\entity\sesstion;

/**
 * Description of SesstionsManager
 *
 * @author Ibrahim
 */
class SesstionsManager {
    private $sesstionsArr;
    
    private static $inst;
    /**
     * 
     * @return SesstionsManager
     */
    private static function get() {
        if (self::$inst === null) {
            self::$inst = new SesstionsManager();
        }
        return self::$inst;
    }
    private function __construct() {
        $this->sesstionsArr = [];
    }
    public static function hasSesstion($sIdOrName) {
        foreach (self::get()->sesstionsArr as $sesstionObj) {
            $sesstionObj instanceof Sesstion;
            if ($sesstionObj->getId() == $sIdOrName || $sesstionObj->getName() == $sIdOrName) {
                return true;
            }
        }
        return false;
    }
    public static function startNew($sesstionName) {
        if (!self::hasSesstion($sesstionName)) {
            
        }
    }
}
