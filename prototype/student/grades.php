<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_student();

$student_id = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT
    c.title AS course_title,
    a.title AS assignment_title,
    s.submitted_at,
    g.grade,
    g.feedback,
    g.graded_at
  FROM grades g
  INNER JOIN submissions s ON s.id = g.submission_id
  INNER JOIN assignments a ON a.id = s.assignment_id
  INNER JOIN courses c ON c.id = a.course_id
  WHERE s.student_id = :sid
  ORDER BY g.graded_at DESC
");
$stmt->execute(['sid' => $student_id]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Grades</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Student – Grades</h1>
    <a class="btn btn-secondary" href="courses.php">Back to Courses</a>

    <div class="card">
      <?php if (!$rows): ?>
        <p class="muted">No grades yet.</p>
      <?php else: ?>
        <table class="table table-striped table-bordered">
          <tr>
            <th>Course</th>
            <th>Assignment</th>
            <th>Submitted</th>
            <th>Grade</th>
            <th>Feedback</th>
            <th>Graded</th>
          </tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['course_title']) ?></td>
              <td><?= e($r['assignment_title']) ?></td>
              <td><?= e((string)$r['submitted_at']) ?></td>
              <td><?= e((string)$r['grade']) ?></td>
              <td><?= $r['feedback'] ? e((string)$r['feedback']) : '<span class="muted">—</span>' ?></td>
              <td><?= e((string)$r['graded_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
