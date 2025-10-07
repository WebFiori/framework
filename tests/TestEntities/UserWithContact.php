<?php
namespace webfiori\tests\testEntity;

use webfiori\framework\User;

/**
 * Description of UserWithContact
 *
 * @author Ibrahim
 */
class UserWithContact extends User {
    private $contactInfoArr;
    public function __construct() {
        parent::__construct();
        $this->contactInfoArr = [];
    }
    public function addContactInfo($infoName,$info) {
        $this->contactInfoArr[$infoName] = $info;
    }
    public function getContactInfo() {
        return $this->contactInfoArr;
    }
}
