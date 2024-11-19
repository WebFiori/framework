<?php
namespace app\database\super;

use app\entity\super\SuperUser;
use webfiori\framework\DB;
/**
 * A class which is used to perform operations on the table 'super_users'
 */
class SuperUserDB extends DB {
    private static $instance;
    /**
     * Returns an instance of the class.
     * 
     * Calling this method multiple times will return same instance.
     * 
     * @return SuperUserDB An instance of the class.
     */
    public static function get() : SuperUserDB {

        if (self::$instance === null) {
            self::$instance = new SuperUserDB();
        }

        return self::$instance;
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        //TODO: Specify the name of database connection to use in performing operations.
        parent::__construct('');
        $this->register('app\\database\\super');
    }
    /**
     * Adds new record to the table 'super_users'.
     *
     * @param SuperUser $entity An object that holds record information.
     */
    public function addSuperUser(SuperUser $entity) {
        $this->table('super_users')->insert([
            'first-name' => $entity->getFirstName(),
            'is-happy' => $entity->getIsHappy(),
        ])->execute();
    }
    /**
     * Deletes a record from the table 'super_users'.
     *
     * @param SuperUser $entity An object that holds record information.
     */
    public function deleteSuperUser(SuperUser $entity) {
        $this->table('super_users')
                ->delete()
                ->where('id', $entity->getId())
                ->execute();
    }
    /**
     * Returns the information of a record from the table 'super_users'.
     *
     * @return SuperUser|null If a record with given information exist,
     * The method will return an object which holds all record information.
     * Other than that, null is returned.
     */
    public function getSuperUser(int $id) {
        $mappedRecords = $this->table('super_users')
                ->select()
                ->where('id', $id)
                ->execute()
                ->map(function (array $record) {
                    return SuperUser::map($record);
                });
        if ($mappedRecords->getRowsCount() == 1) {
            return $mappedRecords->getRows()[0];
        }
    }
    /**
     * Returns all the records from the table 'super_users'.
     *
     * @param int $pageNum The number of page to fetch. Default is 0.
     *
     * @param int $pageSize Number of records per page. Default is 10.
     *
     * @return array An array that holds all table records as objects
     */
    public function getSuperUsers(int $pageNum = 0, int $pageSize = 10) : array {
        return $this->table('super_users')
                ->select()
                ->page($pageNum, $pageSize)
                ->orderBy(["id"])
                ->execute()
                ->map(function (array $record) {
                    return SuperUser::map($record);
                })->toArray();
    }
    /**
     * Returns number of records on the table 'super_users'.
     *
     * The main use of this method is to compute number of pages.
     *
     * @return int Number of records on the table 'super_users'.
     */
    public function getSuperUsersCount() : int {
        return $this->table('super_users')
                ->selectCount()
                ->execute()
                ->getRows()[0]['count'];
    }
    /**
     * Updates a record on the table 'super_users'.
     *
     * @param SuperUser $entity An object that holds updated record information.
     */
    public function updateSuperUser(SuperUser $entity) {
        $this->table('super_users')
            ->update([
                'first-name' => $entity->getFirstName(),
                'is-happy' => $entity->getIsHappy(),
            ])
            ->where('id', $entity->getId())
            ->execute();
    }
    /**
     * Updates the value of the column 'first_name' on the table 'super_users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateFirstName(int $id, string $newVal) {
        $this->table('super_users')->update([
                'first-name' => $newVal
            ])
            ->where('id', $id)
            ->execute();
    }
    /**
     * Updates the value of the column 'is_happy' on the table 'super_users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param bool $newVal The new value for the column.
     */
    public function updateIsHappy(int $id, bool $newVal) {
        $this->table('super_users')->update([
                'is-happy' => $newVal
            ])
            ->where('id', $id)
            ->execute();
    }
}
