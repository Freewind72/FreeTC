<?php
session_start();
require_once '../class/db/Database.php';
require_once '../class/auth/Auth.php';
use Class\Auth\Auth;
if (!Auth::check() || !Auth::isAdmin()) {
    header('Location: index.php');
    exit;
}

$db = \Class\Db\Database::getInstance();

// 删除图片功能
$admin_msg = '';
if (isset($_POST['delete_img']) && isset($_POST['img_id']) && isset($_POST['user_id'])) {
    $imgId = intval($_POST['img_id']);
    $userId = intval($_POST['user_id']);
    $stmt = $db->prepare("SELECT * FROM images WHERE id=? AND user_id=?");
    $stmt->execute([$imgId, $userId]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($img) {
        $filePath = "../dataw/uploads/" . $img['filename'];
        if (file_exists($filePath)) unlink($filePath);
        $db->prepare("DELETE FROM images WHERE id=?")->execute([$imgId]);
        $admin_msg = "图片已删除";
    } else {
        $admin_msg = "删除失败，图片不存在或无权限";
    }
}

// 获取所有用户及其图片
$users = $db->query("SELECT id, username FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$userImages = [];
foreach ($users as $user) {
    $stmt = $db->prepare("SELECT * FROM images WHERE user_id=? ORDER BY upload_time DESC");
    $stmt->execute([$user['id']]);
    $userImages[$user['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台管理</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f6fa; margin: 0; }
        .topbar {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 64px;
            background: rgba(39,60,117,0.7);
            backdrop-filter: blur(10px);
            color: #fff;
            z-index: 100;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 12px #e1e1e1;
        }
        .topbar-title {
            font-size: 22px;
            font-weight: bold;
            margin-left: 32px;
            letter-spacing: 2px;
        }
        .topbar-menu {
            margin-left: auto;
            margin-right: 32px;
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .topbar-menu a, .topbar-menu button {
            background: none;
            border: none;
            color: #fff;
            font-size: 17px;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }
        .topbar-menu a:hover, .topbar-menu button:hover {
            background: rgba(64,120,192,0.4);
        }
        .main {
            margin: 0 auto;
            padding: 90px 30px 40px 30px;
            max-width: 1100px;
            min-height: 100vh;
            background: #f5f6fa;
        }
        .panel {
            background:#fff; border-radius:10px; box-shadow:0 2px 12px #ddd; padding:30px 30px 20px 30px; max-width:900px; margin:0 auto 30px auto;
        }
        h2 { color:#273c75; text-align:center; margin-bottom:30px; }
        .user-group { margin-bottom:40px; }
        .user-title { font-size:18px; color:#4078c0; margin-bottom:10px; border-bottom:1px solid #eee; padding-bottom:5px; }
        .img-list { display:flex; flex-wrap:wrap; gap:18px; }
        .img-item { background:#fafbfc; border-radius:8px; box-shadow:0 1px 4px #eee; padding:10px; text-align:center; width:180px; position:relative; }
        .img-item img { max-width:140px; max-height:120px; border-radius:6px; cursor:pointer; }
        .img-url { font-size:12px; color:#888; margin-top:6px; word-break:break-all; }
        .img-time { color:#aaa; font-size:11px; margin-top:4px; }
        .img-actions { margin-top:8px; }
        .img-actions button {
            background: #e84118; color: #fff; border: none; padding: 4px 14px;
            border-radius: 4px; font-size: 13px; cursor: pointer; margin-right: 6px;
        }
        .img-actions button:hover { background: #c23616; }
        @media (max-width: 900px) {
            .panel { max-width:100%; }
        }
        @media (max-width: 700px) {
            .main { padding: 90px 5px 20px 5px; }
            .img-list { justify-content:center; }
            .topbar-title { margin-left: 10px; font-size: 18px; }
            .topbar-menu { margin-right: 10px; gap: 10px; }
        }
    </style>
</head>
<body>
<div class="topbar">
    <div class="topbar-title">后台管理</div>
    <div class="topbar-menu">
        <a href="dashboard.php">首页</a>
        <a href="add_admin.php">添加管理</a>
        <a href="profile.php">修改密码</a>
        <form method="post" action="logout.php" style="display:inline;">
            <button type="submit">退出</button>
        </form>
    </div>
</div>
<div class="main">
    <div class="panel">
        <h2>所有用户图片分组</h2>
        <?php if (!empty($admin_msg)) echo "<div class='msg' style='color:#44bd32;text-align:center;'>$admin_msg</div>"; ?>
        <div style="text-align:right;margin-bottom:10px;">
            <a href="profile.php" style="color:#0097e6;text-decoration:underline;font-size:15px;">修改我的账号/密码</a>
        </div>
        <?php foreach ($users as $user): ?>
            <div class="user-group">
                <div class="user-title">用户：<?php echo htmlspecialchars($user['username']); ?> (ID: <?php echo $user['id']; ?>)</div>
                <div class="img-list">
                    <?php
                    if (!empty($userImages[$user['id']])) {
                        foreach ($userImages[$user['id']] as $img) {
                            $relUrl = "../dataw/uploads/" . $img['filename'];
                            $time = date('Y-m-d H:i', strtotime($img['upload_time']));
                            echo "<div class='img-item'>
                                <a href='$relUrl' target='_blank'><img src='$relUrl' title='点击新窗口打开'></a>
                                <div class='img-url'>$relUrl</div>
                                <div class='img-time'>上传时间：$time</div>
                                <div class='img-actions'>
                                    <form method='post' style='display:inline;'>
                                        <input type='hidden' name='img_id' value='{$img['id']}'>
                                        <input type='hidden' name='user_id' value='{$user['id']}'>
                                        <button type='submit' name='delete_img' onclick=\"return confirm('确定要删除此图片吗？')\">删除</button>
                                    </form>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<div style='color:#888;'>暂无图片</div>";
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
