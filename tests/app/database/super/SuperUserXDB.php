<?php
namespace app\database\super;

use app\entity\super\SuperUserX;
use webfiori\framework\DB;
/**
 * A class which is used to perform operations on the table 'super_users'
 */
class SuperUserXDB extends DB {
    private static $instance;
    /**
     * Returns an instance of the class.
     * 
     * Calling this method multiple times will return same instance.
     * 
     * @return SuperUserXDB An instance of the class.
     */
    public static function get() : SuperUserXDB {

        if (self::$instance === null) {
            self::$instance = new SuperUserXDB();
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
     * @param SuperUserX $entity An object that holds record information.
     */
    public function addSuperUserX(SuperUserX $entity) {
        $this->table('super_users')->insert([
            'id' => $entity->getId(),
            'first-name' => $entity->getFirstName(),
            'is-happy' => $entity->getIsHappy(),
        ])->execute();
    }
    /**
     * Deletes a record from the table 'super_users'.
     *
     * @param SuperUserX $entity An object that holds record information.
     */
    public function deleteSuperUserX(SuperUserX $entity) {
        $this->table('super_users')
                ->delete();
            //TODO: Specify delete record condition(s).
    }
    /**
     * Returns the information of a record from the table 'super_users'.
     *
     * @return SuperUserX|null If a record with given information exist,
     * The method will return an object which holds all record information.
     * Other than that, null is returned.
     */
    public function getSuperUserX() {
        $mappedRecords = $this->table('super_users')
                ->select()
                //TODO: Specify select condition for retrieving one record.
                ->execute()
                ->map(function (array $record) {
                    return SuperUserX::map($record);
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
    public function getSuperUserXs(int $pageNum = 0, int $pageSize = 10) : array {
        return $this->table('super_users')
                ->select()
                ->page($pageNum, $pageSize)
                ->orderBy(["id"])
                ->execute()
                ->map(function (array $record) {
                    return SuperUserX::map($record);
                })->toArray();
    }
    /**
     * Returns number of records on the table 'super_users'.
     *
     * The main use of this method is to compute number of pages.
     *
     * @return int Number of records on the table 'super_users'.
     */
    public function getSuperUserXsCount() : int {
        return $this->table('super_users')
                ->selectCount()
                ->execute()
                ->getRows()[0]['count'];
    }
    /**
     * Updates a record on the table 'super_users'.
     *
     * @param SuperUserX $entity An object that holds updated record information.
     */
    public function updateSuperUserX(SuperUserX $entity) {
        $this->table('super_users')
            ->update([
                'id' => $entity->getId(),
                'first-name' => $entity->getFirstName(),
                'is-happy' => $entity->getIsHappy(),
            ]);
            //TODO: Specify update record condition(s).
    }
    /**
     * Updates the value of the column 'id' on the table 'super_users'.
     *
     * @param int $newVal The new value for the column.
     */
    public function updateId(int $newVal) {
        $this->table('super_users')->update([
                'id' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'id'
    }
    /**
     * Updates the value of the column 'first_name' on the table 'super_users'.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateFirstName(string $newVal) {
        $this->table('super_users')->update([
                'first-name' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'first_name'
    }
    /**
     * Updates the value of the column 'is_happy' on the table 'super_users'.
     *
     * @param bool $newVal The new value for the column.
     */
    public function updateIsHappy(bool $newVal) {
        $this->table('super_users')->update([
                'is-happy' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'is_happy'
    }
}
