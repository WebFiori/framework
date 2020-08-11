<?php
namespace webfiori\entity\sesstion;

use webfiori\entity\sesstion\Session;
/**
 * Description of SesstionStore
 *
 * @author Ibrahim
 * 
 * @since 1.1.0
 * 
 * @version 1.0
 */
interface SessionStorage {
    /**
     * 
     * @param Session $sesstion
     * 
     * @since 1.0
     */
    public function save($sesstion);
    /**
     * 
     * @param type $sesstionId
     * 
     * @return Sesstion Description
     * 
     * @since 1.0
     */
    public function read($sesstionId);
    /**
     * 
     * @param type $sesstionId
     * 
     * @since 1.0
     */
    public function remove($sesstionId); 
}
