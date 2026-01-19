<?php
// login.php

declare(strict_types=1);

date_default_timezone_set('Europe/Athens');


require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

start_session();

$errors = [];
$email = '';

if (is_post()) {
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter email and password.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role_id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = (string)$user['username'];
            $_SESSION['role_id']  = (int)$user['role_id'];

            redirect('dashboard.php');
        }
    }
}

$serverTime = date('h:i:s A');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="shell">
    <div class="split">
      <div class="panel-left">
        <div class="brand-badge">
          <img src="pictures/mc_logo.png" alt="Logo">
          <div class="brand-text">
            <strong>University Portal</strong>
            <span>CN5006 Coursework</span>
          </div>
        </div>

        <div class="left-caption">
          <h2>Secure Access</h2>
          <p>Log in with your credentials. Your role (Student/Professor) is handled automatically after authentication.</p>
        </div>
      </div>

      <div class="panel-right">
        <div class="top-meta"> Current Server Time: <?= e($serverTime) ?></div>

        <h1 class="page-title">Sign in</h1>
        <p class="subtitle">Enter your email and password to access your dashboard.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <ul>
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card">
          <form method="post" action="login.php" class="form">
            <label>
              Email
              <input type="email" name="email" value="<?= e($email) ?>" required>
            </label>

            <label>
              Password
              <input type="password" name="password" required>
            </label>

            <button type="submit" class="btn">Login</button>

            <div class="row">
              <span class="small">No account?</span>
              <a class="link" href="register.php">Create one</a>
            </div>
          </form>
        </div>

        <div class="tiny-footer">Developed for CN5006 – Web & Mobile Applications Development</div>
      </div>
    </div>
  </div>
</body>
</html>
