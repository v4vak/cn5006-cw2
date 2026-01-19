<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/rbac.php';

require_student();

$student_id = (int)$_SESSION['user_id'];

/* Assignments for my enrolled courses */
$stmt = $pdo->prepare("
  SELECT a.*, c.title AS course_title
  FROM assignments a
  INNER JOIN courses c ON c.id = a.course_id
  INNER JOIN enrollments e ON e.course_id = c.id AND e.student_id = :sid
  ORDER BY a.created_at DESC
");
$stmt->execute(['sid' => $student_id]);
$assignments = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Assignments</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="container">
    <h1>Student – Assignments</h1>
    <a class="btn btn-secondary" href="courses.php">Back to Courses</a>

    <div class="card">
      <?php if (!$assignments): ?>
        <p class="muted">No assignments available. Enroll in a course and check again.</p>
      <?php else: ?>
        <table class="table table-striped table-bordered">
          <tr>
            <th>Course</th>
            <th>Title</th>
            <th>Due</th>
            <th>Action</th>
          </tr>
          <?php foreach ($assignments as $a): ?>
            <tr>
              <td><?= e($a['course_title']) ?></td>
              <td><?= e($a['title']) ?></td>
              <td><?= $a['due_date'] ? e((string)$a['due_date']) : '<span class="muted">—</span>' ?></td>
              <td>
                <a class="btn" href="submit.php?assignment_id=<?= (int)$a['id'] ?>">Submit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
