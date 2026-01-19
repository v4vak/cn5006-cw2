<?php
// includes/auth.php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function require_login(): void
{
    start_session();
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function current_user_role_id(): ?int
{
    start_session();
    return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
}

function require_role(int $role_id): void
{
    require_login();
    if ((int)$_SESSION['role_id'] !== $role_id) {
        redirect('dashboard.php');
    }
}
