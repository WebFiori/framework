<?php

namespace webfiori\entity\sesstion;

/**
 * Description of SesstionsManager
 *
 * @author Ibrahim
 */
class SesstionsManager {
    private $sesstionsArr;
    private $activeSesstion;
    /**
     *
     * @var SesstionStorage 
     */
    private $sesstionStorage;
    public static function setStorage($storage) {
        if ($storage instanceof SesstionStorage) {
            self::get()->sesstionStorage = $storage;
        }
    }
    /**
     * 
     * @return SesstionStorage
     */
    public static function getStorage() {
        return self::get()->sesstionStorage;
    }
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
    /**
     * 
     * @return Sesstion|null
     * 
     * @since 1.0
     */
    public static function getActiveSesstion() {
        
        if ($this->activeSesstion !== null) {
            return $this->activeSesstion;
        }
        
        foreach (self::get()->sesstionsArr as $sesstion) {
            $sesstion instanceof Sesstion;
            $status = $sesstion->getStatus();
            if ($status == Sesstion::STATUS_NEW || $status == Sesstion::STATUS_RESUMED) {
                $this->activeSesstion = $sesstion;
                return $sesstion;
            }
        }
    }
    private function __construct() {
        $this->sesstionsArr = [];
        $this->sesstionStorage = new DefaultSesstionStorage();
    }
    /**
     * 
     * @param type $sIdOrName
     * 
     * @return boolean
     * 
     * @since 1.0
     */
    public static function hasSesstion($sIdOrName) {
        
        foreach (self::get()->sesstionsArr as $sesstionObj) {
            
            if ($sesstionObj->getId() == $sIdOrName || $sesstionObj->getName() == $sIdOrName) {
                return true;
            }
        }
        $sesstion = self::getStorage()->read($sIdOrName);
        
        if ($sesstion instanceof Sesstion) {
            self::get()->sesstionsArr[] = $sesstion;
            return true;
        }
        
        return false;
    }
    public static function start($sesstionName) {
        
        if (!self::hasSesstion($sesstionName)) {
            self::get()->_pauseSesstions();
            $s = new Sesstion();
            $s->start();
            self::get()->sesstionsArr[] = $s;
        } else {
            self::get()->_pauseSesstions();
            foreach (self::get()->sesstionsArr as $sesstionObj) {
                
                if ($sesstionObj->getName() == $sesstionName) {
                    $sesstionObj->resume();
                }
            }
        }
    }
    private function _pauseSesstions() {
        $this->activeSesstion = null;
        
        foreach ($this->sesstionsArr as $sesstion) {
            $sesstion->pause();
        }
    }
    public static function validateStorage() {
        foreach (self::get()->sesstionsArr as $sesstion) {
            $status = $sesstion->getStatus();
            if ($status == Sesstion::STATUS_NEW ||
                $status == Sesstion::STATUS_PAUSED ||  
                $status == Sesstion::STATUS_RESUMED){
                self::getStorage()->save($sesstion);
            } else if ($status == Sesstion::STATUS_KILLED) {
                self::getStorage()->remove($sesstion->getId());
            }
        }
        
    }
}
