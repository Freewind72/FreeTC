<?php
session_start();
require_once 'class/db/Database.php';
require_once 'class/auth/Auth.php';
require_once 'class/image/Image.php';
use Class\Auth\Auth;
use Class\Image\Image;

// 未登录则跳转到登录页
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['logout'])) {
    Auth::logout();
    header('Location: login.php');
    exit;
}
if (isset($_POST['upload'])) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = Image::upload(Auth::userId(), $_FILES['image']);
        $msg = $filename ? "上传成功" : "上传失败";
    }
}
if (isset($_POST['delete'])) {
    $imgId = intval($_POST['delete']);
    $db = \Class\Db\Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM images WHERE id=? AND user_id=?");
    $stmt->execute([$imgId, Auth::userId()]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($img) {
        $filePath = "dataw/uploads/" . $img['filename'];
        if (file_exists($filePath)) unlink($filePath);
        $db->prepare("DELETE FROM images WHERE id=?")->execute([$imgId]);
        $msg = "图片已删除";
    } else {
        $error = "删除失败，图片不存在或无权限";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>个人图床</title>
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
        .topbar-menu form, .topbar-menu a {
            display: inline-block;
            margin: 0;
        }
        .topbar-menu button, .topbar-menu a {
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
        .topbar-menu button:hover, .topbar-menu a:hover {
            background: rgba(64,120,192,0.4);
        }
        .main {
            margin: 0 auto;
            padding: 90px 30px 40px 30px;
            max-width: 900px;
            min-height: 100vh;
            background: #f5f6fa;
        }
        .panel {
            background:#fff; border-radius:10px; box-shadow:0 2px 12px #ddd; padding:30px 30px 20px 30px; max-width:600px; margin:0 auto 30px auto;
        }
        h2 { color:#273c75; text-align:center; margin-bottom:30px; }
        h3 { color:#4078c0; margin-top:0; }
        form input[type="text"], form input[type="password"], form input[type="file"] {
            width:90%; padding:10px; margin:10px 0 18px 0; border:1px solid #dcdde1; border-radius:5px; font-size:16px;
        }
        form button {
            background: #0097e6; color: #fff; border: none; padding: 10px 40px;
            border-radius: 5px; font-size: 17px; cursor: pointer; transition: background 0.2s;
        }
        form button:hover { background: #4078c0; }
        .msg { margin: 18px 0; font-size: 16px; text-align:center; }
        .img-list { margin-top:20px; display:flex; flex-wrap:wrap; gap:18px; }
        .img-item { background:#fafbfc; border-radius:8px; box-shadow:0 1px 4px #eee; padding:10px; text-align:center; width:180px; position:relative; }
        .img-item img { max-width:140px; max-height:120px; border-radius:6px; cursor:pointer; }
        .img-url { font-size:12px; color:#888; margin-top:6px; word-break:break-all; }
        .img-actions { margin-top:8px; }
        .img-actions button {
            background: #e84118; color: #fff; border: none; padding: 4px 14px;
            border-radius: 4px; font-size: 13px; cursor: pointer; margin-right: 6px;
        }
        .img-actions button.copy-btn {
            background: #0097e6;
        }
        .img-actions button.copy-btn:hover {
            background: #4078c0;
        }
        .img-actions button:hover { background: #c23616; }
        .img-time { color:#aaa; font-size:11px; margin-top:4px; }
        /* 修改密码面板样式 */
        .profile-panel { max-width: 500px; }
        @media (max-width: 700px) {
            .main { padding: 90px 5px 20px 5px; }
            .img-list { justify-content:center; }
            .topbar-title { margin-left: 10px; font-size: 18px; }
            .topbar-menu { margin-right: 10px; gap: 10px; }
        }
        .topbar-menu a.active {
            background: rgba(64,120,192,0.7);
        }
    </style>
    <script>
        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(function() {
                btn.innerText = "已复制";
                setTimeout(function(){ btn.innerText = "复制链接"; }, 1200);
            });
        }
        function getFullUrl(relPath) {
            var loc = window.location;
            var base = loc.protocol + '//' + loc.host + loc.pathname.replace(/\/[^\/]*$/, '/');
            if (relPath.startsWith('/')) {
                return loc.protocol + '//' + loc.host + relPath;
            }
            return base + relPath;
        }
    </script>
</head>
<body>
<div class="topbar">
    <div class="topbar-title">个人图床</div>
    <div class="topbar-menu">
        <a href="profile.php"<?php if (basename($_SERVER['PHP_SELF']) === 'profile.php') echo ' class="active"'; ?>>修改密码</a>
        <form method="post" style="display:inline;">
            <button name="logout">退出登录</button>
        </form>
    </div>
</div>
<div class="main">
    <h2>个人图床</h2>
    <?php if (!empty($error)) echo "<div class='msg' style='color:#e84118;'>$error</div>"; ?>
    <?php if (!empty($msg)) echo "<div class='msg' style='color:#44bd32;'>$msg</div>"; ?>
    <div class="panel">
        <h3>上传图片</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <button name="upload">上传</button>
        </form>
    </div>
    <div class="panel">
        <h3>我的图片</h3>
        <div class="img-list">
            <?php
            $images = Image::getUserImages(Auth::userId());
            foreach ($images as $img) {
                $relUrl = "dataw/uploads/" . $img['filename'];
                $time = date('Y-m-d H:i', strtotime($img['upload_time']));
                echo "<div class='img-item'>
                    <a href='$relUrl' target='_blank'><img src='$relUrl' title='点击新窗口打开'></a>
                    <div class='img-url'>$relUrl</div>
                    <div class='img-time'>上传时间：$time</div>
                    <div class='img-actions'>
                        <button class='copy-btn' type='button' onclick=\"copyToClipboard(getFullUrl('$relUrl'), this)\">复制链接</button>
                        <form method='post' style='display:inline;'>
                            <input type='hidden' name='delete' value='{$img['id']}'>
                            <button type='submit' onclick=\"return confirm('确定要删除此图片吗？')\">删除</button>
                        </form>
                    </div>
                </div>";
            }
            if (empty($images)) {
                echo "<div style='color:#888;'>暂无图片</div>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>