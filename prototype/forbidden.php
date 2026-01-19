<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
start_session();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forbidden</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="shell">
    <div class="split">
      <div class="panel-left"></div>
      <div class="panel-right">
        <h1 class="page-title">Forbidden Action</h1>
        <p class="subtitle">You do not have permission to access this page.</p>

        <div class="card">
          <a class="btn" href="dashboard.php">Back to Dashboard</a>
          <a class="btn btn-secondary" href="index.php">Home</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
