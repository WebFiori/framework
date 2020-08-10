<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace webfiori\entity\sesstion;

use webfiori\entity\Util;
use webfiori\entity\File;
/**
 * Description of DefaultSesstionStorage
 *
 * @author Eng.Ibrahim
 */
class DefaultSesstionStorage implements SessionStorage {
    private $storeLoc;
    public function __construct() {
        $this->storeLoc = ROOT_DIR.DS.'app'.DS.'storage'.DS.'sesstions';
        Util::isDirectory($this->storeLoc, true);
    }
    /**
     * 
     * @param string $sesstionId
     * @return Session
     */
    public function read($sesstionId) {
        $file = new File($sesstionId, $this->storeLoc);
        if ($file->isExist()) {
            $file->read();
            $sesstionObj = unserialize($file->getRawData());
            return $sesstionObj;
        }
    }

    public function remove($sesstionId) {
        unlink($this->storeLoc.DS.$sesstionId);
    }
    /**
     * 
     * @param Session $sesstion
     */
    public function save($sesstion) {
        if ($sesstion instanceof Session) {
            $serializedSesstion = serialize($sesstion);
            $file = new File($sesstion->getId(), $this->storeLoc);
            $file->setRawData($serializedSesstion);
            $file->write(false, true);
        }
    }

}
