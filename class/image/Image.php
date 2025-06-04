<?php
namespace Class\Image;
use Class\Db\Database;

class Image {
    public static function upload($userId, $file) {
        $uploadDir = dirname(__DIR__, 2) . '/dataw/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO images (user_id, filename) VALUES (?, ?)");
            $stmt->execute([$userId, $filename]);
            return $filename;
        }
        return false;
    }
    public static function getUserImages($userId) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM images WHERE user_id=? ORDER BY upload_time DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
