<?php
/**
 * SimpleEdu - Database Class (PDO Wrapper)
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $prefix;

    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            $this->prefix = DB_PREFIX;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function table($name) {
        return $this->prefix . $name;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data) {
        $table = $this->table($table);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $table = $this->table($table);
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($sql, $params)->rowCount();
    }

    public function delete($table, $where, $params = []) {
        $table = $this->table($table);
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    public function count($table, $where = '1=1', $params = []) {
        $table = $this->table($table);
        $result = $this->fetch("SELECT COUNT(*) as cnt FROM {$table} WHERE {$where}", $params);
        return (int)$result['cnt'];
    }

    public function getSetting($key) {
        $result = $this->fetch(
            "SELECT setting_value FROM {$this->prefix}settings WHERE setting_key = ?",
            [$key]
        );
        return $result ? $result['setting_value'] : null;
    }

    public function setSetting($key, $value) {
        $existing = $this->fetch(
            "SELECT id FROM {$this->prefix}settings WHERE setting_key = ?",
            [$key]
        );
        if ($existing) {
            $this->query(
                "UPDATE {$this->prefix}settings SET setting_value = ? WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            $this->query(
                "INSERT INTO {$this->prefix}settings (setting_key, setting_value) VALUES (?, ?)",
                [$key, $value]
            );
        }
    }
}
