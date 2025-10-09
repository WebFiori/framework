<?php
/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020 Ibrahim BinAlshikh
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\DatabaseException;
use WebFiori\Framework\DB;
/**
 * A class which includes all database related operations to add, update,
 * and delete sessions from a database.
 *
 * @author Ibrahim
 *
 * @version 1.0
 *
 * @since 2.1.1
 */
class SessionDB extends DB {
    /**
     * Creates new instance of the class.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function __construct() {
        parent::__construct('sessions-connection');
        $this->addTable(new MySQLSessionsTable());
        $this->addTable(new MSSQLSessionsTable());
        $this->addTable(new MSSQLSessionDataTable());
        $this->addTable(new MySQLSessionDataTable());
    }
    /**
     * Clears the sessions which are older than the constant 'SESSION_GC' or
     * older than 30 days if the constant is not defined.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function gc() {
        $date = SessionsManager::getGCTime();
        $ids = $this->getSessionsIDs($date);

        foreach ($ids as $id) {
            $this->removeSession($id);
        }
    }

    /**
     * Returns the number of data chunks a session has.
     *
     * @param string $sId The ID of the session.
     *
     * @return int If the session does not exist, the method will return 0.
     * Other than that, it will return data chunks count.
     *
     * @throws DatabaseException
     * @since 2.1.1
     */
    public function getChunksCount(string $sId): int {
        $resultSet = $this->table('session_data')
            ->selectCount()
            ->where('s-id', $sId)
            ->execute();
        $row = $resultSet->getRows()[0];

        if ($row['count'] !== null) {
            return $row['count'];
        }

        return 0;
    }

    /**
     * Returns a record that holds session data given Its ID.
     *
     * @param string $sId The ID of the session.
     *
     * @return string|null This method will return a string which holds serialized
     * session info. If no session has given ID exist, null is returned.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function getSession(string $sId) {
        $this->table('session_data')->select()->where('s-id', $sId)
            ->orderBy(['chunk-number' => 'a'])->execute();
        $resultSet = $this->getLastResultSet();

        if ($resultSet->getRowsCount() != 0) {
            $retVal = '';

            foreach ($resultSet->getRows() as $record) {
                $retVal .= $record['data'];
            }

            return base64_decode($retVal);
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
     * @throws DatabaseException
     * @since 1.0
     */
    public function getSessionsIDs(string $olderThan): array {
        return $this->table('sessions')->select()->where('last-used', $olderThan, '<=')->execute()
            ->map(function ($record)
            {
                return $record['s_id'];
            })->toArray();
    }

    /**
     * Checks if a session which has the given ID exist or not in the database.
     *
     * @param string $sId The unique identifier of the session.
     *
     * @return bool If a session which has the given ID exist, the method will
     * return true. Other than that, the method will return false.
     *
     * @throws DatabaseException
     * @since 2.1.1
     */
    public function isSessionExist(string $sId): bool {
        $resultSet = $this->table('sessions')->select()->where('s-id', $sId)->execute();

        return $resultSet->getRowsCount() == 1;
    }

    /**
     * Removes a session from the database given its ID.
     *
     * @param string $sId The ID of the session.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function removeSession(string $sId) {
        $this->table('session_data')->delete()->where('s-id', $sId)->execute();
        $this->table('sessions')->delete()->where('s-id', $sId)->execute();
    }
    /**
     * Removes database Tables which are used to store session information.
     */
    public function removeTables() {
        $this->transaction(function (DB $db)
        {
            try {
                $db->table('session_data')->drop()->execute();
                $db->table('sessions')->drop()->execute();
            } catch (DatabaseException $ex) {
                return;
            }
        });
    }

    /**
     * Store session state.
     *
     * @param string $sId The ID of the session.
     *
     * @param string $session A string that holds serialized
     * session info.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function saveSession(string $sId, string $session) {
        if ($this->isSessionExist($sId)) {
            $this->table('sessions')->update([
                'last-used' => date('Y-m-d H:i:s')
            ])->where('s-id', $sId)
                ->execute();
        } else {
            $this->table('sessions')->insert([
                's-id' => $sId,
                'last-used' => date('Y-m-d H:i:s'),
                'started-at' => date('Y-m-d H:i:s'),
            ])->execute();
        }
        $this->storeChunks($sId, base64_encode($session));
    }
    /**
     * Split session data into smaller chunks.
     *
     * @param string $data
     * @return array
     */
    private function getChunks(string $data) : array {
        $retVal = [];
        $index = 0;
        $chunkSize = $this->getTable('session_data')->getColByKey('data')->getSize() - 50;
        $dataLen = strlen($data);

        while ($index < $dataLen) {
            $retVal[] = substr($data, $index, $chunkSize);
            $index += $chunkSize;
        }

        //This part is to add any extra remaining
        //data in the last part of the session
        $remainingChars = $dataLen - count($retVal) * $chunkSize;

        if ($remainingChars > 0) {
            $retVal[] = substr($data, $index);
        }

        return $retVal;
    }

    /**
     * This method is used to remove any extra chunks which remains in the
     * database after updating a session.
     *
     * @param string $sId
     * @param int $chunksCount
     * @param int $startNumber
     * @throws DatabaseException
     */
    private function removeExtraChunks(string $sId, int $chunksCount, int $startNumber) {
        for ($x = 0 ; $x < $chunksCount ; $x++) {
            $this->table('session_data')
                ->delete()->where('s-id', $sId)
                ->andWhere('chunk-number', $startNumber)
                ->execute();
            $startNumber++;
        }
    }

    /**
     * @throws DatabaseException
     */
    private function storeChunks($sId, $data) {
        $chunks = $this->getChunks($data);
        $currentChunksCount = $this->getChunksCount($sId);

        for ($x = 0 ; $x < count($chunks) ; $x++) {
            try {
                $this->table('session_data')->insert([
                    'data' => $chunks[$x],
                    's-id' => $sId,
                    'chunk-number' => $x
                ])->execute();
            } catch (DatabaseException $ex) {
                $this->clear();
                $this->table('session_data')
                    ->update([
                        'data' => $chunks[$x]
                    ])->where('s-id', $sId)
                    ->andWhere('chunk-number', $x);
                $this->execute();
            }
        }
        $newChunksCount = count($chunks);

        if ($currentChunksCount > $newChunksCount) {
            $chunksCountToRemove = $currentChunksCount - $newChunksCount;
            $this->removeExtraChunks($sId, $chunksCountToRemove, $newChunksCount + 1);
        }
    }
}
