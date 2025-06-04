<?php
$installed = false;
$msg = '';
$dataDir = __DIR__ . '/dataw';
$dbFile = $dataDir . '/db.sqlite';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    if (!is_writable($dataDir)) {
        $msg = '<span style="color:red;">dataw 目录不可写，请检查权限。</span>';
    } elseif (!file_exists($dbFile)) {
        $db = new PDO('sqlite:' . $dbFile);
        $db->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0
        )");
        $db->exec("CREATE TABLE images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            upload_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )");
        // 创建默认管理员
        $adminUser = 'admin';
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, 1)")
            ->execute([$adminUser, $adminPass]);
        $installed = true;
        $msg = '<span style="color:green;">安装完成，默认管理员账号：admin 密码：admin123</span>';
    } else {
        $installed = true;
        $msg = '<span style="color:green;">已安装，无需重复安装。</span>';
    }
} elseif (file_exists($dbFile)) {
    $installed = true;
    $msg = '<span style="color:green;">已安装，无需重复安装。</span>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>一键安装 - 图床系统</title>
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 40px 30px; text-align: center; }
        h2 { color: #273c75; margin-bottom: 20px; }
        .msg { margin: 20px 0; font-size: 16px; }
        button {
            background: #0097e6;
            color: #fff;
            border: none;
            padding: 12px 40px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }
        button:hover { background: #4078c0; }
        .tips { color: #888; font-size: 14px; margin-top: 18px; }
    </style>
</head>
<body>
<div class="container">
    <h2>图床系统一键安装</h2>
    <div class="msg"><?php echo $msg; ?></div>
    <?php if (!$installed): ?>
        <form method="post">
            <button type="submit" name="install">点击开始安装</button>
        </form>
        <div class="tips">安装完成后，默认管理员账号：admin 密码：admin123</div>
    <?php else: ?>
        <div class="tips"><a href="index.php" style="color:#0097e6;text-decoration:none;">进入首页</a></div>
    <?php endif; ?>
</div>
</body>
</html>
