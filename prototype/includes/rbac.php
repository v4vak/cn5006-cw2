<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

function require_student(): void
{
    require_login();
    if ((int)($_SESSION['role_id'] ?? 0) !== 1) {
        redirect('forbidden.php');
    }
}

function require_professor(): void
{
    require_login();
    if ((int)($_SESSION['role_id'] ?? 0) !== 2) {
        redirect('forbidden.php');
    }
}
