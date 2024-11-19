<?php
namespace app\database;

use webfiori\framework\DB;
use C;
/**
 * A class which is used to perform operations on the table 'users'
 */
class Position2xDB extends DB {
    private static $instance;
    /**
     * Returns an instance of the class.
     * 
     * Calling this method multiple times will return same instance.
     * 
     * @return Position2xDB An instance of the class.
     */
    public static function get() : Position2xDB {

        if (self::$instance === null) {
            self::$instance = new Position2xDB();
        }

        return self::$instance;
    }
    /**
     * Creates new instance of the class.
     */
    public function __construct() {
        parent::__construct('Test Connection');
        $this->register('app\\database');
    }
    /**
     * Adds new record to the table 'users'.
     *
     * @param C $entity An object that holds record information.
     */
    public function addC(C $entity) {
        $this->table('users')->insert([
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'company' => $entity->getCompany(),
            'salary' => $entity->getSalary(),
            'last-updated' => $entity->getLastUpdated(),
        ])->execute();
    }
    /**
     * Deletes a record from the table 'users'.
     *
     * @param C $entity An object that holds record information.
     */
    public function deleteC(C $entity) {
        $this->table('users')
                ->delete()
                ->where('id', $entity->getId())
                ->andWhere('name', $entity->getName())
                ->execute();
    }
    /**
     * Returns the information of a record from the table 'users'.
     *
     * @return C|null If a record with given information exist,
     * The method will return an object which holds all record information.
     * Other than that, null is returned.
     */
    public function getC(int $id, string $name) {
        $mappedRecords = $this->table('users')
                ->select()
                ->where('id', $id)
                ->andWhere('name', $name)
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
                'company' => $entity->getCompany(),
                'salary' => $entity->getSalary(),
                'created-on' => $entity->getCreatedOn(),
                'last-updated' => $entity->getLastUpdated(),
            ])
            ->where('id', $entity->getId())
            ->andWhere('name', $entity->getName())
            ->execute();
    }
    /**
     * Updates the value of the column 'company' on the table 'users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param string $name One of the values which are used in 'where' condition.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateCompany(int $id, string $name, string $newVal) {
        $this->table('users')->update([
                'company' => $newVal
            ])
            ->where('id', $id)
            ->andWhere('name', $name)
            ->execute();
    }
    /**
     * Updates the value of the column 'salary' on the table 'users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param string $name One of the values which are used in 'where' condition.
     *
     * @param float $newVal The new value for the column.
     */
    public function updateSalary(int $id, string $name, float $newVal) {
        $this->table('users')->update([
                'salary' => $newVal
            ])
            ->where('id', $id)
            ->andWhere('name', $name)
            ->execute();
    }
    /**
     * Updates the value of the column 'created_on' on the table 'users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param string $name One of the values which are used in 'where' condition.
     *
     * @param string $newVal The new value for the column.
     */
    public function updateCreatedOn(int $id, string $name, string $newVal) {
        $this->table('users')->update([
                'created-on' => $newVal
            ])
            ->where('id', $id)
            ->andWhere('name', $name)
            ->execute();
    }
    /**
     * Updates the value of the column 'last_updated' on the table 'users'.
     *
     * @param int $id One of the values which are used in 'where' condition.
     *
     * @param string $name One of the values which are used in 'where' condition.
     *
     * @param string|null $newVal The new value for the column.
     */
    public function updateLastUpdated(int $id, string $name, string $newVal = null) {
        $this->table('users')->update([
                'last-updated' => $newVal
            ])
            ->where('id', $id)
            ->andWhere('name', $name)
            ->execute();
    }
}
