<?php
// register.php

declare(strict_types=1);

date_default_timezone_set('Europe/Athens');

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

start_session();

$errors = [];
$success = '';

$username = '';
$email = '';
$role = '';
$reg_code = '';

if (is_post()) {
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role     = (string)($_POST['role'] ?? '');
    $reg_code = trim((string)($_POST['reg_code'] ?? ''));

    if ($username === '' || $email === '' || $password === '' || $role === '' || $reg_code === '') {
        $errors[] = 'Please fill in all fields.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    $role_id = 0;
    if ($role === 'student') {
        if ($reg_code !== 'STUD2025') {
            $errors[] = 'Invalid registration code for Student.';
        } else {
            $role_id = 1;
        }
    } elseif ($role === 'professor') {
        if ($reg_code !== 'PROF2025') {
            $errors[] = 'Invalid registration code for Professor.';
        } else {
            $role_id = 2;
        }
    } else {
        $errors[] = 'Invalid role selected.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $errors[] = 'This email is already registered.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role_id)
                VALUES (:username, :email, :password_hash, :role_id)
            ");

            $stmt->execute([
                'username'      => $username,
                'email'         => $email,
                'password_hash' => $password_hash,
                'role_id'       => $role_id
            ]);

            $success = 'Registration successful! You can now log in.';
            $username = $email = $role = $reg_code = '';
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
  <title>Register</title>
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
            <span>Account Registration</span>
          </div>
        </div>

        <div class="left-caption">
          <h2>Role-Based Access</h2>
          <p>Select your role (Student/Professor). The registration code assigns the correct role securely.</p>
        </div>
      </div>

      <div class="panel-right">
        <div class="top-meta"> Current Server Time: <?= e($serverTime) ?></div>

        <h1 class="page-title">Create account</h1>
        <p class="subtitle">Fill the form below. Use the correct registration code for your selected role.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <ul>
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="card">
          <form method="post" action="register.php" class="form">
            <label>
              Username
              <input type="text" name="username" value="<?= e($username) ?>" required>
            </label>

            <label>
              Email
              <input type="email" name="email" value="<?= e($email) ?>" required>
            </label>

            <label>
              Password
              <input type="password" name="password" required>
            </label>

            <label>
              Role
              <select name="role" required>
                <option value="" <?= $role === '' ? 'selected' : '' ?>>Select role</option>
                <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="professor" <?= $role === 'professor' ? 'selected' : '' ?>>Professor</option>
              </select>
            </label>

            <label>
              Registration Code
              <input type="text" name="reg_code" value="<?= e($reg_code) ?>" required>
              <!--<span class="small">Student: <strong>STUD2025</strong> | Professor: <strong>PROF2025</strong></span>-->
            </label>

            <button type="submit" class="btn">Register</button>

            <div class="row">
              <span class="small">Already have an account?</span>
              <a class="link" href="login.php">Log in</a>
            </div>
          </form>
        </div>

        <div class="tiny-footer">Your data is stored securely in the database (passwords hashed).</div>
      </div>
    </div>
  </div>
</body>
</html>
