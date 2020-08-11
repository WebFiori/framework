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
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class DefaultSessionStorage implements SessionStorage {
    private $storeLoc;
    public function __construct() {
        $this->storeLoc = ROOT_DIR.DS.'app'.DS.'storage'.DS.'sessions';
        Util::isDirectory($this->storeLoc, true);
    }
    /**
     * 
     * @param string $sessionId
     * @return Session
     */
    public function read($sessionId) {
        $file = new File($sessionId, $this->storeLoc);
        if ($file->isExist()) {
            $file->read();
            $sesstion = new Session([
                'session-id' => $sessionId
            ]);
            $sesstion->unserialize($file->getRawData());
            
            if ($sesstion->getId() == $sessionId) {
                return $sesstion;
            }
        }
    }

    public function remove($sessionId) {
        unlink($this->storeLoc.DS.$sessionId);
    }
    /**
     * 
     * @param Session $session
     */
    public function save($session) {
        if ($session instanceof Session) {
            $serializedSesstion = $session->serialize();
            $file = new File($session->getId(), $this->storeLoc);
            $file->setRawData($serializedSesstion);
            $file->write(false, true);
        }
    }

}
