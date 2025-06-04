<?php
session_start();
require_once '../class/db/Database.php';
require_once '../class/auth/Auth.php';
use Class\Auth\Auth;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    if (Auth::loginAdmin($user, $pass)) {
        // 防止后续输出，确保 session 正确写入
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "登录失败";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>后台登录</title>
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 400px; margin: 100px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 40px 30px; text-align: center; }
        h2 { color: #273c75; margin-bottom: 30px; }
        .msg { margin: 20px 0; font-size: 16px; }
        form input[type="text"], form input[type="password"] {
            width:90%; padding:10px; margin:10px 0 18px 0; border:1px solid #dcdde1; border-radius:5px; font-size:16px;
        }
        form button {
            background: #0097e6; color: #fff; border: none; padding: 10px 40px;
            border-radius: 5px; font-size: 17px; cursor: pointer; transition: background 0.2s;
        }
        form button:hover { background: #4078c0; }
        .tips { color: #888; font-size: 14px; margin-top: 18px; }
    </style>
</head>
<body>
<div class="container">
    <h2>后台登录</h2>
    <?php if (!empty($error)) echo "<div class='msg' style='color:#e84118;'>$error</div>"; ?>
    <form method="post">
        <input name="username" type="text" placeholder="管理员账号" required><br>
        <input type="password" name="password" placeholder="密码" required><br>
        <button type="submit">登录</button>
    </form>
    <div class="tips"><a href="../index.php" style="color:#0097e6;text-decoration:none;">返回首页</a></div>
</div>
</body>
</html>