<?php

namespace eru\nczone\zone;

use eru\nczone\utility\db;
use eru\nczone\zone\error\UnableToAquireLockError;

class locks
{
    /** @var db */
    private $db;

    public function __construct(db $db)
    {
        $this->db = $db;
    }

    /**
     * Aquire a lock, then call $fn, then release the lock.
     *
     * The lock hash is calculated from the given $payload. If $timeout_sec
     * is greater than 0, the lock is released (ignored) after this
     * many seconds.
     *
     * @param mixed $payload
     * @param callable $fn
     * @param int $timeout_sec
     *
     * @return mixed
     * @throws UnableToAquireLockError
     */
    public function runExclusive($payload, callable $fn, int $timeout_sec = 0)
    {
        $hash = $this->hash($payload);

        $this->aquire($hash, $payload, $timeout_sec);

        $ret = $fn();

        $this->release($hash);

        return $ret;
    }

    /**
     * Return hash over all parameters.
     *
     * @param mixed ...$params
     *
     * @return string
     */
    private function hash(...$params): string
    {
        return \md5(\serialize($params));
    }

    /**
     * Aquire lock for a given hash.
     *
     * Payload is saved for easier debugging in case something goes wrong..
     *
     * @param string $hash
     * @param mixed $payload
     * @param int $timeout_sec
     *
     * @throws UnableToAquireLockError
     */
    private function aquire(string $hash, $payload, int $timeout_sec): void
    {
        $now = \time();

        $this->releaseExpired($hash, $now);

        $payloadStr = \json_encode($payload);
        // hash is the unique key, so the insert fails when there is already
        // an entry in the table. when that happens, we throw an exception
        $this->db->sql_return_on_error(true);
        $inserted = $this->db->insert_only($this->db->locks_table, [
            'hash' => $hash,
            'payload' => $payloadStr,
            'created' => $now,
            'expires' => $timeout_sec ? $now + $timeout_sec : 0,
        ]);
        $this->db->sql_return_on_error(false);
        if (!$inserted) {
            throw new UnableToAquireLockError("{$payloadStr} ({$hash})");
        }
    }

    /**
     * Release the lock identified by the given hash if it expired before $time.
     *
     * @param string $hash
     * @param $time
     */
    private function releaseExpired(string $hash, int $time): void
    {
        $this->db->delete($this->db->locks_table, [
            'hash' => $hash,
            'expires' => ['$gt' => 0, '$lt' => $time]
        ]);
    }

    /**
     * Release the lock identified by the given hash.
     *
     * @param string $hash
     */
    private function release(string $hash): void
    {
        // releases all locks by a hash
        $this->db->delete($this->db->locks_table, [
            'hash' => $hash,
        ]);
    }
}
