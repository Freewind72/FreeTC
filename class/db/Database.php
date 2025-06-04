<?php
namespace Class\Db;
class Database {
    private static $instance = null;
    public static function getInstance() {
        if (self::$instance === null) {
            $dbFile = dirname(__DIR__, 2) . '/dataw/db.sqlite';
            self::$instance = new \PDO('sqlite:' . $dbFile);
            self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // 禁止多语句执行，防止批量注入
            self::$instance->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$instance;
    }
}
?>
