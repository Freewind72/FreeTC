<?php
namespace Class\Auth;
use Class\Db\Database;

class Auth {
    public static function login($username, $password, $adminOnly = false) {
        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE username=?";
        if ($adminOnly) {
            $sql .= " AND is_admin=1";
        }
        $stmt = $db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
        return false;
    }
    // 后台专用登录
    public static function loginAdmin($username, $password) {
        return self::login($username, $password, true);
    }
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    public static function isAdmin() {
        return ($_SESSION['is_admin'] ?? 0) == 1;
    }
    public static function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>