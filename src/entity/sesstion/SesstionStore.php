<?php
namespace webfiori\entity\sesstion;

use webfiori\entity\sesstion\Sesstion;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SesstionStore
 *
 * @author Ibrahim
 */
interface SesstionStore {
    /**
     * 
     * @param Sesstion $sesstion
     */
    public function save($sesstion);
    public function read($sesstionId);
    public function destroy($sesstionId);
}
