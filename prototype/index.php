<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Athens');

require_once __DIR__ . '/includes/functions.php';

start_session();
$logged_in = !empty($_SESSION['user_id']);
$serverTime = date('h:i:s A');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>University Portal</title>

  <link rel="stylesheet" href="css/style.css">

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
  <div class="shell">
    <div class="split">
      <div class="panel-left">
        <div class="brand-badge">
          <img src="pictures/mc_logo.png" alt="Logo">
          <div class="brand-text">
            <strong>University Portal</strong>
            <span>Public Campus Page</span>
          </div>
        </div>

        <div class="left-caption">
          <h2>Welcome</h2>
          <p>Explore the campus and access the portal through secure login and registration.</p>
        </div>
      </div>

      <div class="panel-right">
        <div class="top-meta"> Current Server Time: <?= e($serverTime) ?></div>

        <h1 class="page-title">Campus Information</h1>
        <p class="subtitle">Public homepage for visitors. Login/register available for students and professors.</p>

        <div class="card">
          <div class="row">
            <?php if ($logged_in): ?>
              <a class="btn" href="dashboard.php">Dashboard</a>
              <a class="btn btn-danger" href="logout.php">Logout</a>
            <?php else: ?>
              <a class="btn" href="login.php">Login</a>
              <a class="btn btn-secondary" href="register.php">Register</a>
            <?php endif; ?>
          </div>

          <div class="section">
            <h2>About the Campus</h2>
            <p>
              Our campus includes modern lecture halls, research labs, a central library,
              student facilities and collaborative spaces designed for academic excellence.
              This portal demonstrates role-based access for students and professors.
            </p>

            <div class="thumb-row">
              <img src="pictures/mc_pc_class.jpg" alt="Campus view 1">
              <img src="pictures/mc_class.jpg" alt="Campus view 2">
            </div>
          </div>

          <div class="section">
            <h2>Campus Location</h2>
            <p>Interactive map loaded via JavaScript (Leaflet + OpenStreetMap).</p>
            <div id="map"></div>
          </div>
        </div>

        <div class="tiny-footer">© 2025-2026 University Portal – CN5006 Coursework</div>
      </div>
    </div>
  </div>

  <!-- Leaflet JS + map JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="js/map.js"></script>
</body>
</html>
