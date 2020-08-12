<?php
namespace webfiori\entity\sesstion;

use webfiori\entity\sesstion\Session;
/**
 * An interface which can be used to implement different types of sessions storage.
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
interface SessionStorage {
    /**
     * Store session state.
     * 
     * This method must store serialized session. The developer can use the method 
     * <code>Session::serialize()</code> to serialize any session.
     * 
     * @param Session $sesstion The session that will be stored.
     * 
     * @since 1.0
     */
    public function save($sesstion);
    /**
     * Reads session state.
     * 
     * This method must be implemented in a way that it returns an object of type 
     * Session when session state is loaded. Note that the stored session 
     * will be serialized. To unserialize the session, the developer can use 
     * the method <code>Sestion::unserialize()</code>
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @return Session|null If the session state was restored, the method must 
     * return an object of type <code>Session</code>. Other than that, the method 
     * should return null.
     * 
     * @since 1.0
     */
    public function read($sesstionId);
    /**
     * Kill a session and remove its state from the storage.
     * 
     * @param string $sesstionId The unique identifier of the session.
     * 
     * @since 1.0
     */
    public function remove($sesstionId);
    /**
     * Removes all inactive sessions from the storage.
     * 
     * @since 1.0
     */
    public function gc();
}
