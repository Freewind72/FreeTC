<?php
session_start();
require_once 'class/db/Database.php';
require_once 'class/auth/Auth.php';
use Class\Auth\Auth;

$error = '';
$msg = '';
$show = $_GET['action'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        if (Auth::login($user, $pass)) {
            header('Location: index.php');
            exit;
        } else {
            $error = "登录失败";
            $show = 'login';
        }
    }
    if (isset($_POST['register'])) {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        if ($user && $pass) {
            $db = \Class\Db\Database::getInstance();
            $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            try {
                $stmt->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
                $msg = "注册成功，请登录";
                $show = 'login';
            } catch (\PDOException $e) {
                $error = "注册失败，用户名已存在";
                $show = 'register';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登录/注册 - 个人图床</title>
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #ddd; padding: 40px 30px; text-align: center; }
        h2 { color: #273c75; margin-bottom: 20px; }
        .msg { margin: 20px 0; font-size: 16px; }
        .switch-link { color: #0097e6; cursor:pointer; text-decoration:underline; margin-left:8px; }
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
    <script>
        function switchPanel(panel) {
            if(panel === 'login') {
                document.getElementById('login-panel').style.display = '';
                document.getElementById('register-panel').style.display = 'none';
            } else {
                document.getElementById('login-panel').style.display = 'none';
                document.getElementById('register-panel').style.display = '';
            }
        }
    </script>
</head>
<body>
<div class="container">
    <h2>个人图床</h2>
    <?php if (!empty($error)) echo "<div class='msg' style='color:#e84118;'>$error</div>"; ?>
    <?php if (!empty($msg)) echo "<div class='msg' style='color:#44bd32;'>$msg</div>"; ?>
    <div id="login-panel" style="display:<?php echo $show==='register'?'none':'block'; ?>">
        <h3>登录</h3>
        <form method="post">
            <input name="username" type="text" placeholder="用户名" required><br>
            <input type="password" name="password" placeholder="密码" required><br>
            <button name="login">登录</button>
        </form>
        <div class="tips">
            没有账号？
            <span class="switch-link" onclick="switchPanel('register')">注册</span>
        </div>
    </div>
    <div id="register-panel" style="display:<?php echo $show==='register'?'block':'none'; ?>">
        <h3>注册</h3>
        <form method="post">
            <input name="username" type="text" placeholder="用户名" required><br>
            <input type="password" name="password" placeholder="密码" required><br>
            <button name="register">注册</button>
        </form>
        <div class="tips">
            已有账号？
            <span class="switch-link" onclick="switchPanel('login')">登录</span>
        </div>
    </div>
</div>
<script>
    // 保证页面刷新后切换面板
    <?php if ($show === 'register'): ?>
    switchPanel('register');
    <?php else: ?>
    switchPanel('login');
    <?php endif; ?>
</script>
</body>
</html>
