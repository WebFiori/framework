<?php

/**
 * This file is licensed under MIT License.
 *
 * Copyright (c) 2020-present WebFiori Framework
 *
 * For more information on the license, please visit:
 * https://github.com/WebFiori/.github/blob/main/LICENSE
 *
 */
namespace WebFiori\Framework\Session;

use WebFiori\Database\ConnectionInfo;
use WebFiori\Database\DatabaseException;
use WebFiori\Framework\DB;
/**
 * A class which includes all database related operations to add, update,
 * and delete sessions from a database.
 *
 * @author Ibrahim
 *
 * @version 2.0
 *
 * @since 2.1.1
 */
class SessionDB extends DB {
    /**
     * Creates new instance of the class.
     *
     * @param string|ConnectionInfo $connection The name of the connection or a ConnectionInfo object.
     *
     * @throws DatabaseException
     * @since 1.0
     */
    public function __construct($connection = 'sessions-connection') {
        parent::__construct($connection);
        $dbType = $this->getConnectionInfo()->getDatabaseType();
        $this->addTable(SessionSchema::createSessionsTable($dbType));
        $this->addTable(SessionSchema::createSessionDataTable($dbType));

        // Register the per-key table if it exists in the database.
        // The table is created by SessionSchemaMigration::run().
        try {
            $this->addTable(SessionSchema::createSessionKvDataTable($dbType));
        } catch (\Throwable $e) {
            // Table registration may fail if schema is not yet migrated; that
            // is acceptable — per-key operations will fail gracefully at use time.
        }
    }
    /**
     * Creates all session tables (sessions, session_data, session_kv_data).
     * Called during test setup or initial deployment.
     */
    public function createTables(): void {
        // Use the parent DB class createTables() which creates all registered tables.
        parent::createTables();
    }
    /**
     * Drops all session-related tables.
     */
    public function dropAllTables(): void {
        try {
            $this->table('session_kv_data')->drop()->execute();
        } catch (\Throwable $e) {
        }
        $this->table('session_data')->drop()->execute();
        $this->table('sessions')->drop()->execute();
    }
    /**
     * Clears the sessions which are older than the given date.
     *
     * @param string $olderThan A date-time string in the format 'YYYY-MM-DD HH:MM:SS'.
     * @param int $maxCount Maximum number of sessions to remove. 0 means no limit.
     *
     * @throws DatabaseException
     * @since 2.0
     */
    public function gc(string $olderThan, int $maxCount = 0) {
        $ids = $this->getSessionsIDs($olderThan);

        if ($maxCount > 0) {
            $ids = array_slice($ids, 0, $maxCount);
        }

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

        return null;
    }
    /**
     * Reads a single key from session_kv_data.
     *
     * @return array{value: mixed, version: int}|null
     */
    public function getSessionKey(string $sId, string $key): ?array {
        $resultSet = $this->table('session_kv_data')
            ->select(['svalue', 'version'])
            ->where('s-id', $sId)
            ->andWhere('skey', $key)
            ->execute();

        if ($resultSet->getRowsCount() === 0) {
            return null;
        }

        $row = $resultSet->getRows()[0];

        return [
            'value' => json_decode($row['svalue'] ?? $row['s_value'] ?? 'null', true),
            'version' => (int) ($row['version'] ?? 1),
        ];
    }
    /**
     * Reads all keys for a session from session_kv_data.
     *
     * @return array<string, array{value: mixed, version: int}>
     */
    public function getSessionKeys(string $sId): array {
        $resultSet = $this->table('session_kv_data')
            ->select(['skey', 'svalue', 'version'])
            ->where('s-id', $sId)
            ->execute();

        $result = [];

        foreach ($resultSet->getRows() as $row) {
            $k = $row['skey'] ?? $row['s_key'] ?? null;

            if ($k === null) {
                continue;
            }

            $result[$k] = [
                'value' => json_decode($row['svalue'] ?? $row['s_value'] ?? 'null', true),
                'version' => (int) ($row['version'] ?? 1),
            ];
        }

        return $result;
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
                return $record['s-id'] ?? $record['s_id'] ?? null;
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
     * Removes both the chunked blob data (session_data) and per-key data (session_kv_data).
     *
     * @param string $sId The ID of the session.
     * @throws DatabaseException
     */
    public function removeSession(string $sId) {
        $this->table('session_data')->delete()->where('s-id', $sId)->execute();

        try {
            $this->table('session_kv_data')->delete()->where('s-id', $sId)->execute();
        } catch (\Throwable $e) {
            // session_kv_data may not exist yet; ignore.
        }

        $this->table('sessions')->delete()->where('s-id', $sId)->execute();
    }
    /**
     * Removes a single key from session_kv_data.
     */
    public function removeSessionKey(string $sId, string $key): void {
        $this->table('session_kv_data')
            ->delete()
            ->where('s-id', $sId)
            ->andWhere('skey', $key)
            ->execute();
    }
    /**
     * Removes database Tables which are used to store session information.
     */
    public function removeTables() {
        $this->transaction(function (DB $db)
        {
            try {
                $db->table('session_kv_data')->drop()->execute();
            } catch (DatabaseException $ex) {
            }

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
     * Writes a single key to session_kv_data with conflict detection.
     *
     * @throws SessionConflictException when strategy is REJECT and version mismatch.
     * @return int The new version number.
     */
    public function writeSessionKey(
        string $sId,
        string $key,
        mixed $value,
        string|int|null $expectedVersion,
        ConflictStrategy $strategy
    ): int {
        // Ensure the session row exists.
        if (!$this->isSessionExist($sId)) {
            $this->table('sessions')->insert([
                's-id' => $sId,
                'last-used' => date('Y-m-d H:i:s'),
                'started-at' => date('Y-m-d H:i:s'),
            ])->execute();
        } else {
            $this->table('sessions')->update([
                'last-used' => date('Y-m-d H:i:s'),
            ])->where('s-id', $sId)->execute();
        }

        $existing = $this->getSessionKey($sId, $key);
        $currentVersion = $existing !== null ? $existing['version'] : 0;

        if ($strategy === ConflictStrategy::REJECT
            && $expectedVersion !== null
            && $currentVersion !== (int) $expectedVersion
        ) {
            throw new SessionConflictException($key, $expectedVersion, $currentVersion);
        }

        $newVersion = $currentVersion + 1;
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($existing === null) {
            $this->table('session_kv_data')->insert([
                's-id' => $sId,
                'skey' => $key,
                'svalue' => $encoded,
                'version' => $newVersion,
                'updated-at' => date('Y-m-d H:i:s'),
            ])->execute();
        } else {
            $this->table('session_kv_data')->update([
                'svalue' => $encoded,
                'version' => $newVersion,
                'updated-at' => date('Y-m-d H:i:s'),
            ])->where('s-id', $sId)->andWhere('skey', $key)->execute();
        }

        return $newVersion;
    }
    /**
     * Split session data into smaller chunks.
     *
     * @param string $data
     * @return array
     */
    private function getChunks(string $data) : array {
        $retVal = [];
        $chunkSize = $this->getTable('session_data')->getColByKey('data')->getSize() - 50;
        $dataLen = strlen($data);
        $index = 0;

        while ($index < $dataLen) {
            $retVal[] = substr($data, $index, $chunkSize);
            $index += $chunkSize;
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
            $this->removeExtraChunks($sId, $chunksCountToRemove, $newChunksCount);
        }
    }
}
