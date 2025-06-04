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
$msg = '';
$error = '';
$adminId = Auth::userId();

// 获取当前管理员信息
$stmt = $db->prepare("SELECT username FROM users WHERE id=? AND is_admin=1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $error = "未找到管理员信息";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUser = trim($_POST['username'] ?? '');
    $newPass = $_POST['password'] ?? '';
    $newPass2 = $_POST['password2'] ?? '';
    if ($newUser === '') {
        $error = "用户名不能为空";
    } elseif ($newPass !== '' && $newPass !== $newPass2) {
        $error = "两次输入的密码不一致";
    } else {
        // 检查用户名是否被其他管理员占用
        $stmt = $db->prepare("SELECT id FROM users WHERE username=? AND id<>?");
        $stmt->execute([$newUser, $adminId]);
        if ($stmt->fetch()) {
            $error = "该用户名已被占用";
        } else {
            if ($newPass !== '') {
                $stmt = $db->prepare("UPDATE users SET username=?, password_hash=? WHERE id=?");
                $stmt->execute([$newUser, password_hash($newPass, PASSWORD_DEFAULT), $adminId]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username=? WHERE id=?");
                $stmt->execute([$newUser, $adminId]);
            }
            // 修改成功后强制退出登录
            Auth::logout();
            header('Location: index.php?msg=请重新登录');
            exit;
        }
    }
    // 刷新最新用户名
    $stmt = $db->prepare("SELECT username FROM users WHERE id=?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>修改账号密码</title>
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
            max-width: 600px;
            min-height: 100vh;
            background: #f5f6fa;
        }
        .panel {
            background:#fff; border-radius:10px; box-shadow:0 2px 12px #ddd; padding:30px 30px 20px 30px; max-width:500px; margin:0 auto 30px auto;
        }
        h2 { color:#273c75; text-align:center; margin-bottom:30px; }
        form input[type="text"], form input[type="password"] {
            width:90%; padding:10px; margin:10px 0 18px 0; border:1px solid #dcdde1; border-radius:5px; font-size:16px;
        }
        form button {
            background: #0097e6; color: #fff; border: none; padding: 10px 40px;
            border-radius: 5px; font-size: 17px; cursor: pointer; transition: background 0.2s;
        }
        form button:hover { background: #4078c0; }
        .msg { margin: 18px 0; font-size: 16px; text-align:center; }
        @media (max-width: 700px) {
            .main { padding: 90px 5px 20px 5px; }
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
        <a href="profile.php" class="active">修改密码</a>
        <form method="post" action="logout.php" style="display:inline;">
            <button type="submit">退出</button>
        </form>
    </div>
</div>
<div class="main">
    <div class="panel">
        <h2>修改账号密码</h2>
        <?php if ($error) echo "<div class='msg' style='color:#e84118;'>$error</div>"; ?>
        <?php if ($msg) echo "<div class='msg' style='color:#44bd32;'>$msg</div>"; ?>
        <form method="post">
            <input name="username" type="text" placeholder="管理员账号" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" required><br>
            <input type="password" name="password" placeholder="新密码（留空则不修改）"><br>
            <input type="password" name="password2" placeholder="重复新密码"><br>
            <button type="submit">保存修改</button>
        </form>
    </div>
</div>
</body>
</html>