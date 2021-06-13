<?php
namespace webfiori\framework\session;

use app\database\MainDatabase;
/**
 * A class which includes all database related operations to add, update, 
 * and delete sessions from a database.
 *
 * @author Ibrahim
 * 
 * @version 1.0
 * 
 * @since 2.1.0
 */
class SessionOperations extends MainDatabase {
    /**
     * Creates new instance of the class.
     * 
     * @since 1.0
     */
    public function __construct() {
        parent::__construct();
        $this->addTable(new SessionsTable());
    }
    /**
     * Clears the sessions which are older than the constant 'SESSION_GC' or 
     * older than 30 days if the constant is not defined.
     * 
     * @since 1.0
     */
    public function gc() {
        if (defined('SESSION_GC') && SESSION_GC > 0) {
            $olderThan = time() - SESSION_GC;
        } else {
            //Clear any sesstion which is older than 30 days
            $olderThan = time() - 60 * 60 * 24 * 30;
        }
        $date = date('Y-m-d H:i:s', $olderThan);
        $ids = $this->getSessionsIDs($date);

        foreach ($ids as $id) {
            $this->removeSession($id);
        }
    }
    /**
     * Returns a record that holds session data given Its ID.
     * 
     * @param string $sId The ID of the session.
     * 
     * @return string This method will return a string which holds serialized 
     * session info.
     * 
     * @since 1.0
     */
    public function getSession($sId) {
        $this->table('sessions')->select()->where('s-id', '=', $sId)->execute();
        $resultSet = $this->getLastResultSet();

        if ($resultSet->getRowsCount() == 1) {
            return $resultSet->getRows()[0]['session_data'];
        }
    }
    /**
     * Returns an array that holds the IDs of sessions which are older than 
     * specific date and time.
     * 
     * @param string $olderThan A date-time string in the format 'YYYY-MM-DD HH:MM:SS'. 
     * This also can only be a date.
     * 
     * @return array An array that holds the IDs of sessions which are older than 
     * given date.
     * 
     * @since 1.0
     */
    public function getSessionsIDs($olderThan) {
        $this->table('sessions')->select()->where('last-used', '<=', $olderThan)->execute();
        $resultSet = $this->getLastResultSet();
        $resultSet->setMappingFunction(function ($data)
        {
            $retVal = [];

            foreach ($data as $record) {
                $retVal[] = $record['s_id'];
            }

            return $retVal;
        });

        return $resultSet->getMappedRows();
    }
    /**
     * Removes a session from the database given its ID.
     * 
     * @param string $sId The ID of the session.
     * 
     * @since 1.0
     */
    public function removeSession($sId) {
        $this->table('sessions')->delete()->where('s-id', '=', $sId)->execute();
    }
    /**
     * Store session state.
     * 
     * @param string $sId The ID of the session.
     * 
     * @param string $session A string that holds serialized 
     * session info.
     * 
     * @since 1.0
     */
    public function saveSession($sId, $session) {
        $sData = $this->getSession($sId);

        if ($sData !== null) {
            $this->table('sessions')->update([
                'session-data' => $session,
                'last-used' => date('Y-m-d H:i:s')
            ])->where('s-id', '=', $sId)->execute();
        } else {
            $this->table('sessions')->insert([
                's-id' => $sId,
                'session-data' => $session,
                'last-used' => date('Y-m-d H:i:s'),
                'started-at' => date('Y-m-d H:i:s'),
            ])->execute();
        }
    }
}
