<?php
// dashboard.php

declare(strict_types=1);

date_default_timezone_set('Europe/Athens');

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$username = (string)($_SESSION['username'] ?? 'User');
$role_id  = (int)($_SESSION['role_id'] ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="muted">Welcome, <strong><?= e($username) ?></strong> (<?= e(role_name($role_id)) ?>)</p>
            </div>
            <div class="topbar-actions">
                <a class="btn btn-secondary" href="index.php">Home</a>
                <a class="btn btn-danger" href="logout.php">Logout</a>
            </div>
        </div>

        <div class="card">

<?php if ($role_id === 1): ?>

    <h2 class="mb-2">Student Area</h2>
    <p class="text-muted mb-4">Quick access to your courses, assignments and grades.</p>

    <div class="row g-3">
        <div class="col-12 col-md-4 col-lg-3">
            <a href="student/courses.php" class="text-decoration-none">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">My Courses</h5>
                                <p class="text-muted mb-0">Enroll & view your modules</p>
                            </div>
                            <span style="font-size:28px;"></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4 col-lg-3">
            <a href="student/assignments.php" class="text-decoration-none">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">Assignments</h5>
                                <p class="text-muted mb-0">View & submit work</p>
                            </div>
                            <span style="font-size:28px;"></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-4 col-lg-3">
            <a href="student/grades.php" class="text-decoration-none">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-1">Grades</h5>
                                <p class="text-muted mb-0">Check results & feedback</p>
                            </div>
                            <span style="font-size:28px;"></span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

<?php elseif ($role_id === 2): ?>

    <h2>Professor Area</h2>
    <p>You are logged in as a professor.</p>
    <a class="btn btn-primary" href="professor/courses.php">Manage Courses</a>

<?php else: ?>

    <h2>Unknown Role</h2>
    <p>Your role is not recognized.</p>

<?php endif; ?>

</div>
