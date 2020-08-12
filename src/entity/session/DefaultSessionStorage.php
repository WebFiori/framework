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
 * The default sessions storage engine.
 *
 * This storage engine will store session state as a file in the folder 
 * 'app/storage/sessions'. The name of the file that contains session state 
 * will be the ID of the session.
 * 
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
class DefaultSessionStorage implements SessionStorage {
    private $storeLoc;
    /**
     * Creates new instance of the class.
     * 
     * @since 1.0
     */
    public function __construct() {
        $this->storeLoc = ROOT_DIR.DS.'app'.DS.'storage'.DS.'sessions';
        Util::isDirectory($this->storeLoc, true);
    }
    /**
     * Reads a session from session file.
     * 
     * @param string $sessionId The ID of the session.
     * 
     * @return Session|null If the method successfully accessed session state, 
     * the method will return an object of type 'Session'. Other than that, 
     * the method will return null.
     * 
     * @since 1.0
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
    /**
     * Removes session file.
     * 
     * @param string $sessionId The ID of the session.
     * 
     * @since 1.0
     */
    public function remove($sessionId) {
        unlink($this->storeLoc.DS.$sessionId);
    }
    /**
     * Stores session state to a file.
     * 
     * @param Session $session The session that will be stored.
     * 
     * @since 1.0
     */
    public function save($session) {
        if ($session instanceof Session) {
            $serializedSesstion = $session->serialize();
            $file = new File($session->getId(), $this->storeLoc);
            $file->setRawData($serializedSesstion);
            $file->write(false, true);
        }
    }
    /**
     * Removes all inactive sessions.
     * 
     * This method will check if the constant 'SESSION_GC' is exist and its value 
     * is valid. If exist and valid, it will be used as reference for removing 
     * old sessions. If it does not exist, the method will remove any inactive 
     * session which is older than 30 days.
     * 
     * @since 1.0
     */
    public function gc() {
        $sessionsFiles = array_diff(scandir($this->storeLoc), ['.','..']);
        
        if (defined('SESSION_GC') && SESSION_GC > 0) {
            $olderThan = SESSION_GC;
        } else {
            //Clear any sesstion which is older than 30 days
            $olderThan = time() - 60*60*24*30;
        }
        
        foreach ($sessionsFiles as $file) {
            $fileObj = new File($this->storeLoc.DS.$file);
            if ($fileObj->getLastModified() < $olderThan) {
                $fileObj->remove();
            }
        }
    }

}
