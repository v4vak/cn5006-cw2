<?php
// logout.php

declare(strict_types=1);

date_default_timezone_set('Europe/Athens');

require_once __DIR__ . '/includes/functions.php';

start_session();

session_unset();
session_destroy();

redirect('index.php');
