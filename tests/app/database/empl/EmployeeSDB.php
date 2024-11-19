<?php
namespace app\database\empl;

use webfiori\framework\DB;
use C;
/**
 * A class which is used to perform operations on the table 'users'
 */
class EmployeeSDB extends DB {
    private static $instance;
    /**
     * Returns an instance of the class.
     * 
     * Calling this method multiple times will return same instance.
     * 
     * @return EmployeeSDB An instance of the class.
     */
    public static function get() : EmployeeSDB {

        if (self::$instance === null) {
            self::$instance = new EmployeeSDB();
        }

        return self::$instance;
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        //TODO: Specify the name of database connection to use in performing operations.
        parent::__construct('');
        $this->register('app\\database\\empl');
    }
    /**
     * Adds new record to the table 'users'.
     *
     * @param C $entity An object that holds record information.
     */
    public function addC(C $entity) {
        $this->table('users')->insert([
            'id' => $entity->getId(),
            'email' => $entity->getEmail(),
            'first-name' => $entity->getFirstName(),
            'last-name' => $entity->getLastName(),
            'joining-date' => $entity->getJoiningDate(),
        ])->execute();
    }
    /**
     * Deletes a record from the table 'users'.
     *
     * @param C $entity An object that holds record information.
     */
    public function deleteC(C $entity) {
        $this->table('users')
                ->delete();
            //TODO: Specify delete record condition(s).
    }
    /**
     * Returns the information of a record from the table 'users'.
     *
     * @return C|null If a record with given information exist,
     * The method will return an object which holds all record information.
     * Other than that, null is returned.
     */
    public function getC() {
        $mappedRecords = $this->table('users')
                ->select()
                //TODO: Specify select condition for retrieving one record.
                ->execute()
                ->map(function (array $record) {
                    return C::map($record);
                });
        if ($mappedRecords->getRowsCount() == 1) {
            return $mappedRecords->getRows()[0];
        }
    }
    /**
     * Returns all the records from the table 'users'.
     *
     * @param int $pageNum The number of page to fetch. Default is 0.
     *
     * @param int $pageSize Number of records per page. Default is 10.
     *
     * @return array An array that holds all table records as objects
     */
    public function getCs(int $pageNum = 0, int $pageSize = 10) : array {
        return $this->table('users')
                ->select()
                ->page($pageNum, $pageSize)
                ->orderBy(["id"])
                ->execute()
                ->map(function (array $record) {
                    return C::map($record);
                })->toArray();
    }
    /**
     * Returns number of records on the table 'users'.
     *
     * The main use of this method is to compute number of pages.
     *
     * @return int Number of records on the table 'users'.
     */
    public function getCsCount() : int {
        return $this->table('users')
                ->selectCount()
                ->execute()
                ->getRows()[0]['count'];
    }
    /**
     * Updates a record on the table 'users'.
     *
     * @param C $entity An object that holds updated record information.
     */
    public function updateC(C $entity) {
        $this->table('users')
            ->update([
                'id' => $entity->getId(),
                'email' => $entity->getEmail(),
                'first-name' => $entity->getFirstName(),
                'last-name' => $entity->getLastName(),
                'joining-date' => $entity->getJoiningDate(),
                'created-on' => $entity->getCreatedOn(),
            ]);
            //TODO: Specify update record condition(s).
    }
    /**
     * Updates the value of the column 'id' on the table 'users'.
     *
     * @param int $newVal The new value for the column.
     */
    public function updateId(int $newVal) {
        $this->table('users')->update([
                'id' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'id'
    }
    /**
     * Updates the value of the column 'email' on the table 'users'.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateEmail(string $newVal) {
        $this->table('users')->update([
                'email' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'email'
    }
    /**
     * Updates the value of the column 'first_name' on the table 'users'.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateFirstName(string $newVal) {
        $this->table('users')->update([
                'first-name' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'first_name'
    }
    /**
     * Updates the value of the column 'last_name' on the table 'users'.
     *
     * @param string|null $newVal The new value for the column.
     */
    public function updateLastName(string $newVal = null) {
        $this->table('users')->update([
                'last-name' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'last_name'
    }
    /**
     * Updates the value of the column 'joining_date' on the table 'users'.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateJoiningDate(string $newVal) {
        $this->table('users')->update([
                'joining-date' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'joining_date'
    }
    /**
     * Updates the value of the column 'created_on' on the table 'users'.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateCreatedOn(string $newVal) {
        $this->table('users')->update([
                'created-on' => $newVal
            ])->execute();
            //TODO: Specify conditions for updating the value of the record 'created_on'
    }
}
