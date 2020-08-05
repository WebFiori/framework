<?php
namespace webfiori\entity\sesstion;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sesstion
 *
 * @author Ibrahim
 */
class Sesstion {
    private $sName;
    /**
     * The name of random function which is used in session ID generation.
     * 
     * @var string
     * 
     * @since 1.0 
     */
    private static $randFunc;
    private $sId;
    public function __construct() {
        //used to support older PHP versions which does not have 'random_int'.
        self::$randFunc = is_callable('random_int') ? 'random_int' : 'rand';
    }
    public function getName() {
        return $this->sName;
    }
    public function getId() {
        return $this->sId;
    }
    /**
     * Generate a random session ID.
     * 
     * @return string A new random session ID.
     * 
     * @since 1.6
     */
    private function _generateSessionID() {
        $date = date(DATE_ISO8601);
        $hash = hash('sha256', $date);
        $time = time() + call_user_func(self::$randFunc, 0, 100);
        $hash2 = hash('sha256',$hash.$time);

        return substr($hash2, 0, 27);
    }
    public function regenerateId() {
        $this->sId = $this->_generateSessionID();
        return $this->sId;
    }
}
