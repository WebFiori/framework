<?php
namespace webfiori\db;

use webfiori\entity\Employee;
use webfiori\framework\DB;
/**
 * A class which is used to perform operations on the table 'users'
 */
class EmployeeDB extends DB {
    private static $instance;
    /**
     * Returns an instance of the class.
     * 
     * Calling this method multiple times will return same instance.
     * 
     * @return EmployeeDB An instance of the class.
     */
    public static function get() : EmployeeDB {

        if (self::$instance === null) {
            self::$instance = new EmployeeDB();
        }

        return self::$instance;
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        //TODO: Specify the name of database connection to use in performing operations.
        parent::__construct('');
        $this->register('webfiori\\db');
    }
    /**
     * Adds new record to the table 'users'.
     *
     * @param Employee $entity An object that holds record information.
     */
    public function addEmployee(Employee $entity) {
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
     * @param Employee $entity An object that holds record information.
     */
    public function deleteEmployee(Employee $entity) {
        $this->table('users')
                ->delete();
            //TODO: Specify delete record condition(s).
    }
    /**
     * Returns the information of a record from the table 'users'.
     *
     * @return Employee|null If a record with given information exist,
     * The method will return an object which holds all record information.
     * Other than that, null is returned.
     */
    public function getEmployee() {
        $mappedRecords = $this->table('users')
                ->select()
                //TODO: Specify select condition for retrieving one record.
                ->execute()
                ->map(function (array $record) {
                    return Employee::map($record);
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
    public function getEmployees(int $pageNum = 0, int $pageSize = 10) : array {
        return $this->table('users')
                ->select()
                ->page($pageNum, $pageSize)
                ->orderBy(["id"])
                ->execute()
                ->map(function (array $record) {
                    return Employee::map($record);
                })->toArray();
    }
    /**
     * Returns number of records on the table 'users'.
     *
     * The main use of this method is to compute number of pages.
     *
     * @return int Number of records on the table 'users'.
     */
    public function getEmployeesCount() : int {
        return $this->table('users')
                ->selectCount()
                ->execute()
                ->getRows()[0]['count'];
    }
    /**
     * Updates a record on the table 'users'.
     *
     * @param Employee $entity An object that holds updated record information.
     */
    public function updateEmployee(Employee $entity) {
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
}
