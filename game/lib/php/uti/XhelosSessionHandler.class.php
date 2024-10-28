<?php

class XhelosSessionHandler implements SessionHandlerInterface
{
    private $db;
    private $table = 'sessions';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function open($savePath, $sessionName): bool
    {
        // Ensure the database connection is established
        return $this->db !== null;
    }

    public function close(): bool
    {
        // Close the database connection if needed
        return true;
    }

    public function read($sessionId): string
    {
        $sql = "SELECT session_data FROM {$this->table} WHERE session_id = '{$sessionId}' LIMIT 1";
        $result = $this->db->fetch($sql);
        if ($result) {
            return $result['session_data'];
        }
        return '';
    }

    public function write($sessionId, $data): bool
    {
        $expiry = time() + ini_get('session.gc_maxlifetime');
        $sql = "REPLACE INTO {$this->table} (session_id, session_data, session_expiry) VALUES ('{$sessionId}', '{$data}', {$expiry})";
        return $this->db->query($sql) ? true : false;
    }

    public function destroy($sessionId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE session_id = '{$sessionId}'";
        return $this->db->query($sql) ? true : false;
    }

    public function gc($maxLifetime): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE session_expiry < " . time();
        return $this->db->query($sql) ? true : false;
    }
}

// Database session table creation SQL
// You can run this SQL in phpMyAdmin to create the table:
/*
CREATE TABLE `sessions` (
    `session_id` VARCHAR(128) NOT NULL PRIMARY KEY,
    `session_data` TEXT NOT NULL,
    `session_expiry` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/
?>
